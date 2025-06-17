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
                    <a href="products.php" class="btn">View All Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): 
                    // Use uploaded image or fallback to emoji based on category
                    $productImage = null;
                    if (!empty($product['image_url'])) {
                        // With base64 storage, image_url contains the full data URL
                        $productImage = htmlspecialchars($product['image_url']);
                    }
                    
                    // Set fallback icon based on category
                    $fallbackIcon = '📱'; // Default
                    if (isset($product['category_slug'])) {
                        switch($product['category_slug']) {
                            case 'phone':
                            case 'phones':
                                $fallbackIcon = '📱';
                                break;
                            case 'laptop':
                            case 'laptops':
                                $fallbackIcon = '💻';
                                break;
                            case 'tablet':
                            case 'tablets':
                                $fallbackIcon = '📱';
                                break;
                            default:
                                $fallbackIcon = '📱';
                        }
                    } elseif (isset($product['category_name'])) {
                        // Fallback if category_slug is not available
                        $categoryName = strtolower($product['category_name']);
                        if (strpos($categoryName, 'laptop') !== false) {
                            $fallbackIcon = '💻';
                        } elseif (strpos($categoryName, 'tablet') !== false) {
                            $fallbackIcon = '📱';
                        } else {
                            $fallbackIcon = '📱';
                        }
                    }
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($productImage): ?>
                                <img src="<?php echo $productImage; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="fallback-icon" style="display: none; font-size: 48px; color: #dee2e6; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                    <?php echo $fallbackIcon; ?>
                                </div>
                            <?php else: ?>
                                <div class="fallback-icon" style="font-size: 48px; color: #dee2e6;">
                                    <?php echo $fallbackIcon; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-category">
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                            </div>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <div class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?>
                            </div>
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
                                    <input type="hidden" name="redirect" value="products.php?category=<?php echo urlencode($category); ?>">
                                    
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
                                <a href="login.php?redirect=<?php echo urlencode('products.php?category=' . $category); ?>" 
                                   class="btn" style="width: 100%; text-align: center; display: block; text-decoration: none; margin-top: 15px;">
                                   Login to Purchase
                                </a>
                            <?php else: ?>
                                <button class="btn btn-disabled" style="width: 100%; margin-top: 15px;" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($products) && count($products) >= 6): ?>
            <div class="load-more" style="text-align: center; margin-top: 40px;">
                <p>Showing <?php echo count($products); ?> products</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Enhanced Product Card Styles for Images */
.product-image {
    width: 100%;
    height: 220px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-info {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
    line-height: 1.3;
}

.product-category {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-weight: 500;
}

.product-price {
    font-size: 1.4rem;
    color: #28a745;
    font-weight: bold;
    margin-bottom: 10px;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 15px;
    flex: 1;
}

.product-stock {
    margin-bottom: 15px;
}

.in-stock {
    color: #28a745;
    font-weight: 500;
    font-size: 0.9rem;
}

.out-of-stock {
    color: #dc3545;
    font-weight: 500;
    font-size: 0.9rem;
}

.quantity-selector {
    margin-bottom: 15px;
}

.quantity-selector label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    font-size: 0.9rem;
}

.quantity-selector select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.category-filter {
    text-align: center;
    margin-bottom: 40px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}

.category-filter .btn {
    margin: 5px;
    padding: 10px 20px;
    font-size: 14px;
}

.no-products {
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.no-products p {
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.fallback-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
}

/* Grid responsiveness */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .category-filter {
        flex-direction: column;
        align-items: center;
    }
    
    .category-filter .btn {
        margin: 5px 0;
        width: 200px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-title {
        font-size: 1.1rem;
    }
    
    .product-price {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .product-card {
        max-width: 100%;
    }
}
</style>

<script>
// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to add to cart buttons
    const addToCartButtons = document.querySelectorAll('form[action="cart_actions.php"] button[type="submit"]');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = 'Adding...';
            this.disabled = true;
            
            // Re-enable after form submission
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
});
</script>

<?php include 'footer.php'; ?>