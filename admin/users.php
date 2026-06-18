<?php
require_once dirname(__DIR__) . '/config/app.php';

// Start session only if no session exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Only admin can access this page
if ($_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// Get all users
$sql = "SELECT * FROM users ORDER BY userID DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">

    <h1 class="mb-4">Manage Users</h1>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'user_deleted') { ?>
        <div class="alert alert-success">User account deleted successfully.</div>
    <?php } ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete_self') { ?>
        <div class="alert alert-danger">You cannot delete your own admin account.</div>
    <?php } ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'delete_failed') { ?>
        <div class="alert alert-danger">User account could not be deleted.</div>
    <?php } ?>

    <div class="table-responsive">

        <table class="table table-bordered align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Shop Name</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($user = mysqli_fetch_assoc($result)) { ?>

                    <tr>
                        <td><?php echo $user['userID']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo !empty($user['shop_name']) ? $user['shop_name'] : '-'; ?></td>
                        <td>
                            <?php if ($user['userID'] != $_SESSION['userID']) { ?>
                                <a
                                    href="<?php echo app_url('/admin/delete-user.php?id=' . $user['userID']); ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this account?');">
                                    Delete
                                </a>
                            <?php } else { ?>
                                <span class="text-muted">Current Admin</span>
                            <?php } ?>
                        </td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
