<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if (($_SESSION['role'] ?? '') === 'admin') {
    die('Administrators cannot add products to a cart.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/products.php');
}

verify_csrf();

$productID = filter_input(INPUT_POST, 'productID', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$returnTo = $_POST['returnTo'] ?? 'cart';

if (!$productID) {
    redirect_to('/products.php?cart=invalid');
}

$quantity = $quantity && $quantity > 0 ? min($quantity, 99) : 1;
$userID = (int)$_SESSION['userID'];

$check = mysqli_prepare($conn, 'SELECT sellerID FROM products WHERE productID = ? LIMIT 1');
mysqli_stmt_bind_param($check, 'i', $productID);
mysqli_stmt_execute($check);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

if (!$product) {
    redirect_to('/products.php?cart=missing');
}

if ((int)$product['sellerID'] === $userID) {
    redirect_to('/products.php?cart=own');
}

$sql = 'INSERT INTO cart (userID, productID, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), 99)';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $userID, $productID, $quantity);
mysqli_stmt_execute($stmt);

if ($returnTo === 'shop') {
    redirect_to('/products.php?cart=added');
}

redirect_to('/cart.php?added=1');
