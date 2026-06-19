<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if (($_SESSION['role'] ?? '') === 'admin') {
    die('Administrators do not have shopping carts.');
}

$userID = (int)$_SESSION['userID'];
$sql = 'SELECT c.cartID, c.quantity, c.addedAt,
               p.productID, p.title, p.description, p.price, p.image, p.shop_name,
               p.sellerID, p.availableQuantity
        FROM cart c
        JOIN products p ON p.productID = c.productID
        WHERE c.userID = ?
        ORDER BY c.addedAt DESC';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
$total = 0.0;
$hasStockProblems = false;

while ($row = mysqli_fetch_assoc($result)) {
    $row['availableQuantity'] = max(0, (int)($row['availableQuantity'] ?? 0));
    $row['quantity'] = (int)$row['quantity'];
    $row['stockProblem'] = $row['availableQuantity'] < 1 || $row['quantity'] > $row['availableQuantity'];

    if ($row['stockProblem']) {
        $hasStockProblems = true;
    }

    $row['subtotal'] = (float)$row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="mb-0">Shopping Cart</h1>
        <a class="btn btn-outline-dark" href="<?php echo app_url('/products.php'); ?>">Continue Shopping</a>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Product added to your cart.</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Cart quantities updated.</div>
    <?php elseif (isset($_GET['removed'])): ?>
        <div class="alert alert-success">Product removed from your cart.</div>
    <?php elseif (isset($_GET['cleared'])): ?>
        <div class="alert alert-success">Your cart was cleared.</div>
    <?php endif; ?>

    <?php if (($_GET['stock'] ?? '') === 'adjusted'): ?>
        <div class="alert alert-warning">Some quantities were adjusted to match available stock.</div>
    <?php endif; ?>

    <?php if ($hasStockProblems): ?>
        <div class="alert alert-warning">
            One or more cart items exceed available stock. Update the cart before checkout.
        </div>
    <?php endif; ?>

    <?php if (!$items): ?>
        <div class="card p-5 text-center">
            <h3>Your cart is empty</h3>
            <p>Add products from the Shop before proceeding to checkout.</p>
            <a class="btn btn-shop mx-auto" style="max-width:240px" href="<?php echo app_url('/products.php'); ?>">Open Shop</a>
        </div>
    <?php else: ?>
        <form id="updateCartForm" method="post" action="<?php echo app_url('/update-cart.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        </form>

        <div class="table-responsive">
            <table class="table align-middle cart-table">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Shop</th>
                        <th>Price</th>
                        <th>Available</th>
                        <th style="width:120px">Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img class="cart-product-image" src="<?php echo app_url('/uploads/' . rawurlencode($item['image'])); ?>" alt="<?php echo h($item['title']); ?>">
                                <div>
                                    <strong><?php echo h($item['title']); ?></strong><br>
                                    <a class="small" href="<?php echo app_url('/buy-product.php?id=' . $item['productID']); ?>">View details</a>
                                    <?php if ($item['stockProblem']): ?>
                                        <br><small class="text-danger">Stock problem</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?php echo h($item['shop_name']); ?></td>
                        <td>R<?php echo number_format((float)$item['price'], 2); ?></td>
                        <td>
                            <?php if ($item['availableQuantity'] > 0): ?>
                                <?php echo (int)$item['availableQuantity']; ?>
                            <?php else: ?>
                                <span class="badge bg-danger">Out</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input
                                form="updateCartForm"
                                type="number"
                                name="quantities[<?php echo (int)$item['cartID']; ?>]"
                                value="<?php echo (int)$item['quantity']; ?>"
                                min="1"
                                max="<?php echo max(1, (int)$item['availableQuantity']); ?>"
                                class="form-control"
                            >
                        </td>
                        <td>R<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                        <td>
                            <form method="post" action="<?php echo app_url('/remove-from-cart.php'); ?>" onsubmit="return confirm('Remove this product from your cart?');">
                                <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                                <input type="hidden" name="cartID" value="<?php echo (int)$item['cartID']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5 col-lg-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong>R<?php echo number_format($total, 2); ?></strong>
                    </div>

                    <button form="updateCartForm" type="submit" class="btn btn-outline-dark mb-2">Update Cart</button>

                    <?php if ($hasStockProblems): ?>
                        <button class="btn btn-secondary mb-2" disabled>Update Cart Before Checkout</button>
                    <?php else: ?>
                        <a class="btn btn-shop mb-2" href="<?php echo app_url('/cart-checkout.php'); ?>">Proceed to Checkout</a>
                    <?php endif; ?>

                    <form method="post" action="<?php echo app_url('/clear-cart.php'); ?>" onsubmit="return confirm('Clear all products from your cart?');">
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                        <button type="submit" class="btn btn-link text-danger w-100">Clear Cart</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
