<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$orderSummary = $conn->query(
    'SELECT status, COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS revenue
     FROM orders
     GROUP BY status
     ORDER BY status'
)->fetch_all(MYSQLI_ASSOC);

$lowStock = $conn->query(
    'SELECT p.pharmacy_name, p.area, p.city, m.name AS medicine_name,
            s.quantity, s.reorder_level, s.expiry_date
     FROM stock s
     INNER JOIN pharmacies p ON p.id = s.pharmacy_id
     INNER JOIN medicines m ON m.id = s.medicine_id
     WHERE s.quantity <= s.reorder_level
     ORDER BY s.quantity ASC, p.city, p.pharmacy_name
     LIMIT 50'
)->fetch_all(MYSQLI_ASSOC);

$topMedicines = $conn->query(
    'SELECT m.name, m.generic_name, SUM(o.quantity) AS units_ordered, SUM(o.total_amount) AS revenue
     FROM orders o
     INNER JOIN medicines m ON m.id = o.medicine_id
     GROUP BY m.id
     ORDER BY units_ordered DESC
     LIMIT 10'
)->fetch_all(MYSQLI_ASSOC);

$pharmacyValue = $conn->query(
    'SELECT p.pharmacy_name, p.city,
            COUNT(s.id) AS stock_rows,
            SUM(s.quantity) AS units,
            SUM(s.quantity * s.price) AS inventory_value
     FROM pharmacies p
     LEFT JOIN stock s ON s.pharmacy_id = p.id
     GROUP BY p.id
     ORDER BY inventory_value DESC'
)->fetch_all(MYSQLI_ASSOC);

$expiredStock = $conn->query(
    'SELECT p.pharmacy_name, m.name AS medicine_name, s.batch_no, s.quantity, s.expiry_date
     FROM stock s
     INNER JOIN pharmacies p ON p.id = s.pharmacy_id
     INNER JOIN medicines m ON m.id = s.medicine_id
     WHERE s.expiry_date < CURDATE()
     ORDER BY s.expiry_date ASC
     LIMIT 50'
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Reports';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Reports</h1>
        <p class="muted">Operational summaries for inventory, orders, and stock risk.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('admin/dashboard.php')); ?>">Dashboard</a>
</section>

<section class="grid two">
    <article class="card">
        <h2>Orders by Status</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderSummary as $row): ?>
                        <tr>
                            <td><?= order_status_badge($row['status']); ?></td>
                            <td><?= (int) $row['total_orders']; ?></td>
                            <td>Rs. <?= number_format((float) $row['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <h2>Top Ordered Medicines</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Units</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topMedicines as $row): ?>
                        <tr>
                            <td><?= e($row['name']); ?><br><span class="muted"><?= e($row['generic_name']); ?></span></td>
                            <td><?= (int) $row['units_ordered']; ?></td>
                            <td>Rs. <?= number_format((float) $row['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card">
    <h2>Low Stock Inventory</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pharmacy</th>
                    <th>Medicine</th>
                    <th>Qty</th>
                    <th>Reorder</th>
                    <th>Expiry</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStock as $row): ?>
                    <tr>
                        <td><?= e($row['pharmacy_name']); ?><br><span class="muted"><?= e($row['area']); ?>, <?= e($row['city']); ?></span></td>
                        <td><?= e($row['medicine_name']); ?></td>
                        <td><?= (int) $row['quantity']; ?></td>
                        <td><?= (int) $row['reorder_level']; ?></td>
                        <td><?= e($row['expiry_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="grid two">
    <article class="card">
        <h2>Inventory Value by Pharmacy</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pharmacy</th>
                        <th>Rows</th>
                        <th>Units</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pharmacyValue as $row): ?>
                        <tr>
                            <td><?= e($row['pharmacy_name']); ?><br><span class="muted"><?= e($row['city']); ?></span></td>
                            <td><?= (int) $row['stock_rows']; ?></td>
                            <td><?= (int) $row['units']; ?></td>
                            <td>Rs. <?= number_format((float) $row['inventory_value'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <article class="card">
        <h2>Expired Stock</h2>
        <?php if (!$expiredStock): ?>
            <p>No expired stock rows in the current database.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>Medicine</th>
                            <th>Batch</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiredStock as $row): ?>
                            <tr>
                                <td><?= e($row['pharmacy_name']); ?></td>
                                <td><?= e($row['medicine_name']); ?></td>
                                <td><?= e($row['batch_no']); ?></td>
                                <td><?= (int) $row['quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
