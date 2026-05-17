<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

require_login('user');

$userId = current_user_id('user');

$stmt = $conn->prepare("
    SELECT
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

    INNER JOIN medicines m
        ON m.id = o.medicine_id

    INNER JOIN pharmacies p
        ON p.id = o.pharmacy_id

    WHERE o.user_id = ?

    ORDER BY o.created_at DESC
");

$stmt->bind_param("i", $userId);

$stmt->execute();

$result = $stmt->get_result();

$orders = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders | MediFind</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body{
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .page{
            padding:40px;
        }

        .card-box{
            background:white;
            border-radius:12px;
            padding:25px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
        }

        .title{
            font-size:42px;
            font-weight:700;
            margin-bottom:10px;
        }

        .muted{
            color:#777;
        }

        table{
            margin-top:20px;
        }

        .badge-pending{
            background:#ffc107;
            color:black;
            padding:6px 12px;
            border-radius:8px;
        }

        .badge-completed{
            background:#198754;
            color:white;
            padding:6px 12px;
            border-radius:8px;
        }

        .badge-cancelled{
            background:#dc3545;
            color:white;
            padding:6px 12px;
            border-radius:8px;
        }

    </style>

</head>

<body>

<div class="container page">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="title">My Orders</div>

            <p class="muted">
                Reservation history
            </p>

        </div>

        <a href="search.php" class="btn btn-outline-primary">

            Search Stock

        </a>

    </div>

    <div class="card-box">

        <?php if (!$orders): ?>

            <p>No orders placed yet.</p>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

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

                            <td>
                                <?= $order['id']; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($order['medicine_name']); ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($order['pharmacy_name']); ?>
                                </strong>

                                <br>

                                <span class="text-muted">

                                    <?= htmlspecialchars($order['area']); ?>,
                                    <?= htmlspecialchars($order['city']); ?>

                                </span>

                            </td>

                            <td>
                                <?= $order['quantity']; ?>
                            </td>

                            <td>
                                Rs. <?= number_format($order['total_amount'],2); ?>
                            </td>

                            <td>

                                <?php if($order['status']=='pending'): ?>

                                    <span class="badge-pending">
                                        Pending
                                    </span>

                                <?php elseif($order['status']=='completed'): ?>

                                    <span class="badge-completed">
                                        Completed
                                    </span>

                                <?php else: ?>

                                    <span class="badge-cancelled">
                                        Cancelled
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= $order['created_at']; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>