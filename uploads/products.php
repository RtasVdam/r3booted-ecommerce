<?php
$pageTitle = "Products";
include 'header.php';

$category = $_GET['category'] ?? 'all';
$products = getProductsByCategory($category === 'all' ? null : $category);
$categories = getCategories();
?>

<div class="page-content">
    <div class="container">
        <h1 class="page-title">Our Products</h1>
        
        <!-- Category Filter -->
        <div class="category-filter">
            <a href="products.php" class="btn <?php echo $category === 'all' ? 'active' : 'btn-secondary'; ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="products.php?category=<?php echo $cat['slug']; ?>" 
                   class="btn <?php echo $category === $cat['slug'] ? 'active' : 'btn-secondary'; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>No products found in this category.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): 
                    $icon = $product['category_slug'] === 'phone' ? '📱' : 
                           ($product['category_slug'] === 'laptop' ? '💻' : '📱');
                ?>
                    <div class="product-card">
                        <div class="product-image"><?php echo $icon; ?></div>
                        <div class="product-info">
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-stock">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="in-stock">In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isLoggedIn() && $product['stock_quantity'] > 0): ?>
                                <form action="cart_actions.php" method="POST" style="margin-top: 15px;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="quantity-selector">
                                        <label for="quantity_<?php echo $product['id']; ?>">Quantity:</label>
                                        <select name="quantity" id="quantity_<?php echo $product['id']; ?>">
                                            <?php for ($i = 1; $i <= min(10, $product['stock_quantity']); $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn" style="width: 100%;">Add to Cart</button>
                                </form>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="login.php" class="btn" style="width: 100%; text-align: center; display: block; text-decoration: none; margin-top: 15px;">Login to Purchase</a>
                            <?php else: ?>
                                <button class="btn btn-disabled" style="width: 100%; margin-top: 15px;" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>