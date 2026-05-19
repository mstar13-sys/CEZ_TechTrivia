<?php
// ── Session Helpers ───────────────────────────────────────────

// Check if the current user is logged in
function is_logged_in() {
    return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if the current user is an admin
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

// Redirect to a path relative to the project root (no trailing slash on $path)
// Example: redirect_to('/login.php');
function redirect_to($path) {
    header('Location: ' . $path);
    exit;
}

// Store a one-time flash message in the session
function set_flash($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}

// Read and clear a flash message
function get_flash($key) {
    $val = $_SESSION['flash_' . $key] ?? null;
    unset($_SESSION['flash_' . $key]);
    return $val;
}
