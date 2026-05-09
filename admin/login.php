<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in('admin')) {
    redirect('admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = input('email');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT id, name, password FROM admins WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        login_session('admin', (int) $admin['id'], $admin['name']);
        set_flash('success', 'Welcome, ' . $admin['name'] . '.');
        redirect('admin/dashboard.php');
    }

    set_flash('danger', 'Invalid admin credentials.');
}

$page_title = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap">
    <h1>Admin Login</h1>
    <form method="post" class="card form-card">
        <?= csrf_field(); ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
