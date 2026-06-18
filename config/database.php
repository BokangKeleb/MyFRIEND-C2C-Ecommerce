<?php
require_once __DIR__ . '/app.php';

// Show PHP errors while working locally
error_reporting(E_ALL);
ini_set('display_errors', 1);

// database server
$servername = "YOUR-DATABASE-HOST";

// database username
$username = "YOUR DATABASE USERNAME";

// password for the database user
$password = "YOUR DATABASE PASSWORD";

// database name
$database = "YOUR DATABASE NAME";

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
