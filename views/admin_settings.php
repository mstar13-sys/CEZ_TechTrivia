<?php
// ── Settings Page (Admin) ────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$user      = $userModel->getUserById($_SESSION['user_id']);

$flash_success = get_flash('success');
$flash_error   = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ Admin</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🛡️</div>
        <div class="brand-name">CEZ<br><span>Admin</span></div>
    </div>
    <div class="admin-badge">⚙ Admin Panel</div>
    <p class="nav-section-label">Management</p>
    <ul class="nav-menu">
        <li><a href="admin.php" class="nav-link"><span class="nav-icon">📊</span> Overview</a></li>
        <li><a href="admin.php?tab=players" class="nav-link"><span class="nav-icon">👥</span> Players</a></li>
        <li><a href="admin.php?tab=questions" class="nav-link"><span class="nav-icon">❓</span> Questions</a></li>
        <li><a href="admin.php?tab=achievements" class="nav-link"><span class="nav-icon">🏅</span> Achievements</a></li>
        <li><a href="admin.php?tab=ranks" class="nav-link"><span class="nav-icon">R</span> Ranks</a></li>
    </ul>
    <p class="nav-section-label">Account</p>
    <ul class="nav-menu">
        <li><a href="admin_settings.php" class="active"><span class="nav-icon">⚙️</span> Settings</a></li>
    </ul>
    <div class="sidebar-footer">
        <form class="logout-form" action="../controllers/AuthController.php" method="POST"
              onsubmit="return confirmLogout(event)">
            <input type="hidden" name="action" value="logout">
            <?= csrf_field() ?>
            <button type="submit">🚪 Sign Out</button>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Settings</h1>
            <p>Manage your admin account preferences</p>
        </div>
        <span style="color:var(--text-muted);font-size:13px" id="adminDate"></span>
    </div>

    <!-- Settings Cards -->
    <div class="settings-container">
        <!-- Theme Toggle -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">🎨</div>
                <div>
                    <h3>Appearance</h3>
                    <p>Customize your visual experience</p>
                </div>
            </div>
            <div class="settings-card-body">
                <div class="theme-toggle">
                    <span class="theme-label">Theme Mode</span>
                    <div class="theme-switch">
                        <button class="theme-btn" data-theme="dark" title="Dark Mode">
                            <span>🌙</span>
                            <span>Dark</span>
                        </button>
                        <button class="theme-btn" data-theme="light" title="Light Mode">
                            <span>☀️</span>
                            <span>Light</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Username Change -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">👤</div>
                <div>
                    <h3>Username</h3>
                    <p>Change your display name</p>
                </div>
            </div>
            <div class="settings-card-body">
                <form method="POST" action="../controllers/SettingsController.php" class="settings-form">
                    <input type="hidden" name="settings_action" value="update_username">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Current Username</label>
                        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="form-control disabled">
                    </div>
                    <div class="form-group">
                        <label for="new_username">New Username</label>
                        <input type="text" id="new_username" name="new_username" class="form-control" placeholder="Enter new username..." required minlength="3">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Username</button>
                </form>
            </div>
        </div>

        <!-- Password Change -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">🔒</div>
                <div>
                    <h3>Password</h3>
                    <p>Update your password for security</p>
                </div>
            </div>
            <div class="settings-card-body">
                <form method="POST" action="../controllers/SettingsController.php" class="settings-form">
                    <input type="hidden" name="settings_action" value="update_password">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password..." required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password..." required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password..." required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Account Info -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon">ℹ️</div>
                <div>
                    <h3>Account Information</h3>
                    <p>Your account details</p>
                </div>
            </div>
            <div class="settings-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role</span>
                        <span class="info-value"><?= ucfirst($user['role']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total XP</span>
                        <span class="info-value"><?= (int)$user['total_xp'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Joined</span>
                        <span class="info-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/settings.js"></script>
<?php if ($flash_success): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLightMode = document.body.classList.contains('light-mode');
    Swal.fire({ icon:'success', title:'Done!', text:<?= json_encode($flash_success) ?>, background:isLightMode?'#ffffff':'#1a1830', color:isLightMode?'#111827':'#f1f0ff', confirmButtonColor:'#2563eb' });
});
</script>
<?php endif; ?>
<?php if ($flash_error): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLightMode = document.body.classList.contains('light-mode');
    Swal.fire({ icon:'error', title:'Error', text:<?= json_encode($flash_error) ?>, background:isLightMode?'#ffffff':'#1a1830', color:isLightMode?'#111827':'#f1f0ff', confirmButtonColor:'#2563eb' });
});
</script>
<?php endif; ?>
</body>
</html>

