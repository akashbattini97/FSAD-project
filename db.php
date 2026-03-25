<?php
$host = "localhost";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create db and table if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS animart");
$conn->select_db("animart");

$tableQuery = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orderId VARCHAR(255) UNIQUE NOT NULL,
    userId VARCHAR(255),
    customerName VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pinCode VARCHAR(10),
    items JSON NOT NULL,
    subtotal DECIMAL(10, 2),
    shipping DECIMAL(10, 2),
    total DECIMAL(10, 2),
    paymentMethod VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Confirmed',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_userId (userId),
    INDEX idx_orderId (orderId)
)";

$conn->query($tableQuery);
?>
