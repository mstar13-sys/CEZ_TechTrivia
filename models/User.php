<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/SoftDeleteStore.php';
require_once __DIR__ . '/../core/NotificationStore.php';

// ── User Model ────────────────────────────────────────────────
class User
{
    private const XP_PER_LEVEL = 100;
    private const XP_BY_DIFFICULTY = [
        'easy' => 5,
        'medium' => 10,
        'hard' => 15,
    ];

    private $conn;
    private $softDeletes;
    private $notifications;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->softDeletes = new SoftDeleteStore();
        $this->notifications = new NotificationStore();
    }

    // ── Auth ──────────────────────────────────────────────────

    public function register($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "INSERT INTO player (username, email, password) VALUES (:username, :email, :password)"
        );
        return $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hash]);
    }

    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();
            if (($user['role'] ?? 'player') !== 'admin' && $this->softDeletes->isDeleted('players', $user['player_id'])) {
                return false;
            }
            if (password_verify($password, $user['password'])) return $user;
        }
        return false;
    }

    public function getDeletedLoginDetails($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM player WHERE username = :username");
        $stmt->execute([':username' => trim((string)$username)]);
        $user = $stmt->fetch();

        if (!$user || ($user['role'] ?? 'player') === 'admin') {
            return null;
        }

        if (!$this->softDeletes->isDeleted('players', $user['player_id']) || !password_verify((string)$password, $user['password'])) {
            return null;
        }

        $deleted = $this->softDeletes->listDeleted('players');
        return [
            'player_id' => (int)$user['player_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'deleted_meta' => $deleted[(string)(int)$user['player_id']] ?? [],
        ];
    }

    public function getDeletedRecoveryAccount($username, $email, $password)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM player WHERE username = :username AND email = :email AND role != 'admin'"
        );
        $stmt->execute([
            ':username' => trim((string)$username),
            ':email' => trim((string)$email),
        ]);
        $user = $stmt->fetch();

        if (!$user || !$this->softDeletes->isDeleted('players', $user['player_id']) || !password_verify((string)$password, $user['password'])) {
            return null;
        }

        $deleted = $this->softDeletes->listDeleted('players');
        return [
            'player_id' => (int)$user['player_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'deleted_meta' => $deleted[(string)(int)$user['player_id']] ?? [],
        ];
    }

    public function userExists($username)
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }

    public function resetPasswordByUsernameEmail($username, $email, $new_password)
    {
        $stmt = $this->conn->prepare(
            "SELECT player_id FROM player WHERE username = :username AND email = :email"
        );
        $stmt->execute([':username' => $username, ':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'No account matches that username and email.'];
        }

        $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "UPDATE player SET password = :password WHERE player_id = :id"
        );
        $result = $stmt->execute([':password' => $hash, ':id' => $user['player_id']]);

        return [
            'success' => $result,
            'message' => $result ? 'Password changed successfully. You can now sign in.' : 'Failed to change password.',
        ];
    }

    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM player WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    // ── Admin: Players ────────────────────────────────────────

    public function getAllPlayers()
    {
        $stmt = $this->conn->prepare(
            "SELECT p.player_id, p.username, p.email, p.role, p.total_xp, p.level, p.created_at,
                    COUNT(gs.session_id) AS games_played,
                    COALESCE(MAX(gs.total_score), 0) AS best_score,
                    COALESCE(AVG(gs.total_score), 0) AS avg_score
             FROM player p
             LEFT JOIN gamesession gs ON p.player_id = gs.player_id
             WHERE p.role = 'player'
             GROUP BY p.player_id, p.username, p.email, p.role, p.total_xp, p.level, p.created_at
             ORDER BY p.created_at DESC"
        );
        $stmt->execute();
        return $this->softDeletes->filterRows('players', $stmt->fetchAll(), 'player_id');
    }

    public function deletePlayer($id, $reason = '', ?array $deletedBy = null)
    {
        $stmt = $this->conn->prepare("SELECT player_id, username, email FROM player WHERE player_id = :id AND role != 'admin'");
        $stmt->execute([':id' => (int)$id]);
        $player = $stmt->fetch();
        if (!$player) {
            return false;
        }

        $result = $this->softDeletes->markDeleted('players', (int)$id, $reason, $deletedBy);
        if ($result) {
            $this->notifications->add(
                'account_deleted',
                'Account deleted',
                $player['username'] . ' was moved to deleted records.',
                [
                    'player_id' => (int)$player['player_id'],
                    'username' => $player['username'],
                    'email' => $player['email'],
                    'delete_reason' => function_exists('normalize_delete_reason') ? normalize_delete_reason($reason) : trim((string)$reason),
                    'deleted_by_username' => $deletedBy['username'] ?? 'Self',
                ]
            );
        }

        return $result;
    }

    public function restorePlayer($id)
    {
        return $this->softDeletes->restore('players', (int)$id);
    }

    public function getDeletedPlayers()
    {
        $deleted = $this->softDeletes->listDeleted('players');
        $players = [];

        foreach ($deleted as $id => $meta) {
            $stmt = $this->conn->prepare(
                "SELECT player_id, username, email, role, total_xp, level, created_at
                 FROM player
                 WHERE player_id = :id"
            );
            $stmt->execute([':id' => (int)$id]);
            $player = $stmt->fetch();
            if ($player) {
                $player['deleted_meta'] = $meta;
                $players[] = $player;
            }
        }

        usort($players, function ($a, $b) {
            return strcmp($b['deleted_meta']['deleted_at'] ?? '', $a['deleted_meta']['deleted_at'] ?? '');
        });

        return $players;
    }

    // ── Admin: Stats ──────────────────────────────────────────

    public function getStats()
    {
        return [
            'total_players'   => count($this->getAllPlayers()),
            'total_sessions'  => $this->conn->query("SELECT COUNT(*) FROM gamesession")->fetchColumn(),
            'total_questions' => $this->countActiveQuestions(),
            'avg_score'       => $this->conn->query("SELECT COALESCE(AVG(total_score),0) FROM gamesession")->fetchColumn(),
        ];
    }

    private function countActiveQuestions()
    {
        $stmt = $this->conn->prepare("SELECT question_id FROM question");
        $stmt->execute();
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $questionId) {
            if (!$this->softDeletes->isDeleted('questions', $questionId)) {
                $count++;
            }
        }
        return $count;
    }

    // Achievements and ranks

    public function getAllAchievements()
    {
        $stmt = $this->conn->prepare(
            "SELECT achievement_id, title, description, condition_type, condition_value
             FROM achievement
             ORDER BY condition_type ASC, condition_value ASC, title ASC"
        );
        $stmt->execute();
        return $this->softDeletes->filterRows('achievements', $stmt->fetchAll(), 'achievement_id');
    }

    public function getAchievementById($achievement_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT achievement_id, title, description, condition_type, condition_value
             FROM achievement
             WHERE achievement_id = :id"
        );
        $stmt->execute([':id' => (int)$achievement_id]);
        return $stmt->fetch();
    }

    public function addAchievement($title, $description, $condition_type, $condition_value)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO achievement (title, description, condition_type, condition_value)
             VALUES (:title, :description, :condition_type, :condition_value)"
        );
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':condition_type' => $condition_type,
            ':condition_value' => (int)$condition_value,
        ]);
    }

    public function updateAchievement($achievement_id, $title, $description, $condition_type, $condition_value)
    {
        $stmt = $this->conn->prepare(
            "UPDATE achievement
             SET title = :title,
                 description = :description,
                 condition_type = :condition_type,
                 condition_value = :condition_value
             WHERE achievement_id = :id"
        );
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':condition_type' => $condition_type,
            ':condition_value' => (int)$condition_value,
            ':id' => (int)$achievement_id,
        ]);
    }

    public function deleteAchievement($achievement_id)
    {
        return $this->deleteAchievementWithReason($achievement_id, 'Deleted by admin.');
    }

    public function deleteAchievementWithReason($achievement_id, $reason = '', ?array $deletedBy = null)
    {
        $stmt = $this->conn->prepare("SELECT achievement_id FROM achievement WHERE achievement_id = :id");
        $stmt->execute([':id' => (int)$achievement_id]);
        if (!$stmt->fetch()) {
            return false;
        }

        return $this->softDeletes->markDeleted('achievements', (int)$achievement_id, $reason, $deletedBy);
    }

    public function restoreAchievement($achievement_id)
    {
        return $this->softDeletes->restore('achievements', (int)$achievement_id);
    }

    public function getDeletedAchievements()
    {
        $deleted = $this->softDeletes->listDeleted('achievements');
        $achievements = [];

        foreach ($deleted as $id => $meta) {
            $stmt = $this->conn->prepare(
                "SELECT achievement_id, title, description, condition_type, condition_value
                 FROM achievement
                 WHERE achievement_id = :id"
            );
            $stmt->execute([':id' => (int)$id]);
            $achievement = $stmt->fetch();
            if ($achievement) {
                $achievement['deleted_meta'] = $meta;
                $achievements[] = $achievement;
            }
        }

        usort($achievements, function ($a, $b) {
            return strcmp($b['deleted_meta']['deleted_at'] ?? '', $a['deleted_meta']['deleted_at'] ?? '');
        });

        return $achievements;
    }

    public function getPlayerAchievements($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT a.achievement_id, a.title, a.description, a.condition_type, a.condition_value,
                    pa.date_unlocked,
                    CASE WHEN pa.player_achievement_id IS NULL THEN 0 ELSE 1 END AS unlocked
             FROM achievement a
             LEFT JOIN playerachievement pa
                    ON pa.achievement_id = a.achievement_id
                   AND pa.player_id = :pid
             ORDER BY unlocked DESC, a.condition_type ASC, a.condition_value ASC, a.title ASC"
        );
        $stmt->execute([':pid' => (int)$player_id]);
        return $this->softDeletes->filterRows('achievements', $stmt->fetchAll(), 'achievement_id');
    }

    public function getAchievementSummary($player_id)
    {
        $achievements = $this->getPlayerAchievements($player_id);
        $unlocked = array_filter($achievements, function ($achievement) {
            return (int)($achievement['unlocked'] ?? 0) === 1;
        });

        return [
            'total_achievements' => count($achievements),
            'unlocked_achievements' => count($unlocked),
        ];
    }

    public function getAllRanks()
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `rank`
             ORDER BY min_xp ASC"
        );
        $stmt->execute();
        return $this->softDeletes->filterRows('ranks', $stmt->fetchAll(), 'rank_id');
    }

    public function getRankById($rank_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `rank`
             WHERE rank_id = :id"
        );
        $stmt->execute([':id' => (int)$rank_id]);
        return $stmt->fetch();
    }

    public function addRank($rank_name, $min_xp, $max_xp, $medal)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO `rank` (rank_name, min_xp, max_xp, medal)
             VALUES (:rank_name, :min_xp, :max_xp, :medal)"
        );
        return $stmt->execute([
            ':rank_name' => $rank_name,
            ':min_xp' => (int)$min_xp,
            ':max_xp' => $max_xp === null ? null : (int)$max_xp,
            ':medal' => $medal,
        ]);
    }

    public function updateRank($rank_id, $rank_name, $min_xp, $max_xp, $medal)
    {
        $stmt = $this->conn->prepare(
            "UPDATE `rank`
             SET rank_name = :rank_name,
                 min_xp = :min_xp,
                 max_xp = :max_xp,
                 medal = :medal
             WHERE rank_id = :id"
        );
        return $stmt->execute([
            ':rank_name' => $rank_name,
            ':min_xp' => (int)$min_xp,
            ':max_xp' => $max_xp === null ? null : (int)$max_xp,
            ':medal' => $medal,
            ':id' => (int)$rank_id,
        ]);
    }

    public function deleteRank($rank_id)
    {
        return $this->deleteRankWithReason($rank_id, 'Deleted by admin.');
    }

    public function deleteRankWithReason($rank_id, $reason = '', ?array $deletedBy = null)
    {
        $stmt = $this->conn->prepare("SELECT rank_id FROM `rank` WHERE rank_id = :id");
        $stmt->execute([':id' => (int)$rank_id]);
        if (!$stmt->fetch()) {
            return false;
        }

        return $this->softDeletes->markDeleted('ranks', (int)$rank_id, $reason, $deletedBy);
    }

    public function restoreRank($rank_id)
    {
        return $this->softDeletes->restore('ranks', (int)$rank_id);
    }

    public function getDeletedRanks()
    {
        $deleted = $this->softDeletes->listDeleted('ranks');
        $ranks = [];

        foreach ($deleted as $id => $meta) {
            $stmt = $this->conn->prepare(
                "SELECT rank_id, rank_name, min_xp, max_xp, medal
                 FROM `rank`
                 WHERE rank_id = :id"
            );
            $stmt->execute([':id' => (int)$id]);
            $rank = $stmt->fetch();
            if ($rank) {
                $rank['deleted_meta'] = $meta;
                $ranks[] = $rank;
            }
        }

        usort($ranks, function ($a, $b) {
            return strcmp($b['deleted_meta']['deleted_at'] ?? '', $a['deleted_meta']['deleted_at'] ?? '');
        });

        return $ranks;
    }

    public function getRankForXp($xp)
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `rank`
             WHERE :xp_min >= min_xp AND (max_xp IS NULL OR :xp_max <= max_xp)
             ORDER BY min_xp DESC"
        );
        $stmt->execute([':xp_min' => (int)$xp, ':xp_max' => (int)$xp]);
        $ranks = $this->softDeletes->filterRows('ranks', $stmt->fetchAll(), 'rank_id');
        $rank = $ranks[0] ?? null;
        return $rank ?: [
            'rank_name' => 'Rookie',
            'min_xp' => 0,
            'max_xp' => 499,
            'medal' => 'Bronze',
        ];
    }

    public function getNextRankForXp($xp)
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `rank`
             WHERE min_xp > :xp
             ORDER BY min_xp ASC"
        );
        $stmt->execute([':xp' => (int)$xp]);
        $ranks = $this->softDeletes->filterRows('ranks', $stmt->fetchAll(), 'rank_id');
        return $ranks[0] ?? false;
    }

    public function getXpPerCorrectAnswer($difficulty)
    {
        $difficulty = strtolower((string)$difficulty);
        return self::XP_BY_DIFFICULTY[$difficulty] ?? self::XP_BY_DIFFICULTY['medium'];
    }

    public function calculateXpForScore($score, $difficulty)
    {
        return max(0, (int)$score) * $this->getXpPerCorrectAnswer($difficulty);
    }

    public function calculateLevelForXp($totalXp)
    {
        return max(1, intdiv(max(0, (int)$totalXp), self::XP_PER_LEVEL) + 1);
    }

    public function syncPlayerAchievements($player_id)
    {
        return $this->unlockQualifiedAchievements((int)$player_id);
    }

    public function syncAllPlayerAchievements()
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM player WHERE role = 'player'");
        $stmt->execute();
        $totalUnlocked = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $playerId) {
            if ($this->softDeletes->isDeleted('players', $playerId)) {
                continue;
            }
            $totalUnlocked += count($this->syncPlayerAchievements((int)$playerId));
        }
        return $totalUnlocked;
    }

    // ── Admin: Questions ──────────────────────────────────────

    public function getAllQuestions()
    {
        $stmt = $this->conn->prepare(
            "SELECT q.*, COUNT(c.choice_id) AS choice_count
             FROM question q
             LEFT JOIN choice c ON q.question_id = c.question_id
             GROUP BY q.question_id
             ORDER BY q.created_at DESC"
        );
        $stmt->execute();
        return $this->softDeletes->filterRows('questions', $stmt->fetchAll(), 'question_id');
    }

    public function getQuestionWithChoices($question_id)
    {
        if ($this->softDeletes->isDeleted('questions', $question_id)) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM question WHERE question_id = :id");
        $stmt->execute([':id' => $question_id]);
        $question = $stmt->fetch();
        if (!$question) return null;

        $stmt2 = $this->conn->prepare("SELECT * FROM choice WHERE question_id = :id");
        $stmt2->execute([':id' => $question_id]);
        $question['choices'] = $stmt2->fetchAll();
        return $question;
    }

    public function addQuestion($text, $difficulty, $category, $choices, $correct_index)
    {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO question (question_text, difficulty, category) VALUES (:text, :diff, :cat)"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category]);
            $qid = $this->conn->lastInsertId();
            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
                );
                $stmt2->execute([':qid' => $qid, ':text' => $choice, ':correct' => ($i == $correct_index ? 1 : 0)]);
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function updateQuestion($id, $text, $difficulty, $category, $choices, $correct_index)
    {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "UPDATE question SET question_text=:text, difficulty=:diff, category=:cat WHERE question_id=:id"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category, ':id' => $id]);
            $this->conn->prepare("DELETE FROM choice WHERE question_id = :id")->execute([':id' => $id]);
            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
                );
                $stmt2->execute([':qid' => $id, ':text' => $choice, ':correct' => ($i == $correct_index ? 1 : 0)]);
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function deleteQuestion($id, $reason = '')
    {
        $stmt = $this->conn->prepare("SELECT question_id FROM question WHERE question_id = :id");
        $stmt->execute([':id' => (int)$id]);
        if (!$stmt->fetch()) {
            return false;
        }

        return $this->softDeletes->markDeleted('questions', (int)$id, $reason);
    }

    public function restoreQuestion($id)
    {
        return $this->softDeletes->restore('questions', (int)$id);
    }

    public function getDeletedQuestions()
    {
        $deleted = $this->softDeletes->listDeleted('questions');
        $questions = [];

        foreach ($deleted as $id => $meta) {
            $stmt = $this->conn->prepare(
                "SELECT q.*, COUNT(c.choice_id) AS choice_count
                 FROM question q
                 LEFT JOIN choice c ON q.question_id = c.question_id
                 WHERE q.question_id = :id
                 GROUP BY q.question_id"
            );
            $stmt->execute([':id' => (int)$id]);
            $question = $stmt->fetch();
            if ($question) {
                $question['deleted_meta'] = $meta;
                $questions[] = $question;
            }
        }

        usort($questions, function ($a, $b) {
            return strcmp($b['deleted_meta']['deleted_at'] ?? '', $a['deleted_meta']['deleted_at'] ?? '');
        });

        return $questions;
    }

    // ── Category / Difficulty helpers ─────────────────────────

    /** All distinct categories that have questions with choices */
    public function getAvailableCategories()
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.question_id, q.category
             FROM question q
             INNER JOIN choice c ON q.question_id = c.question_id
             ORDER BY q.category ASC"
        );
        $stmt->execute();
        $categories = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!$this->softDeletes->isDeleted('questions', $row['question_id'])) {
                $categories[$row['category']] = true;
            }
        }
        $categories = array_keys($categories);
        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);
        return $categories;
    }

    /** Difficulties available for a given category */
    public function getAvailableDifficulties($category)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.question_id, q.difficulty
             FROM question q
             INNER JOIN choice c ON q.question_id = c.question_id
             WHERE q.category = :cat
             ORDER BY FIELD(q.difficulty,'easy','medium','hard')"
        );
        $stmt->execute([':cat' => $category]);
        $difficulties = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!$this->softDeletes->isDeleted('questions', $row['question_id'])) {
                $difficulties[$row['difficulty']] = true;
            }
        }
        $order = ['easy' => 1, 'medium' => 2, 'hard' => 3];
        $difficulties = array_keys($difficulties);
        usort($difficulties, function ($a, $b) use ($order) {
            return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
        });
        return $difficulties;
    }

    /** Count questions available for a category + difficulty combo */
    public function countAvailableQuestions($category, $difficulty)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.question_id
             FROM question q
             INNER JOIN choice c ON q.question_id = c.question_id
             WHERE q.category = :cat AND q.difficulty = :diff"
        );
        $stmt->execute([':cat' => $category, ':diff' => $difficulty]);
        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $questionId) {
            if (!$this->softDeletes->isDeleted('questions', $questionId)) {
                $count++;
            }
        }
        return $count;
    }

    // ── Leaderboard ───────────────────────────────────────────

    /** Global leaderboard */
    public function getLeaderboard($limit = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.player_id, p.username, p.total_xp, p.level,
                    COUNT(gs.session_id) AS games_played,
                    COALESCE(MAX(gs.total_score), 0) AS best_score
             FROM player p
             LEFT JOIN gamesession gs ON p.player_id = gs.player_id
             WHERE p.role = 'player'
             GROUP BY p.player_id
             ORDER BY p.total_xp DESC, best_score DESC"
        );
        $stmt->execute();
        $rows = $this->softDeletes->filterRows('players', $stmt->fetchAll(), 'player_id');
        return array_slice($rows, 0, (int)$limit);
    }

    /** Leaderboard filtered by category and/or difficulty */
    public function getFilteredLeaderboard($category = null, $difficulty = null, $limit = 20)
    {
        $where  = ["p.role = 'player'"];
        $params = [];

        if ($category !== null && $category !== '') {
            $where[]       = "gs.category = :cat";
            $params[':cat'] = $category;
        }
        if ($difficulty !== null && $difficulty !== '') {
            $where[]        = "gs.difficulty = :diff";
            $params[':diff'] = $difficulty;
        }

        $whereSQL = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT p.player_id, p.username, p.total_xp, p.level,
                    COUNT(gs.session_id)             AS games_played,
                    COALESCE(MAX(gs.total_score), 0) AS best_score,
                    COALESCE(SUM(gs.xp_earned), 0)   AS filter_xp
             FROM player p
             INNER JOIN gamesession gs ON p.player_id = gs.player_id
             WHERE {$whereSQL}
             GROUP BY p.player_id
             ORDER BY filter_xp DESC, best_score DESC"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $this->softDeletes->filterRows('players', $stmt->fetchAll(), 'player_id');
        return array_slice($rows, 0, (int)$limit);
    }

    /** Categories that appear in completed GameSessions */
    public function getLeaderboardCategories()
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT category FROM gamesession
             WHERE category IS NOT NULL AND category != ''
             ORDER BY category ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Quiz / Game Session ───────────────────────────────────

    /** Filtered questions by category and/or difficulty */
    public function getFilteredQuestions($category = null, $difficulty = null, $limit = 10)
    {
        $where  = ['1=1'];
        $params = [];

        if ($category !== null && $category !== '') {
            $where[]       = "q.category = :cat";
            $params[':cat'] = $category;
        }
        if ($difficulty !== null && $difficulty !== '') {
            $where[]        = "q.difficulty = :diff";
            $params[':diff'] = $difficulty;
        }

        $whereSQL = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT q.question_id, q.question_text, q.difficulty, q.category,
                    c.choice_id, c.choice_text, c.is_correct
             FROM question q
             JOIN choice c ON q.question_id = c.question_id
             WHERE {$whereSQL}
             ORDER BY RAND()"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Group choices under each question
        $questions = [];
        foreach ($rows as $row) {
            $qid = $row['question_id'];
            if ($this->softDeletes->isDeleted('questions', $qid)) {
                continue;
            }
            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'question_id'   => $qid,
                    'question_text' => $row['question_text'],
                    'difficulty'    => $row['difficulty'],
                    'category'      => $row['category'],
                    'choices'       => [],
                ];
            }
            $questions[$qid]['choices'][] = [
                'choice_id'   => $row['choice_id'],
                'choice_text' => $row['choice_text'],
                'is_correct'  => $row['is_correct'],
            ];
        }

        $questions = array_values($questions);
        shuffle($questions);
        return array_slice($questions, 0, $limit);
    }

    /** Backward-compatible wrapper */
    public function getRandomQuestions($limit = 10)
    {
        return $this->getFilteredQuestions(null, null, $limit);
    }

    public function saveGameSession($player_id, $score, $xp, $category = null, $difficulty = null)
    {
        $player_id = (int)$player_id;
        if ($this->softDeletes->isDeleted('players', $player_id)) {
            return ['session_id' => null, 'unlocked' => [], 'total_xp' => null, 'level' => null];
        }

        $score = max(0, (int)$score);
        $xp = max(0, (int)$xp);
        $newTotalXp = null;
        $newLevel = null;

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO gamesession (player_id, total_score, xp_earned, category, difficulty)
                 VALUES (:pid, :score, :xp, :cat, :diff)"
            );
            $stmt->execute([
                ':pid'   => $player_id,
                ':score' => $score,
                ':xp'    => $xp,
                ':cat'   => $category,
                ':diff'  => $difficulty,
            ]);
            $session_id = $this->conn->lastInsertId();

            $this->updateDailyStreak($player_id);

            $stmt2 = $this->conn->prepare(
                "UPDATE player
                 SET total_xp = GREATEST(0, COALESCE(total_xp, 0) + :xp)
                 WHERE player_id = :pid"
            );
            $stmt2->execute([':xp' => $xp, ':pid' => $player_id]);

            $stmt3 = $this->conn->prepare(
                "SELECT total_xp FROM player WHERE player_id = :pid"
            );
            $stmt3->execute([':pid' => $player_id]);
            $newTotalXp = (int)$stmt3->fetchColumn();
            $newLevel = $this->calculateLevelForXp($newTotalXp);

            $stmt4 = $this->conn->prepare(
                "UPDATE player SET level = :level WHERE player_id = :pid"
            );
            $stmt4->execute([':level' => $newLevel, ':pid' => $player_id]);

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['session_id' => null, 'unlocked' => [], 'total_xp' => null, 'level' => null];
        }

        $unlocked = $this->unlockQualifiedAchievements($player_id);

        return [
            'session_id' => $session_id,
            'unlocked' => $unlocked,
            'total_xp' => $newTotalXp,
            'level' => $newLevel,
        ];
    }

    public function getPlayerStats($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.username, p.total_xp, p.level,
                    COUNT(gs.session_id) AS games_played,
                    COALESCE(AVG(gs.total_score), 0) AS avg_score,
                    COALESCE(MAX(gs.total_score), 0) AS best_score,
                    COALESCE(s.current_streak, 0) AS current_streak,
                    COALESCE(s.max_streak, 0) AS max_streak
             FROM player p
             LEFT JOIN gamesession gs ON p.player_id = gs.player_id
             LEFT JOIN streak s ON p.player_id = s.player_id
             WHERE p.player_id = :pid
             GROUP BY p.player_id"
        );
        $stmt->execute([':pid' => $player_id]);
        return $stmt->fetch();
    }

    private function updateDailyStreak($player_id)
    {
        $stmt = $this->conn->prepare("SELECT current_streak, max_streak, last_played_date FROM streak WHERE player_id = :pid");
        $stmt->execute([':pid' => $player_id]);
        $streak = $stmt->fetch();

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if (!$streak) {
            $stmt = $this->conn->prepare(
                "INSERT INTO streak (player_id, current_streak, max_streak, last_played_date)
                 VALUES (:pid, 1, 1, :today)"
            );
            $stmt->execute([':pid' => $player_id, ':today' => $today]);
            return;
        }

        if ($streak['last_played_date'] === $today) {
            return;
        }

        $current = ($streak['last_played_date'] === $yesterday) ? ((int)$streak['current_streak'] + 1) : 1;
        $max = max((int)$streak['max_streak'], $current);

        $stmt = $this->conn->prepare(
            "UPDATE streak
             SET current_streak = :current_streak,
                 max_streak = :max_streak,
                 last_played_date = :today
             WHERE player_id = :pid"
        );
        $stmt->execute([
            ':current_streak' => $current,
            ':max_streak' => $max,
            ':today' => $today,
            ':pid' => $player_id,
        ]);
    }

    private function unlockQualifiedAchievements($player_id)
    {
        $achievements = $this->getPlayerAchievements($player_id);
        $stats = $this->getAchievementProgressStats($player_id);
        $unlocked = [];

        foreach ($achievements as $achievement) {
            if ((int)$achievement['unlocked'] === 1) {
                continue;
            }

            $conditionType = $achievement['condition_type'];
            $needed = (int)$achievement['condition_value'];
            $current = (int)($stats[$conditionType] ?? 0);

            if ($current < $needed) {
                continue;
            }

            $this->conn->beginTransaction();
            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO playerachievement (player_id, achievement_id)
                     SELECT :pid_insert, :aid_insert
                     WHERE NOT EXISTS (
                         SELECT 1 FROM playerachievement
                         WHERE player_id = :pid_check AND achievement_id = :aid_check
                     )"
                );
                $stmt->execute([
                    ':pid_insert' => $player_id,
                    ':aid_insert' => (int)$achievement['achievement_id'],
                    ':pid_check' => $player_id,
                    ':aid_check' => (int)$achievement['achievement_id'],
                ]);

                $this->conn->commit();
                if ($stmt->rowCount() > 0) {
                    $unlocked[] = $achievement;
                }
            } catch (Exception $e) {
                $this->conn->rollBack();
            }
        }

        return $unlocked;
    }

    public function getAchievementProgressStats($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                COUNT(*) AS quiz_count,
                COALESCE(MAX(total_score), 0) AS best_score,
                SUM(CASE WHEN total_score >= 10 THEN 1 ELSE 0 END) AS perfect_quiz_count,
                SUM(CASE WHEN difficulty = 'easy' THEN 1 ELSE 0 END) AS easy_quiz_count,
                SUM(CASE WHEN difficulty = 'medium' THEN 1 ELSE 0 END) AS medium_quiz_count,
                SUM(CASE WHEN difficulty = 'hard' THEN 1 ELSE 0 END) AS hard_quiz_count
             FROM gamesession
             WHERE player_id = :pid"
        );
        $stmt->execute([':pid' => $player_id]);
        $sessionStats = $stmt->fetch() ?: [];

        $stmt2 = $this->conn->prepare(
            "SELECT total_xp FROM player WHERE player_id = :pid"
        );
        $stmt2->execute([':pid' => $player_id]);
        $totalXp = (int)$stmt2->fetchColumn();

        $stmt3 = $this->conn->prepare(
            "SELECT current_streak, max_streak FROM streak WHERE player_id = :pid"
        );
        $stmt3->execute([':pid' => $player_id]);
        $streak = $stmt3->fetch() ?: ['current_streak' => 0, 'max_streak' => 0];
        $sessionStreak = $this->calculateSessionStreaks($player_id);

        return [
            'quiz_count' => (int)($sessionStats['quiz_count'] ?? 0),
            'total_xp' => $totalXp,
            'best_score' => (int)($sessionStats['best_score'] ?? 0),
            'perfect_quiz_count' => (int)($sessionStats['perfect_quiz_count'] ?? 0),
            'current_streak' => max((int)($streak['current_streak'] ?? 0), (int)$sessionStreak['current_streak']),
            'max_streak' => max((int)($streak['max_streak'] ?? 0), (int)$sessionStreak['max_streak']),
            'easy_quiz_count' => (int)($sessionStats['easy_quiz_count'] ?? 0),
            'medium_quiz_count' => (int)($sessionStats['medium_quiz_count'] ?? 0),
            'hard_quiz_count' => (int)($sessionStats['hard_quiz_count'] ?? 0),
        ];
    }

    private function calculateSessionStreaks($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT DATE(date_played) AS played_date
             FROM gamesession
             WHERE player_id = :pid
             ORDER BY played_date ASC"
        );
        $stmt->execute([':pid' => (int)$player_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($dates)) {
            return ['current_streak' => 0, 'max_streak' => 0];
        }

        $max = 1;
        $run = 1;
        $previous = null;

        foreach ($dates as $date) {
            if ($previous !== null) {
                $expected = date('Y-m-d', strtotime($previous . ' +1 day'));
                $run = ($date === $expected) ? ($run + 1) : 1;
                $max = max($max, $run);
            }
            $previous = $date;
        }

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $last = end($dates);
        $current = ($last === $today || $last === $yesterday) ? $run : 0;

        return ['current_streak' => $current, 'max_streak' => $max];
    }

    // ── Settings: Update Username ────────────────────────────────

    public function updateUsername($player_id, $new_username)
    {
        if ($this->userExists($new_username)) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }
        $stmt = $this->conn->prepare(
            "UPDATE player SET username = :username WHERE player_id = :id"
        );
        $result = $stmt->execute([':username' => $new_username, ':id' => $player_id]);
        return ['success' => $result, 'message' => $result ? 'Username updated successfully!' : 'Failed to update username.'];
    }

    // ── Settings: Update Password ────────────────────────────────

    public function updatePassword($player_id, $current_password, $new_password)
    {
        $stmt = $this->conn->prepare("SELECT password FROM player WHERE player_id = :id");
        $stmt->execute([':id' => $player_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!password_verify($current_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "UPDATE player SET password = :password WHERE player_id = :id"
        );
        $result = $stmt->execute([':password' => $hash, ':id' => $player_id]);
        return ['success' => $result, 'message' => $result ? 'Password updated successfully!' : 'Failed to update password.'];
    }

    // ── Settings: Get User by ID ──────────────────────────────────

    public function getUserById($player_id)
    {
        $stmt = $this->conn->prepare("SELECT player_id, username, email, role, total_xp, level, created_at FROM player WHERE player_id = :id");
        $stmt->execute([':id' => $player_id]);
        return $stmt->fetch();
    }

    // Alias for backward compatibility
    public function getById($player_id)
    {
        return $this->getUserById($player_id);
    }
}
