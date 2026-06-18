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

$cartID = filter_input(INPUT_POST, 'cartID', FILTER_VALIDATE_INT);
$userID = (int)$_SESSION['userID'];

if ($cartID) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM cart WHERE cartID = ? AND userID = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $cartID, $userID);
    mysqli_stmt_execute($stmt);
}

redirect_to('/cart.php?removed=1');
