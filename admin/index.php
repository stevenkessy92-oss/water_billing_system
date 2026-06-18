<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

$totalCustomers = $db->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
$totalDebt = $db->query(
    "SELECT COALESCE(SUM(bill_amount - amount_paid), 0) FROM meter_readings WHERE bill_amount > amount_paid"
)->fetchColumn();
$monthPaid = $db->query(
    "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())"
)->fetchColumn();
$unpaidBills = $db->query("SELECT COUNT(*) FROM meter_readings WHERE status IN ('unpaid', 'partial')")->fetchColumn();

$recentPayments = $db->query(
    "SELECT p.*, c.full_name, c.house_number
     FROM payments p
     JOIN customers c ON c.id = p.customer_id
     ORDER BY p.created_at DESC LIMIT 5"
)->fetchAll();

$recentReadings = $db->query(
    "SELECT r.*, c.full_name, c.meter_number
     FROM meter_readings r
     JOIN customers c ON c.id = r.customer_id
     ORDER BY r.created_at DESC LIMIT 5"
)->fetchAll();
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card primary h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">Wateja Hai</div>
                    <div class="fs-4 fw-bold"><?= (int) $totalCustomers ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card danger h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="text-muted small">Jumla ya Deni</div>
                    <div class="fs-5 fw-bold"><?= formatMoney((float) $totalDebt) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card success h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-muted small">Malipo Mwezi Huu</div>
                    <div class="fs-5 fw-bold"><?= formatMoney((float) $monthPaid) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card warning h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="text-muted small">Bili Zisizolipwa</div>
                    <div class="fs-4 fw-bold"><?= (int) $unpaidBills ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Malipo ya Hivi Karibuni</h6>
                <a href="payments/index.php" class="btn btn-sm btn-outline-primary">Angalia Zote</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mteja</th>
                            <th>Kiasi</th>
                            <th>Tarehe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Hakuna malipo</td></tr>
                        <?php else: foreach ($recentPayments as $p): ?>
                        <tr>
                            <td><?= sanitize($p['full_name']) ?></td>
                            <td class="text-success fw-semibold"><?= formatMoney((float) $p['amount']) ?></td>
                            <td><?= formatDate($p['payment_date']) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Usomaji wa Hivi Karibuni</h6>
                <a href="readings/index.php" class="btn btn-sm btn-outline-primary">Angalia Zote</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mteja</th>
                            <th>Kipindi</th>
                            <th>Bili</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentReadings)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Hakuna usomaji</td></tr>
                        <?php else: foreach ($recentReadings as $r): ?>
                        <tr>
                            <td><?= sanitize($r['full_name']) ?></td>
                            <td><?= periodLabel((int) $r['reading_month'], (int) $r['reading_year']) ?></td>
                            <td><?= formatMoney((float) $r['bill_amount']) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--div class="row mt-4">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="customers/add.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Sajili Mteja</a>
            <a href="readings/add.php" class="btn btn-outline-primary"><i class="bi bi-speedometer"></i> Ingiza Usomaji</a>
            <a href="payments/add.php" class="btn btn-outline-success"><i class="bi bi-cash"></i> Rekodi Malipo</a>
            <a href="reports/index.php" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-bar-graph"></i> Ripoti</a>
        </div>
    </div>
</div-->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
