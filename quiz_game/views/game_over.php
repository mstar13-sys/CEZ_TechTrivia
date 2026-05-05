<?php
// ── Game Over Page ────────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

if (!empty($_SESSION['quiz_active'])) {
    redirect_to('../views/dashboard.php');
}

$score        = (int)($_SESSION['quiz_score']  ?? 0);
$total        = (int)($_SESSION['quiz_total']  ?? 0);
$wrongAnswers = $_SESSION['quiz_wrong_answers'] ?? [];
$xpEarned     = $score * 10;
$answered     = (int)($_SESSION['quiz_index']  ?? 0);

// Clear quiz session data
unset($_SESSION['quiz_questions'], $_SESSION['quiz_index'], $_SESSION['quiz_score'],
      $_SESSION['quiz_lives'],     $_SESSION['quiz_total'],  $_SESSION['quiz_active'],
      $_SESSION['quiz_wrong_answers']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Over — TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quiz.css">
    <style>
        .result-icon { animation: shake 0.5s ease; }
        @keyframes shake {
            0%,100% { transform: rotate(0); }
            20%     { transform: rotate(-15deg); }
            40%     { transform: rotate(15deg); }
            60%     { transform: rotate(-10deg); }
            80%     { transform: rotate(10deg); }
        }
    </style>
</head>
<body>

<div class="quiz-wrapper">
    <div class="result-card">
        <div class="result-icon">💀</div>
        <h1>Game Over!</h1>
        <p style="color:#ef4444;font-size:1.1rem;font-weight:700;margin-bottom:6px">You ran out of lives</p>
        <p>You answered <?= $score ?> out of <?= $answered ?> questions correctly before losing all 3 lives.</p>

        <div class="result-stats">
            <div class="result-stat">
                <div class="val"><?= $score ?>/<?= $answered ?></div>
                <div class="lbl">Score</div>
            </div>
            <div class="result-stat">
                <div class="val">0</div>
                <div class="lbl">Lives Left</div>
            </div>
            <div class="result-stat">
                <div class="val">+<?= $xpEarned ?></div>
                <div class="lbl">XP Earned</div>
            </div>
        </div>

        <?php if (!empty($wrongAnswers)): ?>
        <div class="wrong-list">
            <h3>❌ Wrong Answers</h3>
            <?php foreach ($wrongAnswers as $item): ?>
            <div class="wrong-item">
                <div class="q"><?= htmlspecialchars($item['question']) ?></div>
                <div class="a">✓ <?= htmlspecialchars($item['correct_answer']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="../views/select_quiz.php" class="btn btn-primary">🔄 Try Again</a>
            <a href="dashboard.php" class="btn btn-outline">🏠 Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
