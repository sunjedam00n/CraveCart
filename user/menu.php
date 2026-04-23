<?php
require_once '../config/security.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../db.php';

// Get all food items - they should persist and never be deleted when ordered
$res = $conn->query("SELECT * FROM food ORDER BY created_at DESC");
if (!$res) {
    die('Database error: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Crave Cart</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">🍔 Crave Cart</div>
    <div class="page-title">Menu</div>
    <div class="section-intro">
        <h2>Fresh dishes prepared for every craving.</h2>
        <p>Explore our premium menu and add your favorites to the cart in one tap. Each item is ready for speedy delivery.</p>
    </div>
    <div class="container">
        <?php while ($row = $res->fetch_assoc()): ?>
            <div class="card">
                <?php if (!empty($row['image']) && file_exists('../uploads/' . $row['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                    <div class="card-no-image">No Image Available</div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p class="price-label">৳<?= number_format($row['price'], 2) ?></p>
                <?php if (intval($row['quantity']) > 0): ?>
                    <p style="color: #28a745; font-size: 12px; margin: 4px 0;"><strong>In Stock: <?= intval($row['quantity']) ?></strong></p>
                    <form action="cart.php" method="POST">
                        <label for="qty_<?= htmlspecialchars($row['id']) ?>" style="font-size: 12px;">Qty:</label>
                        <input id="qty_<?= htmlspecialchars($row['id']) ?>" type="number" name="quantity" value="1" min="1" max="<?= intval($row['quantity']) ?>" style="width: 50px; padding: 4px; margin-right: 8px;">
                        <input type="hidden" name="food_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p style="color: #dc3545; font-size: 12px; margin: 4px 0;"><strong>Out of Stock</strong></p>
                    <button type="button" disabled style="opacity: 0.5; cursor: not-allowed;">Out of Stock</button>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="dashboard-actions">
        <a class="button" href="view_cart.php">View Cart</a>
        <a class="button" href="dashboard.php">My Account</a>
        <a class="button" href="track.php">Track Orders</a>
        <a class="button button-secondary" href="logout.php">Logout</a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🍔 Crave Cart</h3>
                <p>Your favorite cloud kitchen delivering delicious meals right to your doorstep. Fresh ingredients, authentic flavors, and lightning-fast delivery.</p>
            </div>

            <div class="footer-section">
                <h3>Contact & Feedback</h3>
                <div class="footer-contact">
                    <p><i>📧</i> <strong>Email:</strong> feedback@crave_cart.com</p>
                    <p><i>📱</i> <strong>Phone:</strong> +880 1316853415</p>
                    <p><i>📍</i> <strong>Address:</strong>Uttara, Dhaka-1230, Bangladesh</p>
                </div>
                <p>Have feedback or suggestions? We'd love to hear from you!</p>
            </div>

            <div class="footer-section">
                <h3>Follow Us</h3>
                <p>Stay connected and follow us for the latest updates, special offers, and mouth-watering food photos!</p>
                <div class="footer-social">
                    <a href="https://instagram.com/cravecart" target="_blank" title="Follow us on Instagram">
                        📷
                    </a>
                    <!-- TODO: Add Instagram URL here when provided -->
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 <strong>SunjedaSoftwares</strong>. All rights reserved.</p>
            <p>Built with 🤍 for food lovers</p>
        </div>
    </footer>
</body>
</html>
