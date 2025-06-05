<?php
$pageTitle = "Shopping Cart";
include 'header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);
$shippingCost = 50.00;
$finalTotal = $cartTotal + $shippingCost;
?>

<div class="page-content">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="products.php" class="btn">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): 
                        $icon = strpos($item['name'], 'Phone') !== false || strpos($item['name'], 'iPhone') !== false || strpos($item['name'], 'Galaxy') !== false ? '📱' : 
                               (strpos($item['name'], 'MacBook') !== false || strpos($item['name'], 'Laptop') !== false || strpos($item['name'], 'XPS') !== false ? '💻' : '📱');
                    ?>
                        <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="cart-item-image"><?php echo $icon; ?></div>
                            <div class="cart-item-info">
                                <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-price"><?php echo formatPrice($item['price']); ?></div>
                                <div class="quantity-controls">
                                    <form action="cart_actions.php" method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                        <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>" class="quantity-input">
                                    </form>
                                </div>
                                <div class="item-total">
                                    Subtotal: <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <form action="cart_actions.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-small" onclick="return confirm('Remove this item from cart?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal"><?php echo formatPrice($cartTotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span><?php echo formatPrice($shippingCost); ?></span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                        <span><strong>Total:</strong></span>
                        <span><strong id="cart-total"><?php echo formatPrice($finalTotal); ?></strong></span>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                        <a href="checkout.php" class="btn">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(productId, change) {
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    const quantityDisplay = cartItem.querySelector('.quantity-display');
    const quantityInput = cartItem.querySelector('.quantity-input');
    const quantityForm = cartItem.querySelector('.quantity-form');
    
    let currentQuantity = parseInt(quantityDisplay.textContent);
    let newQuantity = currentQuantity + change;
    
    if (newQuantity <= 0) {
        if (confirm('Remove this item from cart?')) {
            // Submit remove form
            const removeForm = cartItem.querySelector('form[action="cart_actions.php"] input[value="remove"]').closest('form');
            removeForm.submit();
        }
        return;
    }
    
    quantityDisplay.textContent = newQuantity;
    quantityInput.value = newQuantity;
    quantityForm.submit();
}
</script>

<?php include 'footer.php'; ?>