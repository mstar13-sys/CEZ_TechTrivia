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
    <link rel="icon" type="image/png" href="assets/image/logo.png">
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







        /* About */
        .about-section {
            padding: 80px 10%;
            background: #ffffff;
            text-align: center;
        }
        .about-container { max-width: 1000px; margin: auto; }
        .about-section h2 {
            font-size: 2.2rem;
            color: #050a30;
            margin-bottom: 15px;
        }
        .about-text {
            font-size: 1rem;
            color: #555;
            line-height: 1.9;
            max-width: 850px;
            margin: auto;
        }
        .team-wrapper { margin-top: 60px; }
        .team-wrapper h3 { font-size: 1.8rem; color: #050a30; }
        .team-subtitle { color: #666; margin-bottom: 35px; }

        /* ── TEAM NAME TAGS ── */
        .team-wrap {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
            padding: 1.5rem 1rem 3rem;
        }

        .tmember {
            position: relative;
            cursor: pointer;
        }

        .t-name-tag {
            display: inline-block;
            padding: 10px 24px;
            background: #f0f4ff;
            border: 1.5px solid #4f46e5;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            color: #4f46e5;
            transition: background .2s, color .2s, transform .2s, box-shadow .2s;
            user-select: none;
        }
        .tmember:hover .t-name-tag {
            background: #4f46e5;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(79,70,229,0.25);
        }

        /* Hover preview card */
        .t-hover-card {
            position: absolute;
            bottom: calc(100% + 14px);
            left: 50%;
            transform: translateX(-50%) scale(0.92);
            width: 180px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s, transform .22s;
            z-index: 20;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .tmember:hover .t-hover-card {
            opacity: 1;
            transform: translateX(-50%) scale(1);
        }
        .t-hover-card img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            display: block;
        }
        .t-hover-card .t-pic-ph {
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            font-size: 2rem;
            font-weight: 700;
            color: #4f46e5;
        }
        .t-hover-card .t-hc-info {
            padding: 8px 10px 10px;
            text-align: center;
        }
        .t-hover-card .t-hc-info strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #111;
        }
        .t-hover-card .t-hc-info span {
            font-size: 11px;
            color: #666;
        }

        /* ── BIG CARD (opens on click, no flip) ── */
        .t-overlay {
            position: fixed;
            inset: 0;
            z-index: 998;
            background: rgba(0,0,0,0.45);
        }

        .t-big-card {
            position: fixed;
            z-index: 999;
            width: 280px;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(160deg, #050a30 60%, #1e1b6e);
            opacity: 0;
            pointer-events: none;
            transform: scale(0.88);
            transition: opacity .25s, transform .25s;
        }
        .t-big-card.open {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }

        .t-big-card .bc-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: top;
            display: block;
        }
        .t-big-card .bc-photo-ph {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #312e81, #4f46e5);
            font-size: 4rem;
            font-weight: 700;
            color: #fff;
        }
        .t-big-card .bc-body {
            padding: 18px 20px 22px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            text-align: center;
        }
        .t-big-card .bc-name {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
        }
        .t-big-card .bc-role {
            font-size: 12px;
            font-weight: 500;
            color: #a5b4fc;
        }
        .t-big-card .bc-desc {
            font-size: 12px;
            color: #cbd5e1;
            line-height: 1.65;
            margin-top: 6px;
        }
        .t-big-card .bc-socials {
            display: flex;
            gap: 20px;
            margin-top: 14px;
        }
        .t-big-card .bc-socials a {
            color: #a5b4fc;
            font-size: 1.3rem;
            transition: color .2s, transform .2s;
            text-decoration: none;
        }
        .t-big-card .bc-socials a:hover {
            color: #fde68a;
            transform: scale(1.22);
        }
        .t-big-card .bc-close {
            position: absolute;
            top: 10px;
            right: 12px;
            background: rgba(0,0,0,0.4);
            border: none;
            color: #fff;
            font-size: 1rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
            z-index: 2;
        }
        .t-big-card .bc-close:hover {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo"><img src="assets/image/logo.png" alt="CEZ TechTrivia logo"> TechTrivia</a>
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
    <section id="about" class="about-section">
        <div class="about-container">

            <h2>About TechTrivia</h2>
            <p class="about-text">
                <strong>TechTrivia</strong> is a web-based interactive learning platform designed to help students
                strengthen their knowledge in <strong>programming</strong>,
                <strong>networking</strong>, and other IT topics through engaging quizzes.
                <br><br>
                Instead of traditional studying, TechTrivia transforms learning into a
                competitive experience through
                <strong>quiz challenges, XP progression, leaderboards, and achievements</strong>.
                <br><br>
                <em>Learn. Challenge. Improve.</em>
            </p>

            <div class="team-wrapper">
                <h3>👨‍💻 Meet the Team Behind TechTrivia</h3>
                <p class="team-subtitle">Developed by passionate IT students.</p>

                <div class="team-wrap">

                    <!-- Kent -->
                    <div class="tmember" data-idx="0">
                        <div class="t-name-tag">Kent Lester Zabala</div>
                        <div class="t-hover-card">
                            <img src="assets/image/kent.png" alt="Kent"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="t-pic-ph" style="display:none">KZ</div>
                            <div class="t-hc-info">
                                <strong>Kent Lester Zabala</strong>
                                <span>Backend Programmer</span>
                            </div>
                        </div>
                    </div>

                    <!-- Arvin -->
                    <div class="tmember" data-idx="1">
                        <div class="t-name-tag">Arvin E. Endico</div>
                        <div class="t-hover-card">
                            <img src="assets/image/arvin.png" alt="Arvin"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="t-pic-ph" style="display:none">AE</div>
                            <div class="t-hc-info">
                                <strong>Arvin E. Endico</strong>
                                <span>Game Developer</span>
                            </div>
                        </div>
                    </div>

                    <!-- Diana -->
                    <div class="tmember" data-idx="2">
                        <div class="t-name-tag">Diana Mae Canoy</div>
                        <div class="t-hover-card">
                            <img src="assets/image/diana.png" alt="Diana"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="t-pic-ph" style="display:none">DC</div>
                            <div class="t-hc-info">
                                <strong>Diana Mae Canoy</strong>
                                <span>Database / QA Tester</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Big card (shared, no flip) -->
    <div class="t-overlay" id="t-overlay" style="display:none"></div>
    <div class="t-big-card" id="big-card">
        <button class="bc-close" id="bc-close" title="Close">✕</button>
        <img class="bc-photo" id="bc-photo" src="" alt="" style="display:none">
        <div class="bc-photo-ph" id="bc-ph"></div>
        <div class="bc-body">
            <div class="bc-name" id="bc-name"></div>
            <div class="bc-role" id="bc-role"></div>
            <div class="bc-desc" id="bc-desc"></div>
            <div class="bc-socials" id="bc-socials"></div>
        </div>
    </div>

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





     <script>
        const members = [
            {
                initials: 'KZ',
                name: 'Kent Lester Zabala',
                role: 'Backend Programmer',
                desc: 'Focuses on system architecture, backend logic, and full-stack development of TechTrivia.',
                img: 'assets/image/kent.png',
                linkedin: 'https://www.linkedin.com/in/kent-lester-zabala-0730803b8',
                github: 'https://github.com/mstar13-sys',
                fb: 'https://www.facebook.com/d.philophobia.3'
            },
            {
                initials: 'AE',
                name: 'Arvin E. Endico',
                role: 'Game Developer',
                desc: 'Responsible for UI/UX, game mechanics, and interactive system design.',
                img: 'assets/image/arvin.png',
                linkedin: 'https://www.linkedin.com/in/arvin-endico-418353167',
                github: 'https://github.com/PeanutRolls07',
                fb: 'https://www.facebook.com/arvinendico07'
            },
            {
                initials: 'DC',
                name: 'Diana Mae Canoy',
                role: 'Database / QA Tester',
                desc: 'Handles database management, testing, debugging, and system quality assurance.',
                img: 'assets/image/diana.png',
                linkedin: '#',
                github: 'https://github.com/dianamae562-hue',
                fb: 'https://www.facebook.com/profile.php?id=61584980655103'
            }
        ];

        const bigCard = document.getElementById('big-card');
        const overlay = document.getElementById('t-overlay');
        const bcClose = document.getElementById('bc-close');

        const CARD_W = 280;

        document.querySelectorAll('.tmember').forEach(el => {
            el.addEventListener('click', function (e) {
                e.stopPropagation();
                const m = members[+this.dataset.idx];

                // Photo
                const bcPhoto = document.getElementById('bc-photo');
                const bcPh    = document.getElementById('bc-ph');
                bcPhoto.src = m.img;
                bcPhoto.style.display = 'block';
                bcPh.style.display = 'none';
                bcPhoto.onerror = () => {
                    bcPhoto.style.display = 'none';
                    bcPh.style.display = 'flex';
                    bcPh.textContent = m.initials;
                };

                // Text
                document.getElementById('bc-name').textContent = m.name;
                document.getElementById('bc-role').textContent = m.role;
                document.getElementById('bc-desc').textContent = m.desc;
                document.getElementById('bc-socials').innerHTML =
                    `<a href="${m.linkedin}" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                     <a href="${m.github}"   target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
                     <a href="${m.fb}"       target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>`;

                // Center on screen
                const vw = window.innerWidth;
                const vh = window.innerHeight;
                bigCard.style.left = ((vw - CARD_W) / 2) + 'px';
                bigCard.style.top  = Math.max(20, (vh - 440) / 2) + 'px';

                overlay.style.display = 'block';
                bigCard.classList.add('open');
            });
        });

        bcClose.addEventListener('click', e => { e.stopPropagation(); closeCard(); });
        overlay.addEventListener('click', closeCard);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCard(); });

        function closeCard() {
            bigCard.classList.remove('open');
            overlay.style.display = 'none';
        }
    </script>
</body>

</html>
