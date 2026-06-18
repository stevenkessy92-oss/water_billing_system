<?php
$pageTitle = 'Malipo';
require_once __DIR__ . '/../../includes/header.php';

$stmt = getDB()->query(
    "SELECT p.*, c.full_name, c.house_number,
            r.reading_month, r.reading_year
     FROM payments p
     JOIN customers c ON c.id = p.customer_id
     LEFT JOIN meter_readings r ON r.id = p.reading_id
     ORDER BY p.payment_date DESC, p.id DESC
     LIMIT 100"
);
$payments = $stmt->fetchAll();

$methodLabels = [
    'cash' => 'Fedha Taslimu',
    'mobile_money' => 'Simu (M-Pesa/n.k)',
    'bank' => 'Benki',
    'other' => 'Nyingine',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h6 class="mb-0 text-muted">Malipo 100 ya hivi karibuni</h6>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Rekodi Malipo</a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mteja</th>
                    <th>Kipindi</th>
                    <th>Kiasi</th>
                    <th>Tarehe</th>
                    <th>Njia</th>
                    <th>Vitendo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Hakuna malipo</td></tr>
                <?php else: foreach ($payments as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($p['full_name']) ?> <small class="text-muted">(<?= sanitize($p['house_number']) ?>)</small></td>
                    <td><?= $p['reading_month'] ? periodLabel((int)$p['reading_month'], (int)$p['reading_year']) : '-' ?></td>
                    <td class="text-success fw-semibold"><?= formatMoney((float)$p['amount']) ?></td>
                    <td><?= formatDate($p['payment_date']) ?></td>
                    <td><?= $methodLabels[$p['payment_method']] ?? $p['payment_method'] ?></td>
                    <td>
                        <a href="receipt.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
