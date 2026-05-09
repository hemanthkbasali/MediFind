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
        $stmt = $conn->prepare('DELETE FROM pharmacies WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Pharmacy deleted.' : 'Could not delete pharmacy with related records.');
        redirect('admin/manage_pharmacies.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $ownerName = input('owner_name');
    $pharmacyName = input('pharmacy_name');
    $email = input('email');
    $phone = input('phone');
    $licenseNo = input('license_no');
    $address = input('address');
    $city = input('city');
    $area = input('area');
    $status = input('status', 'active');
    $password = (string) ($_POST['password'] ?? '');

    if ($id > 0) {
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'UPDATE pharmacies
                 SET owner_name = ?, pharmacy_name = ?, email = ?, phone = ?, license_no = ?, address = ?, city = ?, area = ?, status = ?, password = ?
                 WHERE id = ?'
            );
            $stmt->bind_param('ssssssssssi', $ownerName, $pharmacyName, $email, $phone, $licenseNo, $address, $city, $area, $status, $passwordHash, $id);
        } else {
            $stmt = $conn->prepare(
                'UPDATE pharmacies
                 SET owner_name = ?, pharmacy_name = ?, email = ?, phone = ?, license_no = ?, address = ?, city = ?, area = ?, status = ?
                 WHERE id = ?'
            );
            $stmt->bind_param('sssssssssi', $ownerName, $pharmacyName, $email, $phone, $licenseNo, $address, $city, $area, $status, $id);
        }

        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Pharmacy updated.' : 'Could not update pharmacy.');
    } else {
        $passwordHash = password_hash($password !== '' ? $password : 'password', PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'INSERT INTO pharmacies (owner_name, pharmacy_name, email, phone, license_no, address, city, area, status, password)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssssssss', $ownerName, $pharmacyName, $email, $phone, $licenseNo, $address, $city, $area, $status, $passwordHash);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Pharmacy added.' : 'Could not add pharmacy. Check email/license uniqueness.');
    }

    redirect('admin/manage_pharmacies.php');
}

if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM pharmacies WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

$pharmacies = $conn->query('SELECT * FROM pharmacies ORDER BY city, area, pharmacy_name')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Pharmacies';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Manage Pharmacies</h1>
        <p class="muted">Add, edit, activate, or suspend pharmacy accounts.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('admin/dashboard.php')); ?>">Dashboard</a>
</section>

<section class="card">
    <h2><?= $editing ? 'Edit Pharmacy' : 'Add Pharmacy'; ?></h2>
    <form method="post" class="form-card">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">
        <div class="grid three">
            <div>
                <label for="owner_name">Owner</label>
                <input type="text" id="owner_name" name="owner_name" value="<?= e($editing['owner_name'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="pharmacy_name">Pharmacy</label>
                <input type="text" id="pharmacy_name" name="pharmacy_name" value="<?= e($editing['pharmacy_name'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($editing['email'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="grid three">
            <div>
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= e($editing['phone'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="license_no">License No.</label>
                <input type="text" id="license_no" name="license_no" value="<?= e($editing['license_no'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach (['active', 'inactive', 'suspended'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= selected($editing['status'] ?? 'active', $status); ?>><?= e(ucfirst($status)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid three">
            <div>
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?= e($editing['city'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="area">Area</label>
                <input type="text" id="area" name="area" value="<?= e($editing['area'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="password">Password <?= $editing ? '(leave blank to keep)' : ''; ?></label>
                <input type="password" id="password" name="password" <?= $editing ? '' : 'required'; ?>>
            </div>
        </div>
        <label for="address">Address</label>
        <input type="text" id="address" name="address" value="<?= e($editing['address'] ?? ''); ?>" required>

        <button type="submit">Save Pharmacy</button>
        <?php if ($editing): ?>
            <a class="button secondary" href="<?= e(app_url('admin/manage_pharmacies.php')); ?>">Cancel Edit</a>
        <?php endif; ?>
    </form>
</section>

<section class="card">
    <h2>Pharmacy List</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pharmacies as $pharmacy): ?>
                    <tr>
                        <td><?= e($pharmacy['pharmacy_name']); ?><br><span class="muted"><?= e($pharmacy['license_no']); ?></span></td>
                        <td><?= e($pharmacy['owner_name']); ?></td>
                        <td><?= e($pharmacy['area']); ?>, <?= e($pharmacy['city']); ?></td>
                        <td><?= e($pharmacy['email']); ?><br><span class="muted"><?= e($pharmacy['phone']); ?></span></td>
                        <td><span class="badge primary"><?= e(ucfirst($pharmacy['status'])); ?></span></td>
                        <td>
                            <a class="button small" href="<?= e(app_url('admin/manage_pharmacies.php?edit=' . (int) $pharmacy['id'])); ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this pharmacy? Existing stock and orders may block deletion.">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $pharmacy['id']; ?>">
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
