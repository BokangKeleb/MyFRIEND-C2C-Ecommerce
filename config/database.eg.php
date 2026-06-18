<?php

$servername = "sql101.infinityfree.com";
$username = "if0_42088700";
$password = "JL2V4uVaPQqZAm8";
$database = "if0_42088700_myfrienddb";

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $database
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
