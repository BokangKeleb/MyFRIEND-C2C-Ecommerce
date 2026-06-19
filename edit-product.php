<?php
require_once __DIR__ . '/config/app.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if ($_SESSION['role'] != 'seller') {
    die("Access Denied");
}

if (!isset($_GET['id'])) {
    redirect_to('/my-products.php');
}

$productID = mysqli_real_escape_string($conn, $_GET['id']);
$sellerID = (int)$_SESSION['userID'];

$sql = "SELECT * FROM products
        WHERE productID = '$productID'
        AND sellerID = '$sellerID'";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Access Denied");
}

if (isset($_POST['update'])) {

    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $price = mysqli_real_escape_string($conn, trim($_POST['price'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $category = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $availableQuantity = filter_input(INPUT_POST, 'availableQuantity', FILTER_VALIDATE_INT);

    if (!$availableQuantity && $availableQuantity !== 0) {
        $availableQuantity = 0;
    }

    if ($availableQuantity < 0) {
        $availableQuantity = 0;
    }

    if ($availableQuantity > 9999) {
        $availableQuantity = 9999;
    }

    $updateSQL = "UPDATE products
                  SET title = '$title',
                      price = '$price',
                      description = '$description',
                      category = '$category',
                      availableQuantity = '$availableQuantity'
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
            <input type="text" name="title" class="form-control" value="<?php echo h($product['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" min="0.01" name="price" class="form-control" value="<?php echo h($product['price']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Available Quantity</label>
            <input type="number" name="availableQuantity" min="0" max="9999" class="form-control" value="<?php echo (int)($product['availableQuantity'] ?? 0); ?>" required>
            <small class="text-muted">Set to 0 when the product is out of stock.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?php echo h($product['description']); ?></textarea>
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

