<?php
require_once 'config.php';
$currentUser = getCurrentUser();
$cartCount = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <header class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div>
                    <span>📞 (+27) 782326445</span>
                    <span style="margin-left: 20px;">✉️ support@r3booted.com</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="logo">
                    <a href="index.php">R3Booted</a>
                </div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Shop</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                <div class="auth-section">
                    <?php if (isLoggedIn()): ?>
                        <span>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?>!</span>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php">Admin Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login / Register</a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="cart-icon">
                        🛒
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notification Messages -->
    <?php if ($message): ?>
        <div class="notification <?php echo $message['type']; ?>" id="notification">
            <?php echo htmlspecialchars($message['message']); ?>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const notification = document.getElementById('notification');
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            });
        </script>
    <?php endif; ?>