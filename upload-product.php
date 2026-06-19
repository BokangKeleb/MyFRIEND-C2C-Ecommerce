<?php
require_once __DIR__ . '/config/app.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if ($_SESSION['role'] == 'admin') {
    die("Admins cannot sell products. Please use a personal seller account.");
}

$userID = (int)$_SESSION['userID'];

$userSQL = "SELECT shop_name, role FROM users WHERE userID = '$userID'";
$userResult = mysqli_query($conn, $userSQL);
$userData = mysqli_fetch_assoc($userResult);

if (empty($userData['shop_name'])) {
    redirect_to('/create-shop.php');
}

$shop_name = $userData['shop_name'];

if (isset($_POST['upload'])) {

    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $price = mysqli_real_escape_string($conn, trim($_POST['price'] ?? ''));
    $category = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $availableQuantity = filter_input(INPUT_POST, 'availableQuantity', FILTER_VALIDATE_INT);

    if ($title === '' || $description === '' || $price === '' || $category === '') {
        $error = 'Please complete all product details.';
    } elseif (!$availableQuantity || $availableQuantity < 1) {
        $error = 'Please enter an available quantity of at least 1.';
    } elseif ($availableQuantity > 9999) {
        $error = 'The available quantity is too high.';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a product image.';
    } else {
        $imageName = basename($_FILES['image']['name']);
        $tempName = $_FILES['image']['tmp_name'];
        $imageName = time() . "_" . $imageName;
        $folder = "uploads/" . $imageName;

        if (move_uploaded_file($tempName, $folder)) {
            $sql = "INSERT INTO products
                    (title, description, price, availableQuantity, image, category, shop_name, sellerID)
                    VALUES
                    ('$title', '$description', '$price', '$availableQuantity', '$imageName', '$category', '$shop_name', '$userID')";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                $message = "Product uploaded successfully.";
            } else {
                $error = "Product could not be saved.";
            }
        } else {
            $error = "Image upload failed.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="alert alert-light border">
        <strong>Seller notice:</strong>
        You are responsible for arranging delivery or collection with the buyer.
        MyFriend does not transport products or calculate delivery fees.
    </div>

    <div class="container mt-5">

        <h1 class="mb-4">Upload Product</h1>

        <p class="mb-4">Shop: <strong><?php echo h($shop_name); ?></strong></p>

        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Product Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" min="0.01" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Available Quantity</label>
                <input type="number" name="availableQuantity" min="1" max="9999" value="1" class="form-control" required>
                <small class="text-muted">Buyers will not be able to buy more than this quantity.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Food">Food</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <input type="file" name="image" class="form-control" required>
            </div>

            <button type="submit" name="upload" class="btn btn-shop">
                Upload Product
            </button>

        </form>

    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
