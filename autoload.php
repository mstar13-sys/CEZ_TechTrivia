<?php
// ── Bootstrap ─────────────────────────────────────────────────
// Include this file at the top of every entry-point PHP file.
// It starts the session, generates a CSRF token, and loads core files.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load core classes and helpers
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/SoftDeleteStore.php';
require_once __DIR__ . '/core/NotificationStore.php';
require_once __DIR__ . '/helpers/csrf_helper.php';
require_once __DIR__ . '/helpers/session_helper.php';
