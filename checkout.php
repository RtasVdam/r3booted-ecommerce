<?php
$pageTitle = "Checkout";
include 'header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);
$shippingCost = 50.00;
$finalTotal = $cartTotal + $shippingCost;

if (empty($cartItems)) {
    setMessage('Your cart is empty.', 'error');
    redirect('cart.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingName = sanitizeInput($_POST['shipping_name'] ?? '');
    $shippingAddress = sanitizeInput($_POST['shipping_address'] ?? '');
    $shippingCity = sanitizeInput($_POST['shipping_city'] ?? '');
    $shippingPostal = sanitizeInput($_POST['shipping_postal'] ?? '');
    $shippingPhone = sanitizeInput($_POST['shipping_phone'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    $errors = [];
    
    if (empty($shippingName)) $errors[] = 'Full name is required.';
    if (empty($shippingAddress)) $errors[] = 'Address is required.';
    if (empty($shippingCity)) $errors[] = 'City is required.';
    if (empty($shippingPostal)) $errors[] = 'Postal code is required.';
    if (empty($shippingPhone)) $errors[] = 'Phone number is required.';
    if (!in_array($paymentMethod, ['card', 'cash'])) $errors[] = 'Please select a payment method.';
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_cost, shipping_name, shipping_address, 
                                  shipping_city, shipping_postal, shipping_phone, payment_method) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'], $cartTotal, $shippingCost, $shippingName, $shippingAddress,
                $shippingCity, $shippingPostal, $shippingPhone, $paymentMethod
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Add order items and update stock
            foreach ($cartItems as $item) {
                // Add to order_items
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update stock
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            
            setMessage('Order placed successfully! Thank you for your purchase.');
            redirect('order_success.php?order=' . $orderId);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'An error occurred while processing your order. Please try again.';
        }
    }
}
?>

<div class="page-content">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-content">
            <div class="checkout-form">
                <form method="POST" action="">
                    <div class="form-section">
                        <h3>Shipping Information</h3>
                        
                        <div class="form-group">
                            <label for="shipping_name">Full Name *</label>
                            <input type="text" id="shipping_name" name="shipping_name" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City *</label>
                                <input type="text" id="shipping_city" name="shipping_city" 
                                       value="<?php echo htmlspecialchars($_POST['shipping_city'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_postal">Postal Code *</label>
                                <input type="text" id="shipping_postal" name="shipping_postal" 
                                       value="<?php echo htmlspecialchars($_POST['shipping_postal'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_phone">Phone Number *</label>
                            <input type="tel" id="shipping_phone" name="shipping_phone" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Payment Method</h3>
                        
                        <div class="payment-options">
                            <div class="payment-option">
                                <label>
                                    <input type="radio" name="payment_method" value="card" 
                                           <?php echo (($_POST['payment_method'] ?? '') === 'card') ? 'checked' : ''; ?>>
                                    <span class="payment-label">💳 Credit/Debit Card</span>
                                </label>
                                <p class="payment-description">Secure online payment</p>
                            </div>
                            
                            <div class="payment-option">
                                <label>
                                    <input type="radio" name="payment_method" value="cash" 
                                           <?php echo (($_POST['payment_method'] ?? '') === 'cash') ? 'checked' : ''; ?>>
                                    <span class="payment-label">💰 Cash on Delivery</span>
                                </label>
                                <p class="payment-description">Pay when you receive your order</p>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-large">Place Order</button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <div class="summary-items">
                    <?php foreach ($cartItems as $item): 
                        $icon = strpos($item['name'], 'Phone') !== false || strpos($item['name'], 'iPhone') !== false || strpos($item['name'], 'Galaxy') !== false ? '📱' : 
                               (strpos($item['name'], 'MacBook') !== false || strpos($item['name'], 'Laptop') !== false || strpos($item['name'], 'XPS') !== false ? '💻' : '📱');
                    ?>
                        <div class="summary-item">
                            <div class="item-icon"><?php echo $icon; ?></div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($cartTotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span><?php echo formatPrice($shippingCost); ?></span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                        <span><strong>Total:</strong></span>
                        <span><strong><?php echo formatPrice($finalTotal); ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>