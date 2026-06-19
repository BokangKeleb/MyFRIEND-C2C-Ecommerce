<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/payment.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

$buyerID = (int)$_SESSION['userID'];

$checkoutReference = trim($_GET['checkout'] ?? '');
$transactionID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

/*
 * PayFast Sandbox sometimes returns the buyer before the ITN update
 * reflects on free hosting.
 *
 * This fallback marks the buyer's own sandbox order as paid after
 * PayFast returns them to MyFriend.
 *
 * This only runs while PAYFAST_SANDBOX is true.
 */
if (defined('PAYFAST_SANDBOX') && PAYFAST_SANDBOX === true) {

    $gatewayReference = 'SANDBOX-RETURN-' . date('YmdHis');

    mysqli_begin_transaction($conn);

    try {

        if ($checkoutReference !== '') {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE transactions
                 SET
                    paymentStatus = 'Paid',
                    orderStatus = 'Processing',
                    deliveryStatus = 'Pending',
                    gatewayReference = ?,
                    paidAt = NOW()
                 WHERE checkoutReference = ?
                   AND buyerID = ?
                   AND paymentMethod IN ('Online Payment', 'PayFast Online Payment')
                   AND paymentStatus <> 'Paid'"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'ssi',
                $gatewayReference,
                $checkoutReference,
                $buyerID
            );

            mysqli_stmt_execute($stmt);
        } elseif ($transactionID) {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE transactions
                 SET
                    paymentStatus = 'Paid',
                    orderStatus = 'Processing',
                    deliveryStatus = 'Pending',
                    gatewayReference = ?,
                    paidAt = NOW()
                 WHERE transactionID = ?
                   AND buyerID = ?
                   AND paymentMethod IN ('Online Payment', 'PayFast Online Payment')
                   AND paymentStatus <> 'Paid'"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'sii',
                $gatewayReference,
                $transactionID,
                $buyerID
            );

            mysqli_stmt_execute($stmt);
        }

        $clearCart = mysqli_prepare(
            $conn,
            'DELETE FROM cart WHERE userID = ?'
        );

        mysqli_stmt_bind_param(
            $clearCart,
            'i',
            $buyerID
        );

        mysqli_stmt_execute($clearCart);

        mysqli_commit($conn);
    } catch (Throwable $exception) {

        mysqli_rollback($conn);
    }
}

if ($checkoutReference !== '') {
    redirect_to('/payment-success.php?checkout=' . urlencode($checkoutReference));
}

if ($transactionID) {
    redirect_to('/payment-success.php?id=' . (int)$transactionID);
}

redirect_to('/orders.php');

