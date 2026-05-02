<?php
// ── API: Leaderboard ──────────────────────────────────────────
// Returns the top 10 players as JSON for the landing page.

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

$user  = new User();
$limit = min((int)($_GET['limit'] ?? 10), 50); // max 50

echo json_encode($user->getLeaderboard($limit));
