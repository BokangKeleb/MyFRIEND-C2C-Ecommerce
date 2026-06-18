<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/payment.php';

$post = $_POST;
$receivedSignature = $post['signature'] ?? '';
$calculatedSignature = payfast_signature($post, PAYFAST_PASSPHRASE);

if ($receivedSignature === '' || !hash_equals($calculatedSignature, $receivedSignature)) {
    http_response_code(400);
    exit('Invalid signature');
}

$reference = trim($post['m_payment_id'] ?? '');
$status = $post['payment_status'] ?? '';
$gatewayReference = $post['pf_payment_id'] ?? '';

if ($status === 'COMPLETE' && $reference !== '') {
    mysqli_begin_transaction($conn);

    try {
        $findBuyer = mysqli_prepare(
            $conn,
            'SELECT buyerID FROM transactions
             WHERE checkoutReference = ? OR transactionReference = ?
             LIMIT 1'
        );
        mysqli_stmt_bind_param($findBuyer, 'ss', $reference, $reference);
        mysqli_stmt_execute($findBuyer);
        $buyer = mysqli_fetch_assoc(mysqli_stmt_get_result($findBuyer));

        $update = mysqli_prepare(
            $conn,
            "UPDATE transactions
             SET paymentStatus = 'Paid', orderStatus = 'Processing',
                 gatewayReference = ?, paidAt = NOW()
             WHERE (checkoutReference = ? OR transactionReference = ?)
               AND paymentStatus <> 'Paid'"
        );
        mysqli_stmt_bind_param($update, 'sss', $gatewayReference, $reference, $reference);
        mysqli_stmt_execute($update);

        if ($buyer) {
            $buyerID = (int)$buyer['buyerID'];
            $clearCart = mysqli_prepare($conn, 'DELETE FROM cart WHERE userID = ?');
            mysqli_stmt_bind_param($clearCart, 'i', $buyerID);
            mysqli_stmt_execute($clearCart);
        }

        mysqli_commit($conn);
    } catch (Throwable $exception) {
        mysqli_rollback($conn);
        http_response_code(500);
        exit('Database error');
    }
}

http_response_code(200);
echo 'OK';
