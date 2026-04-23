<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

$order_id = intval($_GET['id'] ?? 0);
$user_id = intval($_SESSION['user_id']);

$order_stmt = $conn->prepare("SELECT id, total, status, tracking_status, created_at FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

$items = [];
if ($order) {
    $items_stmt = $conn->prepare("SELECT food_name, price, quantity FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="page-title">Order Invoice</div>
    <div class="track-box">
        <?php if ($order): ?>
            <div class="invoice-card">
                <div class="invoice-header">
                    <div>
                        <h2>Invoice #<?= htmlspecialchars($order['id']) ?></h2>
                        <p class="invoice-meta">Placed on <?= htmlspecialchars(date('F j, Y', strtotime($order['created_at']))) ?> · Status: <strong><?= htmlspecialchars($order['tracking_status']) ?></strong></p>
                    </div>
                    <div class="invoice-total">৳<?= htmlspecialchars(number_format($order['total'], 2)) ?></div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['food_name']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>৳<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                                    <td>৳<?= htmlspecialchars(number_format($subtotal, 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="order-summary">
                    <div class="invoice-meta">Thank you for ordering with Crave Cart.</div>
                    <div class="invoice-total">Grand Total: ৳<?= htmlspecialchars(number_format($order['total'], 2)) ?></div>
                </div>

                <div class="dashboard-actions">
                    <button onclick="window.print()" class="button">Print Invoice</button>
                    <a class="button button-secondary" href="dashboard.php">Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <div class="message-box">Invoice not found or you're not authorized to view it.</div>
            <div class="dashboard-actions">
                <a class="button button-secondary" href="dashboard.php">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>