<?php
$pageTitle = 'Risiti ya Malipo';
require_once __DIR__ . '/../../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = getDB()->prepare(
    'SELECT p.*, c.full_name, c.phone, c.house_number, c.address, c.meter_number,
            r.reading_month, r.reading_year
     FROM payments p
     JOIN customers c ON c.id = p.customer_id
     LEFT JOIN meter_readings r ON r.id = p.reading_id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    flash('danger', 'Risiti haijapatikana.');
    redirect(getBaseUrl() . '/admin/payments/index.php');
}

$methodLabels = [
    'cash' => 'Fedha Taslimu',
    'mobile_money' => 'Simu (M-Pesa)',
    'bank' => 'Benki',
    'other' => 'Nyingine',
];

$companyName = getSetting('company_name', 'Maji Majumbani');
$companyAddress = getSetting('company_address', '');
$companyPhone = getSetting('company_phone', '');
$balance = getCustomerBalance((int)$payment['customer_id']);
?>

<div class="no-print mb-3 d-flex gap-2">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Chapisha</button>
    <a href="index.php" class="btn btn-outline-secondary">Rudi</a>
</div>

<div class="receipt-box">
    <div class="text-center mb-4">
        <h3><i class="bi bi-droplet-fill text-primary"></i> <?= sanitize($companyName) ?></h3>
        <p class="text-muted mb-0"><?= sanitize($companyAddress) ?></p>
        <p class="text-muted"><?= sanitize($companyPhone) ?></p>
        <hr>
        <h5>RISITI YA MALIPO</h5>
        <p class="mb-0">Nambari: RCP-<?= str_pad((string)$id, 6, '0', STR_PAD_LEFT) ?></p>
    </div>

    <table class="table table-borderless table-sm">
        <tr><td width="40%">Mteja:</td><td><strong><?= sanitize($payment['full_name']) ?></strong></td></tr>
        <tr><td>Nyumba:</td><td><?= sanitize($payment['house_number']) ?></td></tr>
        <tr><td>Namba Mita:</td><td><code><?= sanitize($payment['meter_number']) ?></code></td></tr>
        <?php if ($payment['reading_month']): ?>
        <tr><td>Kipindi:</td><td><?= periodLabel((int)$payment['reading_month'], (int)$payment['reading_year']) ?></td></tr>
        <?php endif; ?>
        <tr><td>Tarehe:</td><td><?= formatDate($payment['payment_date']) ?></td></tr>
        <tr><td>Njia:</td><td><?= $methodLabels[$payment['payment_method']] ?? $payment['payment_method'] ?></td></tr>
        <?php if ($payment['reference_number']): ?>
        <tr><td>Rejea:</td><td><?= sanitize($payment['reference_number']) ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="text-center my-4 p-3 bg-light rounded">
        <div class="text-muted">Kiasi Kilicholipwa</div>
        <div class="display-6 text-success fw-bold"><?= formatMoney((float)$payment['amount']) ?></div>
    </div>

    <?php if ($payment['notes']): ?>
    <p><strong>Maelezo:</strong> <?= sanitize($payment['notes']) ?></p>
    <?php endif; ?>

    <p class="text-center">
        Salio la Deni Linalobaki: <strong class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>"><?= formatMoney($balance) ?></strong>
    </p>

    <p class="text-muted small text-center mt-4">
        Asante kwa malipo yako!<br>
        Tarehe ya uchapishaji: <?= date('d/m/Y H:i') ?>
    </p>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
