<?php
declare(strict_types=1);

/*
 * MediFind database connection.
 *
 * Settings can be overridden with environment variables (MEDIFIND_DB_HOST,
 * MEDIFIND_DB_USER, MEDIFIND_DB_PASS, MEDIFIND_DB_NAME, MEDIFIND_DB_PORT).
 * The defaults below match the local XAMPP setup this project was built on.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_OFF);

function medifind_env(string $name, string $default): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

define('DB_HOST', medifind_env('MEDIFIND_DB_HOST', 'localhost'));
define('DB_USER', medifind_env('MEDIFIND_DB_USER', 'root'));
define('DB_PASS', medifind_env('MEDIFIND_DB_PASS', ''));
define('DB_NAME', medifind_env('MEDIFIND_DB_NAME', 'medifind_db'));
define('DB_PORT', (int) medifind_env('MEDIFIND_DB_PORT', '3307')); // local MySQL runs on 3307

// The @ keeps PHP from printing the host/port warning to visitors; the error is handled below.
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    error_log('MediFind database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    die('Service temporarily unavailable. Please try again later.');
}

$conn->set_charset('utf8mb4');
?>
