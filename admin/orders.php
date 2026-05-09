<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = input('status');
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $orderId);
        $stmt->execute();
        set_flash('success', 'Order status updated.');
    }

    redirect('admin/orders.php');
}

$statusFilter = input('status');
$allowedFilters = ['', 'pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($statusFilter, $allowedFilters, true)) {
    $statusFilter = '';
}

$stmt = $conn->prepare(
    'SELECT o.id, o.quantity, o.unit_price, o.total_amount, o.status, o.created_at,
            u.name AS user_name, u.phone AS user_phone,
            p.pharmacy_name, p.area, p.city,
            m.name AS medicine_name
     FROM orders o
     INNER JOIN users u ON u.id = o.user_id
     INNER JOIN pharmacies p ON p.id = o.pharmacy_id
     INNER JOIN medicines m ON m.id = o.medicine_id
     WHERE (? = \'\' OR o.status = ?)
     ORDER BY o.created_at DESC'
);
$stmt->bind_param('ss', $statusFilter, $statusFilter);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Orders';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Manage Orders</h1>
        <p class="muted">Review reservations and update fulfillment status.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('admin/dashboard.php')); ?>">Dashboard</a>
</section>

<section class="card">
    <form method="get" class="inline-form filter-form">
        <label for="status">Filter</label>
        <select id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                <option value="<?= e($status); ?>" <?= selected($statusFilter, $status); ?>><?= e(ucfirst($status)); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Pharmacy</th>
                    <th>Medicine</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= (int) $order['id']; ?></td>
                        <td><?= e($order['user_name']); ?><br><span class="muted"><?= e($order['user_phone']); ?></span></td>
                        <td><?= e($order['pharmacy_name']); ?><br><span class="muted"><?= e($order['area']); ?>, <?= e($order['city']); ?></span></td>
                        <td><?= e($order['medicine_name']); ?></td>
                        <td><?= (int) $order['quantity']; ?></td>
                        <td>Rs. <?= number_format((float) $order['total_amount'], 2); ?></td>
                        <td>
                            <form method="post" class="inline-form">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id']; ?>">
                                <select name="status">
                                    <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                                        <option value="<?= e($status); ?>" <?= selected($order['status'], $status); ?>><?= e(ucfirst($status)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        </td>
                        <td><?= e($order['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
