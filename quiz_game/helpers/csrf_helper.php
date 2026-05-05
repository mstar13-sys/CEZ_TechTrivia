<?php
// ── CSRF Helpers ──────────────────────────────────────────────

// Returns the current CSRF token (for use in hidden form fields)
function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

// Validates a CSRF token submitted from a form
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Renders a hidden CSRF input field (convenience shortcut)
function csrf_field() {
    $token = htmlspecialchars(csrf_token());
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
}
