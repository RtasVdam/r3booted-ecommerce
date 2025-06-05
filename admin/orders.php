<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "Manage Orders";
$action = $_GET['action'] ?? 'list';
$orderId = $_GET['id'] ?? null;

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $validStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            setMessage('Order status updated successfully!');
        } catch (Exception $e) {
            setMessage('Error updating order status: ' . $e->getMessage(), 'error');
        }
    }
    
    redirect('orders.php');
}

// Get orders
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name, u.email as customer_email,
               COUNT(oi.id) as item_count
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
}

// Get single order details
if ($action === 'view' && $orderId) {
    // Get order
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
    }
}
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
        
        .orders-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1edfe;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .order-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-items-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .back-to-site {
            text-align: center;
            margin: 30px 0;
        }
        
        .status-form {
            display: inline-block;
            margin-left: 10px;
        }
        
        .status-form select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-row.total {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>R3Booted Admin - <?php echo $pageTitle; ?></h1>
        </div>
    </div>
    
    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($action === 'view' && isset($order)): ?>
            <!-- Order Details View -->
            <div class="page-header">
                <h2>Order #<?php echo $order['id']; ?> Details</h2>
                <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
            
            <div class="order-details">
                <div>
                    <!-- Order Information -->
                    <div class="order-card">
                        <h3>Order Information</h3>
                        <div style="margin: 20px 0;">
                            <strong>Order ID:</strong> #<?php echo $order['id']; ?><br>
                            <strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?><br>
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">Change Status</option>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="order-card">
                        <h3>Customer Information</h3>
                        <div style="margin: 20px 0;">
                            <strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?><br>
                            <strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="order-items-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span><?php echo formatPrice($order['total_amount']); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span><?php echo formatPrice($order['shipping_cost']); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span><?php echo formatPrice($order['total_amount'] + $order['shipping_cost']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <!-- Shipping Information -->
                    <div class="order-card">
                        <h3>Shipping Address</h3>
                        <div style="margin: 20px 0; line-height: 1.6;">
                            <?php echo htmlspecialchars($order['shipping_name']); ?><br>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                            <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_postal']); ?><br>
                            Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="order-card">
                        <h3>Payment Information</h3>
                        <div style="margin: 20px 0;">
                            <strong>Payment Method:</strong><br>
                            <?php if ($order['payment_method'] === 'card'): ?>
                                💳 Credit/Debit Card
                            <?php else: ?>
                                💰 Cash on Delivery
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Orders List -->
            <div class="page-header">
                <h2>All Orders</h2>
            </div>
            
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    No orders found yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                        <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> item(s)</td>
                                    <td><?php echo formatPrice($order['total_amount'] + $order['shipping_cost']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($order['payment_method'] === 'card'): ?>
                                            💳 Card
                                        <?php else: ?>
                                            💰 Cash
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" 
                                           class="btn btn-secondary btn-small">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="back-to-site">
            <a href="index.php" class="btn">← Back to Dashboard</a>
            <a href="../index.php" class="btn btn-secondary">← Back to Website</a>
        </div>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>