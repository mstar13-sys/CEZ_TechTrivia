<?php
require_once __DIR__ . '/../autoload.php';

if (!is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Question.php';

$userModel = new User();
$questionModel = new Question();
$message = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $msgType = 'error';
    } else {
        $action = $_POST['deleted_action'] ?? '';

        if ($action === 'restore_player') {
            $result = $userModel->restorePlayer((int)($_POST['player_id'] ?? 0));
            $message = $result ? 'Player restored.' : 'Failed to restore player.';
            $msgType = $result ? 'success' : 'error';
        }

        if ($action === 'restore_question') {
            $result = $questionModel->restore((int)($_POST['question_id'] ?? 0));
            $message = $result ? 'Question restored.' : 'Failed to restore question.';
            $msgType = $result ? 'success' : 'error';
        }

        if ($action === 'restore_achievement') {
            $result = $userModel->restoreAchievement((int)($_POST['achievement_id'] ?? 0));
            $message = $result ? 'Achievement restored.' : 'Failed to restore achievement.';
            $msgType = $result ? 'success' : 'error';
        }

        if ($action === 'restore_rank') {
            $result = $userModel->restoreRank((int)($_POST['rank_id'] ?? 0));
            $message = $result ? 'Rank restored.' : 'Failed to restore rank.';
            $msgType = $result ? 'success' : 'error';
        }
    }
}

