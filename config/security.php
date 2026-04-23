<?php
// Security Configuration and Utilities

// Session security settings
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Password hashing functions
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Input sanitization
function sanitize_input($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

// CSRF token generation
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token validation
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Secure error handling
function log_error($message, $severity = 'WARNING') {
    $log_file = __DIR__ . '/../logs/error.log';
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] [$severity] $message\n", FILE_APPEND);
}

// Rate limiting helper
function check_rate_limit($key, $max_attempts = 5, $time_window = 300) {
    $session_key = 'rate_limit_' . $key;
    $current_time = time();
    
    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = array('attempts' => 0, 'first_attempt' => $current_time);
    }
    
    $attempts = $_SESSION[$session_key]['attempts'];
    $first_attempt_time = $_SESSION[$session_key]['first_attempt'];
    
    // Reset if time window has passed
    if ($current_time - $first_attempt_time > $time_window) {
        $_SESSION[$session_key] = array('attempts' => 1, 'first_attempt' => $current_time);
        return true;
    }
    
    // Check if exceeded max attempts
    if ($attempts >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$session_key]['attempts']++;
    return true;
}

?>
