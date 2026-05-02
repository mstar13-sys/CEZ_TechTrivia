<?php
// ── Leaderboard Page ──────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_logged_in()) {
    redirect_to('../login.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel   = new User();
$leaderboard = $userModel->getLeaderboard(20);
$medals      = ['🥇', '🥈', '🥉'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .lb-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .lb-card .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .lb-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .lb-card th {
            padding: 12px 20px;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
            background: rgba(0, 0, 0, .2);
            text-align: left;
        }

        .lb-card td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: .9rem;
        }

        .lb-card tr:last-child td {
            border-bottom: none;
        }

        .lb-card tr:hover td {
            background: rgba(255, 255, 255, .03);
        }

        .rank-medal {
            font-size: 1.3rem;
        }

        .lb-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary, #4f46e5);
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            margin-right: 10px;
        }

        .highlight-row td {
            background: rgba(79, 70, 229, .08);
        }

        .xp-val {
            color: #818cf8;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">🎯</div>
            <div class="brand-name">CEZ<br><span>TechTrivia</span></div>
        </div>
        <div class="user-profile">
            <div class="user-avatar">🎮</div>
            <div class="user-name"><?= htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Player')) ?></div>
            <div class="user-role">Player</div>
        </div>
        <p class="nav-section-label">Navigation</p>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
            <li><a href="../controllers/QuizController.php?action=start"><span class="nav-icon">🎮</span> Start Quiz</a></li>
            <li><a href="leaderboard.php" class="active"><span class="nav-icon">🏆</span> Leaderboard</a></li>
        </ul>
        <div class="sidebar-footer">
            <form class="logout-form" action="../controllers/AuthController.php" method="POST"
                onsubmit="return confirmLogout(event)">
                <input type="hidden" name="action" value="logout">
                <?= csrf_field() ?>
                <button type="submit">🚪 Sign Out</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>🏆 Leaderboard</h1>
                <p>Top players ranked by total XP earned</p>
            </div>
        </div>

        <div class="lb-card">
            <div class="table-header">
                <h3>Top Players</h3>
                <span style="color:var(--text-muted);font-size:.85rem">Updated in real time</span>
            </div>

            <?php if (empty($leaderboard)): ?>
                <div style="text-align:center;padding:48px;color:var(--text-muted)">
                    <div style="font-size:3rem;margin-bottom:12px">🏆</div>
                    <p>No players on the leaderboard yet. Be the first!</p>
                </div>
            <?php else: ?>
                <table>
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
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/global.js"></script>
    <script>
        function confirmLogout(e) {
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                icon: 'question',
                title: 'Sign Out?',
                text: 'Are you sure you want to sign out?',
                background: '#1a1830',
                color: '#f1f0ff',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4f46e5',
                confirmButtonText: 'Yes, sign out',
            }).then(r => {
                if (r.isConfirmed) form.submit();
            });
            return false;
        }
    </script>
</body>

</html>