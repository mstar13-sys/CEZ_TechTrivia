<?php
// ── API: Leaderboard ──────────────────────────────────────────
// Returns top players as JSON. Supports optional category/difficulty filters.
// Used by the landing page and any JS consumers.

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

$user       = new User();
$limit      = min((int)($_GET['limit']     ?? 10), 50);
$category   = trim($_GET['category']   ?? '');
$difficulty = trim($_GET['difficulty'] ?? '');

$allowedDiff = ['easy', 'medium', 'hard'];
if (!in_array($difficulty, $allowedDiff, true)) {
    $difficulty = '';
}

if ($category !== '' || $difficulty !== '') {
    echo json_encode($user->getFilteredLeaderboard(
        $category   ?: null,
        $difficulty ?: null,
        $limit
    ));
} else {
    echo json_encode($user->getLeaderboard($limit));
}
