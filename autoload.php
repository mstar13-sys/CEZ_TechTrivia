<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database
require_once __DIR__ . "/quiz_game/config/database.php";

// Helpers
require_once __DIR__ . "/quiz_game/helpers/csrf_helper.php";
require_once __DIR__ . "/quiz_game/helpers/encrypt_helper.php";