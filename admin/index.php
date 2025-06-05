<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "Admin Dashboard";

// Get dashboard statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$stats['products'] = $stmt->fetch()['total'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $stmt->fetch()['total'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch()['total'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(total_amount + shipping_cost) as revenue FROM orders WHERE status IN ('processing', 'shipped', 'delivered')");
$stats['revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->query("
    SELECT * FROM products 
    WHERE stock_quantity < 10 AND status = 'active' 
    ORDER BY stock_quantity ASC 
    LIMIT 5
");
$lowStockProducts = $stmt->fetchAll();

// Recent contact messages
$stmt = $pdo->query("
    SELECT * FROM contact_messages 
    WHERE status = 'new' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentMessages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-header {
            background: #343a40;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            text-align: center;
        }
        
        .admin-nav {
            background: #495057;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .admin-nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 0;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background-color: #007bff;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-card h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-meta {
            font-size: 14px;
            color: #666;
        }
        
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .quick-action {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .quick-action:hover {
            background: #0056b3;
            color: white;
        }
        
        .back-to-site {
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>R3Booted Admin Dashboard</h1>
        </div>
    </div>
    
    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <!-- Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['products']; ?></div>
                <div>Active Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['orders']; ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div>Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatPrice($stats['revenue']); ?></div>
                <div>Total Revenue</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="products.php?action=add" class="quick-action">
                <div style="font-size: 24px; margin-bottom: 10px;">➕</div>
                <div>Add Product</div>
            </a>
            <a href="orders.php" class="quick-action">
                <div style="font-size: 24px; margin-bottom: 10px;">📦</div>
                <div>View Orders</div>
            </a>
            <a href="users.php" class="quick-action">
                <div style="font-size: 24px; margin-bottom: 10px;">👥</div>
                <div>Manage Users</div>
            </a>
            <a href="messages.php" class="quick-action">
                <div style="font-size: 24px; margin-bottom: 10px;">💬</div>
                <div>View Messages</div>
            </a>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dashboard-grid">
            <!-- Recent Orders -->
            <div class="dashboard-card">
                <h3>Recent Orders</h3>
                <?php if (empty($recentOrders)): ?>
                    <p>No orders yet.</p>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="recent-item">
                            <div class="item-info">
                                <strong>Order #<?php echo $order['id']; ?></strong>
                                <div class="item-meta">
                                    <?php echo htmlspecialchars($order['customer_name']); ?> - 
                                    <?php echo formatPrice($order['total_amount'] + $order['shipping_cost']); ?>
                                </div>
                            </div>
                            <div>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                </div>
            </div>
            
            <!-- Low Stock Products -->
            <div class="dashboard-card">
                <h3>Low Stock Alert</h3>
                <?php if (empty($lowStockProducts)): ?>
                    <p>All products are well stocked.</p>
                <?php else: ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <div class="recent-item">
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <div class="item-meta">
                                    <span class="stock-warning">
                                        Only <?php echo $product['stock_quantity']; ?> left
                                    </span>
                                </div>
                            </div>
                            <div>
                                <?php echo formatPrice($product['price']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="products.php" class="btn btn-secondary">Manage Products</a>
                </div>
            </div>
            
            <!-- Recent Messages -->
            <div class="dashboard-card">
                <h3>New Messages</h3>
                <?php if (empty($recentMessages)): ?>
                    <p>No new messages.</p>
                <?php else: ?>
                    <?php foreach ($recentMessages as $message): ?>
                        <div class="recent-item">
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                <div class="item-meta">
                                    <?php echo htmlspecialchars($message['subject']); ?>
                                </div>
                            </div>
                            <div>
                                <small><?php echo date('M j', strtotime($message['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="messages.php" class="btn btn-secondary">View All Messages</a>
                </div>
            </div>
        </div>
        
        <div class="back-to-site">
            <a href="../index.php" class="btn">← Back to Website</a>
            <a href="../logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>