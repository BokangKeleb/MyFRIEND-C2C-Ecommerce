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

// Check product ID
if (!isset($_GET['id'])) {
    redirect_to('/my-products.php');
}

// Get product ID and logged-in seller ID
$productID = mysqli_real_escape_string($conn, $_GET['id']);
$sellerID = $_SESSION['userID'];

// Check that product belongs to logged-in seller
$checkSQL = "SELECT * FROM products
             WHERE productID = '$productID'
             AND sellerID = '$sellerID'";
$checkResult = mysqli_query($conn, $checkSQL);

if (mysqli_num_rows($checkResult) == 0) {
    die("Access Denied");
}

// Delete transactions linked to this product first
$deleteTransactions = "DELETE FROM transactions WHERE productID = '$productID'";
mysqli_query($conn, $deleteTransactions);

// Delete product after linked transactions are removed
$deleteProduct = "DELETE FROM products
                  WHERE productID = '$productID'
                  AND sellerID = '$sellerID'";
mysqli_query($conn, $deleteProduct);

redirect_to('/my-products.php?success=product_deleted');

?>
