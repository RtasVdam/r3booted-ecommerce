<?php
// Database configuration - Railway MySQL URL
$mysql_url = getenv('MYSQL_URL');

if ($mysql_url) {
    // Parse the MySQL URL for Railway
    $url_parts = parse_url($mysql_url);
    define('DB_HOST', $url_parts['host']);
    define('DB_USER', $url_parts['user']);
    define('DB_PASS', $url_parts['pass']);
    define('DB_NAME', ltrim($url_parts['path'], '/'));
    $port = $url_parts['port'] ?? 3306;
} else {
    // Fallback for local development
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'r3booted_ecommerce');
    $port = 3306;
}

define('SITE_NAME', 'R3Booted Technology');
$railway_url = getenv('RAILWAY_STATIC_URL');
define('SITE_URL', $railway_url ? 'https://' . $railway_url : 'http://localhost');

// Create database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Rest of your functions...