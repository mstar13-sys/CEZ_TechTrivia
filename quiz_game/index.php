<?php
// ── Landing Page (index.php) ──────────────────────────────────
require_once __DIR__ . '/autoload.php';

// Redirect logged-in users straight to their dashboard
if (is_logged_in()) {
    redirect_to(is_admin() ? 'views/admin.php' : 'views/dashboard.php');
}

$flash_registered = get_flash('registered');

require_once __DIR__ . '/models/User.php';
$userModel   = new User();
$leaderboard = $userModel->getLeaderboard(20);
$medals      = ['🥇', '🥈', '🥉'];
?>
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechTrivia | Level Up Your Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <style>
        /* Leaderboard section styles */
        #leaderboard {
            padding: 80px 10%;
            background: #f0f4ff;
        }

        #leaderboard h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 8px;
            color: #050a30;
        }

        #leaderboard .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 32px;
        }

        .lb-table {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .lb-table thead tr {
            background: #050a30;
            color: #fff;
        }

        .lb-table th,
        .lb-table td {
            padding: 14px 18px;
            text-align: left;
            font-size: .9rem;
        }

        .lb-table tbody tr {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            transition: background .2s;
        }

        .lb-table tbody tr:hover {
            background: #f0f4ff;
        }

        .lb-row.lb-top td:first-child {
            font-size: 1.2rem;
        }

        .lb-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #4f46e5;
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            margin-right: 8px;
        }
        .rank-medal {
            font-size: 1.5rem;
        }
        .lb-xp {
            color: #4f46e5;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo"><i class="fas fa-brain"></i> TechTrivia</a>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#leaderboard">Leaderboard</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <a href="login.php" class="btn-login">Login</a>
            <a href="views/register.php" class="btn-signup">Sign Up</a>
        </div>
    </header>

    <!-- Hero -->
    <section id="home">
        <div class="hero-content">
            <div class="hero-badge">Test your knowledge. Level up your skills.</div>
            <h1>Welcome to <br><span>TechTrivia</span></h1>
            <p>An interactive quiz platform for IT enthusiasts. Challenge yourself, learn new things, and climb the leaderboard!</p>
            <div class="hero-btns">
                <a href="login.php" class="btn-primary"><i class="fas fa-play"></i> Start Quiz Now</a>
                <a href="#leaderboard" class="btn-secondary"><i class="fas fa-trophy"></i> View Leaderboard</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/image/bg.png"
                alt="Picture Putek" class="laptop-mockup">
        </div>
    </section>

    <div class="wave-container">
        <div class="wave"></div>
    </div>

    <!-- Features -->
    <section id="features">
        <div class="features-grid">
            <div class="feature-card blue">
                <i class="fas fa-book-open"></i>
                <h3>Multiple Categories</h3>
                <p>Explore IT topics from programming to networking.</p>
            </div>
            <div class="feature-card green">
                <i class="fas fa-layer-group"></i>
                <h3>3 Difficulty Levels</h3>
                <p>Easy, Medium, or Hard — pick your challenge.</p>
            </div>
            <div class="feature-card purple">
                <i class="fas fa-heart"></i>
                <h3>Lives System</h3>
                <p>You have 3 lives per game. Answer wisely!</p>
            </div>
            <div class="feature-card orange">
                <i class="fas fa-medal"></i>
                <h3>Climb the Leaderboard</h3>
                <p>Earn XP and compete for the top spot.</p>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="content-area">
        <h2>About TechTrivia</h2>
        <p>Built for developers, by developers. We believe learning should be fun and competitive.</p>
    </section>

    <!-- Leaderboard -->
    <section id="leaderboard">
        <h2>🏆 Leaderboard</h2>
        <p class="subtitle">Top players ranked by total XP earned</p>
        <table class="lb-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>XP</th>
                    <th>Level</th>
                    <th>Games Played</th>
                    <th>Best Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $p): ?>
                    <tr <?= $p['username'] === ($_SESSION['username'] ?? '') ? 'class="highlight-row"' : '' ?>>
                        <td class="rank-medal"><?= $medals[$i] ?? ($i + 1) ?></td>
                        <td>
                            <span class="lb-avatar"><?= strtoupper(substr($p['username'], 0, 1)) ?></span>
                            <?= htmlspecialchars($p['username']) ?>
                            <?php if ($p['username'] === ($_SESSION['username'] ?? '')): ?>
                                <span style="font-size:.75rem;color:#818cf8;margin-left:6px">(you)</span>
                            <?php endif; ?>
                        </td>
                        <td class="xp-val"><?= number_format((int)$p['total_xp']) ?></td>
                        <td><?= (int)$p['level'] ?></td>
                        <td><?= (int)$p['games_played'] ?></td>
                        <td><?= (int)$p['best_score'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <footer>
        <p>&copy; <?= date('Y') ?> TechTrivia. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/global.js"></script>
    <script src="assets/js/landing.js"></script>
    <?php if ($flash_registered): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '🎉 Account Created!',
                text: 'Registration successful! You can now sign in.',
                background: '#fff',
                confirmButtonColor: '#4f46e5',
                timer: 4000,
                timerProgressBar: true,
            });
        </script>
    <?php endif; ?>
</body>

</html>