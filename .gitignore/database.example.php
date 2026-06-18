<?php

$servername = "YOUR_DATABASE_HOST";
$username = "YOUR_DATABASE_USERNAME";
$password = "YOUR_DATABASE_PASSWORD";
$database = "YOUR_DATABASE_NAME";

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $database
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
