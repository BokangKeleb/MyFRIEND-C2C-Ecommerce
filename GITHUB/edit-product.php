<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if (($_SESSION['role'] ?? '') === 'admin') {
    die('Administrators cannot place orders.');
}

$userID = (int)$_SESSION['userID'];

function load_cart_items(mysqli $conn, int $userID): array
{
    $sql = 'SELECT
            c.cartID,
            c.quantity,
            p.productID,
            p.title,
            p.price,
            p.image,
            p.shop_name,
            p.sellerID,
            u.collectionAvailable,
            u.collectionAddress
        FROM cart c
        JOIN products p
            ON p.productID = c.productID
        JOIN users u
            ON u.userID = p.sellerID
        WHERE c.userID = ?
        ORDER BY c.addedAt ASC';

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $row['lineTotal'] = (float)$row['price'] * (int)$row['quantity'];
        $items[] = $row;
    }

    return $items;
}

$items = load_cart_items($conn, $userID);

if (!$items) {
    redirect_to('/cart.php');
}

/*
 * Collection is available only when every seller represented
 * in the cart offers collection and has supplied an address.
 */
$collectionAvailableForCart = true;

foreach ($items as $item) {
    if (
        (int)($item['collectionAvailable'] ?? 0) !== 1
        || empty($item['collectionAddress'])
    ) {
        $collectionAvailableForCart = false;
        break;
    }
}

$total = array_sum(array_column($items, 'lineTotal'));
$error = '';

$userStmt = mysqli_prepare(
    $conn,
    'SELECT name, email FROM users WHERE userID = ? LIMIT 1'
);
mysqli_stmt_bind_param($userStmt, 'i', $userID);
mysqli_stmt_execute($userStmt);
$currentUser = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt)) ?: [];

