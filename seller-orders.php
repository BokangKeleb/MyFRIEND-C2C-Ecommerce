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

$sellerID = (int)$_SESSION['userID'];

$shopStmt = mysqli_prepare($conn, 'SELECT shop_name FROM users WHERE userID = ?');
mysqli_stmt_bind_param($shopStmt, 'i', $sellerID);
mysqli_stmt_execute($shopStmt);
$shop = mysqli_fetch_assoc(mysqli_stmt_get_result($shopStmt));
$shopName = $shop['shop_name'] ?: 'Shop';

$sql = 'SELECT t.*, p.title
        FROM transactions t
        JOIN products p ON p.productID = t.productID
        WHERE p.sellerID = ?
        ORDER BY t.transactionDate DESC';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $sellerID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$countStmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM transactions t
     JOIN products p ON p.productID = t.productID
     WHERE p.sellerID = ?
       AND t.orderStatus <> 'Completed'
       AND (
            t.paymentStatus = 'Paid'
            OR t.paymentMethod IN ('Cash on Delivery', 'Cash on Collection')
       )"
);
mysqli_stmt_bind_param($countStmt, 'i', $sellerID);
mysqli_stmt_execute($countStmt);
$count = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
$outstanding = (int)$count['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($shopName); ?> Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=91'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <h1><?php echo h($shopName); ?> Orders</h1>

        <p>
            Process online orders only after payment is marked Paid. For cash orders,
            collect the cash when the product is delivered or collected.
        </p>

        <div class="alert alert-success">
            Outstanding orders ready to process:
            <strong><?php echo $outstanding; ?></strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Buyer</th>
                        <th>Order Method</th>
                        <th>Address / Notes</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Delivery Status</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $isCashOrder = in_array(
                                $order['paymentMethod'],
                                ['Cash on Delivery', 'Cash on Collection'],
                                true
                            );
                            $canComplete = $order['orderStatus'] !== 'Completed'
                                && ($order['paymentStatus'] === 'Paid' || $isCashOrder);
                            $buttonLabel = ($order['fulfilmentMethod'] ?? 'Delivery') === 'Collection'
                                ? 'Mark Collected'
                                : 'Mark Delivered';
                            ?>
                            <tr>
                                <td><?php echo h($order['checkoutReference'] ?: $order['transactionReference']); ?></td>
                                <td><?php echo h($order['title']); ?></td>
                                <td><?php echo (int)($order['quantity'] ?? 1); ?></td>
                                <td>
                                    <strong><?php echo h($order['buyerName']); ?></strong><br>
                                    <?php echo h($order['buyerEmail']); ?><br>
                                    <?php echo h($order['buyerPhone']); ?>
                                </td>
                                <td><?php echo h($order['fulfilmentMethod'] ?? 'Delivery'); ?></td>
                                <td><?php echo nl2br(h($order['deliveryAddress'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $order['paymentStatus'] === 'Paid'
                                                            ? 'bg-success'
                                                            : ($order['paymentStatus'] === 'Failed'
                                                                ? 'bg-danger'
                                                                : 'bg-warning text-dark'); ?>">
                                        <?php echo h($order['paymentStatus']); ?>
                                    </span><br>
                                    <small><?php echo h($order['paymentMethod']); ?></small>
                                </td>
                                <td><?php echo h($order['orderStatus'] ?: 'Pending'); ?></td>
                                <td><?php echo h($order['deliveryStatus'] ?? 'Pending'); ?></td>
                                <td>R<?php echo number_format((float)$order['amount'], 2); ?></td>
                                <td>
                                    <?php if ($canComplete): ?>
                                        <?php if ($isCashOrder): ?>
                                            <small class="d-block mb-2 text-muted">Collect cash first.</small>
                                        <?php endif; ?>
                                        <a class="btn btn-dark btn-sm" href="<?php echo app_url('/complete-order.php?id=' . $order['transactionID']); ?>">
                                            <?php echo h($buttonLabel); ?>
                                        </a>
                                    <?php elseif ($order['orderStatus'] === 'Completed'): ?>
                                        <span class="text-success">Completed</span>
                                    <?php else: ?>
                                        <span class="text-muted">Awaiting online payment</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>