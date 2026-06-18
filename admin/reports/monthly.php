<?php
$pageTitle = 'Ripoti ya Mwezi';
require_once __DIR__ . '/../../includes/header.php';

$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

$db = getDB();

$summary = $db->prepare(
    "SELECT COUNT(*) AS total_bills,
            COALESCE(SUM(consumption), 0) AS total_consumption,
            COALESCE(SUM(bill_amount), 0) AS total_billed,
            COALESCE(SUM(amount_paid), 0) AS total_collected
     FROM meter_readings WHERE reading_month = ? AND reading_year = ?"
);
$summary->execute([$month, $year]);
$summary = $summary->fetch();

$paymentsTotal = $db->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM payments
     WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?"
);
$paymentsTotal->execute([$month, $year]);
$paymentsInMonth = (float) $paymentsTotal->fetchColumn();

$readings = $db->prepare(
    "SELECT r.*, c.full_name, c.house_number
     FROM meter_readings r JOIN customers c ON c.id = r.customer_id
     WHERE r.reading_month = ? AND r.reading_year = ?
     ORDER BY c.full_name"
);
$readings->execute([$month, $year]);
$readings = $readings->fetchAll();

$outstanding = (float)$summary['total_billed'] - (float)$summary['total_collected'];
?>

<div class="no-print d-flex flex-wrap gap-2 mb-4">
    <form class="d-flex gap-2" method="GET">
        <select name="month" class="form-select" style="width:auto">
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-select" style="width:auto">
            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary">Angalia</button>
    </form>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Chapisha</button>
    <a href="index.php" class="btn btn-outline-secondary">Rudi</a>
</div>

<h5 class="mb-3">Ripoti - <?= periodLabel($month, $year) ?></h5>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="text-muted small">Bili Zote</div>
            <div class="fs-4 fw-bold"><?= (int)$summary['total_bills'] ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="text-muted small">Matumizi (jumla)</div>
            <div class="fs-4 fw-bold"><?= number_format((float)$summary['total_consumption'], 2) ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="text-muted small">Jumla Bili</div>
            <div class="fs-5 fw-bold"><?= formatMoney((float)$summary['total_billed']) ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="text-muted small">Imekusanywa</div>
            <div class="fs-5 fw-bold text-success"><?= formatMoney((float)$summary['total_collected']) ?></div>
        </div></div>
    </div>
</div>

<div class="alert alert-warning">
    <strong>Deni la Mwezi:</strong> <?= formatMoney($outstanding) ?> |
    <strong>Malipo yaliyorekodiwa mwezi huu:</strong> <?= formatMoney($paymentsInMonth) ?>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mteja</th>
                    <th>Nyumba</th>
                    <th>Matumizi</th>
                    <th>Bili</th>
                    <th>Imelipwa</th>
                    <th>Deni</th>
                    <th>Hali</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($readings)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Hakuna data</td></tr>
                <?php else: foreach ($readings as $r):
                    $debt = (float)$r['bill_amount'] - (float)$r['amount_paid'];
                ?>
                <tr>
                    <td><?= sanitize($r['full_name']) ?></td>
                    <td><?= sanitize($r['house_number']) ?></td>
                    <td><?= number_format((float)$r['consumption'], 2) ?></td>
                    <td><?= formatMoney((float)$r['bill_amount']) ?></td>
                    <td><?= formatMoney((float)$r['amount_paid']) ?></td>
                    <td class="<?= $debt > 0 ? 'text-danger' : '' ?>"><?= formatMoney($debt) ?></td>
                    <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
