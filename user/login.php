<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in('user')) {
    redirect('user/search.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = input('email');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        login_session('user', (int) $user['id'], $user['name']);
        set_flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect('user/search.php');
    }

    set_flash('danger', 'Invalid email or password.');
}

$page_title = 'User Login';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap">
    <h1>User Login</h1>
    <form method="post" class="card form-card">
        <?= csrf_field(); ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
        <p class="muted">New here? <a href="<?= e(app_url('user/register.php')); ?>">Create an account</a>.</p>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
