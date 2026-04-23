<?php
// Migrate existing database to add quantity column to food table
$conn = new mysqli("localhost","root","","crave_cart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if quantity column exists
$result = $conn->query("SHOW COLUMNS FROM food LIKE 'quantity'");

if ($result && $result->num_rows == 0) {
    // Column doesn't exist, add it
    $alter_sql = "ALTER TABLE food ADD COLUMN quantity INT NOT NULL DEFAULT 0 AFTER price";
    
    if ($conn->query($alter_sql)) {
        echo "✓ Successfully added 'quantity' column to food table!<br>";
    } else {
        echo "✗ Error adding quantity column: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Quantity column already exists!<br>";
}

echo "Migration complete! Your database is ready for stock management.<br>";
$conn->close();
?>
