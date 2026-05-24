<?php
require_once __DIR__ . '/../autoload.php';

if (!is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';

$notificationStore = new NotificationStore();
$userModel = new User();
$message = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $msgType = 'error';
    } else {
        $action = $_POST['notification_action'] ?? '';
        $notificationId = $_POST['notification_id'] ?? '';

        if ($action === 'mark_read') {
            $result = $notificationStore->markRead($notificationId);
            $message = $result ? 'Notification marked as read.' : 'Failed to update notification.';
            $msgType = $result ? 'success' : 'error';
        }

        if ($action === 'restore_player') {
            $playerId = (int)($_POST['player_id'] ?? 0);
            $result = $userModel->restorePlayer($playerId);
            if ($result && $notificationId !== '') {
                $notificationStore->markRead($notificationId);
            }
            $message = $result ? 'Player account restored.' : 'Failed to restore player account.';
            $msgType = $result ? 'success' : 'error';
        }
    }
}

$notifications = $notificationStore->all();
$notificationCount = $notificationStore->unreadCount();
$softDeletes = new SoftDeleteStore();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CEZ Admin</title>
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ Admin</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><img src="../assets/image/logo.png" alt="CEZ TechTrivia logo"></div>
        <div class="brand-name">CEZ<br><span>Admin</span></div>
    </div>
    <div class="admin-badge">&#9881; Admin Panel</div>
    <p class="nav-section-label">Management</p>
    <ul class="nav-menu">
        <li><a href="admin.php" class="nav-link"><span class="nav-icon">&#128202;</span> Overview</a></li>
        <li><a href="admin.php?tab=players" class="nav-link"><span class="nav-icon">&#128101;</span> Players</a></li>
        <li><a href="admin.php?tab=questions" class="nav-link"><span class="nav-icon">&#10067;</span> Questions</a></li>
        <li><a href="admin.php?tab=achievements" class="nav-link"><span class="nav-icon">&#127941;</span> Achievements</a></li>
        <li><a href="admin.php?tab=ranks" class="nav-link"><span class="nav-icon">&#127942;</span> Ranks</a></li>
        <li><a href="deleted.php"><span class="nav-icon">&#128465;</span> Deleted</a></li>
        <li><a href="notifications.php" class="active"><span class="nav-icon">&#128276;</span> Notifications<?= $notificationCount > 0 ? ' <span class="nav-count">' . (int)$notificationCount . '</span>' : '' ?></a></li>
    </ul>
    <p class="nav-section-label">Account</p>
    <ul class="nav-menu">
        <li><a href="admin_settings.php"><span class="nav-icon">&#9881;</span> Settings</a></li>
    </ul>
    <div class="sidebar-footer">
        <form class="logout-form" action="../controllers/AuthController.php" method="POST" onsubmit="return confirmLogout(event)">
            <input type="hidden" name="action" value="logout">
            <?= csrf_field() ?>
            <button type="submit">&#128682; Sign Out</button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Notifications</h1>
            <p>Account deletion and recovery requests</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">&#127769;</span>
            </button>
            <span style="color:var(--text-muted);font-size:13px" id="adminDate"></span>
        </div>
    </div>

    <div class="notification-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state"><div class="empty-icon">&#128276;</div><p>No notifications yet.</p></div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <?php $payload = is_array($notification['payload'] ?? null) ? $notification['payload'] : []; ?>
                <?php
                    $playerId = (int)($payload['player_id'] ?? 0);
                    $isRecoveryRequest = ($notification['type'] ?? '') === 'recovery_request';
                    $isAccountStillDeleted = $playerId > 0 && $softDeletes->isDeleted('players', $playerId);
                ?>
                <div class="notification-card <?= empty($notification['is_read']) ? 'unread' : '' ?>">
                    <div class="notification-main">
                        <div class="notification-title">
                            <?= htmlspecialchars($notification['title'] ?? 'Notification') ?>
                            <?php if (empty($notification['is_read'])): ?><span class="badge badge-medium">New</span><?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                        <div class="notification-meta">
                            <?= htmlspecialchars($notification['created_at'] ?? '') ?> &middot;
                            <?= htmlspecialchars(str_replace('_', ' ', $notification['type'] ?? 'notice')) ?>
                        </div>

                        <?php if (!empty($payload)): ?>
                            <div class="notification-payload">
                                <?php foreach ($payload as $key => $value): ?>
                                    <div><strong><?= htmlspecialchars(str_replace('_', ' ', $key)) ?>:</strong> <?= htmlspecialchars((string)$value) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="notification-actions">
                        <?php if ($isRecoveryRequest && $isAccountStillDeleted): ?>
                            <form method="POST" onsubmit="return confirmRestore(event, 'player account')">
                                <input type="hidden" name="notification_action" value="restore_player">
                                <input type="hidden" name="notification_id" value="<?= htmlspecialchars($notification['id'] ?? '') ?>">
                                <input type="hidden" name="player_id" value="<?= $playerId ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">Restore Account</button>
                            </form>
                        <?php elseif ($isRecoveryRequest && $playerId > 0): ?>
                            <span class="badge badge-easy">Recovered</span>
                        <?php endif; ?>

                        <?php if (empty($notification['is_read'])): ?>
                            <form method="POST">
                                <input type="hidden" name="notification_action" value="mark_read">
                                <input type="hidden" name="notification_id" value="<?= htmlspecialchars($notification['id'] ?? '') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">Mark Read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
function confirmRestore(event, type) {
    event.preventDefault();
    const form = event.target;
    Swal.fire({
        icon: 'question',
        title: 'Restore ' + type + '?',
        text: 'This will make the account active again.',
        background: '#1a1830',
        color: '#f1f0ff',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#4f46e5',
        confirmButtonText: 'Restore',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
    return false;
}
</script>
<?php if ($message): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLightMode = document.body.classList.contains('light-mode');
    Swal.fire({
        icon: <?= json_encode($msgType) ?>,
        title: <?= $msgType === 'success' ? "'Done!'" : "'Error'" ?>,
        text: <?= json_encode($message) ?>,
        background: isLightMode ? '#ffffff' : '#1a1830',
        color: isLightMode ? '#111827' : '#f1f0ff',
        confirmButtonColor: '#2563eb',
    });
});
</script>
<?php endif; ?>
</body>
</html>
