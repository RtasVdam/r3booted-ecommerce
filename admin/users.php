<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "Manage Users";
$action = $_GET['action'] ?? 'list';
$userId = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user_role'])) {
        $id = (int)$_POST['user_id'];
        $role = $_POST['role'];
        
        if (in_array($role, ['user', 'admin'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $id]);
                setMessage('User role updated successfully!');
            } catch (Exception $e) {
                setMessage('Error updating user role: ' . $e->getMessage(), 'error');
            }
        }
        redirect('users.php');
    }
    
    if (isset($_POST['add_user'])) {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (empty($password)) $errors[] = 'Password is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if (!in_array($role, ['user', 'admin'])) $errors[] = 'Invalid role selected.';
        
        if (empty($errors)) {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists.';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hashedPassword, $role]);
                    setMessage('User created successfully!');
                    redirect('users.php');
                } catch (Exception $e) {
                    $errors[] = 'Error creating user: ' . $e->getMessage();
                }
            }
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $id = (int)$_POST['user_id'];
        
        // Don't allow deleting the current admin
        if ($id == $_SESSION['user_id']) {
            setMessage('You cannot delete your own account.', 'error');
        } else {
            try {
                // Check if user has orders
                $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
                $stmt->execute([$id]);
                $orderCount = $stmt->fetch()['order_count'];
                
                if ($orderCount > 0) {
                    setMessage('Cannot delete user with existing orders. Consider deactivating instead.', 'error');
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    setMessage('User deleted successfully!');
                }
            } catch (Exception $e) {
                setMessage('Error deleting user: ' . $e->getMessage(), 'error');
            }
        }
        redirect('users.php');
    }
}

// Get users with order statistics
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(o.id) as order_count,
               COALESCE(SUM(o.total_amount + o.shipping_cost), 0) as total_spent,
               MAX(o.created_at) as last_order
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
}

// Get user details
if ($action === 'view' && $userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Get user's orders
        $stmt = $pdo->prepare("
            SELECT o.*, COUNT(oi.id) as item_count
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        $userOrders = $stmt->fetchAll();
    }
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
        
        .users-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .role-admin {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .role-user {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .user-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .back-to-site {
            text-align: center;
            margin: 30px 0;
        }
        
        .role-form {
            display: inline-block;
            margin-left: 10px;
        }
        
        .role-form select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
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
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php" class="active">Users</a></li>
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
        
        <?php if ($action === 'add'): ?>
            <!-- Add User Form -->
            <div class="page-header">
                <h2>Add New User</h2>
                <a href="users.php" class="btn btn-secondary">← Back to Users</a>
            </div>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                        <small>Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="user" <?php echo ($_POST['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_user" class="btn">Create User</button>
                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
        <?php elseif ($action === 'view' && isset($user)): ?>
            <!-- User Details View -->
            <div class="page-header">
                <h2><?php echo htmlspecialchars($user['name']); ?>'s Profile</h2>
                <a href="users.php" class="btn btn-secondary">← Back to Users</a>
            </div>
            
            <div class="user-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($userOrders); ?></div>
                    <div>Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalSpent = array_sum(array_column($userOrders, 'total_amount'));
                        echo formatPrice($totalSpent); 
                        ?>
                    </div>
                    <div>Total Spent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo !empty($userOrders) ? date('M j, Y', strtotime($userOrders[0]['created_at'])) : 'Never'; ?>
                    </div>
                    <div>Last Order</div>
                </div>
            </div>
            
            <div class="user-details">
                <div class="user-card">
                    <h3>User Information</h3>
                    <div style="margin: 20px 0;">
                        <strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                        <strong>Role:</strong> 
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span><br>
                        <strong>Registered:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                    </div>
                </div>
                
                <div class="user-card">
                    <h3>Recent Orders</h3>
                    <?php if (empty($userOrders)): ?>
                        <p>No orders yet.</p>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach (array_slice($userOrders, 0, 5) as $order): ?>
                                <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <strong>Order #<?php echo $order['id']; ?></strong> - 
                                    <?php echo formatPrice($order['total_amount'] + $order['shipping_cost']); ?><br>
                                    <small>
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?> - 
                                        <?php echo $order['item_count']; ?> items - 
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Users List -->
            <div class="page-header">
                <h2>All Users</h2>
                <a href="users.php?action=add" class="btn">+ Add New User</a>
            </div>
            
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <small style="color: #007bff;">(You)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="role-form">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="role" onchange="this.form.submit()">
                                                    <option value="">Change Role</option>
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <input type="hidden" name="update_user_role" value="1">
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['order_count']; ?></td>
                                    <td><?php echo formatPrice($user['total_spent']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="users.php?action=view&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-secondary btn-small">View</a>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id'] && $user['order_count'] == 0): ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" 
                                                            class="btn btn-small" style="background: #dc3545;">Delete</button>
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