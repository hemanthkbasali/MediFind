<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in('user')) {
    redirect('user/search.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = input('name');
    $email = input('email');
    $phone = input('phone');
    $address = input('address');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($password !== $confirmPassword) {
        set_flash('danger', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters.');
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $email, $phone, $address, $passwordHash);

        if ($stmt->execute()) {
            login_session('user', $stmt->insert_id, $name);
            set_flash('success', 'Account created successfully.');
            redirect('user/search.php');
        }

        set_flash('danger', 'Could not register. The email may already be used.');
    }
}

$page_title = 'User Registration';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap wide">
    <h1>Create User Account</h1>
    <form method="post" class="card form-card">
        <?= csrf_field(); ?>
        <div class="grid two">
            <div>
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
        </div>

        <div class="grid two">
            <div>
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required>
            </div>
        </div>

        <div class="grid two">
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit">Register</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
