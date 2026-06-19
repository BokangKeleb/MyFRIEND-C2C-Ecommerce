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

function stock_redirect(int $productID, string $returnTo, string $reason): void
{
    if ($returnTo === 'shop') {
        redirect_to('/products.php?cart=' . urlencode($reason));
    }

    redirect_to('/buy-product.php?id=' . $productID . '&stock=' . urlencode($reason));
}

if (!$productID) {
    redirect_to('/products.php?cart=invalid');
}

$quantity = $quantity && $quantity > 0 ? $quantity : 1;
$userID = (int)$_SESSION['userID'];

$check = mysqli_prepare(
    $conn,
    'SELECT sellerID, availableQuantity
     FROM products
     WHERE productID = ?
     LIMIT 1'
);
mysqli_stmt_bind_param($check, 'i', $productID);
mysqli_stmt_execute($check);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

if (!$product) {
    redirect_to('/products.php?cart=missing');
}

if ((int)$product['sellerID'] === $userID) {
    redirect_to('/products.php?cart=own');
}

$availableQuantity = max(0, (int)($product['availableQuantity'] ?? 0));

if ($availableQuantity < 1) {
    stock_redirect($productID, $returnTo, 'out');
}

$currentStmt = mysqli_prepare(
    $conn,
    'SELECT quantity
     FROM cart
     WHERE userID = ? AND productID = ?
     LIMIT 1'
