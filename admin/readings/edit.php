<?php
$pageTitle = 'Hariri Usomaji';
require_once __DIR__ . '/../../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = getDB()->prepare(
    'SELECT r.*, c.full_name FROM meter_readings r JOIN customers c ON c.id = r.customer_id WHERE r.id = ?'
);
$stmt->execute([$id]);
$reading = $stmt->fetch();

if (!$reading) {
    flash('danger', 'Usomaji haujapatikana.');
    redirect(getBaseUrl() . '/admin/readings/index.php');
}

$errors = [];
$pricePerUnit = getPricePerUnit();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ombi halitambuliki.';
    } else {
        $previous = (float) ($_POST['previous_reading'] ?? 0);
        $current = (float) ($_POST['current_reading'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($current < $previous) {
            $errors[] = 'Usomaji wa sasa hauwezi kuwa chini ya uliopita.';
        }

        if (empty($errors)) {
            $calc = calculateBill($previous, $current, $pricePerUnit);
            $stmt = getDB()->prepare(
                'UPDATE meter_readings SET previous_reading=?, current_reading=?, consumption=?,
                 price_per_unit=?, bill_amount=?, notes=? WHERE id=?'
            );
            $stmt->execute([
                $previous, $current, $calc['consumption'],
                $pricePerUnit, $calc['bill_amount'], $notes ?: null, $id,
            ]);
            updateReadingPaymentStatus($id);
            flash('success', 'Usomaji umesasishwa.');
            redirect(getBaseUrl() . '/admin/readings/index.php?month=' . $reading['reading_month'] . '&year=' . $reading['reading_year']);
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="text-muted">Mteja: <strong><?= sanitize($reading['full_name']) ?></strong> |
                    Kipindi: <?= periodLabel((int)$reading['reading_month'], (int)$reading['reading_year']) ?></p>
                <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Usomaji Uliopita</label>
                            <input type="number" step="0.01" name="previous_reading" id="previous_reading"
                                   class="form-control" value="<?= number_format((float)$reading['previous_reading'], 2, '.', '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usomaji wa Sasa</label>
                            <input type="number" step="0.01" name="current_reading" id="current_reading"
                                   class="form-control" value="<?= number_format((float)$reading['current_reading'], 2, '.', '') ?>">
                        </div>
                        <input type="hidden" id="price_per_unit" value="<?= $pricePerUnit ?>">
                        <div class="col-12">
                            <div class="alert alert-info">
                                Matumizi: <span id="consumption_display">0</span> |
                                Bili: TZS <span id="bill_display">0</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Maelezo</label>
                            <textarea name="notes" class="form-control" rows="2"><?= sanitize($reading['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Sasisha</button>
                        <a href="index.php" class="btn btn-outline-secondary">Ghairi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
