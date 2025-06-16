<?php
echo "<h1>Database Connection Test</h1>";

// Show environment variables
echo "<h2>Environment Variables:</h2>";
echo "MYSQLHOST: " . getenv("MYSQLHOST") . "<br>";
echo "MYSQLPORT: " . getenv("MYSQLPORT") . "<br>";
echo "MYSQLUSER: " . getenv("MYSQLUSER") . "<br>";
echo "MYSQLDATABASE: " . getenv("MYSQLDATABASE") . "<br>";
echo "PASSWORD SET: " . (getenv("MYSQLPASSWORD") ? "Yes" : "No") . "<br><br>";

// Test database connection
try {
    require_once 'config.php';
    echo "<h2>Database Connection: SUCCESS ✅</h2>";
    
    // Test tables
    $tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items', 'contact_messages'];
    echo "<h3>Tables Status:</h3>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "$table: $count records ✅<br>";
        } catch (Exception $e) {
            echo "$table: ERROR - " . $e->getMessage() . " ❌<br>";
        }
    }
    
    // Test sample data
    echo "<h3>Sample Data:</h3>";
    try {
        $stmt = $pdo->query("SELECT name, email, role FROM users LIMIT 3");
        $users = $stmt->fetchAll();
        foreach ($users as $user) {
            echo "User: " . $user['name'] . " (" . $user['email'] . ") - " . $user['role'] . "<br>";
        }
    } catch (Exception $e) {
        echo "Error fetching users: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>Database Connection: FAILED ❌</h2>";
    echo "Error: " . $e->getMessage();
}

echo "<br><br><a href='index.php'>Go to Main Site</a>";
?>