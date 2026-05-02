<?php
// ── API: Check username / email availability ──────────────────
// Called via JS fetch() on the register form.
// Returns JSON: { "available": true/false, "message": "..." }

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

$user  = new User();
$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');

if (empty($value) || !in_array($field, ['username', 'email'], true)) {
    echo json_encode(['available' => false, 'message' => 'Invalid request']);
    exit;
}

if ($field === 'username') {
    $exists = $user->userExists($value);
    echo json_encode([
        'available' => !$exists,
        'message'   => $exists ? 'Username is already taken' : 'Username is available',
    ]);
} else {
    $exists = $user->emailExists($value);
    echo json_encode([
        'available' => !$exists,
        'message'   => $exists ? 'Email is already registered' : 'Email is available',
    ]);
}
