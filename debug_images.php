<?php
require_once 'config.php';

echo "<h1>Image Debug Information</h1>";

// Get all products with images
$stmt = $pdo->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL ORDER BY created_at DESC LIMIT 10");
$products = $stmt->fetchAll();

echo "<h2>Products with Images:</h2>";
foreach ($products as $product) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
    echo "<p><strong>Database image_url:</strong> " . htmlspecialchars($product['image_url']) . "</p>";
    
    // Check if it's a tmp upload
    if (strpos($product['image_url'], 'tmp_uploads/') === 0) {
        $tmpFile = '/tmp/product_uploads/' . basename($product['image_url']);
        echo "<p><strong>File type:</strong> TMP upload</p>";
        echo "<p><strong>Expected file path:</strong> " . $tmpFile . "</p>";
        echo "<p><strong>File exists:</strong> " . (file_exists($tmpFile) ? 'YES' : 'NO') . "</p>";
        
        if (file_exists($tmpFile)) {
            echo "<p><strong>File size:</strong> " . filesize($tmpFile) . " bytes</p>";
            $imageInfo = getimagesize($tmpFile);
            if ($imageInfo) {
                echo "<p><strong>Image dimensions:</strong> " . $imageInfo[0] . "x" . $imageInfo[1] . "</p>";
                echo "<p><strong>MIME type:</strong> " . $imageInfo['mime'] . "</p>";
            }
        }
        
        $serveUrl = 'admin/serve_image.php?file=' . urlencode(basename($product['image_url']));
        echo "<p><strong>Serve URL:</strong> <a href='" . $serveUrl . "' target='_blank'>" . $serveUrl . "</a></p>";
        
        // Try to display the image
        echo "<div><strong>Image preview:</strong><br>";
        echo "<img src='" . $serveUrl . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ddd;' onerror='this.style.display=\"none\"; this.nextSibling.style.display=\"block\";'>";
        echo "<div style='display: none; color: red;'>❌ Image failed to load</div>";
        echo "</div>";
        
    } else {
        echo "<p><strong>File type:</strong> Regular upload</p>";
        echo "<p><strong>File exists:</strong> " . (file_exists($product['image_url']) ? 'YES' : 'NO') . "</p>";
        if (file_exists($product['image_url'])) {
            echo "<div><strong>Image preview:</strong><br>";
            echo "<img src='" . htmlspecialchars($product['image_url']) . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ddd;'>";
            echo "</div>";
        }
    }
    
    echo "</div>";
}

// Check /tmp directory
echo "<h2>/tmp Directory Check:</h2>";
$tmpDir = '/tmp/product_uploads/';
if (file_exists($tmpDir)) {
    echo "<p>✅ /tmp/product_uploads/ directory exists</p>";
    $files = scandir($tmpDir);
    $imageFiles = array_filter($files, function($file) use ($tmpDir) {
        return !in_array($file, ['.', '..']) && is_file($tmpDir . $file);
    });
    echo "<p><strong>Files in directory:</strong> " . count($imageFiles) . "</p>";
    foreach ($imageFiles as $file) {
        echo "<p>📁 " . $file . " (" . filesize($tmpDir . $file) . " bytes)</p>";
    }
} else {
    echo "<p>❌ /tmp/product_uploads/ directory does not exist</p>";
}

echo "<br><a href='products.php'>← Back to Products</a>";
echo "<br><a href='admin/products.php'>← Back to Admin</a>";
?>