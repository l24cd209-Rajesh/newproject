<?php
require_once 'config.php';

// Use environment variables for database configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? 'secure_password_123';
$database = $_ENV['DB_NAME'] ?? 'event_db';

// Create connection with error handling
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    // Set charset to prevent character set confusion attacks
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>
