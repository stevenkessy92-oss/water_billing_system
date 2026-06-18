<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    redirect(getBaseUrl() . '/admin/index.php');
}
redirect(getBaseUrl() . '/login.php');
