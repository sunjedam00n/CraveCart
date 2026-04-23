<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/security.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

$user_id = intval($_SESSION['user_id']);

$message = '';
$total = 0;
$cart_items = [];
$order_confirmed = false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payment.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $message = 'Invalid request. Please try again.';
}

$trx = isset($_POST['trx']) ? sanitize_input($_POST['trx']) : '';
$posted_amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;

if ($trx === '') {
    $message = 'Transaction ID is required.';
} else {
    $stmt = $conn->prepare("SELECT cart.quantity, food.id, food.price, food.name, food.admin_id FROM cart JOIN food ON cart.food_id = food.id WHERE cart.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $admin_ids = [];
    while ($r = $res->fetch_assoc()) {
        $total += $r['price'] * $r['quantity'];
        $cart_items[] = $r;
        $admin_ids[] = $r['admin_id'];
    }
    $stmt->close();

    if (empty($cart_items)) {
        $message = 'Your cart is empty. Please add items before confirming payment.';
    } elseif (abs($posted_amount - $total) > 0.01) {
        $message = 'Payment amount mismatch. Please try again.';
    } else {
        $admin_ids = array_unique($admin_ids);
        if (count($admin_ids) > 1) {
            $message = 'Error: Your cart contains items from different restaurants. Please order from one restaurant at a time.';
        } else {
            $admin_id = $admin_ids[0] ?? null;
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, tracking_status, admin_id) VALUES (?, ?, 'Paid', 'Pending', ?)");
            $stmt->bind_param("idi", $user_id, $total, $admin_id);
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                $stmt->close();

                foreach ($cart_items as $item) {
                    $food_id = intval($item['id']);
                    $food_name = $item['name'];
                    $price = floatval($item['price']);
                    $quantity = intval($item['quantity']);

                    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, food_id, food_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                    $item_stmt->bind_param("iisdi", $order_id, $food_id, $food_name, $price, $quantity);
                    $item_stmt->execute();
                    $item_stmt->close();

                    $update_stmt = $conn->prepare("UPDATE food SET quantity = quantity - ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $quantity, $food_id);
                    if (!$update_stmt->execute()) {
                        error_log('Failed to update food quantity: ' . $update_stmt->error);
                    }
                    $update_stmt->close();
                }

                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();

                $message = 'Order Confirmed! Total Amount: ৳' . number_format($total, 2) . '. Thank you for your purchase.';
                $order_confirmed = true;
            } else {
                $message = 'Failed to confirm order. Please try again.';
                error_log('Failed to insert order: ' . $stmt->error);
                $stmt->close();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="page-title">Payment Confirmation</div>
    <div class="track-box">
        <div class="invoice-card">
            <div class="invoice-header">
                <div>
                    <h2>Payment Result</h2>
                    <p class="invoice-meta">Your order is now confirmed and being prepared.</p>
                </div>
                <div class="invoice-total">৳<?= htmlspecialchars(number_format($total, 2)) ?></div>
            </div>
            <div class="message-box"><?= htmlspecialchars($message) ?></div>
            <div class="dashboard-actions">
                <a class="button" href="dashboard.php">Back to Dashboard</a>
                <a class="button button-secondary" href="track.php">Track Order</a>
            </div>
        </div>
    </div>
</body>
</html>
