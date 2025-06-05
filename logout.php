<?php
require_once 'config.php';

// Destroy session and redirect
session_destroy();
setMessage('You have been logged out successfully.');
redirect('index.php');
?>