<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('user/search.php');
}

verify_csrf();

$stockId = (int) ($_POST['stock_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$userId = current_user_id('user');

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        'SELECT
            s.id,
            s.pharmacy_id,
            s.medicine_id,
            s.quantity AS available_quantity,
            s.price,
            s.reorder_level,
            m.name AS medicine_name
         FROM stock s
         INNER JOIN medicines m ON m.id = s.medicine_id
         WHERE s.id = ? AND s.expiry_date >= CURDATE()
         FOR UPDATE'
    );
    $stmt->bind_param('i', $stockId);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc();

    if (!$stock) {
        throw new RuntimeException('Selected stock item is no longer available.');
    }

    if ((int) $stock['available_quantity'] < $quantity) {
        throw new RuntimeException('Only ' . (int) $stock['available_quantity'] . ' unit(s) are available.');
    }

    $unitPrice = (float) $stock['price'];
    $totalAmount = $unitPrice * $quantity;
    $status = 'pending';
    $pharmacyId = (int) $stock['pharmacy_id'];
    $medicineId = (int) $stock['medicine_id'];
    $stmt = $conn->prepare(
        'INSERT INTO orders (user_id, pharmacy_id, medicine_id, quantity, unit_price, total_amount, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'iiiidds',
        $userId,
        $pharmacyId,
        $medicineId,
        $quantity,
        $unitPrice,
        $totalAmount,
        $status
    );
    if (!$stmt->execute()) {
        throw new RuntimeException('Could not create the order. Please try again.');
    }

    $stmt = $conn->prepare('UPDATE stock SET quantity = quantity - ?, updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('ii', $quantity, $stockId);
    if (!$stmt->execute()) {
        throw new RuntimeException('Could not update stock after order.');
    }

    $newQuantity = (int) $stock['available_quantity'] - $quantity;
    if ($newQuantity <= (int) $stock['reorder_level']) {
        $message = $stock['medicine_name'] . ' stock dropped to ' . $newQuantity . ' unit(s).';
        $alertStatus = 'open';
        $stmt = $conn->prepare(
            'INSERT INTO low_stock_alerts (stock_id, pharmacy_id, medicine_id, current_quantity, alert_message, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iiiiss',
            $stockId,
            $pharmacyId,
            $medicineId,
            $newQuantity,
            $message,
            $alertStatus
        );
        $stmt->execute();
    }

    $conn->commit();
    set_flash('success', 'Order reserved successfully. Please visit the pharmacy for pickup and payment.');
} catch (Throwable $exception) {
    $conn->rollback();
    set_flash('danger', $exception->getMessage());
}

redirect('user/orders.php');
