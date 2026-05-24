<?php
// ── Login Page ────────────────────────────────────────────────
require_once __DIR__ . '/autoload.php';

// Redirect already-logged-in users
if (is_logged_in()) {
    redirect_to(is_admin() ? 'views/admin.php' : 'views/dashboard.php');
}

$error      = get_flash('error');
$deletedAccount = get_flash('deleted_account');
$registered = get_flash('registered');
$passwordReset = get_flash('password_reset');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechTrivia — Login</title>
    <link rel="icon" type="image/png" href="assets/image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="particles" id="particles"></div>

<div class="container">
    <div class="logo-area">
        <div class="logo-icon"><img src="assets/image/logo.png" alt="CEZ TechTrivia logo"></div>
        <h2>CEZ TechTrivia</h2>
        <p class="subtitle">Sign in to start your quiz journey</p>
    </div>

    <form id="loginForm" action="controllers/AuthController.php" method="POST" novalidate autocomplete="off">
        <input type="hidden" name="action"     value="login">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
                <span class="input-icon">👤</span>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username" required autocomplete="off">
            </div>
            <div class="field-hint" id="usernameHint"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <span class="input-icon">🔒</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required autocomplete="new-password">
                <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password" title="Show password">&#128065;</button>
                <span class="input-status" id="passwordStatus"></span>
            </div>
            <div class="field-hint" id="passwordHint"></div>
        </div>

        <button type="submit" id="loginBtn">Sign In →</button>
    </form>

    <p class="link forgot-link"><a href="views/forgot_password.php">Forgot password?</a></p>
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
<?php if ($deletedAccount): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'warning',
        title: 'Deleted Account',
        html: '<p>This is a deleted account. Message the admin to recover it.</p><p style="margin-top:10px"><strong>Delete reason:</strong> ' + <?= json_encode(htmlspecialchars($deletedAccount['reason'] ?? 'No reason was recorded.')) ?> + '</p>',
        background: '#1a1830',
        color: '#f1f0ff',
        confirmButtonColor: '#4f46e5',
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
<?php if ($passwordReset): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'success', title: 'Password Changed',
        text: <?= json_encode($passwordReset) ?>,
        background: '#1a1830', color: '#f1f0ff', confirmButtonColor: '#4f46e5',
    });
});
</script>
<?php endif; ?>
</body>
</html>
