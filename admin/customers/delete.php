<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = getDB()->prepare('DELETE FROM customers WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Mteja amefutwa.');
}
redirect(getBaseUrl() . '/admin/customers/index.php');
