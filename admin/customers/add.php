<?php
$pageTitle = 'Sajili Mteja';
require_once __DIR__ . '/../../includes/header.php';

$errors = [];
$data = [
    'full_name' => '', 'phone' => '', 'email' => '',
    'house_number' => '', 'address' => '', 'meter_number' => '', 'status' => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ombi halitambuliki.';
    } else {
        $data = array_merge($data, array_map('trim', $_POST));
        if ($data['full_name'] === '') $errors[] = 'Jina linahitajika.';
        if ($data['phone'] === '') $errors[] = 'Nambari ya simu inahitajika.';
        if ($data['house_number'] === '') $errors[] = 'Nambari ya nyumba inahitajika.';
        if ($data['address'] === '') $errors[] = 'Anwani inahitajika.';
        if ($data['meter_number'] === '') $errors[] = 'Nambari ya mita inahitajika.';

        if (empty($errors)) {
            $stmt = getDB()->prepare(
                'INSERT INTO customers (full_name, phone, email, house_number, address, meter_number, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            try {
                $stmt->execute([
                    $data['full_name'], $data['phone'], $data['email'] ?: null,
                    $data['house_number'], $data['address'], $data['meter_number'], $data['status'],
                ]);
                flash('success', 'Mteja amesajiliwa kikamilifu.');
                redirect(getBaseUrl() . '/admin/customers/index.php');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = 'Nambari ya mita tayari ipo.';
                } else {
                    $errors[] = 'Hitilafu imetokea. Jaribu tena.';
                }
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jina Kamili *</label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= sanitize($data['full_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Simu *</label>
                            <input type="text" name="phone" class="form-control" required
                                   value="<?= sanitize($data['phone']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barua Pepe</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= sanitize($data['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nambari ya Nyumba *</label>
                            <input type="text" name="house_number" class="form-control" required
                                   value="<?= sanitize($data['house_number']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Anwani *</label>
                            <textarea name="address" class="form-control" rows="2" required><?= sanitize($data['address']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nambari ya Mita *</label>
                            <input type="text" name="meter_number" class="form-control" required
                                   value="<?= sanitize($data['meter_number']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hali</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>Hai</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>Haijaamilishwa</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Hifadhi</button>
                        <a href="index.php" class="btn btn-outline-secondary">Ghairi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
