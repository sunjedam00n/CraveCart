<?php
require_once '../config/security.php';
include '../db.php';

$message = '';
$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $show_form = false;
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $message = 'Please provide both email and password.';
        } else {
            // Rate limiting
            if (!check_rate_limit('user_login_' . $email, 5, 300)) {
                $message = 'Too many login attempts. Please try again later.';
            } else {
                $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $user = $result->fetch_assoc();

                        // Verify password using bcrypt
                        if (verify_password($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            header('Location: dashboard.php');
                            exit;
                        }

                        $message = 'Wrong password!';
                    } else {
                        $message = 'User not found! Please register first.';
                    }
                    $stmt->close();
                } else {
                    $message = 'Login system error. Please try again.';
                    log_error('Prepare failed in user login: ' . $conn->error);
                }
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
    <title><?php echo $show_form ? 'User Login' : 'Login Status'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="auth-wrapper">
        <div class="form-box auth-card">
            <?php if ($show_form): ?>
                <div class="auth-header">
                    <h1 class="auth-title">User Login</h1>
                    <p class="auth-subtitle">Log in to order your favorite meals and track delivery status.</p>
                </div>

                <form action="login.php" method="POST">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" placeholder="Email" autocomplete="email" required>

                    <label for="password">Password</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                        <label class="show-password-option">
                            <input type="checkbox" onchange="togglePasswordField('password', this)">
                        </label>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit">Sign In</button>
                </form>

                <div class="auth-note auth-note--tip">
                    <strong>🔒 Security Tip:</strong>
                    Use a strong, unique password and keep your credentials private to protect your account.
                </div>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            <?php else: ?>
                <h2>Login Status</h2>
                <div class="empty-state"><?= htmlspecialchars($message) ?></div>
                <div class="auth-footer">
                    <a class="button button-secondary" href="login.php">Back to Login</a>
                </div>
            <?php endif; ?>
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
<script>
function togglePasswordField(targetId, checkbox) {
    const field = document.getElementById(targetId);
    if (field) {
        field.type = checkbox.checked ? 'text' : 'password';
    }
}

function togglePasswords(checkbox) {
    ['password', 'confirm_password'].forEach((targetId) => {
        const field = document.getElementById(targetId);
        if (field) field.type = checkbox.checked ? 'text' : 'password';
    });
}
</script>
</html>
