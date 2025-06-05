<?php
require_once '../config.php';

if (!isAdmin()) {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$pageTitle = "View Messages";
$action = $_GET['action'] ?? 'list';
$messageId = $_GET['id'] ?? null;

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['message_id'];
        $status = $_POST['status'];
        
        $validStatuses = ['new', 'read', 'replied'];
        
        if (in_array($status, $validStatuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                setMessage('Message status updated successfully!');
            } catch (Exception $e) {
                setMessage('Error updating message status: ' . $e->getMessage(), 'error');
            }
        }
        redirect('messages.php');
    }
    
    if (isset($_POST['delete_message'])) {
        $id = (int)$_POST['message_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            setMessage('Message deleted successfully!');
        } catch (Exception $e) {
            setMessage('Error deleting message: ' . $e->getMessage(), 'error');
        }
        redirect('messages.php');
    }
    
    if (isset($_POST['reply_message'])) {
        $id = (int)$_POST['message_id'];
        $reply = sanitizeInput($_POST['reply']);
        
        if (!empty($reply)) {
            try {
                // Update message status to replied
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
                $stmt->execute([$id]);
                
                // In a real application, you would send an email here
                // For now, we'll just show a success message
                setMessage('Reply sent successfully! (Note: Email functionality not implemented in demo)');
            } catch (Exception $e) {
                setMessage('Error sending reply: ' . $e->getMessage(), 'error');
            }
        }
        redirect('messages.php?action=view&id=' . $id);
    }
}

// Get messages
if ($action === 'list') {
    $filter = $_GET['filter'] ?? 'all';
    
    $sql = "SELECT * FROM contact_messages";
    if ($filter !== 'all') {
        $sql .= " WHERE status = ?";
    }
    $sql .= " ORDER BY created_at DESC";
    
    if ($filter !== 'all') {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$filter]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    $messages = $stmt->fetchAll();
}

// Get single message
if ($action === 'view' && $messageId) {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();
    
    if ($message && $message['status'] === 'new') {
        // Mark as read
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$messageId]);
        $message['status'] = 'read';
    }
}

// Get message counts for filters
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
    FROM contact_messages
");
$messageCounts = $stmt->fetch();
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
        
        .messages-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .messages-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .messages-table th,
        .messages-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .messages-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-new {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-replied {
            background: #d4edda;
            color: #155724;
        }
        
        .message-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 20px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .message-detail {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .message-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .message-content {
            line-height: 1.6;
            padding: 20px 0;
            white-space: pre-wrap;
        }
        
        .reply-form {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
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
        
        .status-form {
            display: inline-block;
            margin-left: 10px;
        }
        
        .status-form select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .unread-row {
            background-color: #f8f9fa;
            font-weight: 500;
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
                <li><a href="users.php">Users</a></li>
                <li><a href="messages.php" class="active">Messages</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($action === 'view' && isset($message)): ?>
            <!-- Message Detail View -->
            <div class="page-header">
                <h2>Message Details</h2>
                <a href="messages.php" class="btn btn-secondary">← Back to Messages</a>
            </div>
            
            <div class="message-detail">
                <div class="message-header">
                    <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <span class="status-badge status-<?php echo $message['status']; ?>">
                            <?php echo ucfirst($message['status']); ?>
                        </span>
                        
                        <form method="POST" class="status-form">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">Change Status</option>
                                <option value="new" <?php echo $message['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $message['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $message['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </div>
                </div>
                
                <div class="message-meta">
                    <div>
                        <strong>From:</strong><br>
                        <?php echo htmlspecialchars($message['name']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                            <?php echo htmlspecialchars($message['email']); ?>
                        </a>
                    </div>
                    <div>
                        <strong>Date:</strong><br>
                        <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?>
                    </div>
                    <div>
                        <strong>Message ID:</strong><br>
                        #<?php echo $message['id']; ?>
                    </div>
                </div>
                
                <div class="message-content">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
                
                <?php if ($message['status'] !== 'replied'): ?>
                    <div class="reply-form">
                        <h4>Send Reply</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            
                            <div class="form-group">
                                <label for="reply">Your Reply:</label>
                                <textarea id="reply" name="reply" rows="6" placeholder="Type your reply here..." required></textarea>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="reply_message" class="btn">Send Reply</button>
                                <span style="margin-left: 15px; color: #666; font-size: 14px;">
                                    Reply will be sent to: <?php echo htmlspecialchars($message['email']); ?>
                                </span>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px; background: #d4edda; border-radius: 8px; margin-top: 20px; color: #155724;">
                        ✅ This message has been replied to.
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <form method="POST" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this message?')">
                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                        <button type="submit" name="delete_message" 
                                class="btn" style="background: #dc3545;">Delete Message</button>
                    </form>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Messages List -->
            <div class="page-header">
                <h2>Contact Messages</h2>
                <div>
                    <span style="margin-right: 20px;">
                        Total: <strong><?php echo $messageCounts['total']; ?></strong>
                    </span>
                    <?php if ($messageCounts['new_count'] > 0): ?>
                        <span style="color: #856404;">
                            New: <strong><?php echo $messageCounts['new_count']; ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Message Filters -->
            <div class="message-filters">
                <a href="messages.php?filter=all" 
                   class="filter-btn <?php echo ($_GET['filter'] ?? 'all') === 'all' ? 'active' : ''; ?>">
                    All (<?php echo $messageCounts['total']; ?>)
                </a>
                <a href="messages.php?filter=new" 
                   class="filter-btn <?php echo ($_GET['filter'] ?? '') === 'new' ? 'active' : ''; ?>">
                    New (<?php echo $messageCounts['new_count']; ?>)
                </a>
                <a href="messages.php?filter=read" 
                   class="filter-btn <?php echo ($_GET['filter'] ?? '') === 'read' ? 'active' : ''; ?>">
                    Read (<?php echo $messageCounts['read_count']; ?>)
                </a>
                <a href="messages.php?filter=replied" 
                   class="filter-btn <?php echo ($_GET['filter'] ?? '') === 'replied' ? 'active' : ''; ?>">
                    Replied (<?php echo $messageCounts['replied_count']; ?>)
                </a>
            </div>
            
            <div class="messages-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No messages found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo $message['status'] === 'new' ? 'unread-row' : ''; ?>">
                                    <td>#<?php echo $message['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                        <?php if ($message['status'] === 'new'): ?>
                                            <span style="color: #007bff; font-weight: bold;">●</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($message['subject']); ?><br>
                                        <small style="color: #666;">
                                            <?php echo substr(htmlspecialchars($message['message']), 0, 60) . '...'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($message['created_at'])); ?><br>
                                        <small><?php echo date('g:i A', strtotime($message['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="messages.php?action=view&id=<?php echo $message['id']; ?>" 
                                               class="btn btn-secondary btn-small">View</a>
                                            
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <button type="submit" name="delete_message" 
                                                        class="btn btn-small" style="background: #dc3545;">Delete</button>
                                            </form>
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