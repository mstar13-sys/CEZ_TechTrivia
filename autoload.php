<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database
require_once __DIR__ . "/config/database.php";

// Helpers
require_once __DIR__ . "/helpers/csrf_helper.php";
require_once __DIR__ . "/helpers/encrypt_helper.php";

// Load all classes
//require_once __DIR__ . "/classes/User.php";
//require_once __DIR__ . "/classes/Order.php";
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/classes/$class.php";
});

// Create shared database connection
$database = new Database();
$db = $database->getConnection();