<?php
require_once dirname(__DIR__) . '/config/app.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$sql = "SELECT * FROM products";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>

        Manage Products

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css'); ?>">
</head>

<body>

    <?php

    include '../includes/navbar.php';

    ?>

    <div class="container mt-5">

        <h1>

            Manage Products

        </h1>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'product_deleted') { ?>
            <div class="alert alert-success">Product deleted successfully.</div>
        <?php } ?>

        <table class="table">

            <tr>

                <th>ID</th>

                <th>Title</th>

                <th>Price</th>

                <th>Action</th>

            </tr>

            <?php

            while ($product = mysqli_fetch_assoc($result)) {

            ?>

                <tr>

                    <td>

                        <?php echo $product['productID']; ?>

                    </td>

                    <td>

                        <?php echo $product['title']; ?>

                    </td>

                    <td>

                        R<?php echo $product['price']; ?>

                    </td>

                    <td>

                        <a
                            href="delete-product.php?id=<?php echo $product['productID']; ?>"
                            class="btn btn-danger">

                            Delete

                        </a>

                    </td>

                </tr>

            <?php

            }

            ?>

        </table>

    </div>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>