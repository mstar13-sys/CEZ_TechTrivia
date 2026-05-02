<?php
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

if (empty($_SESSION['quiz_active']) || empty($_SESSION['quiz_questions'])) {
    set_flash('error', 'No active quiz. Start one from the dashboard!');
    redirect_to('../views/dashboard.php');
}

$questions = $_SESSION['quiz_questions'];
$index     = (int)$_SESSION['quiz_index'];
$lives     = (int)$_SESSION['quiz_lives'];
$score     = (int)$_SESSION['quiz_score'];
$total     = (int)$_SESSION['quiz_total'];

if ($index >= $total) redirect_to('../views/results.php');

$current  = $questions[$index];
$letters  = ['A','B','C','D'];
$progress = round(($index / $total) * 100);

// ⭐ feedback from controller
$feedback = $_SESSION['quiz_feedback'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quiz — TechTrivia</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/quiz.css">
</head>
<body>

<div class="quiz-wrapper">

    <!-- Header -->
    <div class="quiz-header">
        <h2>Question <?= $index+1 ?> / <?= $total ?></h2>
        <div class="lives">
            <?php for ($i=0;$i<3;$i++): ?>
                <span><?= $i < $lives ? '❤️':'🖤' ?></span>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Progress -->
    <div class="progress-bar">
        <div class="progress-fill" style="width:<?= $progress ?>%"></div>
    </div>

    <!-- Question Card -->
    <div class="question-card">
        <div class="question-meta">
            <span class="badge badge-<?= htmlspecialchars($current['difficulty']) ?>">
                <?= ucfirst($current['difficulty']) ?>
            </span>
            <span class="badge badge-category">
                <?= htmlspecialchars($current['category']) ?>
            </span>
        </div>

        <p class="question-text"><?= htmlspecialchars($current['question_text']) ?></p>

        <!-- Answers -->
        <form class="choices-grid"
              id="answerForm"
              action="../controllers/QuizController.php"
              method="POST"
              <?= $feedback ? 'style="pointer-events:none;opacity:.6"' : '' ?>>

            <input type="hidden" name="action" value="answer">
            <?= csrf_field() ?>
            <input type="hidden" name="choice_id" id="choiceInput">

            <?php foreach ($current['choices'] as $i=>$choice): ?>
            <button type="button"
                    class="choice-btn"
                    data-choice="<?= (int)$choice['choice_id'] ?>"
                    onclick="selectAnswer(this)">
                <span class="choice-letter"><?= $letters[$i] ?? $i+1 ?></span>
                <?= htmlspecialchars($choice['choice_text']) ?>
            </button>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- ⭐ FEEDBACK BOX (BELOW QUESTION CARD) -->
    <?php if ($feedback): ?>
    <div class="feedback-box" style="margin-top:20px;text-align:center">

        <h2><?= $feedback['is_correct'] ? '✅ Correct!' : '❌ Wrong!' ?></h2>

        <p>
            Correct Answer:
            <strong><?= htmlspecialchars($feedback['correct_answer']) ?></strong>
        </p>

        <?php if (!empty($feedback['trivia'])): ?>
            <p style="margin-top:10px">
                💡 <?= htmlspecialchars($feedback['trivia']) ?>
            </p>
        <?php endif; ?>

        <form action="../controllers/QuizController.php" method="POST" style="margin-top:15px">
            <input type="hidden" name="action" value="next">
            <?= csrf_field() ?>
            <button class="next-btn">Next Question →</button>
        </form>

    </div>
    <?php endif; ?>

    <!-- Score -->
    <div class="score-bar">
        <span>Score: <strong><?= $score ?></strong></span>
        <span><a href="../views/dashboard.php">Quit Quiz</a></span>
    </div>

</div>

<script>
function selectAnswer(btn){
    document.querySelectorAll('.choice-btn').forEach(b=>b.disabled=true);
    btn.classList.add('selected');
    document.getElementById('choiceInput').value=btn.dataset.choice;
    setTimeout(()=>document.getElementById('answerForm').submit(),200);
}
</script>

</body>
</html>