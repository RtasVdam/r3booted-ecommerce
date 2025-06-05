<?php
$pageTitle = "Order Successful";
include 'header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$orderId = (int)($_GET['order'] ?? 0);

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    setMessage('Order not found.', 'error');
    redirect('index.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
?>

<div class="page-content">
    <div class="container">
        <div class="order-success">
            <div class="success-icon">✅</div>
            <h1>Order Placed Successfully!</h1>
            <p class="success-message">Thank you for your purchase! Your order has been received and is being processed.</p>
            
            <div class="order-details">
                <h2>Order Details</h2>
                <div class="order-info">
                    <div class="order-summary-card">
                        <div class="order-header">
                            <div class="order-number">
                                <strong>Order #<?php echo $order['id']; ?></strong>
                            </div>
                            <div class="order-date">
                                <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="shipping-info">
                            <h4>Shipping Address</h4>
                            <div class="address">
                                <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                                <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_postal']); ?><br>
                                Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?>
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <h4>Payment Method</h4>
                            <div class="payment-method">
                                <?php if ($order['payment_method'] === 'card'): ?>
                                    💳 Credit/Debit Card
                                <?php else: ?>
                                    💰 Cash on Delivery
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items Ordered</h3>
                    <div class="items-list">
                        <?php foreach ($orderItems as $item): 
                            $icon = strpos($item['product_name'], 'Phone') !== false || strpos($item['product_name'], 'iPhone') !== false || strpos($item['product_name'], 'Galaxy') !== false ? '📱' : 
                                   (strpos($item['product_name'], 'MacBook') !== false || strpos($item['product_name'], 'Laptop') !== false || strpos($item['product_name'], 'XPS') !== false ? '💻' : '📱');
                        ?>
                            <div class="order-item">
                                <div class="item-icon"><?php echo $icon; ?></div>
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                    <div class="item-price">Price: <?php echo formatPrice($item['price']); ?></div>
                                </div>
                                <div class="item-total">
                                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Shipping:</span>
                            <span><?php echo formatPrice($order['shipping_cost']); ?></span>
                        </div>
                        <div class="total-row final-total">
                            <span><strong>Total:</strong></span>
                            <span><strong><?php echo formatPrice($order['total_amount'] + $order['shipping_cost']); ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Order Confirmation</h4>
                            <p>You'll receive an email confirmation shortly with your order details.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Processing</h4>
                            <p>We'll prepare your items for shipping within 1-2 business days.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Shipping</h4>
                            <p>Your order will be shipped and you'll receive tracking information.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Delivery</h4>
                            <p>Enjoy your new tech! Standard delivery takes 3-5 business days.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="success-actions">
                <a href="products.php" class="btn">Continue Shopping</a>
                <a href="index.php" class="btn btn-secondary">Return to Home</a>
            </div>
            
            <div class="support-info">
                <h4>Need Help?</h4>
                <p>If you have any questions about your order, please contact us:</p>
                <div class="contact-options">
                    <div class="contact-option">
                        <strong>📞 Phone:</strong> (+27) 782326445
                    </div>
                    <div class="contact-option">
                        <strong>✉️ Email:</strong> support@r3booted.com
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-success {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.success-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

.success-message {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 40px;
}

.order-details {
    text-align: left;
    margin-bottom: 40px;
}

.order-summary-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.order-number {
    font-size: 1.3rem;
}

.order-date {
    color: #666;
}

.status-badge {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-processing {
    background-color: #d1ecf1;
    color: #0c5460;
}

.status-shipped {
    background-color: #d4edda;
    color: #155724;
}

.shipping-info, .payment-info {
    margin-bottom: 25px;
}

.shipping-info h4, .payment-info h4 {
    margin-bottom: 10px;
    color: #333;
}

.address {
    line-height: 1.6;
    color: #666;
}

.payment-method {
    color: #666;
    font-weight: 500;
}

.order-items {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 40px;
}

.items-list {
    margin-bottom: 30px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
    border-bottom: none;
}

.item-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: bold;
    margin-bottom: 5px;
}

.item-quantity, .item-price {
    color: #666;
    font-size: 14px;
}

.item-total {
    font-weight: bold;
    color: #28a745;
    font-size: 1.1rem;
}

.order-totals {
    border-top: 2px solid #f0f0f0;
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.final-total {
    font-size: 1.2rem;
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 15px;
}

.next-steps {
    margin-bottom: 40px;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.step {
    text-align: center;
    padding: 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 15px;
}

.step-content h4 {
    margin-bottom: 10px;
    color: #333;
}

.step-content p {
    color: #666;
    font-size: 14px;
    margin: 0;
}

.success-actions {
    margin-bottom: 40px;
}

.success-actions .btn {
    margin: 0 10px;
}

.support-info {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
}

.contact-options {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 20px;
}

.contact-option {
    color: #666;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .steps {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .contact-options {
        flex-direction: column;
        gap: 15px;
    }
    
    .success-actions .btn {
        display: block;
        margin: 10px 0;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
}
</style>

<?php include 'footer.php'; ?>