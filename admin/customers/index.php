<?php
$pageTitle = 'Wateja';
require_once __DIR__ . '/../../includes/header.php';

$search = trim($_GET['search'] ?? '');
$sql = "SELECT c.*, 
        COALESCE((SELECT SUM(bill_amount - amount_paid) FROM meter_readings WHERE customer_id = c.id AND bill_amount > amount_paid), 0) AS balance
        FROM customers c WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (c.full_name LIKE ? OR c.phone LIKE ? OR c.house_number LIKE ? OR c.meter_number LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}
$sql .= " ORDER BY c.full_name ASC";

$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <form class="d-flex search-box" method="GET">
        <input type="text" name="search" class="form-control" placeholder="Tafuta mteja..."
               value="<?= sanitize($search) ?>">
        <button class="btn btn-outline-secondary ms-2" type="submit"><i class="bi bi-search"></i></button>
    </form>
    <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Sajili Mteja</a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Jina</th>
                    <th>Nyumba</th>
                    <th>Namba Mita</th>
                    <th>Simu</th>
                    <th>Salio Deni</th>
                    <th>Hali</th>
                    <th>Vitendo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Hakuna wateja</td></tr>
                <?php else: foreach ($customers as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= sanitize($c['full_name']) ?></td>
                    <td><?= sanitize($c['house_number']) ?></td>
                    <td><code><?= sanitize($c['meter_number']) ?></code></td>
                    <td><?= sanitize($c['phone']) ?></td>
                    <td class="<?= (float)$c['balance'] > 0 ? 'text-danger fw-semibold' : 'text-success' ?>">
                        <?= formatMoney((float) $c['balance']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $c['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $c['status'] === 'active' ? 'Hai' : 'Haijaamilishwa' ?>
                        </span>
                    </td>
                    <td>
                        <a href="view.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-info" title="Angalia">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="edit.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Hariri">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="delete.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-danger"
                           data-confirm="Una uhakika unataka kufuta mteja huyu?" title="Futa">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
