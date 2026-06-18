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
$isCartCheckout = $checkoutReference !== '';

if (!$isCartCheckout && !$transactionID) {
    redirect_to('/orders.php');
}

if ($isCartCheckout && !preg_match('/^[A-Za-z0-9-]{1,80}$/', $checkoutReference)) {
    die('Invalid checkout reference.');
}

if ($isCartCheckout) {
    $sql = 'SELECT t.*, p.title
            FROM transactions t
            JOIN products p ON p.productID = t.productID
            WHERE t.checkoutReference = ? AND t.buyerID = ?
            ORDER BY t.transactionID ASC';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $checkoutReference, $buyerID);
} else {
    $sql = 'SELECT t.*, p.title
            FROM transactions t
            JOIN products p ON p.productID = t.productID
            WHERE t.transactionID = ? AND t.buyerID = ?
            LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $transactionID, $buyerID);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
$total = 0.0;
$allPaid = true;

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
    $total += (float)$row['amount'];
    if ($row['paymentStatus'] !== 'Paid') {
        $allPaid = false;
    }
}

if (!$orders) {
    die('Transaction not found.');
}

$primaryOrder = $orders[0];

$onlinePaymentMethods = ['Online Payment', 'PayFast Online Payment'];

if (!in_array($primaryOrder['paymentMethod'], $onlinePaymentMethods, true)) {
    redirect_to('/orders.php');
}

$paymentReference = $isCartCheckout
    ? $checkoutReference
    : ($primaryOrder['transactionReference'] ?: ('MF-' . $primaryOrder['transactionID']));

if ($allPaid) {
    $successQuery = $isCartCheckout
        ? '/payment-success.php?checkout=' . urlencode($checkoutReference)
        : '/payment-success.php?id=' . (int)$transactionID;
    redirect_to($successQuery);
}

$cardHolder = '';
$cardNumber = '';
$expiry = '';
$error = '';

if (payment_is_demo() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $cardHolder = trim($_POST['cardHolder'] ?? '');
    $cardNumber = preg_replace('/\D/', '', $_POST['cardNumber'] ?? '');
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv = preg_replace('/\D/', '', $_POST['cvv'] ?? '');

    if (mb_strlen($cardHolder) < 2) {
        $error = 'Please enter the name shown on the card.';
    } elseif (!preg_match('/^\d{13,19}$/', $cardNumber)) {
        $error = 'Please enter a card number containing between 13 and 19 digits.';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
        $error = 'Enter the expiry date using the MM/YY format.';
    } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $error = 'Please enter a three- or four-digit CVV.';
    } else {
        [$expiryMonth, $expiryYear] = explode('/', $expiry);

        $expiryMonth = (int)$expiryMonth;
        $expiryYear = 2000 + (int)$expiryYear;

        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');

        if (
            $expiryYear < $currentYear
            || ($expiryYear === $currentYear && $expiryMonth < $currentMonth)
        ) {
            $error = 'The entered card has expired.';
        }
    }

    if ($error === '') {
        $gatewayReference = 'DEMO-' . strtoupper(bin2hex(random_bytes(4)));
        mysqli_begin_transaction($conn);

        try {
            if ($isCartCheckout) {
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE transactions
                     SET paymentStatus = 'Paid', orderStatus = 'Processing',
                         deliveryStatus = 'Pending', gatewayReference = ?, paidAt = NOW()
                     WHERE checkoutReference = ? AND buyerID = ?"
                );
                mysqli_stmt_bind_param($update, 'ssi', $gatewayReference, $checkoutReference, $buyerID);
            } else {
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE transactions
                     SET paymentStatus = 'Paid', orderStatus = 'Processing',
                         deliveryStatus = 'Pending', gatewayReference = ?, paidAt = NOW()
                     WHERE transactionID = ? AND buyerID = ?"
                );
                mysqli_stmt_bind_param($update, 'sii', $gatewayReference, $transactionID, $buyerID);
            }

            mysqli_stmt_execute($update);

            // The cart is cleared only after the payment succeeds.
            if ($isCartCheckout) {
                $clearCart = mysqli_prepare($conn, 'DELETE FROM cart WHERE userID = ?');
                mysqli_stmt_bind_param($clearCart, 'i', $buyerID);
                mysqli_stmt_execute($clearCart);
            }

            mysqli_commit($conn);

            $successQuery = $isCartCheckout
                ? '/payment-success.php?checkout=' . urlencode($checkoutReference)
                : '/payment-success.php?id=' . (int)$transactionID;
            redirect_to($successQuery);
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $error = 'The payment could not be completed. Please try again.';
        }
    }
}

