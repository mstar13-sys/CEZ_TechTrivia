<?php
// ── Register Page ─────────────────────────────────────────────
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
    <title>TechTrivia — Register</title>
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="particles" id="particles2"></div>
    <div class="container">
        <div class="logo-area">
            <div class="logo-icon"><img src="../assets/image/logo.png" alt="CEZ TechTrivia logo"></div>
            <h2>Create Account</h2>
            <p class="subtitle">Join TechTrivia and start playing</p>
        </div>

        <form id="registerForm" action="../controllers/AuthController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="register">
            <?= csrf_field() ?>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="username" name="username"
                        placeholder="3–20 chars, letters/numbers/_" required
                        minlength="3" maxlength="20" autocomplete="username">
                    <span class="input-status" id="userStatus"></span>
                </div>
                <div class="field-hint" id="userHint"></div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉️</span>
                    <input type="email" id="email" name="email"
                        placeholder="you@example.com" required autocomplete="email">
                    <span class="input-status" id="emailStatus"></span>
                </div>
                <div class="field-hint" id="emailHint"></div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password"
                        placeholder="Min 8 chars, 1 uppercase, 1 number" required
                        minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password" title="Show password">&#128065;</button>
                    <span class="input-status" id="pwStatus"></span>
                </div>
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div class="pw-reqs" id="pwReqs">
                    <div class="pw-req" id="req-length"><span class="check">○</span> 8+ characters</div>
                    <div class="pw-req" id="req-upper"><span class="check">○</span> Uppercase letter</div>
                    <div class="pw-req" id="req-number"><span class="check">○</span> Number</div>
                    <div class="pw-req" id="req-special"><span class="check">○</span> Special char (optional)</div>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔑</span>
                    <input type="password" id="confirmPassword" name="confirmPassword"
                        placeholder="Re-enter your password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show password" title="Show password">&#128065;</button>
                    <span class="input-status" id="confirmStatus"></span>
                </div>
                <div class="field-hint" id="confirmHint"></div>
            </div>

            <button type="submit" id="registerBtn" disabled>Create Account →</button>
        </form>

        <p class="link">Already have an account? <a href="../login.php">Sign in here</a></p>
    </div>

    <script src="../assets/js/register.js"></script>
    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Error',
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
