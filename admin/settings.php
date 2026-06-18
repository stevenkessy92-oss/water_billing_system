<?php
$pageTitle = 'Mipangilio';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ombi halitambuliki.';
    } else {
        $price = (float) ($_POST['price_per_unit'] ?? 0);
        if ($price <= 0) {
            $errors[] = 'Bei kwa kitengo lazima iwe zaidi ya sifuri.';
        } else {
            setSetting('price_per_unit', (string) $price);
            setSetting('company_name', trim($_POST['company_name'] ?? ''));
            setSetting('company_address', trim($_POST['company_address'] ?? ''));
            setSetting('company_phone', trim($_POST['company_phone'] ?? ''));

            if (!empty($_POST['new_password'])) {
                $newPass = $_POST['new_password'];
                $confirm = $_POST['confirm_password'] ?? '';
                if (strlen($newPass) < 6) {
                    $errors[] = 'Nenosiri lazima liwe angalau herufi 6.';
                } elseif ($newPass !== $confirm) {
                    $errors[] = 'Nenosiri hazilingani.';
                } else {
                    $hash = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmt = getDB()->prepare('UPDATE admins SET password = ? WHERE id = ?');
                    $stmt->execute([$hash, getAdmin()['id']]);
                }
            }

            if (empty($errors)) {
                flash('success', 'Mipangilio imesasishwa.');
                redirect(getBaseUrl() . '/admin/settings.php');
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">Mipangilio ya Mfumo</h6></div>
            <div class="card-body p-4">
                <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Bei kwa Kitengo (TZS) *</label>
                        <input type="number" step="0.01" name="price_per_unit" class="form-control" required
                               value="<?= getSetting('price_per_unit', '500') ?>">
                        <div class="form-text">Fomula: Bili = Matumizi × Bei kwa Kitengo</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jina la Kampuni</label>
                        <input type="text" name="company_name" class="form-control"
                               value="<?= sanitize(getSetting('company_name', 'Maji Majumbani')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Anwani</label>
                        <input type="text" name="company_address" class="form-control"
                               value="<?= sanitize(getSetting('company_address', '')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Simu</label>
                        <input type="text" name="company_phone" class="form-control"
                               value="<?= sanitize(getSetting('company_phone', '')) ?>">
                    </div>
                    <hr>
                    <h6>Badilisha Nenosiri</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nenosiri Jipya</label>
                            <input type="password" name="new_password" class="form-control" minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thibitisha Nenosiri</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Hifadhi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
