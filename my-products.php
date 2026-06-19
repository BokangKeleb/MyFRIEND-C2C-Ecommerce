<?php
require_once __DIR__ . '/config/app.php';

// Start session only if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config/database.php';

// Check login
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Only sellers can view their products
if ($_SESSION['role'] != 'seller') {
    die("Access Denied");
}

// Get current seller ID
$userID = $_SESSION['userID'];

// Get seller products
$sql = "SELECT * FROM products
        WHERE sellerID = '$userID'
        ORDER BY productID DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

    <h1 class="mb-4">My Products</h1>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'product_deleted') { ?>
        <div class="alert alert-success">Product deleted successfully.</div>
    <?php } ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'product_updated') { ?>
        <div class="alert alert-success">Product updated successfully.</div>
    <?php } ?>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="row">

            <?php while ($product = mysqli_fetch_assoc($result)) { ?>

                <div class="col-md-4 mb-4">

                    <div class="card product-card h-100">

                        <img src="<?php echo app_url('/uploads/' . rawurlencode($product['image'])); ?>" class="card-img-top" alt="Product Image">

                        <div class="card-body">

                            <h5><?php echo $product['title']; ?></h5>

                            <p>R<?php echo $product['price']; ?></p>

                            <?php if ((int)($product['availableQuantity'] ?? 0) > 0) { ?>
                                <p><strong>Available:</strong> <?php echo (int)$product['availableQuantity']; ?></p>
                            <?php } else { ?>
                                <p><span class="badge bg-danger">Out of Stock</span></p>
                            <?php } ?>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?php echo app_url('/edit-product.php?id=' . $product['productID']); ?>" class="btn btn-dark btn-sm">
                                    Edit
                                </a>

                                <a href="<?php echo app_url('/delete-product.php?id=' . $product['productID']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?');">
                                    Delete
                                </a>
                            </div>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>
        <div class="alert alert-secondary">You have not uploaded any products yet.</div>
    <?php } ?>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
