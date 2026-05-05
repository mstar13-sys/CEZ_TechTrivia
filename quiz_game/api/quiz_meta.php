<?php
// ── API: Quiz Meta (categories / difficulties / question count) ────────────
// Used by the quiz selection screen (AJAX).

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? '';
$user   = new User();

switch ($action) {

    case 'categories':
        echo json_encode(['categories' => $user->getAvailableCategories()]);
        break;

    case 'difficulties':
        $cat = trim($_GET['category'] ?? '');
        if ($cat === '') {
            echo json_encode(['difficulties' => []]);
            break;
        }
        echo json_encode(['difficulties' => $user->getAvailableDifficulties($cat)]);
        break;

    case 'count':
        $cat  = trim($_GET['category']   ?? '');
        $diff = trim($_GET['difficulty'] ?? '');
        if ($cat === '' || $diff === '') {
            echo json_encode(['count' => 0]);
            break;
        }
        echo json_encode(['count' => $user->countAvailableQuestions($cat, $diff)]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
