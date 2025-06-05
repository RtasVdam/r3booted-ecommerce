<?php
// config.sample.php - Sample configuration file
// Copy this to config.php and update with your settings

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');        // Update this
define('DB_PASS', 'your_password');        // Update this
define('DB_NAME', 'r3booted_ecommerce');

// Site configuration
define('SITE_URL', 'http://localhost/r3booted');  // Update for production
define('SITE_NAME', 'R3Booted Technology');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ... rest of your functions from config.php
?>