if (!payment_is_demo()) {
    if (PAYFAST_MERCHANT_ID === '' || PAYFAST_MERCHANT_KEY === '') {
        die('PayFast credentials are missing in config/payment.php.');
    }

    $returnParameter = $isCartCheckout
        ? 'checkout=' . urlencode($checkoutReference)
        : 'id=' . (int)$transactionID;

    $itemNames = array_map(
        static fn(array $order): string => $order['title'] . ' x' . (int)($order['quantity'] ?? 1),
        $orders
    );

    $data = [
        'merchant_id' => PAYFAST_MERCHANT_ID,
        'merchant_key' => PAYFAST_MERCHANT_KEY,
        'return_url' => absolute_url('/payment-return.php?' . $returnParameter),
        'cancel_url' => absolute_url('/payment-cancel.php?' . $returnParameter),
        'notify_url' => absolute_url('/payment-notify.php'),
        'name_first' => $primaryOrder['buyerName'],
        'email_address' => $primaryOrder['buyerEmail'],
        'm_payment_id' => $paymentReference,
        'amount' => number_format($total, 2, '.', ''),
        'item_name' => $isCartCheckout ? 'MyFriend cart order' : substr($primaryOrder['title'], 0, 100),
        'item_description' => substr(implode(', ', $itemNames), 0, 255),
    ];
    $data['signature'] = payfast_signature($data, PAYFAST_PASSPHRASE);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5" style="max-width:800px">
        <div class="card p-4 shadow-sm">
            <h2>Secure Payment</h2>
            <p class="mb-1"><strong>Order:</strong> <?php echo h($paymentReference); ?></p>
            <p class="mb-3"><strong>Amount:</strong> R<?php echo number_format($total, 2); ?></p>

            <div class="mb-4">
                <?php foreach ($orders as $order): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span><?php echo h($order['title']); ?> × <?php echo (int)($order['quantity'] ?? 1); ?></span>
                        <span>R<?php echo number_format((float)$order['amount'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (payment_is_demo()): ?>
                <div class="alert alert-warning">
                    <strong>Secure Payment Gateway:</strong> Enter card details
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">

                    <div class="mb-3">
                        <label class="form-label">Cardholder Name</label>
                        <input name="cardHolder" id="cardHolder" class="form-control" value="<?php echo h($cardHolder); ?>" placeholder="Enter cardholder name" maxlength="100" autocomplete="cc-name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input name="cardNumber" id="cardNumber" class="form-control" value="<?php echo h($cardNumber); ?>" inputmode="numeric" minlength="16" maxlength="16" placeholder="Enter 16 digits" autocomplete="cc-number" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry</label>
                            <input name="expiry" id="expiry" class="form-control" value="<?php echo h($expiry); ?>" placeholder="MM/YY" maxlength="5" inputmode="numeric" autocomplete="cc-exp" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input name="cvv" id="cvv" type="password" class="form-control" inputmode="numeric" minlength="3" maxlength="4" placeholder="3 or 4 digits" autocomplete="cc-csc" required>
                        </div>
                    </div>

                    <button class="btn btn-shop" type="submit">Pay R<?php echo number_format($total, 2); ?></button>
                    <a class="btn btn-outline-secondary mt-2 w-100"
                        href="<?php echo $isCartCheckout
                                    ? app_url('/payment-cancel.php?checkout=' . urlencode($checkoutReference))
                                    : app_url('/payment-cancel.php?id=' . (int)$transactionID); ?>">
                        Cancel
                    </a>
                </form>
            <?php else: ?>
                <p>You are being redirected to PayFast.</p>
                <form id="payfastForm" action="<?php echo h(payfast_process_url()); ?>" method="post">
                    <?php foreach ($data as $key => $value): ?>
                        <input type="hidden" name="<?php echo h($key); ?>" value="<?php echo h($value); ?>">
                    <?php endforeach; ?>
                    <button class="btn btn-shop" type="submit">Continue to PayFast</button>
                </form>
                <script>
                    document.getElementById('payfastForm').submit();
                </script>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const cardNumberInput = document.getElementById('cardNumber');

        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = value.substring(0, 19);
                this.value = value.replace(/(.{4})/g, '$1 ').trim();
            });
        }

        const expiryInput = document.getElementById('expiry');

        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = value.substring(0, 4);

                if (value.length >= 3) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }

                this.value = value;
            });
        }
    </script>
</body>

</html>