<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/cart.php');
}

verify_csrf();

$userID = (int)$_SESSION['userID'];
$quantities = $_POST['quantities'] ?? [];
$stockAdjusted = false;

if (is_array($quantities)) {
    $getStock = mysqli_prepare(
        $conn,
        'SELECT p.availableQuantity
         FROM cart c
         JOIN products p ON p.productID = c.productID
         WHERE c.cartID = ? AND c.userID = ?
         LIMIT 1'
    );

    $update = mysqli_prepare(
        $conn,
        'UPDATE cart SET quantity = ? WHERE cartID = ? AND userID = ?'
    );

    $delete = mysqli_prepare(
        $conn,
        'DELETE FROM cart WHERE cartID = ? AND userID = ?'
    );

    foreach ($quantities as $cartID => $quantity) {
        $cartID = filter_var($cartID, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        if (!$cartID) {
            continue;
        }

        mysqli_stmt_bind_param($getStock, 'ii', $cartID, $userID);
        mysqli_stmt_execute($getStock);
        $stockRow = mysqli_fetch_assoc(mysqli_stmt_get_result($getStock));

        if (!$stockRow) {
            continue;
        }

        $availableQuantity = max(0, (int)$stockRow['availableQuantity']);

        if (!$quantity || $quantity < 1 || $availableQuantity < 1) {
            mysqli_stmt_bind_param($delete, 'ii', $cartID, $userID);
            mysqli_stmt_execute($delete);
            $stockAdjusted = true;
            continue;
        }

        if ($quantity > $availableQuantity) {
            $quantity = $availableQuantity;
            $stockAdjusted = true;
        }

        mysqli_stmt_bind_param($update, 'iii', $quantity, $cartID, $userID);
        mysqli_stmt_execute($update);
    }
}

redirect_to('/cart.php?updated=1' . ($stockAdjusted ? '&stock=adjusted' : ''));