$validFulfilmentMethods = ['Delivery', 'Collection'];
$validPaymentChoices = ['Online Payment', 'Cash'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $buyerName = trim($_POST['buyerName'] ?? '');
    $buyerPhone = trim($_POST['buyerPhone'] ?? '');
    $buyerEmail = strtolower(trim($_POST['buyerEmail'] ?? ''));
    $fulfilmentMethod = trim($_POST['fulfilmentMethod'] ?? '');
    $paymentChoice = trim($_POST['paymentChoice'] ?? '');
    $deliveryAddress = trim($_POST['deliveryAddress'] ?? '');

    if ($buyerName === '') {
        $error = 'Please enter your full name.';
    } elseif ($buyerPhone === '') {
        $error = 'Please enter your phone number.';
    } elseif (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($fulfilmentMethod, $validFulfilmentMethods, true)) {
        $error = 'Please select delivery or collection.';
    } elseif (!in_array($paymentChoice, $validPaymentChoices, true)) {
        $error = 'Please select a payment method.';
    } elseif ($fulfilmentMethod === 'Delivery' && $deliveryAddress === '') {
        $error = 'Please enter a delivery address.';
    }
    if (
        $fulfilmentMethod === 'Collection'
        && !$collectionAvailableForCart
    ) {
        $error = 'Collection is unavailable for one or more products in your cart.';
    }

    if ($error === '') {
        if ($fulfilmentMethod === 'Collection' && $deliveryAddress === '') {
            $deliveryAddress = 'Collection arranged directly with the seller.';
        }

        $paymentMethod = $paymentChoice === 'Online Payment'
            ? 'Online Payment'
            : ($fulfilmentMethod === 'Delivery'
                ? 'Cash on Delivery'
                : 'Cash on Collection');

        $checkoutReference = 'MF-CART-' . date('YmdHis') . '-' . random_int(1000, 9999);
        $paymentStatus = 'Pending';
        $orderStatus = $paymentChoice === 'Online Payment'
            ? 'Pending Payment'
            : 'Processing';
        $deliveryStatus = 'Pending';

        mysqli_begin_transaction($conn);

        try {
            $sql = 'INSERT INTO transactions
                    (buyerID, productID, quantity, amount, paymentStatus, orderStatus,
                     deliveryStatus, transactionReference, checkoutReference,
                     buyerName, buyerPhone, buyerEmail, deliveryAddress,
                     paymentMethod, fulfilmentMethod)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $insert = mysqli_prepare($conn, $sql);

            foreach ($items as $index => $item) {
                $productID = (int)$item['productID'];
                $quantity = (int)$item['quantity'];
                $lineAmount = (float)$item['lineTotal'];
                $transactionReference = $checkoutReference . '-' . ($index + 1);
                $types = 'iiid' . str_repeat('s', 11);

                mysqli_stmt_bind_param(
                    $insert,
                    $types,
                    $userID,
                    $productID,
                    $quantity,
                    $lineAmount,
                    $paymentStatus,
                    $orderStatus,
                    $deliveryStatus,
                    $transactionReference,
                    $checkoutReference,
                    $buyerName,
                    $buyerPhone,
                    $buyerEmail,
                    $deliveryAddress,
                    $paymentMethod,
                    $fulfilmentMethod
                );

                if (!mysqli_stmt_execute($insert)) {
                    throw new RuntimeException('Could not create the order.');
                }
            }

            if ($paymentChoice === 'Cash') {
                $clearCart = mysqli_prepare(
                    $conn,
                    'DELETE FROM cart WHERE userID = ?'
                );
                mysqli_stmt_bind_param($clearCart, 'i', $userID);
                mysqli_stmt_execute($clearCart);
            }

            mysqli_commit($conn);

            if ($paymentChoice === 'Online Payment') {
                redirect_to('/payment.php?checkout=' . urlencode($checkoutReference));
            }

            redirect_to('/orders.php?order=placed');
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $error = 'The order could not be created. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Checkout | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=91'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card p-4">
                    <h3 class="mb-3">Order Summary</h3>

                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between border-bottom py-2 gap-3">
                            <div>
                                <strong><?php echo h($item['title']); ?></strong><br>
                                <small>
                                    <?php echo h($item['shop_name']); ?> ·
                                    Quantity <?php echo (int)$item['quantity']; ?>
                                </small>
                            </div>
                            <strong>R<?php echo number_format((float)$item['lineTotal'], 2); ?></strong>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between pt-3">
                        <h5>Total</h5>
                        <h5>R<?php echo number_format($total, 2); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card p-4">
                    <h2 class="mb-4">Checkout Details</h2>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>

                    <form method="post" id="checkoutForm">
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input
                                type="text"
                                name="buyerName"
                                class="form-control"
                                value="<?php echo h($_POST['buyerName'] ?? ($currentUser['name'] ?? '')); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input
                                type="tel"
                                name="buyerPhone"
                                class="form-control"
                                value="<?php echo h($_POST['buyerPhone'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                name="buyerEmail"
                                class="form-control"
                                value="<?php echo h($_POST['buyerEmail'] ?? ($currentUser['email'] ?? '')); ?>"
                                required>
                        </div>

                        <div class="mb-3">

                            <label
                                for="fulfilmentMethod"
                                class="form-label">
                                Delivery or Collection
                            </label>

                            <select
                                name="fulfilmentMethod"
                                id="fulfilmentMethod"
                                class="form-select"
                                required>

                                <option
                                    value="Delivery"
                                    <?php echo ($_POST['fulfilmentMethod'] ?? 'Delivery') === 'Delivery'
                                        ? 'selected'
                                        : '';
                                    ?>>
                                    Delivery
                                </option>

                                <?php if ($collectionAvailableForCart): ?>

                                    <option
                                        value="Collection"
                                        <?php echo ($_POST['fulfilmentMethod'] ?? '') === 'Collection'
                                            ? 'selected'
                                            : '';
                                        ?>>
                                        Collection
                                    </option>

                                <?php else: ?>

                                    <option value="" disabled>
                                        Collection unavailable
                                    </option>

                                <?php endif; ?>

                            </select>

                            <?php if (!$collectionAvailableForCart): ?>

                                <small class="text-muted">
                                    One or more sellers in your cart do not offer collection.
                                </small>

                            <?php endif; ?>

                        </div>

                        <?php if ($collectionAvailableForCart): ?>

                            <div
                                class="alert alert-light border"
                                id="collectionAddressNotice">

                                <strong>Collection Addresses</strong>

                                <?php
                                $displayedSellerIDs = [];
                                ?>

                                <?php foreach ($items as $item): ?>

                                    <?php
                                    $sellerID = (int)$item['sellerID'];

                                    if (in_array(
                                        $sellerID,
                                        $displayedSellerIDs,
                                        true
                                    )) {
                                        continue;
                                    }

                                    $displayedSellerIDs[] = $sellerID;
                                    ?>

                                    <div class="mt-3">

                                        <strong>
                                            <?php echo h($item['shop_name']); ?>
                                        </strong>

                                        <br>

                                        <?php echo nl2br(
                                            h($item['collectionAddress'])
                                        ); ?>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                        <div
                            class="mb-3"
                            id="deliveryAddressSection">
                            <label
                                for="deliveryAddress"
                                id="addressLabel"
                                class="form-label">
                                Delivery Address
                            </label>

                            <textarea
                                name="deliveryAddress"
                                id="deliveryAddress"
                                class="form-control"
                                rows="4"><?php echo h($_POST['deliveryAddress'] ?? ''); ?></textarea>

                            <small
                                id="addressHelp"
                                class="text-muted">
                                Required when Delivery is selected.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="paymentChoice" class="form-label">Payment Method</label>
                            <select name="paymentChoice" id="paymentChoice" class="form-select" required>
                                <option value="">Select a payment method</option>
                                <option value="Online Payment" <?php echo ($_POST['paymentChoice'] ?? '') === 'Online Payment' ? 'selected' : ''; ?>>Online Card Payment</option>
                                <option value="Cash" <?php echo ($_POST['paymentChoice'] ?? '') === 'Cash' ? 'selected' : ''; ?>>Cash on Delivery / Collection</option>
                            </select>
                        </div>

                        <div class="alert alert-light border">
                            Delivery and collection arrangements are handled directly by the seller.
                            MyFriend does not provide a delivery service.
                        </div>

                        <div class="alert alert-secondary" id="paymentNotice">
                            Online payments will continue to the card-payment page.
                        </div>

                        <button type="submit" class="btn btn-shop" id="checkoutButton">
                            Continue
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fulfilmentMethod =
            document.getElementById('fulfilmentMethod');

        const paymentChoice =
            document.getElementById('paymentChoice');

        const deliveryAddress =
            document.getElementById('deliveryAddress');

        const deliveryAddressSection =
            document.getElementById('deliveryAddressSection');

        const addressLabel =
            document.getElementById('addressLabel');

        const addressHelp =
            document.getElementById('addressHelp');

        const paymentNotice =
            document.getElementById('paymentNotice');

        const checkoutButton =
            document.getElementById('checkoutButton');

        const collectionAddressNotice =
            document.getElementById('collectionAddressNotice');


        function updateCheckoutForm() {

            const isDelivery =
                fulfilmentMethod.value === 'Delivery';

            const isCollection =
                fulfilmentMethod.value === 'Collection';

            const isCash =
                paymentChoice.value === 'Cash';


            /*
             * Show the delivery-address field only when
             * the buyer selects Delivery.
             */
            if (deliveryAddressSection) {
                deliveryAddressSection.style.display =
                    isCollection ? 'none' : 'block';
            }

            deliveryAddress.required = isDelivery;


            /*
             * Show the seller collection addresses only
             * when Collection is selected.
             */
            if (collectionAddressNotice) {
                collectionAddressNotice.style.display =
                    isCollection ? 'block' : 'none';
            }


            /*
             * Update the address field wording.
             */
            if (isCollection) {

                addressLabel.textContent =
                    'Collection Notes (optional)';

                deliveryAddress.placeholder =
                    'Add any collection notes for the seller';

                addressHelp.textContent =
                    'Use the collection address shown above. The seller may contact you to confirm the collection time.';

            } else {

                addressLabel.textContent =
                    'Delivery Address';

                deliveryAddress.placeholder =
                    'Enter your complete delivery address';

                addressHelp.textContent =
                    'Required when Delivery is selected.';
            }


            /*
             * Update the payment instructions and button.
             */
            if (isCash) {

                paymentNotice.textContent =
                    isCollection ?
                    'You will pay the seller when collecting the order.' :
                    'You will pay the seller when the order is delivered.';

                checkoutButton.textContent =
                    isCollection ?
                    'Place Cash on Collection Order' :
                    'Place Cash on Delivery Order';

            } else {

                paymentNotice.textContent =
                    'You will continue to the online card-payment page.';

                checkoutButton.textContent =
                    'Proceed to Payment';
            }
        }


        if (fulfilmentMethod) {
            fulfilmentMethod.addEventListener(
                'change',
                updateCheckoutForm
            );
        }

        if (paymentChoice) {
            paymentChoice.addEventListener(
                'change',
                updateCheckoutForm
            );
        }

        updateCheckoutForm();
    </script>
</body>

</html>