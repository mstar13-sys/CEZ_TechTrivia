<?php
require_once __DIR__ . "/autoload.php";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: quiz_game/views/dashboard.php");
    exit;
}

$error = $_SESSION['flash_error'] ?? null;
$registered = $_SESSION['flash_registered'] ?? false;
unset($_SESSION['flash_error'], $_SESSION['flash_registered']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="quiz_game/assets/login.css">
</head>

<body>

    <div class="container">
        <h2>Quiz Reviewer</h2>
        <p class="subtitle">Login to your account</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($registered): ?>
            <div class="alert" style="background: #e8f8f0; color: #27ae60; border: 1px solid #27ae60;">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <form id="loginForm" action="quiz_game/controllers/AuthController.php?action=login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <p class="link">New user? <a href="quiz_game/views/register.php">Register here</a></p>
    </div>

</body>
</html>




















