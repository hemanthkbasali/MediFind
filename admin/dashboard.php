<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$recentOrders = $conn->query(
    'SELECT o.id, o.quantity, o.total_amount, o.status, o.created_at,
            u.name AS user_name,
            p.pharmacy_name,
            m.name AS medicine_name
     FROM orders o
     INNER JOIN users u ON u.id = o.user_id
     INNER JOIN pharmacies p ON p.id = o.pharmacy_id
     INNER JOIN medicines m ON m.id = o.medicine_id
     ORDER BY o.created_at DESC
     LIMIT 10'
)->fetch_all(MYSQLI_ASSOC);

$alerts = $conn->query(
    'SELECT la.current_quantity, la.alert_message, la.created_at,
            p.pharmacy_name,
            m.name AS medicine_name
     FROM low_stock_alerts la
     INNER JOIN pharmacies p ON p.id = la.pharmacy_id
     INNER JOIN medicines m ON m.id = la.medicine_id
     WHERE la.status = \'open\'
     ORDER BY la.created_at DESC
     LIMIT 10'
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Admin Dashboard</h1>
        <p class="muted">System overview for <?= e(current_user_name('admin')); ?>.</p>
    </div>
</section>

<section class="stats-grid">
    <a class="stat-card" href="<?= e(app_url('admin/manage_users.php')); ?>">
        <span>Users</span>
        <strong><?= count_table($conn, 'users'); ?></strong>
    </a>
    <a class="stat-card" href="<?= e(app_url('admin/manage_pharmacies.php')); ?>">
        <span>Pharmacies</span>
        <strong><?= count_table($conn, 'pharmacies'); ?></strong>
    </a>
    <a class="stat-card" href="<?= e(app_url('admin/manage_medicines.php')); ?>">
        <span>Medicines</span>
        <strong><?= count_table($conn, 'medicines'); ?></strong>
    </a>
    <a class="stat-card" href="<?= e(app_url('admin/orders.php')); ?>">
        <span>Orders</span>
        <strong><?= count_table($conn, 'orders'); ?></strong>
    </a>
</section>

<section class="admin-links">
    <a class="button secondary" href="<?= e(app_url('admin/manage_pharmacies.php')); ?>">Manage Pharmacies</a>
    <a class="button secondary" href="<?= e(app_url('admin/manage_medicines.php')); ?>">Manage Medicines</a>
    <a class="button secondary" href="<?= e(app_url('admin/manage_users.php')); ?>">Manage Users</a>
    <a class="button secondary" href="<?= e(app_url('admin/orders.php')); ?>">Orders</a>
    <a class="button secondary" href="<?= e(app_url('admin/reports.php')); ?>">Reports</a>
</section>

<section class="grid two">
    <article class="card">
        <h2>Recent Orders</h2>
        <?php if (!$recentOrders): ?>
            <p>No orders yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicine</th>
                            <th>User</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= (int) $order['id']; ?></td>
                                <td><?= e($order['medicine_name']); ?></td>
                                <td><?= e($order['user_name']); ?></td>
                                <td><?= order_status_badge($order['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
    <article class="card">
        <h2>Open Low Stock Alerts</h2>
        <?php if (!$alerts): ?>
            <p>No open low-stock alerts.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>Medicine</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <tr>
                                <td><?= e($alert['pharmacy_name']); ?></td>
                                <td><?= e($alert['medicine_name']); ?></td>
                                <td><?= (int) $alert['current_quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
