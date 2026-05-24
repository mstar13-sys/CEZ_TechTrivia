<?php
// ── Session Helpers ───────────────────────────────────────────

// Check if the current user is logged in
function is_logged_in() {
    if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }

    if (($_SESSION['role'] ?? 'player') !== 'admin' && !empty($_SESSION['user_id']) && class_exists('SoftDeleteStore')) {
        $softDeletes = new SoftDeleteStore();
        if ($softDeletes->isDeleted('players', $_SESSION['user_id'])) {
            return false;
        }
    }

    return true;
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

// Normalize free-text delete reasons before saving them.
function normalize_delete_reason($reason) {
    $reason = trim((string)$reason);
    $reason = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $reason);
    $reason = preg_replace('/\s+/', ' ', $reason);
    return trim($reason);
}

function normalize_recovery_reason($reason) {
    return normalize_delete_reason($reason);
}
