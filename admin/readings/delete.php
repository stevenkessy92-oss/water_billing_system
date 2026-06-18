<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = getDB()->prepare('DELETE FROM meter_readings WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Usomaji umefutwa.');
}
redirect(getBaseUrl() . '/admin/readings/index.php');
