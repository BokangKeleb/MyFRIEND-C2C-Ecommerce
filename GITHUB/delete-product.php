<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

ensure_session_started();

if (
    !isset($_SESSION['userID'])
    || ($_SESSION['role'] ?? '') !== 'admin'
) {
    redirect_to('/login.php');
}

/*
 * Update complaint status.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $complaintID = filter_input(
        INPUT_POST,
        'complaintID',
        FILTER_VALIDATE_INT
    );

    $status = trim($_POST['status'] ?? '');

    $validStatuses = [
        'Open',
        'Investigating',
        'Resolved',
        'Rejected'
    ];

    if (
        $complaintID
        && in_array($status, $validStatuses, true)
    ) {
        $stmt = mysqli_prepare(
            $conn,
            'UPDATE complaints
             SET complaintStatus = ?
             WHERE complaintID = ?'
        );

        mysqli_stmt_bind_param(
            $stmt,
            'si',
            $status,
            $complaintID
        );

        mysqli_stmt_execute($stmt);
    }

    redirect_to('/admin/complaints.php');
}

/*
 * Get all complaints, newest first.
 */
$sql = '
    SELECT
        c.*,
        u.name AS userName
    FROM complaints c
    LEFT JOIN users u
        ON u.userID = c.userID
    ORDER BY c.createdAt DESC
';

$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Complaints | Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="<?php echo app_url('/assets/css/style.css?v=90'); ?>">

</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">

        <h1 class="mb-4">Customer Complaints</h1>

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Shop</th>
                        <th>Reason</th>
                        <th>Complaint</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($complaint = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?php echo (int)$complaint['complaintID']; ?>
                            </td>

                            <td>
                                <?php echo h(
                                    $complaint['userName']
                                        ?: 'Guest'
                                ); ?>
                            </td>

                            <td>
                                <?php echo h($complaint['shopName']); ?>
                            </td>

                            <td>
                                <?php echo h($complaint['complaintReason']); ?>
                            </td>

                            <td style="min-width: 250px;">
                                <?php echo nl2br(
                                    h($complaint['complaintMessage'])
                                ); ?>
                            </td>

                            <td>

                                <a href="mailto:<?php echo h($complaint['contactEmail']); ?>">
                                    <?php echo h($complaint['contactEmail']); ?>
                                </a>

                            </td>

                            <td style="min-width: 170px;">

                                <form method="post">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo h(csrf_token()); ?>">

                                    <input
                                        type="hidden"
                                        name="complaintID"
                                        value="<?php echo (int)$complaint['complaintID']; ?>">

                                    <select
                                        name="status"
                                        class="form-select form-select-sm"
                                        onchange="this.form.submit()">

                                        <?php
                                        $statuses = [
                                            'Open',
                                            'Investigating',
                                            'Resolved',
                                            'Rejected'
                                        ];
                                        ?>

                                        <?php foreach ($statuses as $status): ?>

                                            <option
                                                value="<?php echo h($status); ?>"
                                                <?php echo
                                                $complaint['complaintStatus'] === $status
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                <?php echo h($status); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </form>

                            </td>

                            <td>
                                <?php echo h($complaint['createdAt']); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>