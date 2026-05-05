<?php
// ── Quiz View ─────────────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

if (empty($_SESSION['quiz_active']) || empty($_SESSION['quiz_questions'])) {
    set_flash('error', 'No active quiz. Start one from the dashboard!');
    redirect_to('../views/select_quiz.php');
}

$questions  = $_SESSION['quiz_questions'];
$index      = (int)$_SESSION['quiz_index'];
$lives      = (int)$_SESSION['quiz_lives'];
$score      = (int)$_SESSION['quiz_score'];
$total      = (int)$_SESSION['quiz_total'];
$category   = $_SESSION['quiz_category']   ?? '';
$difficulty = $_SESSION['quiz_difficulty'] ?? '';

if ($index >= $total) {
    redirect_to('../views/results.php');
}

$current  = $questions[$index];
$letters  = ['A', 'B', 'C', 'D'];
$progress = round(($index / $total) * 100);
$feedback = $_SESSION['quiz_feedback'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiz — TechTrivia</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/quiz.css">
</head>
<body>

<div class="quiz-wrapper">

    <!-- Header -->
    <div class="quiz-header">
        <div class="quiz-header-left">
            <h2 class="question-counter">Question <?= $index + 1 ?> / <?= $total ?></h2>
            <?php if ($category): ?>
            <div class="quiz-meta-badges">
                <span class="badge badge-category"><?= htmlspecialchars($category) ?></span>
                <span class="badge badge-<?= htmlspecialchars($difficulty) ?>"><?= ucfirst($difficulty) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="lives">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <span class="life <?= $i < $lives ? 'alive' : 'lost' ?>"><?= $i < $lives ? '❤️' : '🖤' ?></span>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Progress -->
    <div class="progress-bar">
        <div class="progress-fill" style="width:<?= $progress ?>%"></div>
        <span class="progress-label"><?= $progress ?>%</span>
    </div>

    <!-- Question Card -->
    <div class="question-card" id="questionCard">
        <p class="question-text"><?= htmlspecialchars($current['question_text']) ?></p>

        <form class="choices-grid"
              id="answerForm"
              action="../controllers/QuizController.php"
              method="POST"
              <?= $feedback ? 'style="pointer-events:none"' : '' ?>>

            <input type="hidden" name="action"    value="answer">
            <input type="hidden" name="choice_id" id="choiceInput">
            <?= csrf_field() ?>

            <?php foreach ($current['choices'] as $i => $choice): ?>
            <button type="button"
                    class="choice-btn <?= $feedback && (int)$choice['is_correct'] === 1 ? 'correct' : '' ?>"
                    data-choice="<?= (int)$choice['choice_id'] ?>"
                    onclick="selectAnswer(this)"
                    <?= $feedback ? 'disabled' : '' ?>>
                <span class="choice-letter"><?= $letters[$i] ?? ($i + 1) ?></span>
                <?= htmlspecialchars($choice['choice_text']) ?>
            </button>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- Feedback overlay -->
    <?php if ($feedback): ?>
    <div class="feedback-box <?= $feedback['is_correct'] ? 'feedback-correct' : 'feedback-wrong' ?>"
         id="feedbackBox">
        <div class="feedback-icon"><?= $feedback['is_correct'] ? '✅' : '❌' ?></div>
        <h3><?= $feedback['is_correct'] ? 'Correct!' : 'Not quite!' ?></h3>
        <p class="feedback-answer">
            Correct answer: <strong><?= htmlspecialchars($feedback['correct_answer']) ?></strong>
        </p>
        <?php if (!empty($feedback['trivia'])): ?>
        <p class="feedback-trivia">💡 <?= htmlspecialchars($feedback['trivia']) ?></p>
        <?php endif; ?>
        <form action="../controllers/QuizController.php" method="POST">
            <input type="hidden" name="action" value="next">
            <?= csrf_field() ?>
            <button class="next-btn">Next Question →</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Bottom bar -->
    <div class="score-bar">
        <span>Score: <strong><?= $score ?></strong> / <?= $total ?></span>
        <a href="../views/select_quiz.php"
           onclick="return confirm('Quit the quiz? Progress will be lost.')">
            ✕ Quit
        </a>
    </div>

</div>

<script src="../assets/js/quiz.js"></script>
</body>
</html>
