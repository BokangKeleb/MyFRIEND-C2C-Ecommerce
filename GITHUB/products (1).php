<?php
require_once dirname(__DIR__) . '/config/app.php';

// Start session only if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">

    <h1>Admin Dashboard</h1>

    <p>Welcome, <?php echo $_SESSION['name']; ?></p>

    <div class="d-flex gap-3 mt-3 flex-wrap">

        <a href="<?php echo app_url('/admin/users.php'); ?>" class="btn btn-dark">
            Manage Users
        </a>

        <a href="<?php echo app_url('/admin/products.php'); ?>" class="btn btn-primary">
            Manage Products
        </a>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
