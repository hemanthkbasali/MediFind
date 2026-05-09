<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in('pharmacy')) {
    redirect('pharmacy/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = input('email');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT id, pharmacy_name, password, status FROM pharmacies WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $pharmacy = $stmt->get_result()->fetch_assoc();

    if ($pharmacy && $pharmacy['status'] !== 'active') {
        set_flash('warning', 'This pharmacy account is not active. Contact the administrator.');
    } elseif ($pharmacy && password_verify($password, $pharmacy['password'])) {
        login_session('pharmacy', (int) $pharmacy['id'], $pharmacy['pharmacy_name']);
        set_flash('success', 'Welcome, ' . $pharmacy['pharmacy_name'] . '.');
        redirect('pharmacy/dashboard.php');
    } else {
        set_flash('danger', 'Invalid pharmacy email or password.');
    }
}

$page_title = 'Pharmacy Login';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap">
    <h1>Pharmacy Login</h1>
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
