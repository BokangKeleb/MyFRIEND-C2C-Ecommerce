<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('Access denied.');
}

$result = mysqli_query(
    $conn,
    'SELECT t.*, p.title
     FROM transactions t
     JOIN products p ON p.productID = t.productID
     ORDER BY t.transactionDate DESC'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>
<body>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<div class="container py-5">
    <h1>Transactions</h1>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order Reference</th>
                    <th>Line Reference</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Buyer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Order</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($transaction = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo h($transaction['checkoutReference'] ?: $transaction['transactionReference']); ?></td>
                        <td><?php echo h($transaction['transactionReference']); ?></td>
                        <td><?php echo h($transaction['title']); ?></td>
                        <td><?php echo (int)($transaction['quantity'] ?? 1); ?></td>
                        <td><?php echo h($transaction['buyerName']); ?></td>
                        <td>R<?php echo number_format((float)$transaction['amount'], 2); ?></td>
                        <td><?php echo h($transaction['paymentStatus']); ?></td>
                        <td><?php echo h($transaction['orderStatus']); ?></td>
                        <td><?php echo h($transaction['transactionDate']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
