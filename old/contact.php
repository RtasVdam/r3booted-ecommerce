<?php
$pageTitle = "Contact Us";
include 'header.php';

$errors = [];
$success = false;
$name = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (empty($subject)) $errors[] = 'Subject is required.';
    if (empty($message)) $errors[] = 'Message is required.';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $success = true;
            setMessage('Thank you for your message! We\'ll get back to you within 24 hours.');
            
            // Clear form data
            $name = $email = $subject = $message = '';
            
}
?>

<div class="page-content">
    <div class="container">
        <h1 class="page-title">Contact Us</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-content">
            <div class="contact-form">
                <div class="form-container">
                    <h3>Send us a Message</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-full">Send Message</button>
                    </form>
                </div>
            </div>
            
            <div class="contact-info">
                <h3>Get in Touch</h3>
                <p>We'd love to hear from you! Reach out to us with any questions, concerns, or feedback you may have.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-text">
                            <strong>Phone</strong><br>
                            (+27) 782326445
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-text">
                            <strong>Email</strong><br>
                            support@r3booted.com
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-text">
                            <strong>Location</strong><br>
                            Boksburg, Gauteng<br>
                            South Africa
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-text">
                            <strong>Business Hours</strong><br>
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 4:00 PM<br>
                            Sunday: Closed
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="#" class="social-link">📘 Facebook</a>
                        <a href="#" class="social-link">📸 Instagram</a>
                    </div>
                </div>
                
                <div class="faq-section">
                    <h4>Frequently Asked Questions</h4>
                    <div class="faq-item">
                        <strong>Q: What are your return policies?</strong>
                        <p>A: We offer a 30-day return policy for all new items and 14 days for refurbished items.</p>
                    </div>
                    <div class="faq-item">
                        <strong>Q: Do you offer warranties?</strong>
                        <p>A: Yes, all new products come with manufacturer warranty. Refurbished items have a 6-month warranty.</p>
                    </div>
                    <div class="faq-item">
                        <strong>Q: How long does shipping take?</strong>
                        <p>A: Standard shipping takes 3-5 business days within South Africa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>