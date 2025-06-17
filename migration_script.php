<?php
require_once 'config.php';

echo "<h1>Database Migration Script</h1>";

try {
    // Update the image_url column to support base64 data
    $pdo->exec("ALTER TABLE products MODIFY COLUMN image_url LONGTEXT");
    echo "<p style='color: green;'>✅ SUCCESS: image_url column updated to LONGTEXT</p>";
    
    // Check the new column definition
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll();
    
    echo "<h2>Updated Table Structure:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Migration completed successfully!</strong></p>";
    echo "<p><a href='admin/products.php'>Go to Admin Products</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>