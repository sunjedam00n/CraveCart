<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crave Cart Menu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">🍔 Crave Cart</div>
    <div class="page-title">Delicious Menu</div>
    <div class="container">
        <?php
        $res = $conn->query("SELECT * FROM food");
        while($row = $res->fetch_assoc()){
        ?>
            <div class="card">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>৳<?= number_format($row['price'], 2) ?></p>
                <form action="user/cart.php" method="POST">
                    <input type="hidden" name="food_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php } ?>
    </div>
    <div class="dashboard">
        <a class="button" href="user/login.html">Login</a>
        <a class="button" href="user/register.html">Register</a>
        <a class="button" href="user/view_cart.php">View Cart</a>
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