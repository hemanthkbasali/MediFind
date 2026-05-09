<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? APP_NAME;
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title); ?> | MediFind</title>
    <link rel="stylesheet" href="<?= e(app_url('css/style.css')); ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= e(app_url('index.php')); ?>">MediFind</a>
    <nav class="site-nav">
        <a href="<?= e(app_url('index.php')); ?>">Home</a>
        <a href="<?= e(app_url('user/search.php')); ?>">Search</a>
        <?php if ($role === 'user'): ?>
            <a href="<?= e(app_url('user/orders.php')); ?>">My Orders</a>
            <a href="<?= e(app_url('user/logout.php')); ?>">Logout</a>
        <?php elseif ($role === 'pharmacy'): ?>
            <a href="<?= e(app_url('pharmacy/dashboard.php')); ?>">Pharmacy Dashboard</a>
            <a href="<?= e(app_url('pharmacy/logout.php')); ?>">Logout</a>
        <?php elseif ($role === 'admin'): ?>
            <a href="<?= e(app_url('admin/dashboard.php')); ?>">Admin Dashboard</a>
            <a href="<?= e(app_url('admin/logout.php')); ?>">Logout</a>
        <?php else: ?>
            <a href="<?= e(app_url('user/login.php')); ?>">User Login</a>
            <a href="<?= e(app_url('pharmacy/login.php')); ?>">Pharmacy Login</a>
            <a href="<?= e(app_url('admin/login.php')); ?>">Admin</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page">
    <?php foreach (get_flash_messages() as $flash): ?>
        <div class="alert <?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
    <?php endforeach; ?>
