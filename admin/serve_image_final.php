<?php
// serve_image.php - Serves images from /tmp directory

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    http_response_code(404);
    die('Error: No file specified');
}

// Sanitize filename to prevent directory traversal
$filename = basename($filename);
$filepath = '/tmp/product_uploads/' . $filename;

// Debug information (remove this later)
if (isset($_GET['debug'])) {
    echo "Debug Info:<br>";
    echo "Requested file: " . htmlspecialchars($filename) . "<br>";
    echo "Full path: " . htmlspecialchars($filepath) . "<br>";
    echo "File exists: " . (file_exists($filepath) ? 'YES' : 'NO') . "<br>";
    
    if (file_exists($filepath)) {
        echo "File size: " . filesize($filepath) . " bytes<br>";
        $imageInfo = @getimagesize($filepath);
        if ($imageInfo) {
            echo "Image info: " . $imageInfo[0] . "x" . $imageInfo[1] . " - " . $imageInfo['mime'] . "<br>";
        } else {
            echo "Not a valid image file<br>";
        }
    }
    
    // List all files in directory
    echo "<br>Files in /tmp/product_uploads/:<br>";
    if (is_dir('/tmp/product_uploads/')) {
        $files = scandir('/tmp/product_uploads/');
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "- " . htmlspecialchars($file) . "<br>";
            }
        }
    } else {
        echo "Directory does not exist<br>";
    }
    exit;
}

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    die('Error: File not found - ' . htmlspecialchars($filename));
}

// Check if it's an image
$imageInfo = @getimagesize($filepath);
if ($imageInfo === false) {
    http_response_code(404);
    die('Error: Invalid image file - ' . htmlspecialchars($filename));
}

// Set appropriate headers
$mimeType = $imageInfo['mime'];
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filepath)) . ' GMT');

// Output the image
readfile($filepath);
exit();
?>