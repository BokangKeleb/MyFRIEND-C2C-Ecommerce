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

// Only admin can delete users
if ($_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// Check user ID
if (!isset($_GET['id'])) {
    redirect_to('/admin/users.php?error=no_user_selected');
}

// Get user ID
$userID = mysqli_real_escape_string($conn, $_GET['id']);

// Prevent admin from deleting their own account
if ($userID == $_SESSION['userID']) {
    redirect_to('/admin/users.php?error=cannot_delete_self');
}

// Delete transactions where this user is the buyer
$deleteBuyerTransactions = "DELETE FROM transactions WHERE buyerID = '$userID'";
mysqli_query($conn, $deleteBuyerTransactions);

// Delete transactions linked to products owned by this user
$deleteSellerTransactions = "DELETE transactions
                             FROM transactions
                             INNER JOIN products
                                ON transactions.productID = products.productID
                             WHERE products.sellerID = '$userID'";
mysqli_query($conn, $deleteSellerTransactions);

// Delete products owned by this user
$deleteProducts = "DELETE FROM products WHERE sellerID = '$userID'";
mysqli_query($conn, $deleteProducts);

// Delete user account
$deleteUser = "DELETE FROM users WHERE userID = '$userID'";
$result = mysqli_query($conn, $deleteUser);

// Redirect with message
if ($result) {
    redirect_to('/admin/users.php?success=user_deleted');
} else {
    redirect_to('/admin/users.php?error=delete_failed');
}

?>
