<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$loginEmail = '';
$registerName = '';
$registerEmail = '';

// Handle Login
if (isset($_POST['login'])) {
    $loginEmail = sanitizeInput($_POST['login_email']);
    $loginPassword = $_POST['login_password'];
    
    if (empty($loginEmail)) $errors[] = 'Email is required.';
    if (empty($loginPassword)) $errors[] = 'Password is required.';
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$loginEmail]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($loginPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            setMessage('Welcome back, ' . $user['name'] . '!');
            
            // Redirect to admin dashboard if admin
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect($_GET['redirect'] ?? 'index.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

// Handle Registration
if (isset($_POST['register'])) {
    $registerName = sanitizeInput($_POST['register_name']);
    $registerEmail = sanitizeInput($_POST['register_email']);
    $registerPassword = $_POST['register_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($registerName)) $errors[] = 'Name is required.';
    if (empty($registerEmail)) $errors[] = 'Email is required.';
    if (empty($registerPassword)) $errors[] = 'Password is required.';
    if ($registerPassword !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if (strlen($registerPassword) < 6) $errors[] = 'Password must be at least 6 characters long.';
    
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$registerEmail]);
        
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hashedPassword = password_hash($registerPassword, PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$registerName, $registerEmail, $hashedPassword]);
                
                setMessage('Registration successful! Please login with your credentials.');
                redirect('login.php');
            } catch (Exception $e) {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = "Login / Register";
include 'header.php';
?>

<div class="page-content">
    <div class="container">
        <h1 class="page-title">Login / Register</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="auth-container">
            <!-- Login Form -->
            <div class="auth-form">
                <div class="form-container">
                    <h3>Login</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="login_email">Email Address</label>
                            <input type="email" id="login_email" name="login_email" 
                                   value="<?php echo htmlspecialchars($loginEmail); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="login_password">Password</label>
                            <input type="password" id="login_password" name="login_password" required>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-full">Login</button>
                    </form>
                    
                    <div class="demo-credentials">
                        <h4>Demo Credentials:</h4>
                        <p><strong>Admin:</strong> admin@r3booted.com / admin123</p>
                        <p><strong>User:</strong> user@example.com / password123</p>
                    </div>
                </div>
            </div>
            
            <!-- Register Form -->
            <div class="auth-form">
                <div class="form-container">
                    <h3>Register</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="register_name">Full Name</label>
                            <input type="text" id="register_name" name="register_name" 
                                   value="<?php echo htmlspecialchars($registerName); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="register_email">Email Address</label>
                            <input type="email" id="register_email" name="register_email" 
                                   value="<?php echo htmlspecialchars($registerEmail); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="register_password">Password</label>
                            <input type="password" id="register_password" name="register_password" 
                                   minlength="6" required>
                            <small>Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-full">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>