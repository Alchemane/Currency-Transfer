<?php
// database configuration
$host = "localhost";
$dbname = "your_database_name";
$username = "root"; // default for XAMPP
$password = ""; // default for XAMPP

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../database/currency_transfer.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>