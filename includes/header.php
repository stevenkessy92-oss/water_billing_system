<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$admin = getAdmin();
$base = getBaseUrl();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>Maji Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <i class="bi bi-droplet-fill text-primary"></i>
                <span>Maji Billing</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= $base ?>/admin/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'customers') !== false ? 'active' : '' ?>" href="<?= $base ?>/admin/customers/index.php">
                        <i class="bi bi-people"></i> Wateja
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'readings') !== false ? 'active' : '' ?>" href="<?= $base ?>/admin/readings/index.php">
                        <i class="bi bi-speedometer"></i> Usomaji Mita
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'payments') !== false ? 'active' : '' ?>" href="<?= $base ?>/admin/payments/index.php">
                        <i class="bi bi-cash-coin"></i> Malipo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : '' ?>" href="<?= $base ?>/admin/reports/index.php">
                        <i class="bi bi-file-earmark-bar-graph"></i> Ripoti
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="<?= $base ?>/admin/settings.php">
                        <i class="bi bi-gear"></i> Mipangilio
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <div class="admin-info">
                    <i class="bi bi-person-circle"></i>
                    <span><?= sanitize($admin['full_name']) ?></span>
                </div>
                <a href="<?= $base ?>/logout.php" class="btn btn-outline-light btn-sm w-100 mt-2">
                    <i class="bi bi-box-arrow-right"></i> Toka
                </a>
            </div>
        </nav>

        <div id="content" class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <button type="button" id="sidebarToggle" class="btn btn-link text-dark">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="navbar-brand mb-0 h5"><?= isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard' ?></span>
            </nav>
            <main class="p-4">
                <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= sanitize($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
