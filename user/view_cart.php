<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

$user_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = intval($_POST['remove_id']);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    header('Location: view_cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty_id'])) {
    $cart_id = intval($_POST['update_qty_id']);
    $new_qty = intval($_POST['quantity_' . $cart_id]);
    
    if ($new_qty > 0) {
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("iii", $new_qty, $cart_id, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    header('Location: view_cart.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT cart.id AS cart_id, cart.quantity, food.id, food.image, food.name, food.price
     FROM cart
     JOIN food ON cart.food_id = food.id
     WHERE cart.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
$items = [];
while ($row = $res->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .cart-image { max-width: 90px; max-height: 90px; border-radius: 12px; object-fit: cover; }
    </style>
</head>
<body>
    <div class="nav">🍔 Crave Cart</div>
    <div class="page-title">Your Cart</div>
    <div class="track-box">
        <?php if (count($items) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Food</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image']) && file_exists('../uploads/' . $item['image'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-image">
                                    <?php else: ?>
                                        <div class="help-panel">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>৳<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <form method="POST" class="qty-form" style="display: inline; margin: 0;">
                                        <input type="hidden" name="update_qty_id" value="<?= htmlspecialchars($item['cart_id']) ?>">
                                        <input type="number" name="quantity_<?= htmlspecialchars($item['cart_id']) ?>" value="<?= intval($item['quantity']) ?>" min="1" max="999" style="width: 60px; padding: 6px;">
                                        <button type="submit" class="button button-sm" style="padding: 6px 12px; font-size: 12px;">Update</button>
                                    </form>
                                </td>
                                <td>৳<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <form method="POST" class="button-group">
                                        <input type="hidden" name="remove_id" value="<?= htmlspecialchars($item['cart_id']) ?>">
                                        <button type="submit" class="button button-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="order-summary">
                <div class="invoice-meta">Items in cart: <strong><?= count($items) ?></strong></div>
                <div class="invoice-total">Total: ৳<?= number_format($total, 2) ?></div>
            </div>
            <div class="dashboard-actions">
                <a class="button button-secondary" href="menu.php">Continue Shopping</a>
                <a class="button" href="payment.php">Proceed to Payment</a>
            </div>
        <?php else: ?>
            <div class="message-box">Your cart is empty. Add tasty items from the menu to continue.</div>
            <div class="dashboard-actions">
                <a class="button" href="menu.php">Browse Menu</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>