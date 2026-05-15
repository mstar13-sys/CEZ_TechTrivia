<?php
// ── Admin Panel ───────────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

if (!is_admin()) {
    redirect_to('../index.php');
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Question.php';

$userModel    = new User();
$questionModel = new Question();
$message      = null;
$msgType      = 'success';

// ── Handle POST actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.'; $msgType = 'error';
    } else {
        $act = $_POST['admin_action'] ?? '';

        if ($act === 'delete_player') {
            $pid = (int)($_POST['player_id'] ?? 0);
            $result = $userModel->deletePlayer($pid);
            $message = $result ? 'Player deleted.' : 'Cannot delete this account.';
            $msgType = $result ? 'success' : 'error';
        }

        if ($act === 'add_question') {
            $text    = trim($_POST['question_text'] ?? '');
            $diff    = $_POST['difficulty'] ?? 'easy';
            $cat     = trim($_POST['category'] ?? 'General');
            $choices = [
                trim($_POST['choice_0'] ?? ''),
                trim($_POST['choice_1'] ?? ''),
                trim($_POST['choice_2'] ?? ''),
                trim($_POST['choice_3'] ?? ''),
            ];
            $correct = (int)($_POST['correct_index'] ?? 0);

            if (empty($text) || in_array('', $choices, true)) {
                $message = 'Fill in the question and all 4 choices.'; $msgType = 'error';
            } elseif ($questionModel->add($text, $diff, $cat, $choices, $correct)) {
                $message = 'Question added!';
            } else {
                $message = 'Failed to add question.'; $msgType = 'error';
            }
        }

        if ($act === 'edit_question') {
            $qid     = (int)($_POST['question_id'] ?? 0);
            $text    = trim($_POST['question_text'] ?? '');
            $diff    = $_POST['difficulty'] ?? 'easy';
            $cat     = trim($_POST['category'] ?? 'General');
            $choices = [
                trim($_POST['choice_0'] ?? ''),
                trim($_POST['choice_1'] ?? ''),
                trim($_POST['choice_2'] ?? ''),
                trim($_POST['choice_3'] ?? ''),
            ];
            $correct = (int)($_POST['correct_index'] ?? 0);

            if (empty($text) || in_array('', $choices, true)) {
                $message = 'Fill in the question and all 4 choices.'; $msgType = 'error';
            } elseif ($questionModel->update($qid, $text, $diff, $cat, $choices, $correct)) {
                $message = 'Question updated!';
            } else {
                $message = 'Failed to update question.'; $msgType = 'error';
            }
        }

        if ($act === 'delete_question') {
            $qid     = (int)($_POST['question_id'] ?? 0);
            $result = $questionModel->delete($qid);
            $message = $result ? 'Question deleted.' : 'Failed to delete.';
            $msgType = $result ? 'success' : 'error';
        }
    }
}

