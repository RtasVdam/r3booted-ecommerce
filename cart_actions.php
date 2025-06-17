<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add':
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            // Check if product exists and has stock
            $product = getProductById($productId);
            if (!$product) {
                setMessage('Product not found.', 'error');
                redirect('products.php');
            }
            
            if ($product['stock_quantity'] < $quantity) {
                setMessage('Not enough stock available.', 'error');
                redirect('products.php');
            }
            
            // Check if item already in cart
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                if ($newQuantity > $product['stock_quantity']) {
                    setMessage('Cannot add more items. Not enough stock.', 'error');
                    redirect('products.php');
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newQuantity, $userId, $productId]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, $quantity]);
            }
            
            setMessage($product['name'] . ' added to cart!');
            break;
            
        case 'update':
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($quantity <= 0) {
                // Remove item
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
                setMessage('Item removed from cart.');
            } else {
                // Check stock
                $product = getProductById($productId);
                if ($product && $quantity > $product['stock_quantity']) {
                    setMessage('Not enough stock available.', 'error');
                    redirect('cart.php');
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $userId, $productId]);
                setMessage('Cart updated.');
            }
            break;
            
        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            setMessage('Item removed from cart.');
            break;
            
        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            setMessage('Cart cleared.');
            break;
            
        default:
            setMessage('Invalid action.', 'error');
    }
    
} catch (Exception $e) {
    setMessage('An error occurred. Please try again.', 'error');
    error_log('Cart action error: ' . $e->getMessage());
}

// Redirect back
$redirect = $_POST['redirect'] ?? 'cart.php';
redirect($redirect);
?>