<?php
require_once '../config/security.php';
if(!isset($_SESSION['admin_logged_in'])) header("Location: login.php");

include '../db.php';

$message = '';
if (isset($_POST['update'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
    } else {
        $status = sanitize_input($_POST['status']);
        $id = intval($_POST['id']);
        $admin_id = intval($_SESSION['admin_id']);
        
        // Validate status
        $allowed_statuses = array('Pending', 'Cooking', 'On the way', 'Delivered');
        if (!in_array($status, $allowed_statuses)) {
            $message = 'Invalid order status.';
        } else {
            $stmt = $conn->prepare("UPDATE orders SET tracking_status = ?, admin_id = ? WHERE id = ? AND admin_id = ?");
            if ($stmt) {
                $stmt->bind_param("siii", $status, $admin_id, $id, $admin_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $message = 'Order status updated to ' . htmlspecialchars($status) . '.';
                } else {
                    $message = 'Failed to update order status or order not found.';
                    log_error('Failed to update order: ' . $stmt->error);
                }
                $stmt->close();
            }
        }
    }
}

$stmt = $conn->prepare("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.admin_id = ? ORDER BY o.id DESC");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .order-details {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            border-left: 4px solid #ff7810;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .order-title {
            font-weight: 700;
            font-size: 16px;
            color: #333;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
        }
        .order-items-table th, .order-items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .order-items-table th {
            background: #f0f0f0;
            font-weight: 600;
        }
        .order-items-table tr:last-child td {
            border-bottom: none;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
        }
        .order-total {
            font-weight: 700;
            font-size: 18px;
            color: #ff7810;
        }
    </style>
</head>
<body>
    <div class="nav">🍔 Crave Cart - Orders</div>
    <div class="page-title">Manage Orders</div>
    <div class="track-box">
        <?php if ($message): ?>
            <div class="message-box">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php while($r=$res->fetch_assoc()): ?>
            <div class="order-details">
                <div class="order-header">
                    <div>
                        <div class="order-title">Order #<?= htmlspecialchars($r['id']) ?></div>
                        <div class="text-muted">
                            Customer: <strong><?= htmlspecialchars($r['customer_name']) ?></strong>
                        </div>
                    </div>
                    <div class="status-chip">
                        <?= htmlspecialchars($r['tracking_status']) ?>
                    </div>
                </div>

                <!-- Order Items Table -->
                <?php
                $items_res = $conn->query("SELECT * FROM order_items WHERE order_id=" . intval($r['id']));
                ?>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_res->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['food_name']) ?></td>
                                <td class="text-right"><?= intval($item['quantity']) ?></td>
                                <td class="text-right">৳<?= number_format($item['price'], 2) ?></td>
                                <td class="text-right">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="text-muted">
                        Order Date: <?= date('M d, Y H:i', strtotime($r['created_at'])) ?>
                    </div>
                    <div class="order-total">Total: ৳<?= number_format($r['total'], 2) ?></div>
                </div>

                <!-- Status Update Form -->
                <form method="POST" class="form-inline">
                    <select name="status" class="form-control">
                        <option <?= $r['tracking_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option <?= $r['tracking_status'] == 'Cooking' ? 'selected' : '' ?>>Cooking</option>
                        <option <?= $r['tracking_status'] == 'On the way' ? 'selected' : '' ?>>On the way</option>
                        <option <?= $r['tracking_status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    </select>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($r['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    <button name="update" class="button">Update Status</button>
                </form>
            </div>
        <?php endwhile; ?>

        <a class="button" href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>