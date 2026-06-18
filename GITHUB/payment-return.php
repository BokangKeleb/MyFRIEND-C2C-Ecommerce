<?php
require_once __DIR__ . '/config/app.php';

$checkoutReference = trim($_GET['checkout'] ?? '');
$transactionID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($checkoutReference !== '') {
    redirect_to('/payment-success.php?checkout=' . urlencode($checkoutReference));
}

redirect_to('/payment-success.php?id=' . (int)$transactionID);
