<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'User deleted.' : 'Could not delete user with related orders.');
        redirect('admin/manage_users.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = input('name');
    $email = input('email');
    $phone = input('phone');
    $address = input('address');
    $password = (string) ($_POST['password'] ?? '');

    if ($id > 0) {
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $name, $email, $phone, $address, $passwordHash, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
            $stmt->bind_param('ssssi', $name, $email, $phone, $address, $id);
        }

        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'User updated.' : 'Could not update user.');
    } else {
        $passwordHash = password_hash($password !== '' ? $password : 'password', PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $email, $phone, $address, $passwordHash);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'User added.' : 'Could not add user. Email may already exist.');
    }

    redirect('admin/manage_users.php');
}

if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

$users = $conn->query(
    'SELECT u.*,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS order_total
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.name'
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Manage Users</h1>
        <p class="muted">View, add, and update customer accounts.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('admin/dashboard.php')); ?>">Dashboard</a>
</section>

<section class="card">
    <h2><?= $editing ? 'Edit User' : 'Add User'; ?></h2>
    <form method="post" class="form-card">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">

        <div class="grid three">
            <div>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= e($editing['name'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($editing['email'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= e($editing['phone'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="grid two">
            <div>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= e($editing['address'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="password">Password <?= $editing ? '(leave blank to keep)' : ''; ?></label>
                <input type="password" id="password" name="password" <?= $editing ? '' : 'required'; ?>>
            </div>
        </div>

        <button type="submit">Save User</button>
        <?php if ($editing): ?>
            <a class="button secondary" href="<?= e(app_url('admin/manage_users.php')); ?>">Cancel Edit</a>
        <?php endif; ?>
    </form>
</section>

<section class="card">
    <h2>User List</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['name']); ?></td>
                        <td><?= e($user['email']); ?><br><span class="muted"><?= e($user['phone']); ?></span></td>
                        <td><?= e($user['address']); ?></td>
                        <td><?= (int) $user['order_count']; ?></td>
                        <td>Rs. <?= number_format((float) $user['order_total'], 2); ?></td>
                        <td>
                            <a class="button small" href="<?= e(app_url('admin/manage_users.php?edit=' . (int) $user['id'])); ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this user? Related orders may block deletion.">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $user['id']; ?>">
                                <button type="submit" class="danger-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
