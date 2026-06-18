<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

$userID = isset($_SESSION['userID'])
    ? (int)$_SESSION['userID']
    : null;

$shopName = '';
$complaintReason = '';
$complaintMessage = '';
$contactEmail = '';
$error = '';
$success = '';

$validReasons = [
    'Fraud or Scam',
    'Incorrect Product Information',
    'Seller Not Responding',
    'Delivery Problem',
    'Payment Problem',
    'Inappropriate Product',
    'Other'
];

/*
 * Use the logged-in user's email as the default contact email.
 */
if ($userID !== null) {
    $stmt = mysqli_prepare(
        $conn,
        'SELECT email FROM users WHERE userID = ? LIMIT 1'
    );

    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);

    $user = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    if ($user) {
        $contactEmail = $user['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $shopName = trim($_POST['shopName'] ?? '');
    $complaintReason = trim($_POST['complaintReason'] ?? '');
    $complaintMessage = trim($_POST['complaintMessage'] ?? '');
    $contactEmail = trim($_POST['contactEmail'] ?? '');

    if ($shopName === '') {
        $error = 'Please enter the shop name.';
    } elseif (!in_array($complaintReason, $validReasons, true)) {
        $error = 'Please select a valid complaint reason.';
    } elseif (strlen($complaintMessage) < 10) {
        $error = 'Please provide more information about the complaint.';
    } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }

    if ($error === '') {

        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO complaints
            (
                userID,
                shopName,
                complaintReason,
                complaintMessage,
                contactEmail
            )
            VALUES (?, ?, ?, ?, ?)'
        );

        mysqli_stmt_bind_param(
            $stmt,
            'issss',
            $userID,
            $shopName,
            $complaintReason,
            $complaintMessage,
            $contactEmail
        );

        if (mysqli_stmt_execute($stmt)) {

            $success = 'Your complaint has been submitted successfully.';

            $shopName = '';
            $complaintReason = '';
            $complaintMessage = '';
        } else {
            $error = 'The complaint could not be submitted. Please try again.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Contact Admin | MyFriend</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="<?php echo app_url('/assets/css/style.css?v=90'); ?>">

</head>

<body>

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container py-5" style="max-width: 750px;">

        <h1 class="mb-3">Contact the Administrator</h1>

        <p class="text-muted">
            Use this form to report a shop, seller, product or transaction problem.
            Please provide clear and accurate information.
        </p>

        <div class="alert alert-light border">

            <strong>Administrator Contact:</strong><br>

            Email:
            <a href="mailto:kelebonyebokang@gmail.com">
                kelebonyebokang@gmail.com
            </a>

        </div>

        <?php if ($error !== ''): ?>

            <div class="alert alert-danger">
                <?php echo h($error); ?>
            </div>

        <?php endif; ?>

        <?php if ($success !== ''): ?>

            <div class="alert alert-success">
                <?php echo h($success); ?>
            </div>

        <?php endif; ?>

        <form method="post">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo h(csrf_token()); ?>">

            <div class="mb-3">

                <label for="shopName" class="form-label">
                    Shop or Seller Name
                </label>

                <input
                    type="text"
                    name="shopName"
                    id="shopName"
                    class="form-control"
                    value="<?php echo h($shopName); ?>"
                    placeholder="Enter the shop name"
                    maxlength="100"
                    required>

            </div>

            <div class="mb-3">

                <label for="complaintReason" class="form-label">
                    Reason for Complaint
                </label>

                <select
                    name="complaintReason"
                    id="complaintReason"
                    class="form-select"
                    required>

                    <option value="">Select a reason</option>

                    <?php foreach ($validReasons as $reason): ?>

                        <option
                            value="<?php echo h($reason); ?>"
                            <?php echo $complaintReason === $reason
                                ? 'selected'
                                : ''; ?>>
                            <?php echo h($reason); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="mb-3">

                <label for="complaintMessage" class="form-label">
                    Complaint Details
                </label>

                <textarea
                    name="complaintMessage"
                    id="complaintMessage"
                    class="form-control"
                    rows="6"
                    placeholder="Explain what happened..."
                    required><?php echo h($complaintMessage); ?></textarea>

            </div>

            <div class="mb-3">

                <label for="contactEmail" class="form-label">
                    Your Contact Email
                </label>

                <input
                    type="email"
                    name="contactEmail"
                    id="contactEmail"
                    class="form-control"
                    value="<?php echo h($contactEmail); ?>"
                    required>

            </div>

            <button type="submit" class="btn btn-shop">
                Submit Complaint
            </button>

        </form>

    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>