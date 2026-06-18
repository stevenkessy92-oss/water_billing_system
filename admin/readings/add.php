<?php
$pageTitle = 'Ingiza Usomaji';
require_once __DIR__ . '/../../includes/header.php';

$customers = getDB()->query("SELECT id, full_name, house_number, meter_number FROM customers WHERE status='active' ORDER BY full_name")->fetchAll();
$pricePerUnit = getPricePerUnit();
$preselectCustomer = (int) ($_GET['customer_id'] ?? 0);

$errors = [];
$data = [
    'customer_id' => $preselectCustomer ?: '',
    'reading_month' => (int) date('n'),
    'reading_year' => (int) date('Y'),
    'previous_reading' => 0,
    'current_reading' => '',
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ombi halitambuliki.';
    } else {
        $data['customer_id'] = (int) ($_POST['customer_id'] ?? 0);
        $data['reading_month'] = (int) ($_POST['reading_month'] ?? 0);
        $data['reading_year'] = (int) ($_POST['reading_year'] ?? 0);
        $data['current_reading'] = (float) ($_POST['current_reading'] ?? 0);
        $data['notes'] = trim($_POST['notes'] ?? '');
        $useManualPrev = isset($_POST['manual_previous']);
        $data['previous_reading'] = $useManualPrev
            ? (float) ($_POST['previous_reading'] ?? 0)
            : getPreviousReading($data['customer_id'], $data['reading_month'], $data['reading_year']);

        if ($data['customer_id'] <= 0) $errors[] = 'Chagua mteja.';
        if ($data['reading_month'] < 1 || $data['reading_month'] > 12) $errors[] = 'Mwezi si sahihi.';
        if ($data['current_reading'] < $data['previous_reading']) {
            $errors[] = 'Usomaji wa sasa hauwezi kuwa chini ya uliopita.';
        }

        if (empty($errors)) {
            $calc = calculateBill($data['previous_reading'], $data['current_reading'], $pricePerUnit);
            $stmt = getDB()->prepare(
                'INSERT INTO meter_readings (customer_id, reading_month, reading_year, previous_reading,
                 current_reading, consumption, price_per_unit, bill_amount, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            try {
                $stmt->execute([
                    $data['customer_id'], $data['reading_month'], $data['reading_year'],
                    $data['previous_reading'], $data['current_reading'],
                    $calc['consumption'], $pricePerUnit, $calc['bill_amount'], $data['notes'] ?: null,
                ]);
                flash('success', 'Usomaji umehifadhiwa. Bili: ' . formatMoney($calc['bill_amount']));
                redirect(getBaseUrl() . '/admin/readings/index.php?month=' . $data['reading_month'] . '&year=' . $data['reading_year']);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = 'Usomaji wa mwezi huu tayari upo kwa mteja huyu.';
                } else {
                    $errors[] = 'Hitilafu imetokea.';
                }
            }
        }
    }
} elseif ($data['customer_id'] > 0) {
    $data['previous_reading'] = getPreviousReading($data['customer_id'], $data['reading_month'], $data['reading_year']);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="POST" id="readingForm">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mteja *</label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">-- Chagua Mteja --</option>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (int)$data['customer_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($c['full_name']) ?> - Nyumba <?= sanitize($c['house_number']) ?> (<?= sanitize($c['meter_number']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mwezi *</label>
                            <select name="reading_month" class="form-select" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $data['reading_month'] === $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mwaka *</label>
                            <input type="number" name="reading_year" class="form-control" required
                                   min="2020" max="2099" value="<?= (int)$data['reading_year'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usomaji Uliopita</label>
                            <input type="number" step="0.01" name="previous_reading" id="previous_reading"
                                   class="form-control" value="<?= number_format((float)$data['previous_reading'], 2, '.', '') ?>" readonly>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="manual_previous" id="manual_previous">
                                <label class="form-check-label small" for="manual_previous">Weka kwa mkono</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usomaji wa Sasa *</label>
                            <input type="number" step="0.01" name="current_reading" id="current_reading"
                                   class="form-control" required value="<?= $data['current_reading'] !== '' ? $data['current_reading'] : '' ?>">
                        </div>
                        <input type="hidden" id="price_per_unit" value="<?= $pricePerUnit ?>">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <strong>Muhtasari:</strong>
                                Matumizi: <span id="consumption_display">0.00</span> vitengo |
                                Bei kwa kitengo: <?= formatMoney($pricePerUnit) ?> |
                                Bili: TZS <span id="bill_display">0</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Maelezo</label>
                            <textarea name="notes" class="form-control" rows="2"><?= sanitize($data['notes']) ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Hifadhi & Hesabu Bili</button>
                        <a href="index.php" class="btn btn-outline-secondary">Ghairi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('manual_previous')?.addEventListener('change', function() {
    const prev = document.getElementById('previous_reading');
    prev.readOnly = !this.checked;
});
document.getElementById('customer_id')?.addEventListener('change', function() {
    if (this.value) window.location.href = '?customer_id=' + this.value;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
