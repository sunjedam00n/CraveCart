<?php
session_start();
require_once '../config/security.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT cart.quantity, food.price FROM cart JOIN food ON cart.food_id = food.id WHERE cart.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
while ($r = $res->fetch_assoc()) {
    $total += $r['price'] * $r['quantity'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bKash Payment</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="page-title">Payment</div>
    <div class="track-box">
        <div class="form-box">
            <h2>bKash Checkout</h2>
            <p class="invoice-meta">Secure payment with bKash. Confirm your transaction details below.</p>
            <form action="confirm_payment.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <label>Total Amount:</label>
                <input type="number" name="amount" value="<?= htmlspecialchars(number_format($total, 2, '.', '')) ?>" step="0.01" readonly required>
                <label>Transaction ID (TRX):</label>
                <input name="trx" required>
                <button>Confirm Payment</button>
            </form>
            <div class="dashboard-actions">
                <a class="button button-secondary" href="view_cart.php">Back to Cart</a>
            </div>
        </div>
    </div>
</body>
</html>