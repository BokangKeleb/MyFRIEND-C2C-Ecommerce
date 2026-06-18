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

// Prevent admin from selling products
if ($_SESSION['role'] == 'admin') {
    die("Admins cannot sell products. Please use a personal seller account.");
}

// Store logged-in user ID
$userID = $_SESSION['userID'];

// Get logged-in user's shop details
$userSQL = "SELECT shop_name, role FROM users WHERE userID = '$userID'";
$userResult = mysqli_query($conn, $userSQL);
$userData = mysqli_fetch_assoc($userResult);

// If user has no shop name, send them to create shop first
if (empty($userData['shop_name'])) {
    redirect_to('/create-shop.php');
}

// Store shop name from user account
$shop_name = $userData['shop_name'];

// Check if upload form was submitted
if (isset($_POST['upload'])) {

    // Get form values safely
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Get image details
    $imageName = basename($_FILES['image']['name']);
    $tempName = $_FILES['image']['tmp_name'];

    // Add time to image name to avoid duplicate file names
    $imageName = time() . "_" . $imageName;

    // Set image upload path
    $folder = "uploads/" . $imageName;

    // Move image to uploads folder
    if (move_uploaded_file($tempName, $folder)) {

        // Insert product using sellerID and shop name from the user account
        $sql = "INSERT INTO products
                (title, description, price, image, category, shop_name, sellerID)
                VALUES
                ('$title', '$description', '$price', '$imageName', '$category', '$shop_name', '$userID')";

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

        <p class="mb-4">Shop: <strong><?php echo $shop_name; ?></strong></p>

        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
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
                <input type="number" step="0.01" name="price" class="form-control" required>
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