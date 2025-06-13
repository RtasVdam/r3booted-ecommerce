<?php
// config.php - Updated for Railway deployment

// Database configuration - Railway environment variables
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('MYSQL_HOST') ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('MYSQL_USER') ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('MYSQL_PASSWORD') ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('MYSQL_DATABASE') ?? 'r3booted_ecommerce');

// Site configuration - Railway will provide the URL
$railway_url = getenv('RAILWAY_STATIC_URL');
define('SITE_URL', $railway_url ? 'https://' . $railway_url : 'http://localhost');
define('SITE_NAME', 'R3Booted Technology');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // For Railway, we might need to create the database first
    try {
        $pdo_create = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo_create->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e2) {
        die("Connection failed: " . $e2->getMessage());
    }
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Common functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function redirect($url) {
    // Handle relative URLs for Railway
    if (!str_starts_with($url, 'http')) {
        $base_url = rtrim(SITE_URL, '/');
        $url = $base_url . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatPrice($price) {
    return 'R' . number_format($price, 2);
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function getProductsByCategory($categorySlug = null) {
    global $pdo;
    
    if ($categorySlug && $categorySlug !== 'all') {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE c.slug = ? AND p.status = 'active'
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$categorySlug]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
        ");
    }
    
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCartItems($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCartTotal($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * p.price) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCartCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Error and success message handling
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        unset($_SESSION['message'], $_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Initialize database tables if they don't exist (for Railway first deploy)
try {
    $pdo->exec("SHOW TABLES LIKE 'users'");
    if ($pdo->rowCount() == 0) {
        // Run the database schema if tables don't exist
        $schema = file_get_contents(__DIR__ . '/database_schema.sql');
        if ($schema) {
            $pdo->exec($schema);
        }
    }
} catch (Exception $e) {
    // Tables might not exist yet, that's OK
}
?>
