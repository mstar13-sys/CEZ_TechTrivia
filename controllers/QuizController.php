<?php
// ── Quiz Controller ───────────────────────────────────────────
// Handles starting, answering questions, showing feedback, and moving next.

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

$userModel = new User();
$action    = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── Start a new quiz ──────────────────────────────────────
    case 'start':
        $questions = $userModel->getRandomQuestions(10);

        if (empty($questions)) {
            set_flash('error', 'No questions available yet. Ask an admin to add some!');
            redirect_to('../views/dashboard.php');
        }

        $_SESSION['quiz_questions']     = $questions;
        $_SESSION['quiz_index']         = 0;
        $_SESSION['quiz_score']         = 0;
        $_SESSION['quiz_lives']         = 3;
        $_SESSION['quiz_total']         = count($questions);
        $_SESSION['quiz_wrong_answers'] = [];
        $_SESSION['quiz_active']        = true;

        redirect_to('../views/quiz.php');
        break;


    // ── Answer Question (NOW SHOWS FEEDBACK FIRST) ────────────
    case 'answer':
        if (!verify_csrf($_POST['csrf_token'] ?? '') || empty($_SESSION['quiz_active'])) {
            redirect_to('../views/quiz.php');
        }

        $choiceId  = (int)($_POST['choice_id'] ?? 0);
        $index     = (int)($_SESSION['quiz_index'] ?? 0);
        $questions = $_SESSION['quiz_questions'] ?? [];

        if (!isset($questions[$index])) {
            redirect_to('../views/quiz.php');
        }

        $currentQ = $questions[$index];

        // Check if correct
        $isCorrect   = false;
        $correctText = '';

        foreach ($currentQ['choices'] as $choice) {
            if ((int)$choice['is_correct'] === 1) {
                $correctText = $choice['choice_text'];
            }
            if ($choice['choice_id'] == $choiceId && (int)$choice['is_correct'] === 1) {
                $isCorrect = true;
            }
        }

        if ($isCorrect) {
            $_SESSION['quiz_score']++;
        } else {
            $_SESSION['quiz_lives']--;
            $_SESSION['quiz_wrong_answers'][] = [
                'question'       => $currentQ['question_text'],
                'correct_answer' => $correctText,
            ];
        }

        // ⭐ SAVE FEEDBACK STATE (NEW)
        $_SESSION['quiz_feedback'] = [
            'show' => true,
            'is_correct' => $isCorrect,
            'correct_answer' => $correctText,
            'trivia' => $currentQ['trivia'] ?? ''
        ];

        // ❗ Do NOT move to next question yet
        redirect_to('../views/quiz.php');
        break;


    // ── Next Question (after feedback screen) ─────────────────
    case 'next':

        unset($_SESSION['quiz_feedback']); // remove feedback state
        $_SESSION['quiz_index']++;         // NOW move question

        // Game Over check
        if ($_SESSION['quiz_lives'] <= 0) {
            $score = $_SESSION['quiz_score'];
            $xp    = $score * 10;
            $userModel->saveGameSession($_SESSION['user_id'], $score, $xp);
            $_SESSION['quiz_active'] = false;
            redirect_to('../views/game_over.php');
        }

        // Quiz finished check
        if ($_SESSION['quiz_index'] >= $_SESSION['quiz_total']) {
            $score = $_SESSION['quiz_score'];
            $xp    = $score * 10;
            $sessionId = $userModel->saveGameSession($_SESSION['user_id'], $score, $xp);
            $_SESSION['total_xp'] += $xp;
            $_SESSION['quiz_active'] = false;
            redirect_to('../views/results.php');
        }

        redirect_to('../views/quiz.php');
        break;


    default:
        redirect_to('../views/dashboard.php');
}