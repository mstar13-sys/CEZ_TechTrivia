<?php
// ── API: Get a single question with its choices ───────────────
// Used by the admin edit modal (JS fetch call).
// Returns JSON: { question_id, question_text, difficulty, category, choices: [...] }

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

// Only admins may call this
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid question ID']);
    exit;
}

$userModel = new User();
$question  = $userModel->getQuestionWithChoices($id);

if (!$question) {
    http_response_code(404);
    echo json_encode(['error' => 'Question not found']);
    exit;
}

echo json_encode($question);
