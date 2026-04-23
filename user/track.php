<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="page-title">Track Your Orders</div>
    <div class="track-box">
        <?php if ($res && $res->num_rows > 0): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $res->fetch_assoc()): ?>
                        <?php
                        $status = strtolower(trim($r['tracking_status']));
                        $pillClass = 'order-status';
                        if ($status === 'pending') {
                            $pillClass .= ' status-pending';
                        } elseif ($status === 'cooking' || $status === 'on the way') {
                            $pillClass .= ' status-paid';
                        } elseif ($status === 'delivered') {
                            $pillClass .= ' status-delivered';
                        } else {
                            $pillClass .= ' status-pending';
                        }
                        ?>
                        <tr>
                            <td>#<?= htmlspecialchars($r['id']) ?></td>
                            <td>৳<?= htmlspecialchars(number_format($r['total'], 2)) ?></td>
                            <td><span class="<?= $pillClass ?>"><?= htmlspecialchars($r['tracking_status']) ?></span></td>
                            <td><a class="button" href="invoice.php?id=<?= urlencode($r['id']) ?>">View Invoice</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">No orders found yet. Place an order and come back to see your delivery status here.</div>
        <?php endif; ?>
    </div>
</body>
</html>
