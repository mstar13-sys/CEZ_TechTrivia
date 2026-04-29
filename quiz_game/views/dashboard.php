<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - Quiz Reviewer</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 20px;
            color: white;
        }
        
        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .user-profile {
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #667eea;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-email {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-menu li {
            margin-bottom: 10px;
        }
        
        .nav-menu a {
            display: block;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .nav-menu a:hover, .nav-menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .logout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .main-content {
            margin-left: 260px;
            padding: 40px;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: #667eea;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .card p {
            color: #666;
            font-size: 14px;
        }
    </style>
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
        
        <a href="../public/logout.php" class="logout-btn">Logout</a>
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