$deletedPlayers = $userModel->getDeletedPlayers();
$deletedQuestions = $questionModel->getDeletedWithChoiceCount();
$deletedAchievements = $userModel->getDeletedAchievements();
$deletedRanks = $userModel->getDeletedRanks();
$notificationCount = (new NotificationStore())->unreadCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Records - CEZ Admin</title>
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ Admin</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><img src="../assets/image/logo.png" alt="CEZ TechTrivia logo"></div>
        <div class="brand-name">CEZ<br><span>Admin</span></div>
    </div>
    <div class="admin-badge">&#9881; Admin Panel</div>
    <p class="nav-section-label">Management</p>
    <ul class="nav-menu">
        <li><a href="admin.php" class="nav-link"><span class="nav-icon">&#128202;</span> Overview</a></li>
        <li><a href="admin.php?tab=players" class="nav-link"><span class="nav-icon">&#128101;</span> Players</a></li>
        <li><a href="admin.php?tab=questions" class="nav-link"><span class="nav-icon">&#10067;</span> Questions</a></li>
        <li><a href="admin.php?tab=achievements" class="nav-link"><span class="nav-icon">&#127941;</span> Achievements</a></li>
        <li><a href="admin.php?tab=ranks" class="nav-link"><span class="nav-icon">&#127942;</span> Ranks</a></li>
        <li><a href="deleted.php" class="active"><span class="nav-icon">&#128465;</span> Deleted</a></li>
        <li><a href="notifications.php"><span class="nav-icon">&#128276;</span> Notifications<?= $notificationCount > 0 ? ' <span class="nav-count">' . (int)$notificationCount . '</span>' : '' ?></a></li>
    </ul>
    <p class="nav-section-label">Account</p>
    <ul class="nav-menu">
        <li><a href="admin_settings.php"><span class="nav-icon">&#9881;</span> Settings</a></li>
    </ul>
    <div class="sidebar-footer">
        <form class="logout-form" action="../controllers/AuthController.php" method="POST" onsubmit="return confirmLogout(event)">
            <input type="hidden" name="action" value="logout">
            <?= csrf_field() ?>
            <button type="submit">&#128682; Sign Out</button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Deleted Records</h1>
            <p>Restore hidden players, questions, achievements, and ranks</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">&#127769;</span>
            </button>
            <span style="color:var(--text-muted);font-size:13px" id="adminDate"></span>
        </div>
    </div>

    <div class="table-card" style="margin-bottom:24px">
        <div class="table-header">
            <h3>Deleted Players (<?= count($deletedPlayers) ?>)</h3>
            <input class="search-input" type="text" placeholder="Search players..." oninput="filterTable('deletedPlayerTable', this.value)">
        </div>
        <?php if (empty($deletedPlayers)): ?>
            <div class="empty-state"><div class="empty-icon">&#128100;</div><p>No deleted players.</p></div>
        <?php else: ?>
            <table id="deletedPlayerTable">
                <thead>
                    <tr><th>#</th><th>Username</th><th>Email</th><th>Deleted By</th><th>Reason</th><th>Deleted At</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($deletedPlayers as $i => $player): ?>
                    <?php $meta = $player['deleted_meta'] ?? []; ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($player['username']) ?></strong></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($player['email']) ?></td>
                        <td><?= htmlspecialchars($meta['deleted_by_username'] ?? 'Unknown') ?></td>
                        <td style="max-width:320px;word-break:break-word;color:var(--text-muted)"><?= htmlspecialchars($meta['reason'] ?? '') ?></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($meta['deleted_at'] ?? '') ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirmRestore(event, 'player')">
                                <input type="hidden" name="deleted_action" value="restore_player">
                                <input type="hidden" name="player_id" value="<?= (int)$player['player_id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">&#8634; Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="table-card" style="margin-bottom:24px">
        <div class="table-header">
            <h3>Deleted Questions (<?= count($deletedQuestions) ?>)</h3>
            <input class="search-input" type="text" placeholder="Search questions..." oninput="filterTable('deletedQuestionTable', this.value)">
        </div>
        <?php if (empty($deletedQuestions)): ?>
            <div class="empty-state"><div class="empty-icon">&#10067;</div><p>No deleted questions.</p></div>
        <?php else: ?>
            <table id="deletedQuestionTable">
                <thead>
                    <tr><th>#</th><th>Question</th><th>Category</th><th>Difficulty</th><th>Choices</th><th>Reason</th><th>Deleted At</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($deletedQuestions as $i => $question): ?>
                    <?php $meta = $question['deleted_meta'] ?? []; ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
                        <td style="max-width:320px;word-break:break-word"><?= htmlspecialchars($question['question_text']) ?></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($question['category']) ?></td>
                        <td><span class="badge badge-<?= htmlspecialchars($question['difficulty']) ?>"><?= ucfirst(htmlspecialchars($question['difficulty'])) ?></span></td>
                        <td><?= (int)$question['choice_count'] ?></td>
                        <td style="max-width:280px;word-break:break-word;color:var(--text-muted)"><?= htmlspecialchars($meta['reason'] ?? '') ?></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($meta['deleted_at'] ?? '') ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirmRestore(event, 'question')">
                                <input type="hidden" name="deleted_action" value="restore_question">
                                <input type="hidden" name="question_id" value="<?= (int)$question['question_id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">&#8634; Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="table-card" style="margin-bottom:24px">
        <div class="table-header">
            <h3>Deleted Achievements (<?= count($deletedAchievements) ?>)</h3>
            <input class="search-input" type="text" placeholder="Search achievements..." oninput="filterTable('deletedAchievementTable', this.value)">
        </div>
        <?php if (empty($deletedAchievements)): ?>
            <div class="empty-state"><div class="empty-icon">&#127941;</div><p>No deleted achievements.</p></div>
        <?php else: ?>
            <table id="deletedAchievementTable">
                <thead>
                    <tr><th>#</th><th>Title</th><th>Description</th><th>Condition</th><th>Deleted By</th><th>Reason</th><th>Deleted At</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($deletedAchievements as $i => $achievement): ?>
                    <?php $meta = $achievement['deleted_meta'] ?? []; ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($achievement['title']) ?></strong></td>
                        <td style="max-width:320px;word-break:break-word;color:var(--text-muted)"><?= htmlspecialchars($achievement['description']) ?></td>
                        <td>
                            <span class="badge badge-player"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $achievement['condition_type']))) ?></span>
                            <span style="color:var(--text-muted);font-size:13px;margin-left:6px"><?= (int)$achievement['condition_value'] ?></span>
                        </td>
                        <td><?= htmlspecialchars($meta['deleted_by_username'] ?? 'Unknown') ?></td>
                        <td style="max-width:280px;word-break:break-word;color:var(--text-muted)"><?= htmlspecialchars($meta['reason'] ?? '') ?></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($meta['deleted_at'] ?? '') ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirmRestore(event, 'achievement')">
                                <input type="hidden" name="deleted_action" value="restore_achievement">
                                <input type="hidden" name="achievement_id" value="<?= (int)$achievement['achievement_id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">&#8634; Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Deleted Ranks (<?= count($deletedRanks) ?>)</h3>
            <input class="search-input" type="text" placeholder="Search ranks..." oninput="filterTable('deletedRankTable', this.value)">
        </div>
        <?php if (empty($deletedRanks)): ?>
            <div class="empty-state"><div class="empty-icon">&#127942;</div><p>No deleted ranks.</p></div>
        <?php else: ?>
            <table id="deletedRankTable">
                <thead>
                    <tr><th>#</th><th>Rank</th><th>XP Range</th><th>Medal</th><th>Deleted By</th><th>Reason</th><th>Deleted At</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($deletedRanks as $i => $rank): ?>
                    <?php $meta = $rank['deleted_meta'] ?? []; ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($rank['rank_name']) ?></strong></td>
                        <td style="color:var(--text-muted);font-size:13px">
                            <?= number_format((int)$rank['min_xp']) ?> -
                            <?= $rank['max_xp'] === null ? 'No limit' : number_format((int)$rank['max_xp']) ?>
                        </td>
                        <td><?= htmlspecialchars($rank['medal']) ?></td>
                        <td><?= htmlspecialchars($meta['deleted_by_username'] ?? 'Unknown') ?></td>
                        <td style="max-width:280px;word-break:break-word;color:var(--text-muted)"><?= htmlspecialchars($meta['reason'] ?? '') ?></td>
                        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($meta['deleted_at'] ?? '') ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirmRestore(event, 'rank')">
                                <input type="hidden" name="deleted_action" value="restore_rank">
                                <input type="hidden" name="rank_id" value="<?= (int)$rank['rank_id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">&#8634; Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
function confirmRestore(event, type) {
    event.preventDefault();
    const form = event.target;
    Swal.fire({
        icon: 'question',
        title: 'Restore ' + type + '?',
        text: 'This will make the record active again.',
        background: '#1a1830',
        color: '#f1f0ff',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#4f46e5',
        confirmButtonText: 'Restore',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}
</script>
<?php if ($message): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLightMode = document.body.classList.contains('light-mode');
    Swal.fire({
        icon: <?= json_encode($msgType) ?>,
        title: <?= $msgType === 'success' ? "'Done!'" : "'Error'" ?>,
        text: <?= json_encode($message) ?>,
        background: isLightMode ? '#ffffff' : '#1a1830',
        color: isLightMode ? '#111827' : '#f1f0ff',
        confirmButtonColor: '#2563eb',
    });
});
</script>
<?php endif; ?>
</body>
</html>
