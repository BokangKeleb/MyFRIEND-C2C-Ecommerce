<?php
require_once __DIR__ . '/config/app.php';

// Start session only if needed
if (session_status() == PHP_SESSION_NONE) {

    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Character encoding -->
    <meta charset="UTF-8">

    <!-- Responsive website -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>MyFriend</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css'); ?>">

</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <section class="hero-section">

        <div class="container">

            <h1 class="hero-title">
                MyFriend - Quick & Easy.
            </h1>

            <p class="hero-text">
                MyFriend is a proudly South African digital marketplace where anyone can sell and anyone can buy. Built for informal traders around the country.
            </p>

            <a href="<?php echo app_url('/products.php'); ?>" class="btn btn-premium">
                Shop Marketplace
            </a>

        </div>

    </section>

    <section class="category-section">

        <div class="container">

            <h2 class="section-heading">
                Shop by Category
            </h2>

            <div class="row">

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Clothing'); ?>" class="category-link">

                        <div
                            class="category-card category-green"
                            style="background-image: url('/assets/images/clothing.jpg');">

                            <div class="category-content">

                                <h3>
                                    Clothing
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Electronics'); ?>" class="category-link">

                        <div
                            class="category-card category-yellow"
                            style="background-image: url('/assets/images/electronics.jpg');">

                            <div class="category-content">

                                <h3>
                                    Electronics
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Food'); ?>" class="category-link">

                        <div
                            class="category-card category-red"
                            style="background-image: url('/assets/images/food.jpg');">

                            <div class="category-content">

                                <h3>
                                    Food
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Furniture'); ?>" class="category-link">

                        <div
                            class="category-card category-blue"
                            style="background-image: url('/assets/images/furniture.jpg');">

                            <div class="category-content">

                                <h3>
                                    Furniture
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Accessories'); ?>" class="category-link">

                        <div
                            class="category-card category-black"
                            style="background-image: url('/assets/images/accessories.jpg');">

                            <div class="category-content">

                                <h3>
                                    Accessories
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="<?php echo app_url('/products.php?category=Other'); ?>" class="category-link">

                        <div
                            class="category-card category-gold"
                            style="background-image: url('/assets/images/other.jpg');">

                            <div class="category-content">

                                <h3>
                                    Other
                                </h3>

                                <span>
                                    Shop Now
                                </span>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </section>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <?php include 'includes/footer.php'; ?>

</body>

</html>