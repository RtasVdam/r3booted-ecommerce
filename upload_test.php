<?php
echo "<h1>Upload Directory Test</h1>";

$uploadDir = __DIR__ . '/uploads/products/';
$relativeDir = 'uploads/products/';

echo "<h2>Directory Information:</h2>";
echo "Full path: " . $uploadDir . "<br>";
echo "Relative path: " . $relativeDir . "<br>";
echo "Current working directory: " . getcwd() . "<br>";
echo "Script directory: " . __DIR__ . "<br><br>";

// Check if directory exists
if (file_exists($uploadDir)) {
    echo "✅ Directory exists<br>";
} else {
    echo "❌ Directory does not exist<br>";
    echo "Attempting to create directory...<br>";
    if (mkdir($uploadDir, 0775, true)) {
        echo "✅ Directory created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
}

// Check permissions
if (is_writable($uploadDir)) {
    echo "✅ Directory is writable<br>";
} else {
    echo "❌ Directory is not writable<br>";
}

// Get directory permissions
$perms = fileperms($uploadDir);
echo "Directory permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";

// Get owner information
$stat = stat($uploadDir);
echo "Directory owner UID: " . $stat['uid'] . "<br>";
echo "Directory group GID: " . $stat['gid'] . "<br>";

// Current user info
echo "Current process UID: " . getmyuid() . "<br>";
echo "Current process GID: " . getmygid() . "<br>";

// Test file creation
$testFile = $uploadDir . 'test_write.txt';
if (file_put_contents($testFile, 'test content')) {
    echo "✅ Can write files to directory<br>";
    unlink($testFile); // Clean up
} else {
    echo "❌ Cannot write files to directory<br>";
}

echo "<br><h2>PHP Upload Settings:</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";

echo "<br><a href='admin/products.php'>Go to Products Admin</a>";
?>