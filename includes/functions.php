<?php
/**
 * Helper functions
 */

require_once __DIR__ . '/../config/database.php';

function getSetting(string $key, string $default = ''): string
{
    $stmt = getDB()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function setSetting(string $key, string $value): void
{
    $stmt = getDB()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
}

function getPricePerUnit(): float
{
    return (float) getSetting('price_per_unit', '500');
}

function formatMoney(float $amount): string
{
    return 'TZS ' . number_format($amount, 0, '.', ',');
}

function formatDate(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

function monthName(int $month): string
{
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Machi', 4 => 'Aprili',
        5 => 'Mei', 6 => 'Juni', 7 => 'Julai', 8 => 'Agosti',
        9 => 'Septemba', 10 => 'Oktoba', 11 => 'Novemba', 12 => 'Desemba',
    ];
    return $months[$month] ?? '';
}

function periodLabel(int $month, int $year): string
{
    return monthName($month) . ' ' . $year;
}

function getPreviousReading(int $customerId, int $month, int $year): float
{
    $db = getDB();
    // Try previous month in same year
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }

    $stmt = $db->prepare(
        'SELECT current_reading FROM meter_readings
         WHERE customer_id = ? AND reading_month = ? AND reading_year = ?
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$customerId, $prevMonth, $prevYear]);
    $row = $stmt->fetch();
    if ($row) {
        return (float) $row['current_reading'];
    }

    // Latest reading before this period
    $stmt = $db->prepare(
        'SELECT current_reading FROM meter_readings
         WHERE customer_id = ?
         AND (reading_year < ? OR (reading_year = ? AND reading_month < ?))
         ORDER BY reading_year DESC, reading_month DESC LIMIT 1'
    );
    $stmt->execute([$customerId, $year, $year, $month]);
    $row = $stmt->fetch();
    return $row ? (float) $row['current_reading'] : 0;
}

function calculateBill(float $previous, float $current, float $pricePerUnit): array
{
    $consumption = max(0, $current - $previous);
    $billAmount = $consumption * $pricePerUnit;
    return [
        'consumption' => $consumption,
        'bill_amount' => $billAmount,
    ];
}

function updateReadingPaymentStatus(int $readingId): void
{
    $db = getDB();
    $stmt = $db->prepare('SELECT bill_amount, amount_paid FROM meter_readings WHERE id = ?');
    $stmt->execute([$readingId]);
    $reading = $stmt->fetch();
    if (!$reading) {
        return;
    }

    $bill = (float) $reading['bill_amount'];
    $paid = (float) $reading['amount_paid'];

    if ($paid <= 0) {
        $status = 'unpaid';
    } elseif ($paid >= $bill) {
        $status = 'paid';
    } else {
        $status = 'partial';
    }

    $stmt = $db->prepare('UPDATE meter_readings SET status = ? WHERE id = ?');
    $stmt->execute([$status, $readingId]);
}

function getCustomerBalance(int $customerId): float
{
    $stmt = getDB()->prepare(
        'SELECT COALESCE(SUM(bill_amount - amount_paid), 0) AS balance
         FROM meter_readings
         WHERE customer_id = ? AND (bill_amount - amount_paid) > 0'
    );
    $stmt->execute([$customerId]);
    return (float) $stmt->fetch()['balance'];
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}
