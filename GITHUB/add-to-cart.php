<?php
require_once __DIR__ . '/app.php';

// Show PHP errors while working locally
error_reporting(E_ALL);
ini_set('display_errors', 1);

// database server
$servername = "sql101.infinityfree.com";

// database username
$username = "if0_42088700";

// password for the database user
$password = "JL2V4uVaPQqZAm8";

// database name
$database = "if0_42088700_myfrienddb";

// Create database connection
$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $database
);

// Stop page if connection fails
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
