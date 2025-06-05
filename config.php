<?php
// config.php - Database configuration and common functions

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for default XAMPP
define('DB_NAME', 'r3booted_ecommerce');

// Site configuration
define('SITE_URL', 'http://localhost/r3booted');
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
?>