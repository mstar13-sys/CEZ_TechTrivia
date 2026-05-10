<?php
// ── Quiz Selection Screen ─────────────────────────────────────
// Student picks category + difficulty before quiz starts.
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in() || is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel  = new User();
$categories = $userModel->getAvailableCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose Quiz — TechTrivia</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/select_quiz.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar (same as dashboard) -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎯</div>
        <div class="brand-name">CEZ<br><span>TechTrivia</span></div>
    </div>
    <div class="user-profile">
        <div class="user-avatar">🎮</div>
        <div class="user-name"><?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?></div>
        <div class="user-role">Player — Level <?= (int)($_SESSION['level'] ?? 1) ?></div>
    </div>
    <p class="nav-section-label">Navigation</p>
    <ul class="nav-menu">
        <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
        <li><a href="select_quiz.php" class="active"><span class="nav-icon">🎮</span> Start Quiz</a></li>
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

<!-- Main -->
<div class="main-content">
    <div class="page-header">
        <div>
            <h1>🎮 Start a Quiz</h1>
            <p>Choose a category and difficulty level to begin</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">🌙</span>
            </button>
            <div class="header-date" id="currentDate"></div>
        </div>
    </div>

    <div class="selection-container">

        <!-- Step 1: Category -->
        <div class="step-card" id="stepCategory">
            <div class="step-header">
                <span class="step-badge">Step 1</span>
                <h2>Choose a Category</h2>
                <p>What topic do you want to be tested on?</p>
            </div>
            <div class="category-grid">
                <?php
                $catIcons = [
                    'Web Development' => '🌐',
                    'HTML & CSS'      => '🎨',
                    'JavaScript'      => '⚡',
                    'Networking'      => '🔗',
                    'Algorithms'      => '🧠',
                    'General'         => '📚',
                ];
                $catColors = [
                    'Web Development' => 'blue',
                    'HTML & CSS'      => 'orange',
                    'JavaScript'      => 'yellow',
                    'Networking'      => 'green',
                    'Algorithms'      => 'purple',
                    'General'         => 'teal',
                ];
                foreach ($categories as $cat):
                    $icon  = $catIcons[$cat]  ?? '📖';
                    $color = $catColors[$cat] ?? 'indigo';
                ?>
                <button class="cat-btn" data-category="<?= htmlspecialchars($cat) ?>"
                        data-color="<?= $color ?>">
                    <span class="cat-icon"><?= $icon ?></span>
                    <span class="cat-name"><?= htmlspecialchars($cat) ?></span>
                    <span class="cat-check">✓</span>
                </button>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <div style="font-size:3rem;margin-bottom:12px">📭</div>
                    <p>No questions available yet. Ask an admin to add some!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Step 2: Difficulty (hidden until category picked) -->
        <div class="step-card" id="stepDifficulty" style="display:none">
            <div class="step-header">
                <span class="step-badge">Step 2</span>
                <h2>Choose Difficulty</h2>
                <p>How hard do you want it to be?</p>
            </div>
            <div class="difficulty-grid">
                <button class="diff-btn diff-easy" data-difficulty="easy">
                    <span class="diff-icon">🟢</span>
                    <div>
                        <div class="diff-name">Easy</div>
                        <div class="diff-desc">Great for beginners</div>
                    </div>
                    <span class="diff-xp">+5 XP each</span>
                </button>
                <button class="diff-btn diff-medium" data-difficulty="medium">
                    <span class="diff-icon">🟡</span>
                    <div>
                        <div class="diff-name">Medium</div>
                        <div class="diff-desc">For confident players</div>
                    </div>
                    <span class="diff-xp">+10 XP each</span>
                </button>
                <button class="diff-btn diff-hard" data-difficulty="hard">
                    <span class="diff-icon">🔴</span>
                    <div>
                        <div class="diff-name">Hard</div>
                        <div class="diff-desc">Expert level challenge</div>
                    </div>
                    <span class="diff-xp">+15 XP each</span>
                </button>
            </div>
            <p id="diffUnavailableMsg" class="diff-unavailable" style="display:none">
                ⚠️ No questions for this difficulty in the selected category.
            </p>
        </div>

        <!-- Step 3: Confirm & Launch -->
        <div class="step-card launch-card" id="stepLaunch" style="display:none">
            <div class="launch-summary">
                <div class="launch-icon">🚀</div>
                <h2>Ready to Play!</h2>
                <div class="launch-details">
                    <div class="launch-detail">
                        <span class="ld-label">Category</span>
                        <span class="ld-value" id="summaryCategory">—</span>
                    </div>
                    <div class="launch-detail">
                        <span class="ld-label">Difficulty</span>
                        <span class="ld-value" id="summaryDifficulty">—</span>
                    </div>
                    <div class="launch-detail">
                        <span class="ld-label">Questions</span>
                        <span class="ld-value" id="summaryCount">—</span>
                    </div>
                </div>
                <form action="../controllers/QuizController.php" method="POST" id="launchForm">
                    <input type="hidden" name="action"     value="start">
                    <input type="hidden" name="category"   id="inputCategory">
                    <input type="hidden" name="difficulty" id="inputDifficulty">
                    <?= csrf_field() ?>
                    <button type="submit" class="launch-btn" id="launchBtn">
                        <span>Start Quiz</span> <span class="launch-arrow">→</span>
                    </button>
                </form>
                <button class="back-btn" onclick="resetSelection()">← Change Selection</button>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/select_quiz.js"></script>
</body>
</html>
