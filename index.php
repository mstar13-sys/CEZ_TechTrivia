<?php
// Landing Page (index.php) 
require_once __DIR__ . '/autoload.php';

// AJAX: leaderboard
if (($_GET['action'] ?? '') === 'leaderboard') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/models/User.php';
    $user       = new User();
    $limit      = min((int)($_GET['limit']     ?? 10), 50);
    $category   = trim($_GET['category']   ?? '');
    $difficulty = trim($_GET['difficulty'] ?? '');
    $allowedDiff = ['easy', 'medium', 'hard'];
    if (!in_array($difficulty, $allowedDiff, true)) {
        $difficulty = '';
    }
    if ($category !== '' || $difficulty !== '') {
        echo json_encode($user->getFilteredLeaderboard(
            $category   ?: null,
            $difficulty ?: null,
            $limit
        ));
    } else {
        echo json_encode($user->getLeaderboard($limit));
    }
    exit;
}

// Redirect logged-in users straight to their dashboard
if (is_logged_in()) {
    redirect_to(is_admin() ? 'views/admin.php' : 'views/dashboard.php');
}

$flash_registered = get_flash('registered');

require_once __DIR__ . '/models/User.php';
$userModel   = new User();
$leaderboard = $userModel->getLeaderboard(10);
$topPlayers = array_slice($leaderboard, 0, 3);
$remainingPlayers = array_slice($leaderboard, 3, 7, true);
$trophyImages = [
    'assets/image/trophy_gold.png',
    'assets/image/trophy_silver.png',
    'assets/image/trophy_bronze.png',
];
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

        .lb-podium {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 18px;
            max-width: 900px;
            margin: 0 auto 28px;
        }

        .lb-podium-card {
            width: min(100%, 240px);
            min-height: 238px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px 18px;
            text-align: center;
            box-shadow: 0 14px 35px rgba(5, 10, 48, 0.12);
            position: relative;
            overflow: hidden;
        }

        .lb-podium-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 6px;
            background: #4f46e5;
        }

        .lb-podium-card.rank-1 {
            order: 2;
            min-height: 284px;
            transform: translateY(-14px);
            border-color: #facc15;
        }

        .lb-podium-card.rank-1::before {
            background: #facc15;
        }

        .lb-podium-card.rank-2 {
            order: 1;
            border-color: #cbd5e1;
        }

        .lb-podium-card.rank-2::before {
            background: #94a3b8;
        }

        .lb-podium-card.rank-3 {
            order: 3;
            border-color: #f59e0b;
        }

        .lb-podium-card.rank-3::before {
            background: #f59e0b;
        }

        .lb-trophy {
            width: 92px;
            height: 92px;
            object-fit: contain;
            margin: 8px auto 10px;
            display: block;
        }

        .rank-1 .lb-trophy {
            width: 118px;
            height: 118px;
        }

        .lb-place {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #050a30;
            color: #fff;
            font-weight: 800;
            font-size: .9rem;
            margin-bottom: 8px;
        }

        .lb-podium-name {
            color: #050a30;
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 6px;
            overflow-wrap: anywhere;
        }

        .rank-1 .lb-podium-name {
            font-size: 1.25rem;
        }

        .lb-podium-xp {
            color: #4f46e5;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .lb-podium-meta {
            color: #64748b;
            font-size: .82rem;
            font-weight: 600;
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

        .lb-empty {
            text-align: center;
            color: #666;
            padding: 32px 0;
        }

        @media (max-width: 768px) {
            .lb-podium {
                align-items: stretch;
                flex-direction: column;
            }

            .lb-podium-card,
            .lb-podium-card.rank-1 {
                order: initial;
                width: 100%;
                min-height: 0;
                transform: none;
            }

            .lb-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <header>
        <!-- <a href="index.php" class="logo"><i class="fas fa-brain">🧠</i> TechTrivia</a> -->
        <a href="index.php" class="logo"><i>🧠</i> TechTrivia</a>
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
        <p class="subtitle">Top 10 players ranked by total XP earned</p>

        <?php if (empty($leaderboard)): ?>
            <p class="lb-empty">No players yet. Be the first to play!</p>
        <?php else: ?>
            <div class="lb-podium" aria-label="Top three leaderboard players">
                <?php foreach ($topPlayers as $i => $p): ?>
                    <div class="lb-podium-card rank-<?= $i + 1 ?>">
                        <span class="lb-place">#<?= $i + 1 ?></span>
                        <img class="lb-trophy" src="<?= $trophyImages[$i] ?>" alt="Rank <?= $i + 1 ?> trophy">
                        <div class="lb-podium-name"><?= htmlspecialchars($p['username']) ?></div>
                        <div class="lb-podium-xp"><?= number_format((int)$p['total_xp']) ?> XP</div>
                        <div class="lb-podium-meta">
                            Level <?= (int)$p['level'] ?> · Best <?= (int)$p['best_score'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($remainingPlayers)): ?>
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
                <?php foreach ($remainingPlayers as $i => $p): ?>
                    <tr <?= $p['username'] === ($_SESSION['username'] ?? '') ? 'class="highlight-row"' : '' ?>>
                        <td class="rank-medal"><?= $i + 1 ?></td>
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
            <?php endif; ?>
        <?php endif; ?>
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
