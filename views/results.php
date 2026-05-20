<?php
// ── Results Page ──────────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

// Must have a completed quiz (not active, but results available)
if (!empty($_SESSION['quiz_active']) || empty($_SESSION['quiz_total'])) {
    redirect_to('../views/dashboard.php');
}

$score        = (int)($_SESSION['quiz_score']  ?? 0);
$total        = (int)($_SESSION['quiz_total']  ?? 0);
$lives        = (int)($_SESSION['quiz_lives']  ?? 0);
$wrongAnswers = $_SESSION['quiz_wrong_answers'] ?? [];
$xpEarned     = $score * 10;
$unlockedAchievements = $_SESSION['unlocked_achievements'] ?? [];
$pct          = $total > 0 ? round(($score / $total) * 100) : 0;

// Choose a rating
if ($pct >= 90)      { $rating = '🏆 Perfect!';    $color = '#10b981'; }
elseif ($pct >= 70)  { $rating = '⭐ Great job!';   $color = '#4f46e5'; }
elseif ($pct >= 50)  { $rating = '👍 Not bad!';     $color = '#f59e0b'; }
else                  { $rating = '📚 Keep trying!'; $color = '#ef4444'; }

// Clear quiz session data after reading it
$quizCategory = $_SESSION["quiz_category"] ?? "";
$quizDifficulty = $_SESSION["quiz_difficulty"] ?? "";
unset($_SESSION["quiz_questions"], $_SESSION["quiz_index"], $_SESSION["quiz_score"],
      $_SESSION['quiz_lives'],     $_SESSION['quiz_total'],  $_SESSION['quiz_active'],
      $_SESSION['quiz_wrong_answers'], $_SESSION['unlocked_achievements']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results — TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quiz.css">
</head>
<body>

<div class="quiz-wrapper">
    <div class="result-card">
        <div class="result-icon">🎉</div>
        <h1>Quiz Complete!</h1>
        <p style="color: <?= $color ?>; font-size:1.2rem; font-weight:700; margin-bottom:6px"><?= $rating ?></p>
        <p>You answered <?= $score ?> out of <?= $total ?> questions correctly</p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="val"><?= $score ?>/<?= $total ?></div>
                <div class="lbl">Score</div>
            </div>
            <div class="result-stat">
                <div class="val"><?= $pct ?>%</div>
                <div class="lbl">Accuracy</div>
            </div>
            <div class="result-stat">
                <div class="val">+<?= $xpEarned ?></div>
                <div class="lbl">XP Earned</div>
            </div>
            <div class="result-stat">
                <div class="val"><?= $lives ?></div>
                <div class="lbl">Lives Left</div>
            </div>
        </div>

        <?php if (!empty($unlockedAchievements)): ?>
        <div class="wrong-list">
            <h3>Achievements Unlocked</h3>
            <?php foreach ($unlockedAchievements as $achievement): ?>
            <div class="wrong-item">
                <div class="q"><?= htmlspecialchars($achievement['title']) ?></div>
                <div class="a">Badge unlocked</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($wrongAnswers)): ?>
        <div class="wrong-list">
            <h3>❌ Review Wrong Answers (<?= count($wrongAnswers) ?>)</h3>
            <?php foreach ($wrongAnswers as $item): ?>
            <div class="wrong-item">
                <div class="q"><?= htmlspecialchars($item['question']) ?></div>
                <div class="a">✓ <?= htmlspecialchars($item['correct_answer']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="../views/select_quiz.php" class="btn btn-primary">🔄 Play Again</a>
            <a href="achievements.php" class="btn btn-outline">🏅 Achievements</a>
            <a href="leaderboard.php" class="btn btn-outline">🏆 Leaderboard</a>
            <a href="dashboard.php" class="btn btn-outline">🏠 Dashboard</a>
        </div>
    </div>
</div>

<script>
// ── Theme Toggle ───────────────────────────────────────────
const savedTheme = localStorage.getItem('theme') || 'dark';
setTheme(savedTheme);

function setTheme(theme) {
    if (theme === 'light') {
        document.body.classList.add('light-mode');
    } else {
        document.body.classList.remove('light-mode');
    }

    if (theme === 'light') {
        document.documentElement.style.setProperty('--bg', '#ffffff');
        document.documentElement.style.setProperty('--card-bg', '#ffffff');
        document.documentElement.style.setProperty('--border', '#e2e8f0');
        document.documentElement.style.setProperty('--text', '#111827');
        document.documentElement.style.setProperty('--muted', '#64748b');
    } else {
        document.documentElement.style.setProperty('--bg', '#0f0e1a');
        document.documentElement.style.setProperty('--card-bg', '#1a1830');
        document.documentElement.style.setProperty('--border', '#2e2b4a');
        document.documentElement.style.setProperty('--text', '#f1f0ff');
        document.documentElement.style.setProperty('--muted', '#9ca3af');
    }
}
</script>

</body>
</html>
