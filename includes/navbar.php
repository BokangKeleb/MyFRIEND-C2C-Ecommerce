<?php
require_once __DIR__ . '/../config/app.php';
ensure_session_started();

$cartCount = 0;
if (isset($_SESSION['userID']) && (($_SESSION['role'] ?? '') !== 'admin')) {
    require_once __DIR__ . '/../config/database.php';
    $cartTable = mysqli_query($conn, "SHOW TABLES LIKE 'cart'");
    if ($cartTable && mysqli_num_rows($cartTable) > 0) {
        $cartUserID = (int)$_SESSION['userID'];
        $cartStmt = mysqli_prepare($conn, 'SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE userID = ?');
        if ($cartStmt) {
            mysqli_stmt_bind_param($cartStmt, 'i', $cartUserID);
            mysqli_stmt_execute($cartStmt);
            $cartRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cartStmt));
            $cartCount = (int)($cartRow['total'] ?? 0);
        }
    }
}
?>
<nav class="navbar navbar-expand-lg custom-navbar sticky-top">
    <div class="container position-relative">
        <a class="navbar-brand navbar-logo mx-auto" href="<?php echo app_url('/index.php'); ?>">
            <img src="<?php echo app_url('/assets/images/logo.png?v=80'); ?>" class="logo-img" alt="MyFriend Logo">
        </a>

        <button class="navbar-toggler position-absolute end-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center mt-3" id="navbarNav">
            <ul class="navbar-nav text-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/index.php'); ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/products.php'); ?>">Shop</a></li>

                <?php if (isset($_SESSION['userID'])): ?>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin Dashboard</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo app_url('/admin/index.php'); ?>">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/admin/users.php'); ?>">Manage Users</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/admin/products.php'); ?>">Manage Products</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/admin/transactions.php'); ?>">Transactions</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/dashboard.php'); ?>">Account</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/admin/complaints.php'); ?>">Complaints</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/upload-product.php'); ?>">Sell</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo app_url('/cart.php'); ?>">
                                Cart <span class="badge rounded-pill text-bg-dark"><?php echo $cartCount; ?></span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Account</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo app_url('/dashboard.php'); ?>">My Account</a></li>
                                <li><a class="dropdown-item" href="<?php echo app_url('/orders.php'); ?>">My Orders</a></li>
                                <?php if (($_SESSION['role'] ?? '') === 'seller'): ?>
                                    <li><a class="dropdown-item" href="<?php echo app_url('/my-products.php'); ?>">My Products</a></li>
                                    <li><a class="dropdown-item" href="<?php echo app_url('/seller-orders.php'); ?>">Shop Orders</a></li>
                                <?php endif; ?>
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="<?php echo app_url('/contact-admin.php'); ?>">
                                        Contact Admin
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/logout.php'); ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/register.php'); ?>">Register</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo app_url('/login.php'); ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>