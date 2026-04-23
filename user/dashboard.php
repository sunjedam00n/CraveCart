<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../db.php';

$user_id = $_SESSION['user_id'];

// Get user name
$user_query = "SELECT name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_name = $user['name'];

// Get cart count
$cart_query = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_count = $cart_result->fetch_assoc()['total'];

// Get total spent
$spent_query = "SELECT SUM(total) as total FROM orders WHERE user_id = ?";
$spent_stmt = $conn->prepare($spent_query);
$spent_stmt->bind_param("i", $user_id);
$spent_stmt->execute();
$spent_result = $spent_stmt->get_result();
$total_spent = $spent_result->fetch_assoc()['total'] ?? 0;

// Get order count
$orders_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$order_count = $orders_result->fetch_assoc()['total'];

// Get active orders
$active_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND tracking_status NOT IN ('Delivered', 'Cancelled')";
$active_stmt = $conn->prepare($active_query);
$active_stmt->bind_param("i", $user_id);
$active_stmt->execute();
$active_result = $active_stmt->get_result();
$active_count = $active_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-header {
            text-align: center;
            margin: 40px 0 50px;
            padding: 0 20px;
        }

        .welcome-message {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ff4a00 0%, #ff8b2c 50%, #ffb03f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .welcome-subtitle {
            font-size: 1.05rem;
            color: #7c4b20;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 28px;
            width: min(1200px, 94%);
            margin: 40px auto;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 
                0 4px 6px rgba(0, 0, 0, 0.05),
                0 15px 40px rgba(255, 120, 10, 0.08);
            border: 1px solid rgba(255, 146, 64, 0.15);
            transition: all 0.35s cubic-bezier(0.35, 1.56, 0.65, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff4a00, #ffb03f);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 8px 12px rgba(0, 0, 0, 0.1),
                0 25px 50px rgba(255, 120, 10, 0.15);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-icon {
            font-size: 2.4rem;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ff4a00, #ff8b2c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 12px 0;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #7c4b20;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            width: min(1200px, 94%);
            margin: 50px auto;
        }

        .action-card {
            background: linear-gradient(135deg, rgba(255, 74, 0, 0.08), rgba(255, 176, 63, 0.08));
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            border: 1.5px solid rgba(255, 120, 10, 0.2);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            background: linear-gradient(135deg, rgba(255, 74, 0, 0.12), rgba(255, 176, 63, 0.12));
            border-color: rgba(255, 120, 10, 0.4);
            transform: translateY(-4px);
        }

        .action-card a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
            font-size: 2.8rem;
            margin-bottom: 12px;
        }

        .action-label {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2f2f2f;
            margin-bottom: 8px;
        }

        .action-desc {
            font-size: 0.85rem;
            color: #7c4b20;
        }

        .alert-info {
            width: min(1200px, 94%);
            margin: 20px auto;
            padding: 18px 22px;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.08), rgba(66, 165, 245, 0.08));
            border-left: 4px solid #4CAF50;
            border-radius: 10px;
            color: #2e7d32;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .welcome-message {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 700px) {
            .stats-grid,
            .action-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .welcome-message {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="nav">🍔 Crave Cart</div>
    
    <div class="dashboard-header">
        <div class="welcome-message">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</div>
        <div class="welcome-subtitle">Ready to order some delicious food?</div>
    </div>

    <?php if($active_count > 0): ?>
    <div class="alert-info">
        <span>🚀</span>
        <span>You have <strong><?php echo $active_count; ?></strong> active order<?php echo $active_count != 1 ? 's' : ''; ?> being prepared</span>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🛒</div>
            <div class="stat-value"><?php echo $cart_count; ?></div>
            <div class="stat-label">Items in Cart</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?php echo $order_count; ?></div>
            <div class="stat-label">Orders Placed</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💸</div>
            <div class="stat-value">&#2547;<?php echo number_format($total_spent, 2); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-value"><?php echo $active_count; ?></div>
            <div class="stat-label">Active Orders</div>
        </div>
    </div>

    <h3 class="section-heading">Quick Actions</h3>

    <div class="action-grid">
        <div class="action-card">
            <a href="menu.php">
                <div class="action-icon">🍽️</div>
                <div class="action-label">Browse Menu</div>
                <div class="action-desc">Explore our offerings</div>
            </a>
        </div>

        <div class="action-card">
            <a href="view_cart.php">
                <div class="action-icon">🛒</div>
                <div class="action-label">View Cart</div>
                <div class="action-desc"><?php echo $cart_count; ?> item<?php echo $cart_count != 1 ? 's' : ''; ?> waiting</div>
            </a>
        </div>

        <div class="action-card">
            <a href="track.php">
                <div class="action-icon">📍</div>
                <div class="action-label">Track Orders</div>
                <div class="action-desc">Real-time delivery status</div>
            </a>
        </div>

        <div class="action-card">
            <a href="logout.php">
                <div class="action-icon">🚪</div>
                <div class="action-label">Logout</div>
                <div class="action-desc">Exit your account</div>
            </a>
        </div>
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