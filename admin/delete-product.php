<?php
require_once dirname(__DIR__) . '/config/app.php';

// Start session only if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Only admin can delete products here
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// Check product ID
if (!isset($_GET['id'])) {
    redirect_to('/admin/products.php');
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Delete linked transactions first
$deleteTransactions = "DELETE FROM transactions WHERE productID = '$id'";
mysqli_query($conn, $deleteTransactions);

// Delete product
$sql = "DELETE FROM products WHERE productID = '$id'";
mysqli_query($conn, $sql);

redirect_to('/admin/products.php?success=product_deleted');

?>
