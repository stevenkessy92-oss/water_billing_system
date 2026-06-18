<?php
$pageTitle = 'Maelezo ya Mteja';
require_once __DIR__ . '/../../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = getDB()->prepare('SELECT * FROM customers WHERE id = ?');
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    flash('danger', 'Mteja hajapatikana.');
    redirect(getBaseUrl() . '/admin/customers/index.php');
}

$balance = getCustomerBalance($id);

$readings = getDB()->prepare(
    'SELECT * FROM meter_readings WHERE customer_id = ? ORDER BY reading_year DESC, reading_month DESC'
);
$readings->execute([$id]);
$readings = $readings->fetchAll();

$payments = getDB()->prepare(
    'SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC LIMIT 10'
);
$payments->execute([$id]);
$payments = $payments->fetchAll();
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= sanitize($customer['full_name']) ?></h5>
                <hr>
                <p class="mb-1"><i class="bi bi-house"></i> Nyumba: <strong><?= sanitize($customer['house_number']) ?></strong></p>
                <p class="mb-1"><i class="bi bi-geo-alt"></i> <?= sanitize($customer['address']) ?></p>
                <p class="mb-1"><i class="bi bi-speedometer"></i> Mita: <code><?= sanitize($customer['meter_number']) ?></code></p>
                <p class="mb-1"><i class="bi bi-telephone"></i> <?= sanitize($customer['phone']) ?></p>
                <?php if ($customer['email']): ?>
                <p class="mb-1"><i class="bi bi-envelope"></i> <?= sanitize($customer['email']) ?></p>
                <?php endif; ?>
                <hr>
                <p class="mb-0 fs-5">
                    Salio la Deni:
                    <span class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?> fw-bold">
                        <?= formatMoney($balance) ?>
                    </span>
                </p>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-sm btn-primary">Hariri</a>
                    <a href="../readings/add.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">Usomaji</a>
                    <a href="../payments/add.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-success">Malipo</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="table-card mb-4">
            <div class="card-header"><h6 class="mb-0">Historia ya Bili</h6></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kipindi</th>
                            <th>Matumizi</th>
                            <th>Bili</th>
                            <th>Imelipwa</th>
                            <th>Deni</th>
                            <th>Hali</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($readings)): ?>
                        <tr><td colspan="7" class="text-muted text-center py-3">Hakuna bili</td></tr>
                        <?php else: foreach ($readings as $r):
                            $debt = (float)$r['bill_amount'] - (float)$r['amount_paid'];
                        ?>
                        <tr>
                            <td><?= periodLabel((int)$r['reading_month'], (int)$r['reading_year']) ?></td>
                            <td><?= number_format((float)$r['consumption'], 2) ?> u</td>
                            <td><?= formatMoney((float)$r['bill_amount']) ?></td>
                            <td><?= formatMoney((float)$r['amount_paid']) ?></td>
                            <td class="<?= $debt > 0 ? 'text-danger' : '' ?>"><?= formatMoney($debt) ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                            <td><a href="../readings/invoice.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i></a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="table-card">
            <div class="card-header"><h6 class="mb-0">Malipo ya Hivi Karibuni</h6></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Tarehe</th><th>Kiasi</th><th>Njia</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr><td colspan="4" class="text-muted text-center py-3">Hakuna malipo</td></tr>
                        <?php else: foreach ($payments as $p): ?>
                        <tr>
                            <td><?= formatDate($p['payment_date']) ?></td>
                            <td class="text-success"><?= formatMoney((float)$p['amount']) ?></td>
                            <td><?= sanitize($p['payment_method']) ?></td>
                            <td><a href="../payments/receipt.php?id=<?= (int)$p['id'] ?>"><i class="bi bi-receipt"></i></a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
