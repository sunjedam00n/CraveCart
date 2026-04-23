<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if ($username === '' || $password === '' || $confirm_password === '') {
            $message = 'Please fill all fields.';
        } else if (strlen($username) < 3) {
            $message = 'Username must be at least 3 characters long.';
        } else if (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters long.';
        } else if ($password !== $confirm_password) {
            $message = 'Passwords do not match.';
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $message = 'This username is already taken. Please choose a different one.';
                } else {
                    // Hash password
                    $hashed_password = hash_password($password);
                    
                    // Insert admin
                    $insert_stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("ss", $username, $hashed_password);
                        if ($insert_stmt->execute()) {
                            $message = 'Admin account created successfully! You can now login.';
                            $success = true;
                        } else {
                            $message = 'Registration failed. Please try again.';
                            log_error('Admin registration insert failed: ' . $insert_stmt->error);
                        }
                        $insert_stmt->close();
                    } else {
                        $message = 'Registration system error. Please try again.';
                        log_error('Prepare failed in admin registration: ' . $conn->error);
                    }
                }
                $stmt->close();
            } else {
                $message = 'Registration system error. Please try again.';
                log_error('Prepare failed in admin registration check: ' . $conn->error);
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
    <title><?php echo $show_form ? 'Admin Registration' : 'Registration Status'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">🍔 Crave Cart - Admin Registration</div>
    <div class="auth-wrapper">
        <div class="form-box auth-card">
            <?php if ($show_form): ?>
                <div class="auth-header">
                    <h1 class="auth-title">Create Admin Account</h1>
                    <p class="auth-subtitle">Register a secure administrator account for managing menus, orders, and staff operations.</p>
                </div>

                <div class="auth-note">
                    <strong>🔐 Important Security Guidelines:</strong>
                    <ul style="margin: 10px 0 0 18px; font-size: 13px; color: #666;">
                        <li><strong>Create a strong password:</strong> Use at least 8 characters with uppercase, lowercase, numbers, and symbols.</li>
                        <li><strong>Unique passwords only:</strong> Never reuse the same password across accounts.</li>
                        <li><strong>Don't share credentials:</strong> Keep your username and password confidential.</li>
                        <li><strong>Admin accounts are critical:</strong> Protect them with maximum security.</li>
                        <li><strong>Different passwords:</strong> Each admin must have their own unique password.</li>
                    </ul>
                </div>

                <form action="register.php" method="POST">
                    <label for="username">Username (min 3 characters)</label>
                    <input id="username" name="username" autocomplete="username" required minlength="3" maxlength="255">

                    <label for="password">Password (min 6 characters)</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password" autocomplete="new-password" required minlength="6">
                        <label class="show-password-option">
                            <input type="checkbox" onchange="togglePasswordField('password', this)">
                        </label>
                    </div>

                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field">
                        <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" required minlength="6">
                        <label class="show-password-option">
                            <input type="checkbox" onchange="togglePasswordField('confirm_password', this)">
                        </label>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit">Register</button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            <?php else: ?>
                <h2>Registration Status</h2>
                <div class="empty-state"><?= htmlspecialchars($message) ?></div>
                
                <?php if (isset($success) && $success): ?>
                    <div class="auth-footer">
                        <a class="button button-secondary" href="login.php">Go to Login</a>
                    </div>
                <?php else: ?>
                    <div class="auth-footer">
                        <a class="button button-secondary" href="register.php">Back to Registration</a>
                    </div>
                <?php endif; ?>
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
