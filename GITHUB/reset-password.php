<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

$token = trim($_POST['token'] ?? $_GET['token'] ?? '');
$error = '';
$resetRecord = null;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conn,
        'SELECT pr.resetID, pr.userID, u.email
         FROM password_resets pr
         JOIN users u ON u.userID = pr.userID
         WHERE pr.tokenHash = ?
           AND pr.usedAt IS NULL
           AND pr.expiresAt > NOW()
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $tokenHash);
    mysqli_stmt_execute($stmt);
    $resetRecord = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (!$resetRecord) {
        $error = 'This reset link is invalid or has expired.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (strlen($password) < 8) {
            $error = 'Your new password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'The two passwords do not match.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userID = (int)$resetRecord['userID'];
            $resetID = (int)$resetRecord['resetID'];

            mysqli_begin_transaction($conn);

            try {
                $update = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE userID = ?');
                mysqli_stmt_bind_param($update, 'si', $hashedPassword, $userID);
                mysqli_stmt_execute($update);

                $used = mysqli_prepare($conn, 'UPDATE password_resets SET usedAt = NOW() WHERE resetID = ?');
                mysqli_stmt_bind_param($used, 'i', $resetID);
                mysqli_stmt_execute($used);

                $removeOthers = mysqli_prepare($conn, 'DELETE FROM password_resets WHERE userID = ? AND resetID <> ?');
                mysqli_stmt_bind_param($removeOthers, 'ii', $userID, $resetID);
                mysqli_stmt_execute($removeOthers);

                mysqli_commit($conn);
                redirect_to('/login.php?reset=success');
            } catch (Throwable $exception) {
                mysqli_rollback($conn);
                $error = 'The password could not be updated. Please try again.';
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
    <title>Reset Password | MyFriend</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=80'); ?>">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm">
                <h2 class="mb-3">Reset Password</h2>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <?php if (!$resetRecord): ?>
                    <div class="alert alert-warning">This reset link is invalid or has expired.</div>
                    <a class="btn btn-shop" href="<?php echo app_url('/forgot-password.php'); ?>">Request a New Link</a>
                <?php else: ?>
                    <p>Choose a new password for <strong><?php echo h($resetRecord['email']); ?></strong>.</p>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="token" value="<?php echo h($token); ?>">

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirmPassword" class="form-control" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-shop">Update Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
