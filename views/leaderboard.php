<?php
// ── Leaderboard Page ──────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();

// Filter params (empty string = no filter)
$filterCategory   = trim($_GET['category']   ?? '');
$filterDifficulty = trim($_GET['difficulty'] ?? '');

// Allowed difficulties
$allowedDiff = ['easy', 'medium', 'hard'];
if (!in_array($filterDifficulty, $allowedDiff, true)) {
    $filterDifficulty = '';
}

// Fetch filtered or global leaderboard
if ($filterCategory !== '' || $filterDifficulty !== '') {
    $leaderboard = $userModel->getFilteredLeaderboard(
        $filterCategory   ?: null,
        $filterDifficulty ?: null,
        20
    );
} else {
    $leaderboard = $userModel->getLeaderboard(20);
}

// Categories played (for filter dropdown)
$playedCategories = $userModel->getLeaderboardCategories();
$medals = ['🥇', '🥈', '🥉'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/leaderboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎯</div>
        <div class="brand-name">CEZ<br><span>TechTrivia</span></div>
    </div>
    <div class="user-profile">
        <div class="user-avatar">🎮</div>
        <div class="user-name"><?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?></div>
        <div class="user-role">Player</div>
    </div>
    <p class="nav-section-label">Navigation</p>
    <ul class="nav-menu">
        <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
        <li><a href="select_quiz.php"><span class="nav-icon">🎮</span> Start Quiz</a></li>
        <li><a href="leaderboard.php" class="active"><span class="nav-icon">🏆</span> Leaderboard</a></li>
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
            <h1>🏆 Leaderboard</h1>
            <p>
                <?php if ($filterCategory !== '' || $filterDifficulty !== ''): ?>
                    Filtered by:
                    <?= $filterCategory !== '' ? '<strong>' . htmlspecialchars($filterCategory) . '</strong>' : '' ?>
                    <?= ($filterCategory !== '' && $filterDifficulty !== '') ? ' / ' : '' ?>
                    <?= $filterDifficulty !== '' ? '<strong>' . ucfirst($filterDifficulty) . '</strong>' : '' ?>
                <?php else: ?>
                    Top players ranked by total XP earned
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">🌙</span>
            </button>
            <div class="header-date" id="currentDate"></div>
        </div>
    </div>

    <!-- Filter bar -->
    <form class="lb-filters" method="GET" action="leaderboard.php" id="filterForm">
        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select name="category" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Categories</option>
                <?php foreach ($playedCategories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= $filterCategory === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Difficulty</label>
            <select name="difficulty" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Difficulties</option>
                <option value="easy"   <?= $filterDifficulty === 'easy'   ? 'selected' : '' ?>>🟢 Easy</option>
                <option value="medium" <?= $filterDifficulty === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
                <option value="hard"   <?= $filterDifficulty === 'hard'   ? 'selected' : '' ?>>🔴 Hard</option>
            </select>
        </div>
        <?php if ($filterCategory !== '' || $filterDifficulty !== ''): ?>
        <a href="leaderboard.php" class="filter-clear">✕ Clear Filters</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="lb-card">
        <div class="table-header">
            <h3>
                <?php if ($filterCategory !== '' || $filterDifficulty !== ''): ?>
                    <?= htmlspecialchars($filterCategory ?: 'All') ?>
                    <?= $filterDifficulty !== '' ? ' · ' . ucfirst($filterDifficulty) : '' ?>
                    Rankings
                <?php else: ?>
                    Top Players
                <?php endif; ?>
            </h3>
            <span class="lb-live-badge">● Live</span>
        </div>

        <?php if (empty($leaderboard)): ?>
        <div class="lb-empty">
            <div class="lb-empty-icon">🏆</div>
            <p>No players found for this filter.<br>
               <?php if ($filterCategory !== '' || $filterDifficulty !== ''): ?>
               <a href="leaderboard.php">View the global leaderboard</a> instead.
               <?php else: ?>
               Be the first to play!
               <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th><?= ($filterCategory !== '' || $filterDifficulty !== '') ? 'XP (filter)' : 'Total XP' ?></th>
                    <th>Level</th>
                    <th>Games</th>
                    <th>Best Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $p):
                    $isMe = $p['username'] === ($_SESSION['username'] ?? '');
                    $xp   = isset($p['filter_xp']) ? (int)$p['filter_xp'] : (int)$p['total_xp'];
                ?>
                <tr <?= $isMe ? 'class="highlight-row"' : '' ?>>
                    <td class="rank-medal"><?= $medals[$i] ?? ($i + 1) ?></td>
                    <td>
                        <span class="lb-avatar"><?= strtoupper(substr($p['username'], 0, 1)) ?></span>
                        <?= htmlspecialchars($p['username']) ?>
                        <?php if ($isMe): ?>
                            <span class="you-badge">(you)</span>
                        <?php endif; ?>
                    </td>
                    <td class="xp-val"><?= number_format($xp) ?></td>
                    <td><?= (int)$p['level'] ?></td>
                    <td><?= (int)$p['games_played'] ?></td>
                    <td><?= (int)$p['best_score'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/leaderboard.js"></script>
<script>
function confirmLogout(e) {
    e.preventDefault();
    const form = e.target;
    const isLightMode = document.body.classList.contains('light-mode');
    Swal.fire({
        icon: 'question', title: 'Sign Out?',
        text: 'Are you sure you want to sign out?',
        background: isLightMode ? '#ffffff' : '#1a1830',
        color: isLightMode ? '#111827' : '#f1f0ff',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', cancelButtonColor: '#2563eb',
        confirmButtonText: 'Yes, sign out',
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}
</script>
</body>
</html>