$players   = $userModel->getAllPlayers();
$questions = $questionModel->getAllWithChoiceCount();
$stats     = $userModel->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — TechTrivia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ── Edit Modal ── */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #1a1830;
            border: 1px solid #2e2b4a;
            border-radius: 14px;
            padding: 32px;
            width: 100%;
            max-width: 580px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn .25s ease;
        }
        @keyframes slideIn {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .modal-box h3 { margin-bottom: 20px; font-size: 1.1rem; }
        .modal-close {
            float: right; background: none; border: none; color: #9ca3af;
            font-size: 1.4rem; cursor: pointer; line-height: 1;
        }
        .modal-close:hover { color: #f1f0ff; }

        /* Light Mode */
        body.light-mode .modal-box {
            background: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        body.light-mode .modal-close { color: #64748b; }
        body.light-mode .modal-close:hover { color: #111827; }
    </style>
</head>
<body>

<div class="mobile-topbar">
    <button class="mobile-menu-btn" type="button" data-mobile-menu-toggle aria-label="Open menu" aria-expanded="false">&#9776;</button>
    <div class="mobile-brand">CEZ Admin</div>
</div>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🛡️</div>
        <div class="brand-name">CEZ<br><span>Admin</span></div>
    </div>
    <div class="admin-badge">⚙ Admin Panel</div>
    <p class="nav-section-label">Management</p>
    <ul class="nav-menu">
        <li><a href="#" class="nav-link active" data-tab="overview"><span class="nav-icon">📊</span> Overview</a></li>
        <li><a href="#" class="nav-link" data-tab="players"><span class="nav-icon">👥</span> Players</a></li>
        <li><a href="#" class="nav-link" data-tab="questions"><span class="nav-icon">❓</span> Questions</a></li>
    </ul>
    <p class="nav-section-label">Account</p>
    <ul class="nav-menu">
        <li><a href="admin_settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
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

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Manage players, questions, and platform stats</p>
        </div>
        <div class="header-actions">
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
                <span class="theme-icon">🌙</span>
            </button>
            <span style="color:var(--text-muted);font-size:13px" id="adminDate"></span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="overview">📊 Overview</button>
        <button class="tab-btn" data-tab="players">👥 Players</button>
        <button class="tab-btn" data-tab="questions">❓ Questions</button>
    </div>

    <!-- ── Overview ── -->
    <div class="tab-panel active" id="tab-overview">
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon indigo">👥</div><div><div class="stat-value"><?= (int)$stats['total_players'] ?></div><div class="stat-label">Total Players</div></div></div>
            <div class="stat-card"><div class="stat-icon gold">🎮</div><div><div class="stat-value"><?= (int)$stats['total_sessions'] ?></div><div class="stat-label">Game Sessions</div></div></div>
            <div class="stat-card"><div class="stat-icon green">❓</div><div><div class="stat-value"><?= (int)$stats['total_questions'] ?></div><div class="stat-label">Questions</div></div></div>
            <div class="stat-card"><div class="stat-icon red">📈</div><div><div class="stat-value"><?= round((float)$stats['avg_score'], 1) ?></div><div class="stat-label">Avg. Score</div></div></div>
        </div>
        <div class="table-card">
            <div class="table-header"><h3>Recent Players</h3></div>
            <?php if (empty($players)): ?>
            <div class="empty-state"><div class="empty-icon">👤</div><p>No players registered yet.</p></div>
            <?php else: ?>
            <table>
                <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>XP</th><th>Level</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($players, 0, 5) as $i => $p): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($p['username']) ?></strong></td>
                    <td style="color:var(--text-muted)"><?= htmlspecialchars($p['email']) ?></td>
                    <td><span class="badge badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span></td>
                    <td><?= (int)$p['total_xp'] ?></td>
                    <td><?= (int)$p['level'] ?></td>
                    <td style="color:var(--text-muted)"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Players ── -->
    <div class="tab-panel" id="tab-players">
        <div class="table-card">
            <div class="table-header">
                <h3>All Players (<?= count($players) ?>)</h3>
                <input class="search-input" type="text" placeholder="🔍 Search players…"
                       oninput="filterTable('playerTable', this.value)">
            </div>
            <?php if (empty($players)): ?>
            <div class="empty-state"><div class="empty-icon">👥</div><p>No players yet.</p></div>
            <?php else: ?>
            <table id="playerTable">
                <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>XP</th><th>Level</th><th>Joined</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($players as $i => $p): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($p['username']) ?></strong></td>
                    <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($p['email']) ?></td>
                    <td><span class="badge badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span></td>
                    <td><?= (int)$p['total_xp'] ?></td>
                    <td><?= (int)$p['level'] ?></td>
                    <td style="color:var(--text-muted);font-size:13px"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <?php if ($p['role'] !== 'admin'): ?>
                        <form method="POST" style="display:inline"
                              onsubmit="return confirmDelete(event, '<?= htmlspecialchars($p['username'], ENT_QUOTES) ?>')">
                            <input type="hidden" name="admin_action" value="delete_player">
                            <input type="hidden" name="player_id"   value="<?= $p['player_id'] ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">🗑 Delete</button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:12px">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Questions ── -->
    <div class="tab-panel" id="tab-questions">

        <!-- Add question form -->
        <div class="form-card" style="margin-bottom:24px">
            <h3>➕ Add New Question</h3>
            <form method="POST" id="addQuestionForm">
                <input type="hidden" name="admin_action" value="add_question">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Question Text</label>
                    <input class="form-control" type="text" name="question_text" placeholder="Enter the question…" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Difficulty</label>
                        <select class="form-control" name="difficulty">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:span 2">
                        <label>Category</label>
                        <input class="form-control" type="text" name="category" placeholder="e.g. Networking, JavaScript…" value="General">
                    </div>
                </div>
                <div class="form-group">
                    <label>Answer Choices <span style="color:var(--text-muted);font-size:11px;font-weight:400">(select radio = correct answer)</span></label>
                    <div class="choices-grid">
                        <?php foreach (['A','B','C','D'] as $idx => $letter): ?>
                        <div class="choice-item">
                            <input type="radio" name="correct_index" value="<?= $idx ?>" <?= $idx === 0 ? 'checked' : '' ?> required>
                            <span class="choice-label"><?= $letter ?>.</span>
                            <input class="form-control" type="text" name="choice_<?= $idx ?>" placeholder="Choice <?= $letter ?>…" required>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="padding:12px 28px">Add Question</button>
            </form>
        </div>

        <!-- Questions table -->
        <div class="table-card">
            <div class="table-header">
                <h3>All Questions (<?= count($questions) ?>)</h3>
                <input class="search-input" type="text" placeholder="🔍 Search questions…"
                       oninput="filterTable('questionTable', this.value)">
            </div>
            <?php if (empty($questions)): ?>
            <div class="empty-state"><div class="empty-icon">❓</div><p>No questions yet. Add one above!</p></div>
            <?php else: ?>
            <table id="questionTable">
                <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Difficulty</th><th>Choices</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($questions as $i => $q): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i+1 ?></td>
                    <td style="max-width:300px;word-break:break-word"><?= htmlspecialchars($q['question_text']) ?></td>
                    <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($q['category']) ?></td>
                    <td><span class="badge badge-<?= $q['difficulty'] ?>"><?= ucfirst($q['difficulty']) ?></span></td>
                    <td style="color:var(--text-muted)"><?= (int)$q['choice_count'] ?></td>
                    <td style="display:flex;gap:8px;flex-wrap:wrap;padding:12px 20px">
                        <!-- Edit button -->
                        <button type="button" class="btn btn-primary"
                                onclick="openEditModal(<?= $q['question_id'] ?>)">✏️ Edit</button>
                        <!-- Delete button -->
                        <form method="POST" style="display:inline"
                              onsubmit="return confirmDeleteQ(event)">
                            <input type="hidden" name="admin_action" value="delete_question">
                            <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Edit Question Modal ── -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEditModal()">✕</button>
        <h3>✏️ Edit Question</h3>
        <form method="POST" id="editQuestionForm">
            <input type="hidden" name="admin_action" value="edit_question">
            <input type="hidden" name="question_id"  id="edit_question_id">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Question Text</label>
                <input class="form-control" type="text" id="edit_question_text" name="question_text" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Difficulty</label>
                    <select class="form-control" id="edit_difficulty" name="difficulty">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2">
                    <label>Category</label>
                    <input class="form-control" type="text" id="edit_category" name="category">
                </div>
            </div>
            <div class="form-group">
                <label>Answer Choices</label>
                <div class="choices-grid">
                    <?php foreach (['A','B','C','D'] as $idx => $letter): ?>
                    <div class="choice-item">
                        <input type="radio" name="correct_index" value="<?= $idx ?>"
                               id="edit_correct_<?= $idx ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                        <span class="choice-label"><?= $letter ?>.</span>
                        <input class="form-control" type="text" id="edit_choice_<?= $idx ?>"
                               name="choice_<?= $idx ?>" placeholder="Choice <?= $letter ?>…" required>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:12px 28px;margin-top:8px">Save Changes</button>
        </form>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/admin.js"></script>
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
