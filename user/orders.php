<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('user');

$userId = current_user_id('user');
$stmt = $conn->prepare(
    'SELECT
        o.id,
        o.quantity,
        o.unit_price,
        o.total_amount,
        o.status,
        o.created_at,
        m.name AS medicine_name,
        p.pharmacy_name,
        p.area,
        p.city
     FROM orders o
     INNER JOIN medicines m ON m.id = o.medicine_id
     INNER JOIN pharmacies p ON p.id = o.pharmacy_id
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>My Orders</h1>
        <p class="muted">Reservation history for <?= e(current_user_name('user')); ?>.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('user/search.php')); ?>">Search Stock</a>
</section>

<section class="card">
    <?php if (!$orders): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine</th>
                        <th>Pharmacy</th>
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
                            <td><?= e($order['medicine_name']); ?></td>
                            <td><?= e($order['pharmacy_name']); ?><br><span class="muted"><?= e($order['area']); ?>, <?= e($order['city']); ?></span></td>
                            <td><?= (int) $order['quantity']; ?></td>
                            <td>Rs. <?= number_format((float) $order['total_amount'], 2); ?></td>
                            <td><?= order_status_badge($order['status']); ?></td>
                            <td><?= e($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
