<?php
/**
 * Authentication helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $base = getBaseUrl();
        redirect($base . '/login.php');
    }
}

function getBaseUrl(): string
{
    static $base = null;
    if ($base === null) {
        $root = realpath(dirname(__DIR__));
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        if ($root && $docRoot && strpos($root, $docRoot) === 0) {
            $base = str_replace('\\', '/', substr($root, strlen($docRoot)));
        } else {
            $base = '';
        }
        $base = rtrim($base, '/');
    }
    return $base;
}

function getAdmin(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'        => $_SESSION['admin_id'],
        'username'  => $_SESSION['admin_username'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? '',
    ];
}

function loginAdmin(string $username, string $password): bool
{
    $stmt = getDB()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function logoutAdmin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
