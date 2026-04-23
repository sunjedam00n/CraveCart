<?php
$conn = new mysqli("localhost","root","","crave_cart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>