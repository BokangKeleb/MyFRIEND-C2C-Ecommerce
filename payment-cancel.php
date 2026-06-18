<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

$buyerID = (int)$_SESSION['userID'];
$checkoutReference = trim($_GET['checkout'] ?? '');
$transactionID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($checkoutReference !== '') {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE transactions
         SET paymentStatus = 'Failed', orderStatus = 'Payment Cancelled'
         WHERE checkoutReference = ? AND buyerID = ? AND paymentStatus = 'Pending'"
    );
    mysqli_stmt_bind_param($stmt, 'si', $checkoutReference, $buyerID);
    mysqli_stmt_execute($stmt);
} elseif ($transactionID) {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE transactions
         SET paymentStatus = 'Failed', orderStatus = 'Payment Cancelled'
         WHERE transactionID = ? AND buyerID = ? AND paymentStatus = 'Pending'"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $transactionID, $buyerID);
    mysqli_stmt_execute($stmt);
}

redirect_to('/orders.php?payment=cancelled');
