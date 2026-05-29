<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(getBaseUrl() . '/admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Ombi halitambuliki. Jaribu tena.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $error = 'Jaza jina la mtumiaji na nenosiri.';
        } elseif (loginAdmin($username, $password)) {
            redirect(getBaseUrl() . '/admin/index.php');
        } else {
            $error = 'Jina la mtumiaji au nenosiri si sahihi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingia - Maji Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= getBaseUrl() ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-droplet-fill login-logo"></i>
                <h2 class="mt-2">Maji Billing</h2>
                <p class="text-muted">Mfumo wa Usimamizi wa Bili za Maji</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label">Jina la Mtumiaji</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" required autofocus
                               value="<?= sanitize($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Nenosiri</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right"></i> Ingia
                </button>
            </form>
            
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
