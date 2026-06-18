<?php
$pageTitle = 'Waliolipa';
require_once __DIR__ . '/../../includes/header.php';

$month = (int) ($_GET['month'] ?? 0);
$year = (int) ($_GET['year'] ?? 0);

$sql = "SELECT r.*, c.full_name, c.house_number, c.phone, c.meter_number
        FROM meter_readings r
        JOIN customers c ON c.id = r.customer_id
        WHERE r.status = 'paid'";
$params = [];

if ($month > 0 && $year > 0) {
    $sql .= " AND r.reading_month = ? AND r.reading_year = ?";
    $params = [$month, $year];
}
$sql .= " ORDER BY r.reading_year DESC, r.reading_month DESC, c.full_name";

$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap gap-2 mb-4 no-print">
    <form class="d-flex gap-2" method="GET">
        <select name="month" class="form-select" style="width:auto">
            <option value="0">Mwezi Wote</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-select" style="width:auto">
            <option value="0">Mwaka Wote</option>
            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary">Chuja</button>
    </form>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i></button>
    <a href="index.php" class="btn btn-outline-secondary">Rudi</a>
</div>

<div class="table-card">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="bi bi-check-circle"></i> Waliolipa (<?= count($records) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mteja</th>
                    <th>Nyumba</th>
                    <th>Kipindi</th>
                    <th>Bili</th>
                    <th>Imelipwa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Hakuna rekodi</td></tr>
                <?php else: foreach ($records as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($r['full_name']) ?></td>
                    <td><?= sanitize($r['house_number']) ?></td>
                    <td><?= periodLabel((int)$r['reading_month'], (int)$r['reading_year']) ?></td>
                    <td><?= formatMoney((float)$r['bill_amount']) ?></td>
                    <td class="text-success"><?= formatMoney((float)$r['amount_paid']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
