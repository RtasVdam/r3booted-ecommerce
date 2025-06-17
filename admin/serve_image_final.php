<?php
// serve_image.php - Serves images from /tmp directory

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    http_response_code(404);
    exit('File not found');
}

// Sanitize filename to prevent directory traversal
$filename = basename($filename);
$filepath = '/tmp/product_uploads/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    exit('File not found');
}

// Check if it's an image
$imageInfo = @getimagesize($filepath);
if ($imageInfo === false) {
    http_response_code(404);
    exit('Invalid image file');
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