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
$maxDeleteReasonLength = 500;

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
            } elseif (strlen($newUsername) < 3 || strlen($newUsername) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
                $message = 'Username must be 3-20 characters and use only letters, numbers, and underscores.'; $msgType = 'error';
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

        if ($act === 'delete_account') {
            $reason = normalize_delete_reason($_POST['delete_reason'] ?? '');
            if (is_admin()) {
                $message = 'Admin accounts cannot be deleted here.'; $msgType = 'error';
            } elseif ($reason === '') {
                $message = 'A delete reason is required.'; $msgType = 'error';
            } elseif (strlen($reason) > $maxDeleteReasonLength) {
                $message = 'Delete reason must be 500 characters or fewer.'; $msgType = 'error';
            } else {
                $result = $userModel->deletePlayer($userId, $reason, [
                    'id' => $userId,
                    'username' => $_SESSION['username'] ?? 'Self',
                ]);

                if ($result) {
                    $_SESSION = [];
                    if (ini_get('session.use_cookies')) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params['path'], $params['domain'], $params['secure'], $params['httponly']
                        );
                    }
                    session_destroy();
                    redirect_to('../index.php');
                }

                $message = 'Failed to delete your account.'; $msgType = 'error';
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
