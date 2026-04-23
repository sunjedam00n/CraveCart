<?php
include 'db.php';

$foods = [
    ['name' => 'Classic Burger', 'price' => 250.00],
    ['name' => 'Margherita Pizza', 'price' => 300.00],
    ['name' => 'Chicken Nuggets', 'price' => 150.00],
    ['name' => 'French Fries', 'price' => 100.00],
    ['name' => 'Caesar Salad', 'price' => 180.00],
    ['name' => 'Chocolate Milkshake', 'price' => 120.00],
    ['name' => 'Grilled Chicken Sandwich', 'price' => 220.00],
    ['name' => 'Vegetable Stir Fry', 'price' => 200.00],
];

foreach ($foods as $food) {
    $name = $conn->real_escape_string($food['name']);
    $price = $food['price'];
    $conn->query("INSERT INTO food (name, price) VALUES ('$name', $price)");
}

echo "Sample food items added to the menu!";
?>