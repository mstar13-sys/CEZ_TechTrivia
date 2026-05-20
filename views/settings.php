<?php
// ── Settings Page (User) ────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in() || is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$user      = $userModel->getUserById($_SESSION['user_id']);
$playerStats = $userModel->getPlayerStats($_SESSION['user_id']) ?? [];
$rank = $userModel->getRankForXp((int)($playerStats['total_xp'] ?? 0));

$flash_success = get_flash('success');
$flash_error   = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — TechTrivia</title>
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ TechTrivia</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><img src="../assets/image/logo.png" alt="CEZ TechTrivia logo"></div>
        <div class="brand-name">CEZ<br><span>TechTrivia</span></div>
    </div>

    <div class="user-profile">
        <div class="user-avatar">🎮</div>
        <div class="user-name"><?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?></div>
        <div class="user-role"><?= htmlspecialchars($rank['rank_name']) ?> - Level <?= (int)($playerStats['level'] ?? $user['level'] ?? 1) ?></div>
        <div class="user-rank-medal"><?= htmlspecialchars($rank['medal'] ?? 'Bronze') ?></div>
    </div>

    <p class="nav-section-label">Navigation</p>
    <ul class="nav-menu">
        <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
        <li><a href="../views/select_quiz.php"><span class="nav-icon">🎮</span> Start Quiz</a></li>
        <li><a href="achievements.php"><span class="nav-icon">🏅</span> Achievements</a></li>
        <li><a href="leaderboard.php"><span class="nav-icon">🏆</span> Leaderboard</a></li>
        <li><a href="settings.php" class="active"><span class="nav-icon">⚙️</span> Settings</a></li>
    </ul>

    <div class="sidebar-footer">
        <form class="logout-form" action="../controllers/AuthController.php" method="POST"
              onsubmit="return confirmLogout(event)">
            <input type="hidden" name="action"     value="logout">
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
            <p>Manage your account preferences</p>
        </div>
        <div class="header-date" id="currentDate"></div>
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
                        <span class="info-label">Level</span>
                        <span class="info-value"><?= (int)$user['level'] ?></span>
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
