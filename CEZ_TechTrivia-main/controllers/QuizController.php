<?php
// ── Quiz Controller ───────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

$userModel = new User();
$action    = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Allowed values ────────────────────────────────────────────
$allowedDifficulties = ['easy', 'medium', 'hard'];

switch ($action) {

    // ── Start a new quiz (POST from select_quiz.php) ──────────
    case 'start':
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Session expired. Please try again.');
            redirect_to('../views/select_quiz.php');
        }

        $category   = trim($_POST['category']   ?? '');
        $difficulty = trim($_POST['difficulty'] ?? '');

        // Validate difficulty
        if (!in_array($difficulty, $allowedDifficulties, true)) {
            set_flash('error', 'Invalid difficulty selected.');
            redirect_to('../views/select_quiz.php');
        }

        // Sanitize category (non-empty string)
        if ($category === '') {
            set_flash('error', 'Please select a category.');
            redirect_to('../views/select_quiz.php');
        }

        $questions = $userModel->getFilteredQuestions($category, $difficulty, 10);

        if (empty($questions)) {
            set_flash('error', "No questions found for {$category} ({$difficulty}). Try another combination!");
            redirect_to('../views/select_quiz.php');
        }

        // Store quiz state in session
        $_SESSION['quiz_questions']     = $questions;
        $_SESSION['quiz_index']         = 0;
        $_SESSION['quiz_score']         = 0;
        $_SESSION['quiz_lives']         = 3;
        $_SESSION['quiz_total']         = count($questions);
        $_SESSION['quiz_wrong_answers'] = [];
        $_SESSION['quiz_active']        = true;
        $_SESSION['quiz_category']      = $category;
        $_SESSION['quiz_difficulty']    = $difficulty;
        unset($_SESSION['quiz_feedback']);

        redirect_to('../views/quiz.php');
        break;


    // ── Answer Question ───────────────────────────────────────
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

        $currentQ    = $questions[$index];
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

        $_SESSION['quiz_feedback'] = [
            'show'           => true,
            'is_correct'     => $isCorrect,
            'correct_answer' => $correctText,
            'trivia'         => $currentQ['trivia'] ?? '',
        ];

        redirect_to('../views/quiz.php');
        break;


    // ── Next Question (after feedback) ────────────────────────
    case 'next':
        if (!verify_csrf($_POST['csrf_token'] ?? '') || empty($_SESSION['quiz_active'])) {
            redirect_to('../views/quiz.php');
        }

        unset($_SESSION['quiz_feedback']);
        $_SESSION['quiz_index']++;

        $score      = (int)$_SESSION['quiz_score'];
        $xp         = $score * 10;
        $category   = $_SESSION['quiz_category']   ?? null;
        $difficulty = $_SESSION['quiz_difficulty'] ?? null;

        // Game over: out of lives
        if ($_SESSION['quiz_lives'] <= 0) {
            $userModel->saveGameSession($_SESSION['user_id'], $score, $xp, $category, $difficulty);
            $_SESSION['quiz_active'] = false;
            redirect_to('../views/game_over.php');
        }

        // Quiz finished: all questions answered
        if ($_SESSION['quiz_index'] >= $_SESSION['quiz_total']) {
            $userModel->saveGameSession($_SESSION['user_id'], $score, $xp, $category, $difficulty);
            $_SESSION['total_xp']    = ($_SESSION['total_xp'] ?? 0) + $xp;
            $_SESSION['quiz_active'] = false;
            redirect_to('../views/results.php');
        }

        redirect_to('../views/quiz.php');
        break;


    default:
        redirect_to('../views/select_quiz.php');
}
