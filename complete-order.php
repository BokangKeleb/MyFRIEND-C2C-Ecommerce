<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if (($_SESSION['role'] ?? '') !== 'seller') {
    die('Access denied.');
}

$transactionID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$transactionID) {
    redirect_to('/seller-orders.php');
}

$sellerID = (int)$_SESSION['userID'];

$stmt = mysqli_prepare(
    $conn,
    'SELECT t.transactionID, t.paymentStatus, t.paymentMethod,
            t.fulfilmentMethod, t.orderStatus
     FROM transactions t
     JOIN products p ON p.productID = t.productID
     WHERE t.transactionID = ? AND p.sellerID = ?
     LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'ii', $transactionID, $sellerID);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    redirect_to('/seller-orders.php');
}

if ($order['orderStatus'] === 'Completed') {
    redirect_to('/seller-orders.php');
}

$isCashOrder = in_array(
    $order['paymentMethod'],
    ['Cash on Delivery', 'Cash on Collection'],
    true
);

if (!$isCashOrder && $order['paymentStatus'] !== 'Paid') {
    redirect_to('/seller-orders.php');
}

$deliveryStatus = $order['fulfilmentMethod'] === 'Collection'
    ? 'Collected'
    : 'Delivered';

if ($isCashOrder) {
    $update = mysqli_prepare(
        $conn,
        "UPDATE transactions
         SET paymentStatus = 'Paid',
             orderStatus = 'Completed',
             deliveryStatus = ?,
             paidAt = NOW()
         WHERE transactionID = ?"
    );
    mysqli_stmt_bind_param($update, 'si', $deliveryStatus, $transactionID);
} else {
    $update = mysqli_prepare(
        $conn,
        "UPDATE transactions
         SET orderStatus = 'Completed',
             deliveryStatus = ?
         WHERE transactionID = ?"
    );
    mysqli_stmt_bind_param($update, 'si', $deliveryStatus, $transactionID);
}

mysqli_stmt_execute($update);
redirect_to('/seller-orders.php');
