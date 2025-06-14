<?php
define('SITE_NAME', 'R3Booted Technology');

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'R3Booted Technology');
}


$host = getenv("MYSQLHOST") ?: 'localhost';
$user = getenv("MYSQLUSER") ?: 'root';
$pass = getenv("MYSQLPASSWORD") ?: '';
$db   = getenv("MYSQLDATABASE") ?: 'r3booted_ecommerce';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


