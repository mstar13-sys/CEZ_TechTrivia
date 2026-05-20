<?php
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in() || is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel   = new User();
$playerStats = $userModel->getPlayerStats($_SESSION['user_id']) ?? [];
$rank = $userModel->getRankForXp((int)($playerStats['total_xp'] ?? 0));
$nextRank = $userModel->getNextRankForXp((int)($playerStats['total_xp'] ?? 0));
$achievementSummary = $userModel->getAchievementSummary($_SESSION['user_id']) ?: [];

$flash_success = get_flash('success');
$flash_error   = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TechTrivia</title>
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
        <li><a href="dashboard.php" class="active"><span class="nav-icon">🏠</span> Dashboard</a></li>
        <li><a href="../views/select_quiz.php"><span class="nav-icon">🎮</span> Start Quiz</a></li>
        <li><a href="achievements.php"><span class="nav-icon">🏅</span> Achievements</a></li>
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
            <h1>Welcome back, <?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?>!</h1>
            <p>Ready to test your tech knowledge today?</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">🌙</span>
            </button>
            <div class="header-date" id="currentDate"></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon indigo">🎯</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['games_played'] ?? 0) ?></div>
                <div class="stat-label">Quizzes Played</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold">⭐</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['total_xp'] ?? 0) ?></div>
                <div class="stat-label">Total XP</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">📈</div>
            <div>
                <div class="stat-value"><?= round((float)($playerStats['avg_score'] ?? 0), 1) ?></div>
                <div class="stat-label">Avg. Score</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">🏆</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['best_score'] ?? 0) ?></div>
                <div class="stat-label">Best Score</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon indigo">🔥</div>
            <div>
                <div class="stat-value"><?= (int)($playerStats['current_streak'] ?? 0) ?></div>
                <div class="stat-label">Day Streak</div>
            </div>
        </div>
    </div>

    <div class="rank-panel">
        <div>
            <span class="rank-label">Current Rank</span>
            <h2><?= htmlspecialchars($rank['rank_name']) ?></h2>
            <p><?= htmlspecialchars($rank['medal'] ?? 'Bronze') ?> medal tier</p>
        </div>
        <div class="rank-progress">
            <?php
                $totalXp = (int)($playerStats['total_xp'] ?? 0);
                $rankMin = (int)($rank['min_xp'] ?? 0);
                $nextMin = $nextRank ? (int)$nextRank['min_xp'] : max($totalXp, $rankMin + 1);
                $progress = $nextRank ? min(100, max(0, (($totalXp - $rankMin) / max(1, $nextMin - $rankMin)) * 100)) : 100;
            ?>
            <div class="rank-progress-top">
                <span><?= $totalXp ?> XP</span>
                <span><?= $nextRank ? htmlspecialchars($nextRank['rank_name']) . ' at ' . (int)$nextRank['min_xp'] . ' XP' : 'Max rank reached' ?></span>
            </div>
            <div class="rank-bar"><span style="width: <?= round($progress, 1) ?>%"></span></div>
        </div>
    </div>

    <div class="cards-grid">
        <div class="card" onclick="window.location.href='../views/select_quiz.php'">
            <div class="card-icon">🎮</div>
            <h3>Start Quiz</h3>
            <p>Challenge yourself with questions across different tech categories and difficulty levels.</p>
            <div class="card-arrow">-&gt;</div>
        </div>
        <div class="card" onclick="window.location.href='achievements.php'">
            <div class="card-icon">🏅</div>
            <h3>Achievements</h3>
            <p><?= (int)($achievementSummary['unlocked_achievements'] ?? 0) ?> of <?= (int)($achievementSummary['total_achievements'] ?? 0) ?> unlocked. Collect badges as you master quizzes.</p>
            <div class="card-arrow">-&gt;</div>
        </div>
        <div class="card" onclick="window.location.href='leaderboard.php'">
            <div class="card-icon">🏆</div>
            <h3>Leaderboard</h3>
            <p>See how you rank against other players. Climb the ranks and earn your spot at the top.</p>
            <div class="card-arrow">-&gt;</div>
        </div>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/dashboard.js"></script>
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
