<?php
require_once __DIR__ . '/config/app.php';

// Start session
session_start();

// Destroy session
session_destroy();

// Redirect user
redirect_to('/index.php');
