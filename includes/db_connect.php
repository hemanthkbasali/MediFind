<?php
declare(strict_types=1);

/*
 * MediFind Database Connection (XAMPP FIXED)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_OFF);

// Database config
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medifind_db');
define('DB_PORT', 3307); // IMPORTANT: your MySQL is running on 3307

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die('❌ Database connection failed: ' . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');
?>