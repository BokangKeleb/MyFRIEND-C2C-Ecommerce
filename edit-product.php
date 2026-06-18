<?php
require_once __DIR__ . '/config/app.php';

// Start session only if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Only sellers can edit products
if ($_SESSION['role'] != 'seller') {
    die("Access Denied");
}

// Check product ID
if (!isset($_GET['id'])) {
    redirect_to('/my-products.php');
}

// Get product ID and seller ID
$productID = mysqli_real_escape_string($conn, $_GET['id']);
$sellerID = $_SESSION['userID'];

// Get product belonging to current seller only
$sql = "SELECT * FROM products
        WHERE productID = '$productID'
        AND sellerID = '$sellerID'";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

// Stop if product is not found or does not belong to seller
if (!$product) {
    die("Access Denied");
}

// Update product
if (isset($_POST['update'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $updateSQL = "UPDATE products
                  SET title = '$title',
                      price = '$price',
                      description = '$description',
                      category = '$category'
                  WHERE productID = '$productID'
                  AND sellerID = '$sellerID'";

    mysqli_query($conn, $updateSQL);

    redirect_to('/my-products.php?success=product_updated');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

    <h1 class="mb-4">Edit Product</h1>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Product Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo $product['title']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?php echo $product['description']; ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-control" required>
                <option value="Clothing" <?php if ($product['category'] == 'Clothing') echo 'selected'; ?>>Clothing</option>
                <option value="Electronics" <?php if ($product['category'] == 'Electronics') echo 'selected'; ?>>Electronics</option>
                <option value="Food" <?php if ($product['category'] == 'Food') echo 'selected'; ?>>Food</option>
                <option value="Furniture" <?php if ($product['category'] == 'Furniture') echo 'selected'; ?>>Furniture</option>
                <option value="Accessories" <?php if ($product['category'] == 'Accessories') echo 'selected'; ?>>Accessories</option>
                <option value="Other" <?php if ($product['category'] == 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>

        <button type="submit" name="update" class="btn btn-shop">
            Update Product
        </button>

    </form>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
