<?php
$pageTitle = 'Ripoti';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <a href="monthly.php" class="text-decoration-none">
            <div class="card stat-card primary h-100 border-0">
                <div class="card-body text-center p-4">
                    <div class="stat-icon mx-auto mb-3"><i class="bi bi-calendar-month"></i></div>
                    <h5>Ripoti ya Mwezi</h5>
                    <p class="text-muted small mb-0">Muhtasari wa matumizi, bili na malipo kwa mwezi</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="paid.php" class="text-decoration-none">
            <div class="card stat-card success h-100 border-0">
                <div class="card-body text-center p-4">
                    <div class="stat-icon mx-auto mb-3"><i class="bi bi-check-circle"></i></div>
                    <h5>Waliolipa</h5>
                    <p class="text-muted small mb-0">Orodha ya wateja waliolipa bili zao</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="debtors.php" class="text-decoration-none">
            <div class="card stat-card danger h-100 border-0">
                <div class="card-body text-center p-4">
                    <div class="stat-icon mx-auto mb-3"><i class="bi bi-exclamation-circle"></i></div>
                    <h5>Wadaiwa</h5>
                    <p class="text-muted small mb-0">Orodha ya wateja wenye deni</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
