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
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $message = 'Please fill the form completely.';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please provide a valid email address.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $message = 'This email is already registered. Please login.';
                } else {
                    $hashed_password = hash_password($password);

                    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("sss", $name, $email, $hashed_password);
                        if ($stmt->execute()) {
                            $_SESSION['user_id'] = $conn->insert_id;
                            header('Location: dashboard.php');
                            exit;
                        }
                        $message = 'Registration failed. Please try again.';
                        log_error('User registration insert failed: ' . $stmt->error);
                    } else {
                        $message = 'Registration system error. Please try again.';
                        log_error('Prepare failed in user registration: ' . $conn->error);
                    }
                }
                $stmt->close();
            } else {
                $message = 'Registration system error. Please try again.';
                log_error('Prepare failed in user registration check: ' . $conn->error);
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
    <title><?php echo $show_form ? 'Create Account' : 'Registration Status'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">Crave Cart</div>
    <div class="auth-wrapper">
        <div class="form-box auth-card">
            <?php if ($show_form): ?>
                <div class="auth-header">
                    <h1 class="auth-title">Create Account</h1>
                    <p class="auth-subtitle">Sign up to place orders, track deliveries, and manage your cart with ease.</p>
                </div>

                <div class="auth-note auth-note--tip">
                    <strong>Password Requirements:</strong>
                    <ul style="margin: 10px 0 0 18px; font-size: 13px; color: #666;">
                        <li>At least 6 characters long.</li>
                        <li>Use a mix of uppercase, lowercase, numbers, and symbols.</li>
                        <li>Make it unique - don't reuse passwords from other accounts.</li>
                        <li>Don't use the same password as other users.</li>
                        <li>Avoid using personal information (name, email, birthdate).</li>
                    </ul>
                </div>

                <form action="register.php" method="POST">
                    <label for="name">Full Name</label>
                    <input id="name" type="text" name="name" placeholder="Full Name" autocomplete="name" required>

                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="Email Address" autocomplete="email" required>

                    <label for="password">Password (min 6 characters)</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password" placeholder="Password" autocomplete="new-password" required minlength="6">
                        <label class="show-password-option">
                            <input type="checkbox" onchange="togglePasswordField('password', this)">
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
                <div class="message-box"><?= htmlspecialchars($message) ?></div>
                <div class="auth-footer">
                    <a class="button button-secondary" href="register.php">Back to Register</a>
                    <a class="button" href="login.php">Go to Login</a>
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
