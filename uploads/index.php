<?php
$pageTitle = "Home";
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h4>Welcome To R3Booted Technology!</h4>
                <h1>Your new home for <span>good tech</span></h1>
                <p>We're passionate about connecting tech enthusiasts with devices they need. Our platform serves as a premier marketplace for buying and selling quality smartphones, computers, whether brand new or pre-owned.</p>
                <a href="products.php" class="btn">Shop Now</a>
            </div>
            <div class="hero-image">
                <div class="hero-icon">💻</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature">
                <h3>🛍 For All Your Selling Needs</h3>
                <p>Tech support needs for all devices.</p>
            </div>
            <div class="feature">
                <h3>📱 Mobile Devices</h3>
                <p>Find your next personal device from all brands.</p>
            </div>
            <div class="feature">
                <h3>🖥 Desktop</h3>
                <p>Find all your PC hardware, from CPUs to cases.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories">
    <div class="container">
        <h2>Categories</h2>
        <p>Find what you're looking for</p>
        <div class="category-grid">
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3>Tablets</h3>
                <a href="products.php?category=tablet" class="btn">Explore →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📞</div>
                <h3>Phones</h3>
                <a href="products.php?category=phone" class="btn">Explore →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">💻</div>
                <h3>Laptops</h3>
                <a href="products.php?category=laptop" class="btn">Explore →</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php
            $featuredProducts = getProductsByCategory();
            $featuredProducts = array_slice($featuredProducts, 0, 3); // Show only 3 products
            
            foreach ($featuredProducts as $product):
                $icon = $product['category_slug'] === 'phone' ? '📱' : 
                       ($product['category_slug'] === 'laptop' ? '💻' : '📱');
            ?>
                <div class="product-card">
                    <div class="product-image"><?php echo $icon; ?></div>
                    <div class="product-info">
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <?php if (isLoggedIn()): ?>
                            <form action="cart_actions.php" method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn" style="width: 100%;">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn" style="width: 100%; text-align: center; display: block; text-decoration: none;">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="products.php" class="btn btn-secondary">View All Products</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>