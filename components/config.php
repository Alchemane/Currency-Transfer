<?php
// Database configuration
$host = "localhost";
$dbname = "your_database_name";
$username = "root"; // Default for XAMPP
$password = ""; // Default for XAMPP

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../database/currency_transfer.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>