<?php require_once __DIR__ . '/../config/app.php'; ?>
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h5>MyFriend</h5>
                <p>A South African C2C marketplace connecting customers and informal traders.</p>
            </div>
            <div class="col-md-3 mb-3">
                <h6>Quick Links</h6>
                <p><a href="<?php echo app_url('/index.php'); ?>">Home</a></p>
                <p><a href="<?php echo app_url('/products.php'); ?>">Shop</a></p>
                <?php if (!isset($_SESSION['userID'])): ?>
                    <p><a href="<?php echo app_url('/register.php'); ?>">Register</a></p>
                    <p><a href="<?php echo app_url('/login.php'); ?>">Login</a></p>
                <?php endif; ?>
            </div>
            <div class="col-md-3 mb-3">
                <h6>Secure Trading</h6>
                <p>Account protection, order records and secure hosted payment support.</p>
            </div>
        </div>
        <hr>
        <p class="mb-0 text-center">&copy; 2026 MyFriend. All rights reserved.</p>
    </div>
</footer>