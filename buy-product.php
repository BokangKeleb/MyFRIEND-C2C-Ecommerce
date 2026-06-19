<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

$productID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productID) {
    redirect_to('/products.php');
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT
        p.*,
        u.email AS sellerEmail,
        u.sellerPhone,
        u.province,
        u.city,
        u.area,
        u.collectionAvailable,
        u.collectionAddress
     FROM products p
     INNER JOIN users u
        ON u.userID = p.sellerID
     WHERE p.productID = ?
     LIMIT 1'
);

mysqli_stmt_bind_param($stmt, 'i', $productID);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$product) {
    die('Product not found.');
}

$availableQuantity = max(0, (int)($product['availableQuantity'] ?? 0));

$isLoggedIn = isset($_SESSION['userID']);
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$isOwnProduct = $isLoggedIn && (int)$_SESSION['userID'] === (int)$product['sellerID'];

$whatsappNumber = preg_replace('/[^0-9]/', '', (string)$product['sellerPhone']);
if (str_starts_with($whatsappNumber, '0')) {
    $whatsappNumber = '27' . substr($whatsappNumber, 1);
}
$whatsappMessage = urlencode('Hi, I am interested in your product: ' . $product['title']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($product['title']); ?> | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-6">
                <img class="img-fluid product-detail-image" src="<?php echo app_url('/uploads/' . rawurlencode($product['image'])); ?>" alt="<?php echo h($product['title']); ?>">
            </div>

            <div class="col-lg-6">
                <p class="text-uppercase small text-muted mb-2"><?php echo h($product['category']); ?></p>
                <h1><?php echo h($product['title']); ?></h1>
                <h3 class="my-3">R<?php echo number_format((float)$product['price'], 2); ?></h3>

                <?php if ($availableQuantity > 0): ?>
                    <p><strong>Available Quantity:</strong> <?php echo $availableQuantity; ?></p>
                <?php else: ?>
                    <div class="alert alert-danger">Out of stock</div>
                <?php endif; ?>

                <?php if (($_GET['stock'] ?? '') === 'out'): ?>
                    <div class="alert alert-warning">This product is currently out of stock.</div>
                <?php elseif (($_GET['stock'] ?? '') === 'max'): ?>
                    <div class="alert alert-warning">You already have the maximum available quantity in your cart.</div>
                <?php endif; ?>

                <p><?php echo nl2br(h($product['description'])); ?></p>
                <p><strong>Shop:</strong> <?php echo h($product['shop_name']); ?></p>
                <?php
                // Build the seller's public general location
                $sellerLocation = array_filter([
                    $product['area'] ?? '',
                    $product['city'] ?? '',
                    $product['province'] ?? ''
                ]);
                ?>

                <?php if (!empty($sellerLocation)): ?>

                    <?php if (!empty($sellerLocation)): ?>

                        <p class="mb-1">
                            <strong>Seller Location:</strong>
                            <?php echo h(implode(', ', $sellerLocation)); ?>
                        </p>

                    <?php endif; ?>

                    <?php if (
                        (int)($product['collectionAvailable'] ?? 0) === 1
                        && !empty($product['collectionAddress'])
                    ): ?>

                        <div class="alert alert-light border mt-3">

                            <strong>Collection Available</strong>

                            <p class="mb-0 mt-2">
                                <strong>Collection Address:</strong><br>
                                <?php echo nl2br(
                                    h($product['collectionAddress'])
                                ); ?>
                            </p>

                        </div>

                    <?php else: ?>

                        <p class="text-muted">
                            Collection is unavailable for this product.
                        </p>

                    <?php endif; ?>

                <?php endif; ?>

                <div class="seller-contact-box">
                    <h6>Seller Contact Details</h6>

                    <?php if (!empty($product['sellerPhone'])): ?>
                        <p class="mb-2">
                            <strong>Phone:</strong>
                            <a href="tel:<?php echo h($product['sellerPhone']); ?>"><?php echo h($product['sellerPhone']); ?></a>
                        </p>

                        <?php if ($whatsappNumber !== ''): ?>
                            <a class="btn btn-outline-dark btn-sm mb-2" target="_blank" rel="noopener"
                                href="https://wa.me/<?php echo h($whatsappNumber); ?>?text=<?php echo h($whatsappMessage); ?>">
                                Contact on WhatsApp
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($product['sellerEmail'])): ?>
                        <p class="mb-0 mt-2">
                            <strong>Email:</strong>
                            <a href="mailto:<?php echo h($product['sellerEmail']); ?>"><?php echo h($product['sellerEmail']); ?></a>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($isAdmin): ?>
                    <button class="btn btn-secondary w-100" disabled>Admin View Only</button>
                <?php elseif ($isOwnProduct): ?>
                    <button class="btn btn-secondary w-100" disabled>You cannot add your own product</button>
                <?php elseif ($availableQuantity < 1): ?>
                    <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                <?php elseif (!$isLoggedIn): ?>
                    <a class="btn btn-shop" href="<?php echo app_url('/login.php'); ?>">Login to Add to Cart</a>
                <?php else: ?>
                    <form method="post" action="<?php echo app_url('/add-to-cart.php'); ?>" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="productID" value="<?php echo (int)$product['productID']; ?>">
                        <input type="hidden" name="returnTo" value="cart">

                        <div class="row g-2 align-items-end">
                            <div class="col-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" min="1" max="<?php echo $availableQuantity; ?>" value="1" class="form-control" required>
                            </div>
                            <div class="col-8">
                                <button type="submit" class="btn btn-shop">Add to Cart</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</body>

</html>
