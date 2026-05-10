<?php
// ── Logout shortcut ───────────────────────────────────────────
// Kept for backward compatibility; the real logic is in AuthController.
require_once __DIR__ . '/../autoload.php';
redirect_to('../login.php');
