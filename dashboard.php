<?php
require_once __DIR__ . '/config/app.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {

    session_start();
}


// Check if user is logged in
if (!isset($_SESSION['userID'])) {

    // Redirect to login
    redirect_to('/login.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Character encoding -->
    <meta charset="UTF-8">

    <!-- Responsive -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css'); ?>">
</head>

<body>

    <?php

    // Include navbar
    include 'includes/navbar.php';

    ?>

    <!-- Main container -->
    <div class="container mt-5">

        <!-- Welcome heading -->
        <h1>
            Welcome,
            <?php echo $_SESSION['name']; ?>
        </h1>

        <!-- Role -->
        <p>
            Role:
            <strong>
                <?php echo $_SESSION['role']; ?>
            </strong>
        </p>

    </div>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>