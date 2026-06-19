<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

// Get the filter values from the URL
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$province = trim($_GET['province'] ?? '');

// Categories allowed by the website
$validCategories = [
    'Clothing',
    'Electronics',
    'Food',
    'Furniture',
    'Accessories',
    'Other'
];

// South African provinces allowed by the website
$validProvinces = [
    'Gauteng',
    'Limpopo',
    'Mpumalanga',
    'North West',
    'Free State',
    'KwaZulu-Natal',
    'Eastern Cape',
    'Western Cape',
    'Northern Cape'
];

// Remove an invalid category value
if ($category !== '' && !in_array($category, $validCategories, true)) {
    $category = '';
}

// Remove an invalid province value
if ($province !== '' && !in_array($province, $validProvinces, true)) {
    $province = '';
}

// Add wildcard characters for the SQL search
$like = '%' . $search . '%';

/*
 * Get products together with the seller's location.
 *
 * The search box can search:
 * - Product title
 * - Product description
 * - Shop name
 * - Seller city
 * - Seller area/suburb
 *
 * The dropdowns filter by category and province.
 */
$sql = "
    SELECT
        p.*,
        u.province,
        u.city,
        u.area
    FROM products p
    INNER JOIN users u
        ON u.userID = p.sellerID
    WHERE
        (
            ? = ''
            OR p.title LIKE ?
            OR p.description LIKE ?
            OR p.shop_name LIKE ?
            OR u.city LIKE ?
            OR u.area LIKE ?
        )
        AND (
            ? = ''
            OR p.category = ?
        )
        AND (
            ? = ''
            OR u.province = ?
        )
    ORDER BY p.productID DESC
";

// Prepare the secure SQL query
$stmt = mysqli_prepare($conn, $sql);

// Connect all the filter values to the query
mysqli_stmt_bind_param(
    $stmt,
    'ssssssssss',
    $search,
    $like,
    $like,
    $like,
    $like,
    $like,
    $category,
    $category,
    $province,
    $province
);

// Run the query
mysqli_stmt_execute($stmt);

// Get the product results
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Shop</h1>

        <?php if (($_GET['cart'] ?? '') === 'added'): ?>
            <div class="alert alert-success">Product added to your cart.</div>
        <?php elseif (($_GET['cart'] ?? '') === 'own'): ?>
            <div class="alert alert-warning">You cannot add your own product to your cart.</div>
        <?php elseif (in_array($_GET['cart'] ?? '', ['invalid', 'missing'], true)): ?>
            <div class="alert alert-danger">The selected product could not be added.</div>
        <?php elseif (($_GET['cart'] ?? '') === 'out'): ?>
            <div class="alert alert-warning">That product is currently out of stock.</div>
        <?php elseif (($_GET['cart'] ?? '') === 'max'): ?>
            <div class="alert alert-warning">You already have the maximum available quantity in your cart.</div>
        <?php endif; ?>

        <form method="get" class="mb-5">

            <div class="row">

                <!-- Product, shop, city or area search -->
                <div class="col-md-5 mb-2">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search products, shops or areas..."
                        value="<?php echo h($search); ?>">

                </div>

                <!-- Category filter -->
                <div class="col-md-3 mb-2">

                    <select name="category" class="form-select">

                        <option value="">All Categories</option>

                        <?php foreach ($validCategories as $option): ?>

                            <option
                                value="<?php echo h($option); ?>"
                                <?php echo $category === $option ? 'selected' : ''; ?>>
                                <?php echo h($option); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Province filter -->
                <div class="col-md-3 mb-2">

                    <select name="province" class="form-select">

                        <option value="">All Provinces</option>

                        <?php foreach ($validProvinces as $option): ?>

                            <option
                                value="<?php echo h($option); ?>"
                                <?php echo $province === $option ? 'selected' : ''; ?>>
                                <?php echo h($option); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Submit button -->
                <div class="col-md-1 mb-2">

                    <button
                        class="btn btn-premium w-100"
                        type="submit">
                        Go
                    </button>

                </div>

            </div>

            <!-- Clear all active filters -->
            <?php if ($search !== '' || $category !== '' || $province !== ''): ?>

                <div class="mt-2">

                    <a
                        href="<?php echo app_url('/products.php'); ?>"
                        class="small text-dark">
                        Clear filters
                    </a>

                </div>

            <?php endif; ?>

        </form>

        <div class="row">
            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-secondary">No products matched your search.</div>
                </div>
            <?php endif; ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <?php
                $isOwnProduct = isset($_SESSION['userID']) && (int)$_SESSION['userID'] === (int)$product['sellerID'];
                $availableQuantity = max(0, (int)($product['availableQuantity'] ?? 0));
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <img src="<?php echo app_url('/uploads/' . rawurlencode($product['image'])); ?>" class="card-img-top" alt="<?php echo h($product['title']); ?>">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo h($product['title']); ?></h5>
                            <p class="card-text"><?php echo h($product['description']); ?></p>
                            <p><strong>R<?php echo number_format((float)$product['price'], 2); ?></strong></p>
                            <?php if ($availableQuantity > 0): ?>
                                <p><strong>Available:</strong> <?php echo $availableQuantity; ?></p>
                            <?php else: ?>
                                <p><span class="badge bg-danger">Out of Stock</span></p>
                            <?php endif; ?>
                            <p>Category: <?php echo h($product['category']); ?></p>
                            <p>Shop: <?php echo h($product['shop_name']); ?></p>

                            <?php
                            // Build a short public location using only area and city
                            $shortLocation = array_filter([
                                $product['area'] ?? '',
                                $product['city'] ?? ''
                            ]);
                            ?>

                            <?php if (!empty($shortLocation)): ?>

                                <p>
                                    <strong>Location:</strong>
                                    <?php echo h(implode(', ', $shortLocation)); ?>
                                </p>

                            <?php endif; ?>

                            <div class="mt-auto d-grid gap-2">
                                <a href="<?php echo app_url('/buy-product.php?id=' . $product['productID']); ?>" class="btn btn-outline-dark">View Details</a>

                                <?php if (isset($_SESSION['userID']) && (($_SESSION['role'] ?? '') === 'admin')): ?>
                                    <button class="btn btn-secondary" disabled>Admin View Only</button>
                                <?php elseif ($isOwnProduct): ?>
                                    <button class="btn btn-secondary" disabled>Your Product</button>
                                <?php elseif ($availableQuantity < 1): ?>
                                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                                <?php elseif (!isset($_SESSION['userID'])): ?>
                                    <a class="btn btn-shop" href="<?php echo app_url('/login.php'); ?>">Login to Add to Cart</a>
                                <?php else: ?>
                                    <form method="post" action="<?php echo app_url('/add-to-cart.php'); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                                        <input type="hidden" name="productID" value="<?php echo (int)$product['productID']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="returnTo" value="shop">
                                        <button type="submit" class="btn btn-shop">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
