<?php
require_once '../config/security.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';
$message = '';

if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Get image to delete
    $stmt = $conn->prepare("SELECT image FROM food WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $food = $res->fetch_assoc();
            if (!empty($food['image'])) {
                $image_path = '../uploads/' . $food['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        $stmt->close();
    }
    
    // Delete food item
    $stmt = $conn->prepare("DELETE FROM food WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = 'Food item deleted successfully.';
        } else {
            $message = 'Failed to delete item.';
            log_error('Failed to delete food: ' . $stmt->error);
        }
        $stmt->close();
    }
}

if (isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $name = sanitize_input($_POST['name']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    // Validate inputs
    if (empty($name)) {
        $message = 'Food name cannot be empty.';
    } else if ($price <= 0) {
        $message = 'Price must be greater than 0.';
    } else if ($quantity < 0) {
        $message = 'Quantity cannot be negative.';
    } else if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'gif', 'png');
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(!in_array($ext, $allowed)) {
            $message = 'Invalid file type. Only JPG, JPEG, GIF, PNG allowed.';
        } else if($_FILES['image']['size'] > 5000000) {
            $message = 'File too large. Max size is 5MB.';
        } else {
            // Get old image
            $stmt = $conn->prepare("SELECT image FROM food WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $update_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                    $food = $res->fetch_assoc();
                    $old_image = $food['image'];
                    
                    // Delete old image if exists
                    if (!empty($old_image)) {
                        $old_path = '../uploads/' . $old_image;
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                }
                $stmt->close();
            }
            
            // Upload new image
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $image_name = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $image_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $quantity = intval($_POST['quantity'] ?? 0);
                $stmt = $conn->prepare("UPDATE food SET name = ?, price = ?, quantity = ?, image = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("sdisi", $name, $price, $quantity, $image_name, $update_id);
                    if ($stmt->execute()) {
                        $message = 'Food item updated successfully.';
                    } else {
                        $message = 'Failed to update item.';
                        log_error('Failed to update food: ' . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $message = 'Update system error.';
                    log_error('Prepare failed in manage_food update: ' . $conn->error);
                }
            } else {
                $message = 'Failed to upload image.';
            }
        }
    } else {
        // Update without image
        $quantity = intval($_POST['quantity'] ?? 0);
        $stmt = $conn->prepare("UPDATE food SET name = ?, price = ?, quantity = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sdii", $name, $price, $quantity, $update_id);
            if ($stmt->execute()) {
                $message = 'Food item updated successfully.';
            } else {
                $message = 'Failed to update item.';
                log_error('Failed to update food: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
}

$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM food WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $edit_item = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

$foods = $conn->query("SELECT * FROM food ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Food</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .food-image { max-width: 150px; max-height: 150px; border-radius: 8px; }
        .image-preview { max-width: 200px; margin: 10px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="nav">🍔 Crave Cart - Manage Menu</div>
    <div class="page-title">Manage Food Items</div>
    <div class="track-box">
        <?php if ($message): ?>
            <div class="message-box">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($edit_item): ?>
            <div class="form-box">
                <h3>Edit Food Item</h3>
                <?php if (!empty($edit_item['image']) && file_exists('../uploads/' . $edit_item['image'])): ?>
                    <div>
                        <p><strong>Current Image:</strong></p>
                        <img src="../uploads/<?= htmlspecialchars($edit_item['image']) ?>" alt="Food" class="image-preview">
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_id" value="<?= htmlspecialchars($edit_item['id']) ?>">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($edit_item['name']) ?>" required>
                    <label>Price:</label>
                    <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($edit_item['price']) ?>" required>
                    <label>Quantity in Stock:</label>
                    <input type="number" name="quantity" value="<?= htmlspecialchars($edit_item['quantity'] ?? 0) ?>" min="0" required>
                    <label>Image (Leave empty to keep current image):</label>
                    <input type="file" name="image" accept="image/jpeg,image/jpg,image/gif,image/png">
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; while ($row = $foods->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter ?></td>
                            <td>
                                <?php if (!empty($row['image']) && file_exists('../uploads/' . $row['image'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="food-image">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>৳<?= number_format($row['price'], 2) ?></td>
                            <td><?= intval($row['quantity']) ?> items</td>
                            <td>
                                <div class="button-group">
                                    <a class="button button-secondary" href="manage_food.php?edit=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                                    <form method="POST" class="button-group">
                                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['id']) ?>">
                                        <button type="submit" class="button button-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php $counter++; endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-actions">
            <a class="button" href="dashboard.php">Back to Dashboard</a>
            <a class="button" href="add_food.php">Add New Food</a>
        </div>
    </div>
</body>
</html>