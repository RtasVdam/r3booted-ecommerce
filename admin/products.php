<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "Manage Products";
$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle image upload
function handleImageUpload($file) {
    global $uploadDir;
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/products/' . $filename;
    } else {
        throw new Exception('Failed to upload image.');
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $category_id = (int)$_POST['category_id'];
        
        $errors = [];
        if (empty($name)) $errors[] = 'Product name is required.';
        if (empty($description)) $errors[] = 'Description is required.';
        if ($price <= 0) $errors[] = 'Price must be greater than 0.';
        if ($stock < 0) $errors[] = 'Stock quantity cannot be negative.';
        if ($category_id <= 0) $errors[] = 'Please select a category.';
        
        $imagePath = null;
        
        if (empty($errors)) {
            try {
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $imagePath = handleImageUpload($_FILES['image']);
                }
                
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $imagePath]);
                setMessage('Product added successfully!');
                redirect('products.php');
            } catch (Exception $e) {
                $errors[] = 'Error adding product: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['update_product'])) {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $category_id = (int)$_POST['category_id'];
        $status = $_POST['status'];
        
        $errors = [];
        if (empty($name)) $errors[] = 'Product name is required.';
        if (empty($description)) $errors[] = 'Description is required.';
        if ($price <= 0) $errors[] = 'Price must be greater than 0.';
        if ($stock < 0) $errors[] = 'Stock quantity cannot be negative.';
        
        if (empty($errors)) {
            try {
                // Get current product data
                $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $currentProduct = $stmt->fetch();
                
                $imagePath = $currentProduct['image_url'];
                
                // Handle new image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $newImagePath = handleImageUpload($_FILES['image']);
                    
                    // Delete old image if exists
                    if ($imagePath && file_exists('../' . $imagePath)) {
                        unlink('../' . $imagePath);
                    }
                    
                    $imagePath = $newImagePath;
                }
                
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, status = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $status, $imagePath, $id]);
                setMessage('Product updated successfully!');
                redirect('products.php');
            } catch (Exception $e) {
                $errors[] = 'Error updating product: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            setMessage('Product deleted successfully!');
            redirect('products.php');
        } catch (Exception $e) {
            setMessage('Error deleting product: ' . $e->getMessage(), 'error');
        }
    }
    
    if (isset($_POST['delete_image'])) {
        $id = (int)$_POST['product_id'];
        try {
            // Get current image path
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if ($product && $product['image_url']) {
                // Delete image file
                if (file_exists('../' . $product['image_url'])) {
                    unlink('../' . $product['image_url']);
                }
                
                // Remove image from database
                $stmt = $pdo->prepare("UPDATE products SET image_url = NULL WHERE id = ?");
                $stmt->execute([$id]);
                
                setMessage('Product image deleted successfully!');
            }
        } catch (Exception $e) {
            setMessage('Error deleting image: ' . $e->getMessage(), 'error');
        }
        
        redirect('products.php?action=edit&id=' . $id);
    }
}

// Get categories for dropdown
$categories = getCategories();

// Get product for editing
$product = null;
if ($action === 'edit' && $productId) {
    $product = getProductById($productId);
}

// Get all products for listing
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
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
        
        .products-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .products-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 12px;
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
        
        /* Image Upload Styles */
        .image-upload-section {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .image-upload-section:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }
        
        .image-upload-section.dragover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        
        .current-image {
            margin-bottom: 20px;
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .image-preview {
            margin-top: 20px;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin: 10px;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .file-input-button:hover {
            background: #0056b3;
        }
        
        .upload-info {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .product-image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        .no-image-placeholder {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #dee2e6;
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
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="messages.php">Messages</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Product Form -->
            <div class="page-header">
                <h2><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h2>
                <a href="products.php" class="btn btn-secondary">← Back to Products</a>
            </div>
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Image Upload Section -->
                    <div class="image-upload-section" id="imageUploadSection">
                        <h4>Product Image</h4>
                        
                        <?php if ($action === 'edit' && $product['image_url']): ?>
                            <div class="current-image">
                                <p><strong>Current Image:</strong></p>
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current product image">
                                <div style="margin-top: 10px;">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?')">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_image" class="btn" style="background: #dc3545; padding: 5px 10px; font-size: 12px;">Delete Current Image</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" class="file-input" onchange="previewImage(this)">
                            <label for="image" class="file-input-button">
                                📷 Choose Image
                            </label>
                        </div>
                        
                        <div class="upload-info">
                            <p>Supported formats: JPEG, PNG, GIF, WebP</p>
                            <p>Maximum file size: 5MB</p>
                            <p>Recommended size: 800x800 pixels</p>
                        </div>
                        
                        <div id="imagePreview" class="image-preview" style="display: none;">
                            <p><strong>Preview:</strong></p>
                            <img id="previewImg" src="" alt="Image preview">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (R) *</label>
                            <input type="number" step="0.01" id="price" name="price" 
                                   value="<?php echo $product['price'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" 
                                   value="<?php echo $product['stock_quantity'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <?php if ($action === 'edit'): ?>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <?php if ($action === 'add'): ?>
                            <button type="submit" name="add_product" class="btn">Add Product</button>
                        <?php else: ?>
                            <button type="submit" name="update_product" class="btn">Update Product</button>
                        <?php endif; ?>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
        <?php else: ?>
            <!-- Product List -->
            <div class="page-header">
                <h2>Manage Products</h2>
                <a href="products.php?action=add" class="btn">+ Add New Product</a>
            </div>
            
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    No products found. <a href="products.php?action=add">Add your first product</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($product['image_url']): ?>
                                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-image-thumb">
                                        <?php else: ?>
                                            <div class="no-image-placeholder">📷</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br><small><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td>
                                        <?php if ($product['stock_quantity'] < 10): ?>
                                            <span style="color: #dc3545; font-weight: bold;">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        <?php else: ?>
                                            <?php echo $product['stock_quantity']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-<?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                               class="btn btn-secondary">Edit</a>
                                            
                                            <?php if ($product['status'] === 'active'): ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" name="delete_product" 
                                                            class="btn" style="background: #dc3545;">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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
    <script>
        // Image preview functionality
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Drag and drop functionality
        const uploadSection = document.getElementById('imageUploadSection');
        const fileInput = document.getElementById('image');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadSection.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            uploadSection.classList.add('dragover');
        }
        
        function unhighlight(e) {
            uploadSection.classList.remove('dragover');
        }
        
        uploadSection.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        }
    </script>
</body>
</html>