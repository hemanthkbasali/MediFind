<?php
declare(strict_types=1);

require_once __DIR__ . '/db_connect.php';

if (!defined('APP_NAME')) {
    define('APP_NAME', 'MediFind');
}

if (!defined('APP_BASE')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = preg_replace('#/(admin|pharmacy|user)$#', '', $scriptDir);
    $base = ($base === '/' || $base === '.') ? '' : $base;
    define('APP_BASE', $base);
}

function app_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(APP_BASE, '/') . ($path === '' ? '/' : '/' . $path);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if (!hash_equals((string) $expected, (string) $submitted)) {
        http_response_code(419);
        die('Invalid form token. Please go back and try again.');
    }
}

function input(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $_GET[$key] ?? $default));
}

function selected(string $current, string $expected): string
{
    return $current === $expected ? 'selected' : '';
}

function count_table(mysqli $conn, string $table): int
{
    $allowed = ['admins', 'users', 'pharmacies', 'medicines', 'stock', 'substitute_medicines', 'orders', 'low_stock_alerts'];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM {$table}");
    $row = $result ? $result->fetch_assoc() : ['total' => 0];

    return (int) $row['total'];
}

function order_status_badge(string $status): string
{
    switch ($status) {
        case 'completed':
            $class = 'badge success';
            break;
        case 'cancelled':
            $class = 'badge danger';
            break;
        case 'confirmed':
            $class = 'badge primary';
            break;
        default:
            $class = 'badge warning';
            break;
    }

    return '<span class="' . $class . '">' . e(ucfirst($status)) . '</span>';
}
