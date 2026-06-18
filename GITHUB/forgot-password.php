<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/cart.php');
}

verify_csrf();

$userID = (int)$_SESSION['userID'];
$stmt = mysqli_prepare($conn, 'DELETE FROM cart WHERE userID = ?');
mysqli_stmt_bind_param($stmt, 'i', $userID);
mysqli_stmt_execute($stmt);

redirect_to('/cart.php?cleared=1');
