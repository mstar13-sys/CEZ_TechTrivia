<?php
// ── Login Page ────────────────────────────────────────────────
require_once __DIR__ . '/autoload.php';

// Redirect already-logged-in users
if (is_logged_in()) {
    redirect_to(is_admin() ? 'views/admin.php' : 'views/dashboard.php');
}

$error      = get_flash('error');
$registered = get_flash('registered');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechTrivia — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="particles" id="particles"></div>

<div class="container">
    <div class="logo-area">
        <div class="logo-icon">🎯</div>
        <h2>CEZ TechTrivia</h2>
        <p class="subtitle">Sign in to start your quiz journey</p>
    </div>

    <form id="loginForm" action="controllers/AuthController.php" method="POST" novalidate>
        <input type="hidden" name="action"     value="login">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
                <span class="input-icon">👤</span>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username" required autocomplete="username">
            </div>
            <div class="field-hint" id="usernameHint"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <span class="input-icon">🔒</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required autocomplete="current-password">
                <span class="input-status" id="passwordStatus"></span>
            </div>
            <div class="field-hint" id="passwordHint"></div>
        </div>

        <button type="submit" id="loginBtn">Sign In →</button>
    </form>

    <p class="link">New user? <a href="views/register.php">Create an account</a></p>
    <p class="link" style="margin-top:8px"><a href="index.php">← Back to Home</a></p>
</div>

<script src="assets/js/login.js"></script>
<?php if ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'error', title: 'Login Failed',
        text: <?= json_encode($error) ?>,
        background: '#1a1830', color: '#f1f0ff', confirmButtonColor: '#4f46e5',
    });
});
</script>
<?php endif; ?>
<?php if ($registered): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'success', title: '🎉 Account Created!',
        text: 'Registration successful! You can now sign in.',
        background: '#1a1830', color: '#f1f0ff', confirmButtonColor: '#4f46e5',
        timer: 4000, timerProgressBar: true,
    });
});
</script>
<?php endif; ?>
</body>
</html>
