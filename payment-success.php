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
$isCartCheckout = $checkoutReference !== '';

if (!$isCartCheckout && !$transactionID) {
    redirect_to('/orders.php');
}

if ($isCartCheckout) {
    $stmt = mysqli_prepare(
        $conn,
        'SELECT t.*, p.title
         FROM transactions t
         JOIN products p ON p.productID = t.productID
         WHERE t.checkoutReference = ? AND t.buyerID = ?
         ORDER BY t.transactionID ASC'
    );
    mysqli_stmt_bind_param($stmt, 'si', $checkoutReference, $buyerID);
} else {
    $stmt = mysqli_prepare(
        $conn,
        'SELECT t.*, p.title
         FROM transactions t
         JOIN products p ON p.productID = t.productID
         WHERE t.transactionID = ? AND t.buyerID = ?
         LIMIT 1'
    );
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

$reference = $isCartCheckout
    ? $checkoutReference
    : ($orders[0]['transactionReference'] ?: ('MF-' . $orders[0]['transactionID']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="card p-5 text-center mx-auto" style="max-width:760px">
            <h1><?php echo $allPaid ? 'Payment Successful' : 'Payment Processing'; ?></h1>
            <p>
                <?php echo $allPaid
                    ? 'Your payment has been recorded and the seller can now process the order.'
                    : 'The payment provider is still confirming this payment. Refresh My Orders shortly to see the final status.'; ?>
            </p>
            <p><strong>Order reference:</strong> <?php echo h($reference); ?></p>
            <p><strong>Total paid:</strong> R<?php echo number_format($total, 2); ?></p>

            <div class="text-start my-3">
                <?php foreach ($orders as $order): ?>
                    <div class="d-flex justify-content-between border-bottom py-2 gap-3">
                        <span><?php echo h($order['title']); ?> × <?php echo (int)($order['quantity'] ?? 1); ?></span>
                        <span><?php echo h($order['paymentStatus']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <a class="btn btn-shop" href="<?php echo app_url('/orders.php'); ?>">View My Orders</a>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>