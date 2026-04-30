<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - Quiz Reviewer</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
</head>

<body>
    <div class="sidebar">
        <h2>Quiz Reviewer</h2>
        
        <div class="user-profile">
            <div class="user-avatar">👤</div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <div class="user-email">Player</div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="#" class="active">Dashboard</a></li>
            <li><a href="#">Start Quiz</a></li>
            <li><a href="#">History</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        
        <form action="../controllers/AuthController.php" method="POST" style="margin-top: auto;">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
        </div>
        
        <div class="cards">
            <div class="card">
                <h3>📝 Start Quiz</h3>
                <p>Begin a new quiz session and test your knowledge.</p>
            </div>
            <div class="card">
                <h3>📊 View History</h3>
                <p>Review your past quiz results and performance.</p>
            </div>
            <div class="card">
                <h3>🏆 Leaderboard</h3>
                <p>See how you rank against other players.</p>
            </div>
        </div>
    </div>
</body>

</html>