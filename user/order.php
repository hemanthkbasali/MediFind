<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

require_login('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: search.php');
    exit;
}

$stockId = (int)($_POST['stock_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

$userId = current_user_id('user');

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        SELECT
            s.id,
            s.pharmacy_id,
            s.medicine_id,
            s.quantity,
            s.price,
            m.name AS medicine_name
        FROM stock s
        INNER JOIN medicines m ON m.id = s.medicine_id
        WHERE s.id = ?
        FOR UPDATE
    ");

    $stmt->bind_param("i", $stockId);
    $stmt->execute();

    $result = $stmt->get_result();
    $stock = $result->fetch_assoc();

    if (!$stock) {
        throw new Exception("Medicine stock not found");
    }

    if ((int)$stock['quantity'] < $quantity) {
        throw new Exception("Not enough stock available");
    }

    $unitPrice = (float)$stock['price'];
    $totalAmount = $unitPrice * $quantity;

    $status = 'pending';

    $stmt = $conn->prepare("
        INSERT INTO orders
        (
            user_id,
            pharmacy_id,
            medicine_id,
            quantity,
            unit_price,
            total_amount,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iiiidds",
        $userId,
        $stock['pharmacy_id'],
        $stock['medicine_id'],
        $quantity,
        $unitPrice,
        $totalAmount,
        $status
    );

    $stmt->execute();

    $stmt = $conn->prepare("
        UPDATE stock
        SET quantity = quantity - ?
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $quantity, $stockId);
    $stmt->execute();

    $conn->commit();

    header("Location: orders.php");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo "
    <h2>Order Failed</h2>
    <p>" . $e->getMessage() . "</p>
    ";

}
?>