<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/mailer.php';

ensure_session_started();

$message = '';
$error = '';
$previewResetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT userID, name, email FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // Always show the same message so people cannot check which emails are registered.
        $message = 'If this email is registered, password reset instructions have been created.';

        if ($user) {
            $userID = (int)$user['userID'];
            $delete = mysqli_prepare($conn, 'DELETE FROM password_resets WHERE userID = ?');
            mysqli_stmt_bind_param($delete, 'i', $userID);
            mysqli_stmt_execute($delete);

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 1800);

            $insert = mysqli_prepare(
                $conn,
                'INSERT INTO password_resets (userID, tokenHash, expiresAt) VALUES (?, ?, ?)'
            );
            mysqli_stmt_bind_param($insert, 'iss', $userID, $tokenHash, $expiresAt);

            if (mysqli_stmt_execute($insert)) {
                $resetUrl = absolute_url('/reset-password.php?token=' . urlencode($token));
                $mailError = '';

                if (!send_reset_email($user['email'], $user['name'], $resetUrl, $mailError)) {
                    $error = $mailError;
                    $message = '';
                } elseif (MAIL_MODE === 'preview' && is_local_environment()) {
                    $previewResetUrl = $resetUrl;
                }
            } else {
                $error = 'The reset request could not be created. Please try again.';
                $message = '';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm">
                <h2 class="mb-3">Forgot Password</h2>
                <p>Enter the email address connected to your MyFriend account.</p>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-success"><?php echo h($message); ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <?php if ($previewResetUrl !== ''): ?>
                    <div class="alert alert-warning">
                        <strong>Local preview mode:</strong> email sending is disabled. Use the button below to test the reset process.
                    </div>
                    <a class="btn btn-shop mb-3" href="<?php echo h($previewResetUrl); ?>">Open Password Reset Link</a>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-shop">Create Reset Link</button>
                </form>

                <p class="mt-3 mb-0 text-center">
                    <a href="<?php echo app_url('/login.php'); ?>">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
