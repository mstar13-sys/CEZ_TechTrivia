<?php
require_once __DIR__ . '/../autoload.php';

if (is_logged_in()) {
    redirect_to('../views/dashboard.php');
}

$error = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechTrivia - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="particles" id="particles2"></div>
    <div class="container">
        <div class="logo-area">
            <div class="logo-icon">&#128273;</div>
            <h2>Reset Password</h2>
            <p class="subtitle">Enter your username and email to change your password</p>
        </div>

        <form id="forgotPasswordForm" action="../controllers/AuthController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="forgot_password">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128100;</span>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                </div>
                <div class="field-hint" id="userHint"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#9993;</span>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                </div>
                <div class="field-hint" id="emailHint"></div>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128274;</span>
                    <input type="password" id="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 number" required minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password" title="Show password">&#128065;</button>
                    <span class="input-status" id="pwStatus"></span>
                </div>
                <div class="field-hint" id="passwordHint"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">&#128273;</span>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show password" title="Show password">&#128065;</button>
                    <span class="input-status" id="confirmStatus"></span>
                </div>
                <div class="field-hint" id="confirmHint"></div>
            </div>

            <button type="submit" id="resetBtn">Change Password</button>
        </form>

        <p class="link">Remember your password? <a href="../login.php">Sign in here</a></p>
    </div>

    <script src="../assets/js/forgot_password.js"></script>
    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Error',
                    text: <?= json_encode($error) ?>,
                    background: '#1a1830',
                    color: '#f1f0ff',
                    confirmButtonColor: '#4f46e5',
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>
