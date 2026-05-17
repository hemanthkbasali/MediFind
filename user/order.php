<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

require_login('user');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('user/search.php');
}

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
         WHERE s.id = ?
         FOR UPDATE'
    );

    $stmt->bind_param('i', $stockId);
    $stmt->execute();

    $stock = $stmt->get_result()->fetch_assoc();

    if (!$stock) {
        throw new Exception('Medicine stock not found.');
    }

    if ((int)$stock['available_quantity'] < $quantity) {
        throw new Exception('Not enough stock available.');
    }

    $unitPrice = (float)$stock['price'];
    $totalAmount = $unitPrice * $quantity;

    $status = 'pending';

    $stmt = $conn->prepare(
        'INSERT INTO orders
        (user_id, pharmacy_id, medicine_id, quantity, unit_price, total_amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'iiiidds',
        $userId,
        $stock['pharmacy_id'],
        $stock['medicine_id'],
        $quantity,
        $unitPrice,
        $totalAmount,
        $status
    );

    $stmt->execute();

    $stmt = $conn->prepare(
        'UPDATE stock
         SET quantity = quantity - ?
         WHERE id = ?'
    );

    $stmt->bind_param('ii', $quantity, $stockId);
    $stmt->execute();

    $conn->commit();

    echo "
    <html>
    <head>
        <title>Order Success</title>

        <style>
            body{
                font-family: Arial;
                background:#f4f6f9;
                display:flex;
                justify-content:center;
                align-items:center;
                height:100vh;
            }

            .box{
                background:white;
                padding:40px;
                border-radius:12px;
                box-shadow:0 5px 20px rgba(0,0,0,0.1);
                text-align:center;
            }

            a{
                display:inline-block;
                margin-top:20px;
                padding:12px 20px;
                background:#2563eb;
                color:white;
                text-decoration:none;
                border-radius:8px;
            }
        </style>
    </head>

    <body>

        <div class='box'>
            <h1>Order Reserved Successfully</h1>
            <p>Please visit the pharmacy for pickup.</p>

            <a href='../index.php'>
                Back To Home
            </a>
        </div>

    </body>
    </html>
    ";

} catch (Exception $e) {

    $conn->rollback();

    echo "
    <h2>Error:</h2>
    <p>" . $e->getMessage() . "</p>
    ";
}
?>