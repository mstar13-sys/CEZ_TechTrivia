<?php
// ── Settings Controller ──────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$message   = null;
$msgType   = 'success';

// ── Handle POST actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.'; $msgType = 'error';
    } else {
        $act = $_POST['settings_action'] ?? '';
        $userId = $_SESSION['user_id'];

        if ($act === 'update_username') {
            $newUsername = trim($_POST['new_username'] ?? '');
            if (empty($newUsername)) {
                $message = 'Username cannot be empty.'; $msgType = 'error';
            } elseif (strlen($newUsername) < 3) {
                $message = 'Username must be at least 3 characters.'; $msgType = 'error';
            } else {
                $result = $userModel->updateUsername($userId, $newUsername);
                $message = $result['message'];
                $msgType = $result['success'] ? 'success' : 'error';
                if ($result['success']) {
                    $_SESSION['username'] = $newUsername;
                }
            }
        }

        if ($act === 'update_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $message = 'All password fields are required.'; $msgType = 'error';
            } elseif (strlen($newPassword) < 6) {
                $message = 'New password must be at least 6 characters.'; $msgType = 'error';
            } elseif ($newPassword !== $confirmPassword) {
                $message = 'New passwords do not match.'; $msgType = 'error';
            } else {
                $result = $userModel->updatePassword($userId, $currentPassword, $newPassword);
                $message = $result['message'];
                $msgType = $result['success'] ? 'success' : 'error';
            }
        }
    }
}

// Redirect back to settings page with flash message
if ($message) {
    set_flash($msgType, $message);
}

$redirectUrl = is_admin() ? '../views/admin_settings.php' : '../views/settings.php';
redirect_to($redirectUrl);
