<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('pharmacy');

$pharmacyId = current_user_id('pharmacy');
$stockId = (int) ($_GET['id'] ?? $_POST['stock_id'] ?? 0);
$stock = null;

if ($stockId > 0) {
    $stmt = $conn->prepare('SELECT * FROM stock WHERE id = ? AND pharmacy_id = ? LIMIT 1');
    $stmt->bind_param('ii', $stockId, $pharmacyId);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc();

    if (!$stock) {
        set_flash('danger', 'Stock row not found for this pharmacy.');
        redirect('pharmacy/dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $medicineId = (int) ($_POST['medicine_id'] ?? 0);
    $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
    $price = max(0, (float) ($_POST['price'] ?? 0));
    $batchNo = input('batch_no');
    $expiryDate = input('expiry_date');
    $reorderLevel = max(0, (int) ($_POST['reorder_level'] ?? 10));

    if ($medicineId <= 0 || $batchNo === '' || $expiryDate === '') {
        set_flash('danger', 'Medicine, batch number, and expiry date are required.');
        redirect($stockId > 0 ? 'pharmacy/update_stock.php?id=' . $stockId : 'pharmacy/update_stock.php');
    } elseif ($stockId > 0) {
        $stmt = $conn->prepare(
            'UPDATE stock
             SET medicine_id = ?, quantity = ?, price = ?, batch_no = ?, expiry_date = ?, reorder_level = ?, updated_at = NOW()
             WHERE id = ? AND pharmacy_id = ?'
        );
        $stmt->bind_param('iidssiii', $medicineId, $quantity, $price, $batchNo, $expiryDate, $reorderLevel, $stockId, $pharmacyId);
        $ok = $stmt->execute();
        if (!$ok) {
            set_flash('danger', 'Could not update stock row.');
            redirect($stockId > 0 ? 'pharmacy/update_stock.php?id=' . $stockId : 'pharmacy/update_stock.php');
        }
        set_flash('success', 'Stock row updated.');
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO stock (pharmacy_id, medicine_id, quantity, price, batch_no, expiry_date, reorder_level)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiidssi', $pharmacyId, $medicineId, $quantity, $price, $batchNo, $expiryDate, $reorderLevel);
        $ok = $stmt->execute();
        if (!$ok) {
            set_flash('danger', 'Could not add stock row.');
            redirect('pharmacy/update_stock.php');
        }
        $stockId = $stmt->insert_id;
        set_flash('success', 'Stock row added.');
    }

    if ($quantity <= $reorderLevel) {
        $message = 'Stock is at or below reorder level.';
        $alertStatus = 'open';
        $stmt = $conn->prepare(
            'INSERT INTO low_stock_alerts (stock_id, pharmacy_id, medicine_id, current_quantity, alert_message, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiiiss', $stockId, $pharmacyId, $medicineId, $quantity, $message, $alertStatus);
        $stmt->execute();
    } else {
        $resolved = 'resolved';
        $stmt = $conn->prepare('UPDATE low_stock_alerts SET status = ? WHERE stock_id = ? AND status = \'open\'');
        $stmt->bind_param('si', $resolved, $stockId);
        $stmt->execute();
    }

    redirect('pharmacy/dashboard.php');
}

$medicines = $conn->query('SELECT id, name, generic_name, strength, form FROM medicines ORDER BY name')->fetch_all(MYSQLI_ASSOC);

$page_title = $stock ? 'Update Stock' : 'Add Stock';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1><?= $stock ? 'Update Stock' : 'Add Stock'; ?></h1>
        <p class="muted">Maintain medicine quantity, price, batch, and expiry information.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('pharmacy/dashboard.php')); ?>">Back</a>
</section>

<section class="form-wrap wide">
    <form method="post" class="card form-card">
        <?= csrf_field(); ?>
        <input type="hidden" name="stock_id" value="<?= (int) $stockId; ?>">

        <label for="medicine_id">Medicine</label>
        <select id="medicine_id" name="medicine_id" required>
            <option value="">Select medicine</option>
            <?php foreach ($medicines as $medicine): ?>
                <option value="<?= (int) $medicine['id']; ?>" <?= selected((string) ($stock['medicine_id'] ?? ''), (string) $medicine['id']); ?>>
                    <?= e($medicine['name'] . ' - ' . $medicine['generic_name'] . ' ' . $medicine['strength'] . ' ' . $medicine['form']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="grid two">
            <div>
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0" value="<?= e((string) ($stock['quantity'] ?? 0)); ?>" required>
            </div>
            <div>
                <label for="reorder_level">Reorder level</label>
                <input type="number" id="reorder_level" name="reorder_level" min="0" value="<?= e((string) ($stock['reorder_level'] ?? 10)); ?>" required>
            </div>
        </div>

        <div class="grid two">
            <div>
                <label for="price">Price</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?= e((string) ($stock['price'] ?? '0.00')); ?>" required>
            </div>
            <div>
                <label for="batch_no">Batch number</label>
                <input type="text" id="batch_no" name="batch_no" value="<?= e((string) ($stock['batch_no'] ?? '')); ?>" required>
            </div>
        </div>

        <label for="expiry_date">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" value="<?= e((string) ($stock['expiry_date'] ?? '')); ?>" required>

        <button type="submit">Save Stock</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
