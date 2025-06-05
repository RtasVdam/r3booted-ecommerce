<!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <h4>R3Booted Technology</h4>
                    <p>For all your technological needs</p>
                    <p>&copy; 2025 All Rights Reserved Terms of R3Booted Technology LLC</p>
                </div>
                <div class="footer-links">
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="products.php">Shop</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>