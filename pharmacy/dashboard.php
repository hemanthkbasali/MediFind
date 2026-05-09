<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('pharmacy');

$pharmacyId = current_user_id('pharmacy');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order_status') {
    verify_csrf();

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = input('status');
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ? AND pharmacy_id = ?');
        $stmt->bind_param('sii', $status, $orderId, $pharmacyId);
        $stmt->execute();
        set_flash('success', 'Order status updated.');
    }

    redirect('pharmacy/dashboard.php');
}

$summaryStmt = $conn->prepare(
    'SELECT
        COUNT(*) AS total_rows,
        SUM(quantity) AS total_units,
        SUM(CASE WHEN quantity <= reorder_level THEN 1 ELSE 0 END) AS low_stock_rows
     FROM stock
     WHERE pharmacy_id = ?'
);
$summaryStmt->bind_param('i', $pharmacyId);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc();

$stockStmt = $conn->prepare(
    'SELECT s.id, s.quantity, s.price, s.batch_no, s.expiry_date, s.reorder_level,
            m.name, m.generic_name, m.strength, m.form
     FROM stock s
     INNER JOIN medicines m ON m.id = s.medicine_id
     WHERE s.pharmacy_id = ?
     ORDER BY m.name, s.expiry_date'
);
$stockStmt->bind_param('i', $pharmacyId);
$stockStmt->execute();
$stockRows = $stockStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$ordersStmt = $conn->prepare(
    'SELECT o.id, o.quantity, o.total_amount, o.status, o.created_at,
            u.name AS user_name, u.phone AS user_phone,
            m.name AS medicine_name
     FROM orders o
     INNER JOIN users u ON u.id = o.user_id
     INNER JOIN medicines m ON m.id = o.medicine_id
     WHERE o.pharmacy_id = ?
     ORDER BY o.created_at DESC
     LIMIT 20'
);
$ordersStmt->bind_param('i', $pharmacyId);
$ordersStmt->execute();
$orders = $ordersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$alertsStmt = $conn->prepare(
    'SELECT la.id, la.current_quantity, la.alert_message, la.status, la.created_at,
            m.name AS medicine_name
     FROM low_stock_alerts la
     INNER JOIN medicines m ON m.id = la.medicine_id
     WHERE la.pharmacy_id = ? AND la.status = \'open\'
     ORDER BY la.created_at DESC
     LIMIT 10'
);
$alertsStmt->bind_param('i', $pharmacyId);
$alertsStmt->execute();
$alerts = $alertsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Pharmacy Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1><?= e(current_user_name('pharmacy')); ?></h1>
        <p class="muted">Inventory and reservation control panel.</p>
    </div>
    <a class="button" href="<?= e(app_url('pharmacy/update_stock.php')); ?>">Add Stock</a>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span>Total Stock Rows</span>
        <strong><?= (int) ($summary['total_rows'] ?? 0); ?></strong>
    </article>
    <article class="stat-card">
        <span>Total Units</span>
        <strong><?= (int) ($summary['total_units'] ?? 0); ?></strong>
    </article>
    <article class="stat-card">
        <span>Low Stock Rows</span>
        <strong><?= (int) ($summary['low_stock_rows'] ?? 0); ?></strong>
    </article>
</section>

<?php if ($alerts): ?>
    <section class="card">
        <h2>Open Low Stock Alerts</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Qty</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert): ?>
                        <tr>
                            <td><?= e($alert['medicine_name']); ?></td>
                            <td><?= (int) $alert['current_quantity']; ?></td>
                            <td><?= e($alert['alert_message']); ?></td>
                            <td><?= e($alert['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Inventory</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Batch</th>
                    <th>Qty</th>
                    <th>Reorder</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockRows as $row): ?>
                    <tr>
                        <td>
                            <strong><?= e($row['name']); ?></strong><br>
                            <span class="muted"><?= e($row['generic_name']); ?> <?= e($row['strength']); ?> <?= e($row['form']); ?></span>
                        </td>
                        <td><?= e($row['batch_no']); ?></td>
                        <td><?= (int) $row['quantity']; ?></td>
                        <td><?= (int) $row['reorder_level']; ?></td>
                        <td>Rs. <?= number_format((float) $row['price'], 2); ?></td>
                        <td><?= e($row['expiry_date']); ?></td>
                        <td><a class="button small" href="<?= e(app_url('pharmacy/update_stock.php?id=' . (int) $row['id'])); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <h2>Recent Orders</h2>
    <?php if (!$orders): ?>
        <p>No pharmacy orders yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
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
                            <td><?= e($order['medicine_name']); ?></td>
                            <td><?= (int) $order['quantity']; ?></td>
                            <td>Rs. <?= number_format((float) $order['total_amount'], 2); ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="update_order_status">
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
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
