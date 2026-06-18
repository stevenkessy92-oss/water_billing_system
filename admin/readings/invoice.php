<?php
$pageTitle = 'Ankara ya Bili';
require_once __DIR__ . '/../../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = getDB()->prepare(
    'SELECT r.*, c.full_name, c.phone, c.house_number, c.address, c.meter_number
     FROM meter_readings r
     JOIN customers c ON c.id = r.customer_id
     WHERE r.id = ?'
);
$stmt->execute([$id]);
$bill = $stmt->fetch();

if (!$bill) {
    flash('danger', 'Ankara haijapatikana.');
    redirect(getBaseUrl() . '/admin/readings/index.php');
}

$balance = (float) $bill['bill_amount'] - (float) $bill['amount_paid'];
$companyName = getSetting('company_name', 'Maji Majumbani');
$companyAddress = getSetting('company_address', '');
$companyPhone = getSetting('company_phone', '');
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
        <h5>ANKARA YA BILI YA MAJI</h5>
        <p class="mb-0">Nambari: INV-<?= str_pad((string)$id, 6, '0', STR_PAD_LEFT) ?></p>
        <p>Kipindi: <strong><?= periodLabel((int)$bill['reading_month'], (int)$bill['reading_year']) ?></strong></p>
    </div>

    <table class="table table-borderless table-sm">
        <tr><td width="40%">Mteja:</td><td><strong><?= sanitize($bill['full_name']) ?></strong></td></tr>
        <tr><td>Nyumba:</td><td><?= sanitize($bill['house_number']) ?></td></tr>
        <tr><td>Anwani:</td><td><?= sanitize($bill['address']) ?></td></tr>
        <tr><td>Namba Mita:</td><td><code><?= sanitize($bill['meter_number']) ?></code></td></tr>
        <tr><td>Simu:</td><td><?= sanitize($bill['phone']) ?></td></tr>
    </table>

    <hr>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Maelezo</th>
                <th class="text-end">Kiasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Usomaji Uliopita</td>
                <td class="text-end"><?= number_format((float)$bill['previous_reading'], 2) ?></td>
            </tr>
            <tr>
                <td>Usomaji wa Sasa</td>
                <td class="text-end"><?= number_format((float)$bill['current_reading'], 2) ?></td>
            </tr>
            <tr>
                <td>Matumizi (vitengo)</td>
                <td class="text-end"><strong><?= number_format((float)$bill['consumption'], 2) ?></strong></td>
            </tr>
            <tr>
                <td>Bei kwa Kitengo</td>
                <td class="text-end"><?= formatMoney((float)$bill['price_per_unit']) ?></td>
            </tr>
            <tr class="table-primary">
                <td><strong>Jumla ya Bili</strong></td>
                <td class="text-end"><strong><?= formatMoney((float)$bill['bill_amount']) ?></strong></td>
            </tr>
            <tr>
                <td>Imelipwa</td>
                <td class="text-end text-success"><?= formatMoney((float)$bill['amount_paid']) ?></td>
            </tr>
            <tr class="<?= $balance > 0 ? 'table-danger' : 'table-success' ?>">
                <td><strong>Salio la Deni</strong></td>
                <td class="text-end"><strong><?= formatMoney($balance) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <p class="text-muted small text-center mt-4">
        Fomula: Matumizi = Usomaji Sasa - Usomaji Uliopita | Bili = Matumizi × Bei kwa Kitengo<br>
        Tarehe ya kutolewa: <?= date('d/m/Y H:i') ?>
    </p>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
