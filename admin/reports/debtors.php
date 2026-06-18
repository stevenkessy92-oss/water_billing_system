<?php
$pageTitle = 'Wadaiwa';
require_once __DIR__ . '/../../includes/header.php';

$stmt = getDB()->query(
    "SELECT c.id, c.full_name, c.phone, c.house_number, c.meter_number,
            COALESCE(SUM(r.bill_amount - r.amount_paid), 0) AS total_debt,
            COUNT(r.id) AS unpaid_bills
     FROM customers c
     JOIN meter_readings r ON r.customer_id = c.id
     WHERE (r.bill_amount - r.amount_paid) > 0
     GROUP BY c.id
     HAVING total_debt > 0
     ORDER BY total_debt DESC"
);
$debtors = $stmt->fetchAll();
$grandTotal = array_sum(array_column($debtors, 'total_debt'));
?>

<div class="d-flex flex-wrap gap-2 mb-4 no-print">
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Chapisha</button>
    <a href="index.php" class="btn btn-outline-secondary">Rudi</a>
</div>

<div class="alert alert-danger mb-4">
    <strong>Jumla ya Deni:</strong> <?= formatMoney((float)$grandTotal) ?> |
    <strong>Wadaiwa:</strong> <?= count($debtors) ?>
</div>

<div class="table-card">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0"><i class="bi bi-exclamation-circle"></i> Orodha ya Wadaiwa</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mteja</th>
                    <th>Nyumba</th>
                    <th>Simu</th>
                    <th>Bili Zisizolipwa</th>
                    <th>Deni</th>
                    <th class="no-print">Vitendo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($debtors)): ?>
                <tr><td colspan="7" class="text-center text-success py-4">Hakuna wadaiwa - Hongera!</td></tr>
                <?php else: foreach ($debtors as $i => $d): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($d['full_name']) ?></td>
                    <td><?= sanitize($d['house_number']) ?></td>
                    <td><?= sanitize($d['phone']) ?></td>
                    <td><?= (int)$d['unpaid_bills'] ?></td>
                    <td class="text-danger fw-bold"><?= formatMoney((float)$d['total_debt']) ?></td>
                    <td class="no-print">
                        <a href="../customers/view.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-info">Angalia</a>
                        <a href="../payments/add.php?customer_id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-success">Lipa</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
