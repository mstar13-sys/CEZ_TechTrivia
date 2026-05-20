<?php
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in() || is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$playerStats = $userModel->getPlayerStats($_SESSION['user_id']) ?? [];
$rank = $userModel->getRankForXp((int)($playerStats['total_xp'] ?? 0));
$userModel->syncPlayerAchievements($_SESSION['user_id']);
$achievements = $userModel->getPlayerAchievements($_SESSION['user_id']);
$progressStats = $userModel->getAchievementProgressStats($_SESSION['user_id']);
$summary = $userModel->getAchievementSummary($_SESSION['user_id']) ?: [];

$conditionLabels = [
    'quiz_count' => 'Quizzes played',
    'total_xp' => 'Total XP',
    'best_score' => 'Best score',
    'perfect_quiz_count' => 'Perfect quizzes',
    'current_streak' => 'Current daily streak',
    'max_streak' => 'Best daily streak',
    'easy_quiz_count' => 'Easy quizzes completed',
    'medium_quiz_count' => 'Medium quizzes completed',
    'hard_quiz_count' => 'Hard quizzes completed',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ TechTrivia</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎯</div>
        <div class="brand-name">CEZ<br><span>TechTrivia</span></div>
    </div>

    <div class="user-profile">
        <div class="user-avatar">🎮</div>
        <div class="user-name"><?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?></div>
        <div class="user-role"><?= htmlspecialchars($rank['rank_name']) ?> - Level <?= (int)($playerStats['level'] ?? 1) ?></div>
        <div class="user-rank-medal"><?= htmlspecialchars($rank['medal'] ?? 'Bronze') ?></div>
    </div>

    <p class="nav-section-label">Navigation</p>
    <ul class="nav-menu">
        <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
        <li><a href="../views/select_quiz.php"><span class="nav-icon">🎮</span> Start Quiz</a></li>
        <li><a href="achievements.php" class="active"><span class="nav-icon">🏅</span> Achievements</a></li>
        <li><a href="leaderboard.php"><span class="nav-icon">🏆</span> Leaderboard</a></li>
        <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
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

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Achievements</h1>
            <p>Unlock badges by playing, improving, and keeping your streak alive.</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">Moon</span>
            </button>
            <div class="header-date" id="currentDate"></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon gold">A</div>
            <div>
                <div class="stat-value"><?= (int)($summary['unlocked_achievements'] ?? 0) ?>/<?= (int)($summary['total_achievements'] ?? 0) ?></div>
                <div class="stat-label">Unlocked</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon indigo">XP</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['total_xp'] ?? 0) ?></div>
                <div class="stat-label">Total XP</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">STR</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['max_streak'] ?? 0) ?></div>
                <div class="stat-label">Best Streak</div>
            </div>
        </div>
    </div>

    <div class="achievement-grid">
        <?php foreach ($achievements as $achievement): ?>
            <?php
                $unlocked = (int)$achievement['unlocked'] === 1;
                $current = (int)($progressStats[$achievement['condition_type']] ?? 0);
                $needed = max(1, (int)$achievement['condition_value']);
                $pct = min(100, ($current / $needed) * 100);
                $label = $conditionLabels[$achievement['condition_type']] ?? $achievement['condition_type'];
            ?>
            <div class="achievement-card <?= $unlocked ? 'unlocked' : 'locked' ?>">
                <div class="achievement-top">
                    <div class="achievement-icon"><?= $unlocked ? '✓' : '🔒' ?></div>
                    <span class="achievement-status"><?= $unlocked ? 'Unlocked' : 'Locked' ?></span>
                </div>
                <h3><?= htmlspecialchars($achievement['title']) ?></h3>
                <p><?= htmlspecialchars($achievement['description']) ?></p>
                <div class="achievement-condition"><?= htmlspecialchars($label) ?>: <?= $current ?> / <?= $needed ?></div>
                <div class="rank-bar"><span style="width: <?= round($pct, 1) ?>%"></span></div>
                <?php if ($unlocked): ?>
                    <div class="achievement-date">Unlocked <?= date('M j, Y', strtotime($achievement['date_unlocked'])) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
