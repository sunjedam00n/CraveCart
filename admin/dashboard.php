<?php 
session_start(); 
require_once '../db.php';
if(!isset($_SESSION['admin_logged_in'])) header("Location: login.php"); 

$admin_username = $_SESSION['admin_username'];

// Get total food items
$food_query = "SELECT COUNT(*) as total FROM food";
$food_stmt = $conn->prepare($food_query);
$food_stmt->execute();
$food_result = $food_stmt->get_result();
$food_count = $food_result->fetch_assoc()['total'];

// Get total orders
$orders_query = "SELECT COUNT(*) as total FROM orders WHERE admin_id = ?";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $_SESSION['admin_id']);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_count = $orders_result->fetch_assoc()['total'];

// Get total revenue
$revenue_query = "SELECT SUM(total) as revenue FROM orders WHERE admin_id = ?";
$revenue_stmt = $conn->prepare($revenue_query);
$revenue_stmt->bind_param("i", $_SESSION['admin_id']);
$revenue_stmt->execute();
$revenue_result = $revenue_stmt->get_result();
$revenue = $revenue_result->fetch_assoc()['revenue'] ?? 0;

// Get pending orders
$pending_query = "SELECT COUNT(*) as total FROM orders WHERE admin_id = ? AND tracking_status = 'Pending'";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->bind_param("i", $_SESSION['admin_id']);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_count = $pending_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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

        .alert-warning {
            width: min(1200px, 94%);
            margin: 20px auto;
            padding: 18px 22px;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.08), rgba(255, 176, 63, 0.08));
            border-left: 4px solid #ff9800;
            border-radius: 10px;
            color: #7c4b20;
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
    <div class="nav">🍔 Crave Cart - Admin Panel</div>
    
    <div class="dashboard-header">
        <div class="welcome-message">Welcome back, <?php echo htmlspecialchars($admin_username); ?>! 👋</div>
        <div class="welcome-subtitle">Here's your restaurant performance overview</div>
    </div>

    <?php if($pending_count > 0): ?>
    <div class="alert-warning">
        <span>⏰</span>
        <span>You have <strong><?php echo $pending_count; ?></strong> pending order<?php echo $pending_count != 1 ? 's' : ''; ?> to process</span>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🍕</div>
            <div class="stat-value"><?php echo $food_count; ?></div>
            <div class="stat-label">Total Menu Items</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?php echo $orders_count; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">৳<?php echo number_format($revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-value"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>

    <h3 class="section-heading">Quick Actions</h3>

    <div class="action-grid">
        <div class="action-card">
            <a href="add_food.php">
                <div class="action-icon">➕</div>
                <div class="action-label">Add New Food</div>
                <div class="action-desc">Add item to menu</div>
            </a>
        </div>

        <div class="action-card">
            <a href="manage_food.php">
                <div class="action-icon">🔧</div>
                <div class="action-label">Manage Menu</div>
                <div class="action-desc">Edit or remove items</div>
            </a>
        </div>

        <div class="action-card">
            <a href="orders.php">
                <div class="action-icon">📋</div>
                <div class="action-label">View Orders</div>
                <div class="action-desc">Process customer orders</div>
            </a>
        </div>

        <div class="action-card">
            <a href="logout.php">
                <div class="action-icon">🚪</div>
                <div class="action-label">Logout</div>
                <div class="action-desc">Exit admin panel</div>
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