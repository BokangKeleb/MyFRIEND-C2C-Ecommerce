<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

$buyerID = (int)$_SESSION['userID'];
$stmt = mysqli_prepare(
    $conn,
    'SELECT t.*, p.title, p.shop_name
     FROM transactions t
     JOIN products p ON p.productID = t.productID
     WHERE t.buyerID = ?
     ORDER BY t.transactionDate DESC, t.transactionID DESC'
);
mysqli_stmt_bind_param($stmt, 'i', $buyerID);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=91'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="mb-0">My Orders</h1>
            <a class="btn btn-outline-dark" href="<?php echo app_url('/cart.php'); ?>">Open Cart</a>
        </div>

        <?php if (($_GET['payment'] ?? '') === 'cancelled'): ?>
            <div class="alert alert-warning">
                The online payment was cancelled. Your products are still in the cart.
            </div>
        <?php endif; ?>

        <?php if (($_GET['order'] ?? '') === 'placed'): ?>
            <div class="alert alert-success">
                Your cash order was placed successfully. The seller will contact you about delivery or collection.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Shop</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Order Method</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Delivery Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($orders) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                            <?php
                            $displayReference = $order['checkoutReference']
                                ?: ($order['transactionReference'] ?: ('MF-' . $order['transactionID']));

                            $payLink = $order['checkoutReference']
                                ? app_url('/payment.php?checkout=' . urlencode($order['checkoutReference']))
                                : app_url('/payment.php?id=' . $order['transactionID']);

                            $isOnlinePayment = in_array(
                                $order['paymentMethod'],
                                ['Online Payment', 'PayFast Online Payment'],
                                true
                            );
                            ?>
                            <tr>
                                <td><?php echo h($displayReference); ?></td>
                                <td><?php echo h($order['title']); ?></td>
                                <td><?php echo h($order['shop_name']); ?></td>
                                <td><?php echo (int)($order['quantity'] ?? 1); ?></td>
                                <td>R<?php echo number_format((float)$order['amount'], 2); ?></td>
                                <td>
                                    <strong><?php echo h($order['fulfilmentMethod'] ?? 'Delivery'); ?></strong><br>
                                    <small><?php echo h($order['deliveryAddress']); ?></small>
                                </td>
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
                                <td><?php echo h($order['transactionDate']); ?></td>
                                <td>
                                    <?php if ($isOnlinePayment && in_array($order['paymentStatus'], ['Pending', 'Failed'], true)): ?>
                                        <a class="btn btn-sm btn-dark" href="<?php echo $payLink; ?>">Pay Now</a>
                                    <?php elseif (in_array($order['paymentMethod'], ['Cash on Delivery', 'Cash on Collection'], true) && $order['orderStatus'] !== 'Completed'): ?>
                                        <span class="text-muted">Pay seller directly</span>
                                    <?php else: ?>
                                        —
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