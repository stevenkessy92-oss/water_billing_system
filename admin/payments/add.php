<?php
$pageTitle = 'Rekodi Malipo';
require_once __DIR__ . '/../../includes/header.php';

$customers = getDB()->query("SELECT id, full_name, house_number FROM customers WHERE status='active' ORDER BY full_name")->fetchAll();
$preselectCustomer = (int) ($_GET['customer_id'] ?? 0);

$errors = [];
$data = [
    'customer_id' => $preselectCustomer ?: '',
    'reading_id' => '',
    'amount' => '',
    'payment_date' => date('Y-m-d'),
    'payment_method' => 'cash',
    'reference_number' => '',
    'notes' => '',
];

$unpaidBills = [];
if ($data['customer_id'] > 0) {
    $stmt = getDB()->prepare(
        "SELECT id, reading_month, reading_year, bill_amount, amount_paid,
                (bill_amount - amount_paid) AS balance
         FROM meter_readings
         WHERE customer_id = ? AND (bill_amount - amount_paid) > 0
         ORDER BY reading_year, reading_month"
    );
    $stmt->execute([$data['customer_id']]);
    $unpaidBills = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ombi halitambuliki.';
    } else {
        $data['customer_id'] = (int) ($_POST['customer_id'] ?? 0);
        $data['reading_id'] = (int) ($_POST['reading_id'] ?? 0) ?: null;
        $data['amount'] = (float) ($_POST['amount'] ?? 0);
        $data['payment_date'] = $_POST['payment_date'] ?? date('Y-m-d');
        $data['payment_method'] = $_POST['payment_method'] ?? 'cash';
        $data['reference_number'] = trim($_POST['reference_number'] ?? '');
        $data['notes'] = trim($_POST['notes'] ?? '');

        if ($data['customer_id'] <= 0) $errors[] = 'Chagua mteja.';
        if ($data['amount'] <= 0) $errors[] = 'Kiasi lazima kiwe zaidi ya sifuri.';

        if ($data['reading_id']) {
            $stmt = getDB()->prepare(
                'SELECT bill_amount, amount_paid FROM meter_readings WHERE id = ? AND customer_id = ?'
            );
            $stmt->execute([$data['reading_id'], $data['customer_id']]);
            $bill = $stmt->fetch();
            if ($bill) {
                $remaining = (float)$bill['bill_amount'] - (float)$bill['amount_paid'];
                if ($data['amount'] > $remaining + 0.01) {
                    $errors[] = 'Kiasi kinazidi deni la bili (' . formatMoney($remaining) . ').';
                }
            }
        }

        if (empty($errors)) {
            $db = getDB();
            $db->beginTransaction();
            try {
                $admin = getAdmin();
                $stmt = $db->prepare(
                    'INSERT INTO payments (customer_id, reading_id, amount, payment_date, payment_method, reference_number, notes, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $data['customer_id'], $data['reading_id'], $data['amount'],
                    $data['payment_date'], $data['payment_method'],
                    $data['reference_number'] ?: null, $data['notes'] ?: null,
                    $admin['id'] ?? null,
                ]);
                $paymentId = (int) $db->lastInsertId();

                if ($data['reading_id']) {
                    $stmt = $db->prepare(
                        'UPDATE meter_readings SET amount_paid = amount_paid + ? WHERE id = ?'
                    );
                    $stmt->execute([$data['amount'], $data['reading_id']]);
                    updateReadingPaymentStatus($data['reading_id']);
                } else {
                    // Apply to oldest unpaid bills (FIFO)
                    $remaining = $data['amount'];
                    $stmt = $db->prepare(
                        "SELECT id, bill_amount, amount_paid FROM meter_readings
                         WHERE customer_id = ? AND (bill_amount - amount_paid) > 0
                         ORDER BY reading_year, reading_month"
                    );
                    $stmt->execute([$data['customer_id']]);
                    foreach ($stmt->fetchAll() as $bill) {
                        if ($remaining <= 0) break;
                        $owed = (float)$bill['bill_amount'] - (float)$bill['amount_paid'];
                        $apply = min($remaining, $owed);
                        $upd = $db->prepare('UPDATE meter_readings SET amount_paid = amount_paid + ? WHERE id = ?');
                        $upd->execute([$apply, $bill['id']]);
                        updateReadingPaymentStatus((int)$bill['id']);
                        $remaining -= $apply;
                    }
                }

                $db->commit();
                flash('success', 'Malipo yamehifadhiwa.');
                redirect(getBaseUrl() . '/admin/payments/receipt.php?id=' . $paymentId);
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'Hitilafu imetokea wakati wa kuhifadhi malipo.';
            }
        }

        if ($data['customer_id'] > 0) {
            $stmt = getDB()->prepare(
                "SELECT id, reading_month, reading_year, bill_amount, amount_paid,
                        (bill_amount - amount_paid) AS balance
                 FROM meter_readings WHERE customer_id = ? AND (bill_amount - amount_paid) > 0
                 ORDER BY reading_year, reading_month"
            );
            $stmt->execute([$data['customer_id']]);
            $unpaidBills = $stmt->fetchAll();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mteja *</label>
                            <select name="customer_id" class="form-select" required
                                    onchange="window.location='?customer_id='+this.value">
                                <option value="">-- Chagua --</option>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (int)$data['customer_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($c['full_name']) ?> - <?= sanitize($c['house_number']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($data['customer_id']): ?>
                            <p class="small text-muted mt-1">
                                Salio la deni: <strong class="text-danger"><?= formatMoney(getCustomerBalance((int)$data['customer_id'])) ?></strong>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bili (hiari - acha tupu kwa malipo ya jumla)</label>
                            <select name="reading_id" class="form-select">
                                <option value="">-- Malipo ya Jumla (FIFO) --</option>
                                <?php foreach ($unpaidBills as $b): ?>
                                <option value="<?= (int)$b['id'] ?>" <?= (int)($data['reading_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= periodLabel((int)$b['reading_month'], (int)$b['reading_year']) ?>
                                    - Deni: <?= formatMoney((float)$b['balance']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kiasi (TZS) *</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required
                                   value="<?= $data['amount'] !== '' ? $data['amount'] : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tarehe *</label>
                            <input type="date" name="payment_date" class="form-control" required
                                   value="<?= sanitize($data['payment_date']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Njia ya Malipo</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Fedha Taslimu</option>
                                <option value="mobile_money">Simu (M-Pesa)</option>
                                <option value="bank">Benki</option>
                                <option value="other">Nyingine</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nambari ya Rejea</label>
                            <input type="text" name="reference_number" class="form-control"
                                   value="<?= sanitize($data['reference_number']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Maelezo</label>
                            <textarea name="notes" class="form-control" rows="2"><?= sanitize($data['notes']) ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Hifadhi Malipo</button>
                        <a href="index.php" class="btn btn-outline-secondary">Ghairi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
