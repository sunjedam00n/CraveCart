<?php
require_once '../config/security.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['food_id'])) {
    header('Location: menu.php');
    exit;
}

include '../db.php';

$user_id = intval($_SESSION['user_id']);
$food_id = intval($_POST['food_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($quantity <= 0) {
    $quantity = 1;
}

if ($food_id <= 0) {
    header('Location: menu.php');
    exit;
}

// Check stock availability
$check_stmt = $conn->prepare("SELECT quantity FROM food WHERE id = ?");
if ($check_stmt) {
    $check_stmt->bind_param("i", $food_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result && $check_result->num_rows > 0) {
        $food = $check_result->fetch_assoc();
        $available_qty = intval($food['quantity']);
        
        if ($available_qty <= 0) {
            header('Location: menu.php?error=out_of_stock');
            $check_stmt->close();
            exit;
        }
        
        if ($quantity > $available_qty) {
            $quantity = $available_qty;
        }
    } else {
        header('Location: menu.php');
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
}

// Check if item already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ?");
if ($stmt) {
    $stmt->bind_param("ii", $user_id, $food_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cart_id = intval($row['id']);
        $current_qty = intval($row['quantity']);
        $new_qty = $current_qty + $quantity;
        
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("ii", $new_qty, $cart_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, ?)");
        if ($insert_stmt) {
            $insert_stmt->bind_param("iii", $user_id, $food_id, $quantity);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
    }
    $stmt->close();
}

header('Location: view_cart.php');
exit;
