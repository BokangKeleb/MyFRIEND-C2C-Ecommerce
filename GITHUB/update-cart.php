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

if (is_array($quantities)) {
    $update = mysqli_prepare($conn, 'UPDATE cart SET quantity = ? WHERE cartID = ? AND userID = ?');
    $delete = mysqli_prepare($conn, 'DELETE FROM cart WHERE cartID = ? AND userID = ?');

    foreach ($quantities as $cartID => $quantity) {
        $cartID = filter_var($cartID, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        if (!$cartID) {
            continue;
        }

        if (!$quantity || $quantity < 1) {
            mysqli_stmt_bind_param($delete, 'ii', $cartID, $userID);
            mysqli_stmt_execute($delete);
            continue;
        }

        $quantity = min($quantity, 99);
        mysqli_stmt_bind_param($update, 'iii', $quantity, $cartID, $userID);
        mysqli_stmt_execute($update);
    }
}

redirect_to('/cart.php?updated=1');
