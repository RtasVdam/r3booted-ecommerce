<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "Manage Products";
$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;

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
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $stock, $category_id]);
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
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $status, $id]);
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
                <form method="POST" action="">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
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
                            <th>ID</th>
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
                                    <td><?php echo $product['id']; ?></td>
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
</body>
</html>