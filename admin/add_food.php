<?php
require_once '../config/security.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$message = '';

if (isset($_POST['add'])) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
    } else {
        $name = sanitize_input($_POST['name']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity'] ?? 0);
        $admin_id = intval($_SESSION['admin_id']);

        if (empty($name)) {
            $message = 'Food name cannot be empty.';
        } elseif ($price <= 0) {
            $message = 'Price must be greater than 0.';
        } elseif ($quantity < 0) {
            $message = 'Quantity cannot be negative.';
        } elseif (!isset($_FILES['image'])) {
            $message = 'Please select an image file.';
        } elseif ($_FILES['image']['error'] !== 0) {
            $message = 'Error uploading file.';
        } else {
            $allowed = array('jpg', 'jpeg', 'gif', 'png');
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $message = 'Invalid file type. Only JPG, JPEG, GIF, PNG allowed.';
            } elseif ($_FILES['image']['size'] > 5000000) {
                $message = 'File too large. Max size is 5MB.';
            } else {
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $image_name = time() . '_' . uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $stmt = $conn->prepare("INSERT INTO food (name, price, quantity, image, admin_id) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("sdisi", $name, $price, $quantity, $image_name, $admin_id);
                        if ($stmt->execute()) {
                            $message = 'Food item added successfully!';
                        } else {
                            $message = 'Error adding food item.';
                            log_error('Failed to insert food: ' . $stmt->error);
                        }
                        $stmt->close();
                    } else {
                        $message = 'Error adding food item.';
                        log_error('Prepare failed in add_food: ' . $conn->error);
                    }
                } else {
                    $message = 'Failed to upload image.';
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
    <title>Add Food</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav">🍔 Crave Cart - Add Food</div>
    <div class="page-title">Add New Menu Item</div>
    <div class="track-box">
        <?php if ($message): ?>
            <div class="message-box"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2>Food Details</h2>
            <p>Please provide the item name, price, quantity in stock, and high-quality image.</p>
            <form method="POST" enctype="multipart/form-data">
                <label>Name:</label>
                <input name="name" required>
                <label>Price (৳):</label>
                <input name="price" type="number" step="0.01" required>
                <label>Quantity in Stock:</label>
                <input name="quantity" type="number" min="0" value="0" required>
                <label>Image:</label>
                <input name="image" type="file" accept="image/jpeg,image/jpg,image/gif,image/png" required>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <button type="submit" name="add">Add Food</button>
            </form>
        </div>

        <div class="dashboard-actions">
            <a class="button button-secondary" href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>