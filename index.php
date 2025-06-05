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
                <img src="uploads/laptop-hero.png" alt="Professional Laptop" class="hero-laptop">
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
                // Use uploaded image or fallback to emoji
                $productImage = $product['image_url'] ? htmlspecialchars($product['image_url']) : null;
                $fallbackIcon = $product['category_slug'] === 'phone' ? '📱' : 
                               ($product['category_slug'] === 'laptop' ? '💻' : '📱');
            ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($productImage): ?>
                            <img src="<?php echo $productImage; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="font-size: 48px; color: #dee2e6;">
                                <?php echo $fallbackIcon; ?>
                            </div>
                        <?php endif; ?>
                    </div>
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

<style>
/* Enhanced Hero Section Styles */
.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 60px;
}

.hero-text {
    flex: 1;
    max-width: 600px;
}

.hero-image {
    flex: 0 0 450px;
    text-align: center;
    position: relative;
}

.hero-laptop {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
    transition: transform 0.3s ease;
    border-radius: 10px;
}

.hero-laptop:hover {
    transform: translateY(-10px) scale(1.02);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 40px;
    }
    
    .hero-image {
        flex: none;
        max-width: 100%;
    }
    
    .hero-laptop {
        max-width: 90%;
    }
}

/* Featured Products Styles */
.featured-products .product-image {
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
}

.featured-products .product-image img {
    transition: transform 0.3s ease;
}

.featured-products .product-card:hover .product-image img {
    transform: scale(1.05);
}

/* Animation for hero section */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-text {
    animation: fadeInUp 0.8s ease-out;
}

.hero-image {
    animation: fadeInUp 0.8s ease-out 0.2s both;
}
</style>

<?php include 'footer.php'; ?>