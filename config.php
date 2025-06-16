<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'R3Booted Technology');
}

// Database configuration using Railway environment variables
$host = getenv("MYSQLHOST") ?: "localhost";
$port = getenv("MYSQLPORT") ?: "3306";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";
$db   = getenv("MYSQLDATABASE") ?: "railway";

try {
    // PDO connection string
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

function getCartCount($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

function getCartItems($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price, p.image_url
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getCartTotal($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(c.quantity * p.price) as total
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

function getCategories() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getProductById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

function getProductsByCategory($category = null) {
    global $pdo;
    try {
        if ($category) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE c.slug = ? AND p.status = 'active'
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$category]);
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
    } catch (Exception $e) {
        return [];
    }
}

function formatPrice($price) {
    return 'R' . number_format($price, 2);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function setMessage($message, $type = 'success') {
    $_SESSION['message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $msg = $_SESSION['message'];
        unset($_SESSION['message']);
        return $msg;
    }
    return null;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Create database tables if they don't exist
try {
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if (!$stmt->fetch()) {
        // Tables don't exist, create them
        createDatabaseTables();
    }
} catch (Exception $e) {
    // Tables might not exist yet, try to create them
    createDatabaseTables();
}

function createDatabaseTables() {
    global $pdo;
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock_quantity INT DEFAULT 0,
            category_id INT,
            image_url VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10,2) NOT NULL,
            shipping_cost DECIMAL(10,2) DEFAULT 50.00,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            shipping_name VARCHAR(100) NOT NULL,
            shipping_address TEXT NOT NULL,
            shipping_city VARCHAR(50) NOT NULL,
            shipping_postal VARCHAR(20) NOT NULL,
            shipping_phone VARCHAR(20) NOT NULL,
            payment_method ENUM('card', 'cash') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
        } catch (Exception $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }
    
    // Insert default data
    insertDefaultData();
}

function insertDefaultData() {
    global $pdo;
    
    try {
        // Check if categories exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Insert categories
            $categories = [
                ['Phones', 'phone', 'Smartphones and mobile devices'],
                ['Laptops', 'laptop', 'Laptops and notebooks'],
                ['Tablets', 'tablet', 'Tablets and 2-in-1 devices']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
        }
        
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetch()['count'];
        
        if ($adminCount == 0) {
            // Create admin user
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Admin', 'admin@r3booted.com', $adminPassword, 'admin']);
            
            // Create demo user
            $userPassword = password_hash('password123', PASSWORD_DEFAULT);
            $stmt->execute(['Demo User', 'user@example.com', $userPassword, 'user']);
        }
        
        // Check if products exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $productCount = $stmt->fetch()['count'];
        
        if ($productCount == 0) {
            // Insert sample products
            $products = [
                ['iPhone 14 Pro', 'Latest iPhone with Pro camera system and A16 Bionic chip', 15999.00, 10, 1],
                ['Samsung Galaxy S23', 'Android flagship with amazing camera and performance', 13999.00, 12, 1],
                ['MacBook Pro M2', 'Powerful laptop for professionals with M2 chip', 28999.00, 5, 2],
                ['Dell XPS 13', 'Ultrabook with premium design and performance', 22999.00, 6, 2],
                ['iPad Air', 'Versatile tablet for work and play with M1 chip', 12999.00, 8, 3],
                ['Surface Pro 9', '2-in-1 tablet and laptop with Windows 11', 18999.00, 4, 3]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
            foreach ($products as $product) {
                $stmt->execute($product);
            }
        }
        
    } catch (Exception $e) {
        error_log("Error inserting default data: " . $e->getMessage());
    }
}
?>