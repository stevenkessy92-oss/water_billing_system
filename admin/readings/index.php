<?php
$pageTitle = 'Usomaji wa Mita';
require_once __DIR__ . '/../../includes/header.php';

$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

$stmt = getDB()->prepare(
    "SELECT r.*, c.full_name, c.house_number, c.meter_number
     FROM meter_readings r
     JOIN customers c ON c.id = r.customer_id
     WHERE r.reading_month = ? AND r.reading_year = ?
     ORDER BY c.full_name ASC"
);
$stmt->execute([$month, $year]);
$readings = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <form class="d-flex flex-wrap gap-2 align-items-center" method="GET">
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
        <button type="submit" class="btn btn-outline-secondary">Chuja</button>
    </form>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Ingiza Usomaji</a>
</div>

<div class="table-card">
    <div class="card-header">
        <h6 class="mb-0">Usomaji - <?= periodLabel($month, $year) ?></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mteja</th>
                    <th>Mita</th>
                    <th>Iliyopita</th>
                    <th>Ya Sasa</th>
                    <th>Matumizi</th>
                    <th>Bili</th>
                    <th>Imelipwa</th>
                    <th>Hali</th>
                    <th>Vitendo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($readings)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Hakuna usomaji kwa kipindi hiki</td></tr>
                <?php else: foreach ($readings as $r): ?>
                <tr>
                    <td><?= sanitize($r['full_name']) ?> <small class="text-muted">(<?= sanitize($r['house_number']) ?>)</small></td>
                    <td><code><?= sanitize($r['meter_number']) ?></code></td>
                    <td><?= number_format((float)$r['previous_reading'], 2) ?></td>
                    <td><?= number_format((float)$r['current_reading'], 2) ?></td>
                    <td><?= number_format((float)$r['consumption'], 2) ?></td>
                    <td><?= formatMoney((float)$r['bill_amount']) ?></td>
                    <td><?= formatMoney((float)$r['amount_paid']) ?></td>
                    <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                    <td>
                        <a href="invoice.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ankara"><i class="bi bi-receipt"></i></a>
                        <a href="edit.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="delete.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger"
                           data-confirm="Futa usomaji huu?"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
