<?php
require_once __DIR__ . '/../core/Database.php';

// ── User Model ────────────────────────────────────────────────
class User
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // ── Auth ──────────────────────────────────────────────────

    public function register($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "INSERT INTO Player (username, email, password) VALUES (:username, :email, :password)"
        );
        return $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hash]);
    }

    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();
            if (password_verify($password, $user['password'])) return $user;
        }
        return false;
    }

    public function userExists($username)
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM Player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }

    public function resetPasswordByUsernameEmail($username, $email, $new_password)
    {
        $stmt = $this->conn->prepare(
            "SELECT player_id FROM Player WHERE username = :username AND email = :email"
        );
        $stmt->execute([':username' => $username, ':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'No account matches that username and email.'];
        }

        $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "UPDATE Player SET password = :password WHERE player_id = :id"
        );
        $result = $stmt->execute([':password' => $hash, ':id' => $user['player_id']]);

        return [
            'success' => $result,
            'message' => $result ? 'Password changed successfully. You can now sign in.' : 'Failed to change password.',
        ];
    }

    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM Player WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    // ── Admin: Players ────────────────────────────────────────

    public function getAllPlayers()
    {
        $stmt = $this->conn->prepare(
            "SELECT player_id, username, email, role, total_xp, level, created_at
             FROM Player
             WHERE role = 'player'
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deletePlayer($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM Player WHERE player_id = :id AND role != 'admin'");
        return $stmt->execute([':id' => (int)$id]);
    }

    // ── Admin: Stats ──────────────────────────────────────────

    public function getStats()
    {
        return [
            'total_players'   => $this->conn->query("SELECT COUNT(*) FROM Player WHERE role='player'")->fetchColumn(),
            'total_sessions'  => $this->conn->query("SELECT COUNT(*) FROM GameSession")->fetchColumn(),
            'total_questions' => $this->conn->query("SELECT COUNT(*) FROM Question")->fetchColumn(),
            'avg_score'       => $this->conn->query("SELECT COALESCE(AVG(total_score),0) FROM GameSession")->fetchColumn(),
        ];
    }

    // Achievements and ranks

    public function getAllAchievements()
    {
        $stmt = $this->conn->prepare(
            "SELECT achievement_id, title, description, condition_type, condition_value
             FROM Achievement
             ORDER BY condition_type ASC, condition_value ASC, title ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAchievementById($achievement_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT achievement_id, title, description, condition_type, condition_value
             FROM Achievement
             WHERE achievement_id = :id"
        );
        $stmt->execute([':id' => (int)$achievement_id]);
        return $stmt->fetch();
    }

    public function addAchievement($title, $description, $condition_type, $condition_value)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO Achievement (title, description, condition_type, condition_value)
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
            "UPDATE Achievement
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
        $this->conn->beginTransaction();
        try {
            $this->conn->prepare("DELETE FROM PlayerAchievement WHERE achievement_id = :id")
                ->execute([':id' => (int)$achievement_id]);
            $result = $this->conn->prepare("DELETE FROM Achievement WHERE achievement_id = :id")
                ->execute([':id' => (int)$achievement_id]);
            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getPlayerAchievements($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT a.achievement_id, a.title, a.description, a.condition_type, a.condition_value,
                    pa.date_unlocked,
                    CASE WHEN pa.player_achievement_id IS NULL THEN 0 ELSE 1 END AS unlocked
             FROM Achievement a
             LEFT JOIN PlayerAchievement pa
                    ON pa.achievement_id = a.achievement_id
                   AND pa.player_id = :pid
             ORDER BY unlocked DESC, a.condition_type ASC, a.condition_value ASC, a.title ASC"
        );
        $stmt->execute([':pid' => (int)$player_id]);
        return $stmt->fetchAll();
    }

    public function getAchievementSummary($player_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                (SELECT COUNT(*) FROM Achievement) AS total_achievements,
                (SELECT COUNT(*) FROM PlayerAchievement WHERE player_id = :pid) AS unlocked_achievements"
        );
        $stmt->execute([':pid' => (int)$player_id]);
        return $stmt->fetch();
    }

    public function getAllRanks()
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `Rank`
             ORDER BY min_xp ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRankById($rank_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `Rank`
             WHERE rank_id = :id"
        );
        $stmt->execute([':id' => (int)$rank_id]);
        return $stmt->fetch();
    }

    public function addRank($rank_name, $min_xp, $max_xp, $medal)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO `Rank` (rank_name, min_xp, max_xp, medal)
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
            "UPDATE `Rank`
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
        $stmt = $this->conn->prepare("DELETE FROM `Rank` WHERE rank_id = :id");
        return $stmt->execute([':id' => (int)$rank_id]);
    }

    public function getRankForXp($xp)
    {
        $stmt = $this->conn->prepare(
            "SELECT rank_id, rank_name, min_xp, max_xp, medal
             FROM `Rank`
             WHERE :xp_min >= min_xp AND (max_xp IS NULL OR :xp_max <= max_xp)
             ORDER BY min_xp DESC
             LIMIT 1"
        );
        $stmt->execute([':xp_min' => (int)$xp, ':xp_max' => (int)$xp]);
        $rank = $stmt->fetch();
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
             FROM `Rank`
             WHERE min_xp > :xp
             ORDER BY min_xp ASC
             LIMIT 1"
        );
        $stmt->execute([':xp' => (int)$xp]);
        return $stmt->fetch();
    }

    public function syncPlayerAchievements($player_id)
    {
        return $this->unlockQualifiedAchievements((int)$player_id);
    }

    public function syncAllPlayerAchievements()
    {
        $stmt = $this->conn->prepare("SELECT player_id FROM Player WHERE role = 'player'");
        $stmt->execute();
        $totalUnlocked = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $playerId) {
            $totalUnlocked += count($this->syncPlayerAchievements((int)$playerId));
        }
        return $totalUnlocked;
    }

    // ── Admin: Questions ──────────────────────────────────────

    public function getAllQuestions()
    {
        $stmt = $this->conn->prepare(
            "SELECT q.*, COUNT(c.choice_id) AS choice_count
             FROM Question q
             LEFT JOIN Choice c ON q.question_id = c.question_id
             GROUP BY q.question_id
             ORDER BY q.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getQuestionWithChoices($question_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM Question WHERE question_id = :id");
        $stmt->execute([':id' => $question_id]);
        $question = $stmt->fetch();
        if (!$question) return null;

        $stmt2 = $this->conn->prepare("SELECT * FROM Choice WHERE question_id = :id");
        $stmt2->execute([':id' => $question_id]);
        $question['choices'] = $stmt2->fetchAll();
        return $question;
    }

    public function addQuestion($text, $difficulty, $category, $choices, $correct_index)
    {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO Question (question_text, difficulty, category) VALUES (:text, :diff, :cat)"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category]);
            $qid = $this->conn->lastInsertId();
            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO Choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
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
                "UPDATE Question SET question_text=:text, difficulty=:diff, category=:cat WHERE question_id=:id"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category, ':id' => $id]);
            $this->conn->prepare("DELETE FROM Choice WHERE question_id = :id")->execute([':id' => $id]);
            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO Choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
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

    public function deleteQuestion($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM Question WHERE question_id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    // ── Category / Difficulty helpers ─────────────────────────

    /** All distinct categories that have questions with choices */
    public function getAvailableCategories()
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.category
             FROM Question q
             INNER JOIN Choice c ON q.question_id = c.question_id
             ORDER BY q.category ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Difficulties available for a given category */
    public function getAvailableDifficulties($category)
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.difficulty
             FROM Question q
             INNER JOIN Choice c ON q.question_id = c.question_id
             WHERE q.category = :cat
             ORDER BY FIELD(q.difficulty,'easy','medium','hard')"
        );
        $stmt->execute([':cat' => $category]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Count questions available for a category + difficulty combo */
    public function countAvailableQuestions($category, $difficulty)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT q.question_id)
             FROM Question q
             INNER JOIN Choice c ON q.question_id = c.question_id
             WHERE q.category = :cat AND q.difficulty = :diff"
        );
        $stmt->execute([':cat' => $category, ':diff' => $difficulty]);
        return (int)$stmt->fetchColumn();
    }

    // ── Leaderboard ───────────────────────────────────────────

    /** Global leaderboard */
    public function getLeaderboard($limit = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.username, p.total_xp, p.level,
                    COUNT(gs.session_id) AS games_played,
                    COALESCE(MAX(gs.total_score), 0) AS best_score
             FROM Player p
             LEFT JOIN GameSession gs ON p.player_id = gs.player_id
             WHERE p.role = 'player'
             GROUP BY p.player_id
             ORDER BY p.total_xp DESC, best_score DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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
            "SELECT p.username, p.total_xp, p.level,
                    COUNT(gs.session_id)             AS games_played,
                    COALESCE(MAX(gs.total_score), 0) AS best_score,
                    COALESCE(SUM(gs.xp_earned), 0)   AS filter_xp
             FROM Player p
             INNER JOIN GameSession gs ON p.player_id = gs.player_id
             WHERE {$whereSQL}
             GROUP BY p.player_id
             ORDER BY filter_xp DESC, best_score DESC
             LIMIT :limit"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Categories that appear in completed GameSessions */
    public function getLeaderboardCategories()
    {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT category FROM GameSession
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
             FROM Question q
             JOIN Choice c ON q.question_id = c.question_id
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
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO GameSession (player_id, total_score, xp_earned, category, difficulty)
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

            $this->updateDailyStreak((int)$player_id);

            $stmt2 = $this->conn->prepare(
                "UPDATE Player
                 SET total_xp = total_xp + :xp,
                     level    = GREATEST(1, FLOOR((total_xp + :xp) / 100) + 1)
                 WHERE player_id = :pid"
            );
            $stmt2->execute([':xp' => $xp, ':pid' => $player_id]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['session_id' => null, 'unlocked' => []];
        }

        $unlocked = $this->unlockQualifiedAchievements((int)$player_id);

        return [
            'session_id' => $session_id,
            'unlocked' => $unlocked,
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
             FROM Player p
             LEFT JOIN GameSession gs ON p.player_id = gs.player_id
             LEFT JOIN Streak s ON p.player_id = s.player_id
             WHERE p.player_id = :pid
             GROUP BY p.player_id"
        );
        $stmt->execute([':pid' => $player_id]);
        return $stmt->fetch();
    }

    private function updateDailyStreak($player_id)
    {
        $stmt = $this->conn->prepare("SELECT current_streak, max_streak, last_played_date FROM Streak WHERE player_id = :pid");
        $stmt->execute([':pid' => $player_id]);
        $streak = $stmt->fetch();

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if (!$streak) {
            $stmt = $this->conn->prepare(
                "INSERT INTO Streak (player_id, current_streak, max_streak, last_played_date)
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
            "UPDATE Streak
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
                    "INSERT INTO PlayerAchievement (player_id, achievement_id)
                     SELECT :pid_insert, :aid_insert
                     WHERE NOT EXISTS (
                         SELECT 1 FROM PlayerAchievement
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
             FROM GameSession
             WHERE player_id = :pid"
        );
        $stmt->execute([':pid' => $player_id]);
        $sessionStats = $stmt->fetch() ?: [];

        $stmt2 = $this->conn->prepare(
            "SELECT total_xp FROM Player WHERE player_id = :pid"
        );
        $stmt2->execute([':pid' => $player_id]);
        $totalXp = (int)$stmt2->fetchColumn();

        $stmt3 = $this->conn->prepare(
            "SELECT current_streak, max_streak FROM Streak WHERE player_id = :pid"
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
             FROM GameSession
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
            "UPDATE Player SET username = :username WHERE player_id = :id"
        );
        $result = $stmt->execute([':username' => $new_username, ':id' => $player_id]);
        return ['success' => $result, 'message' => $result ? 'Username updated successfully!' : 'Failed to update username.'];
    }

    // ── Settings: Update Password ────────────────────────────────

    public function updatePassword($player_id, $current_password, $new_password)
    {
        $stmt = $this->conn->prepare("SELECT password FROM Player WHERE player_id = :id");
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
            "UPDATE Player SET password = :password WHERE player_id = :id"
        );
        $result = $stmt->execute([':password' => $hash, ':id' => $player_id]);
        return ['success' => $result, 'message' => $result ? 'Password updated successfully!' : 'Failed to update password.'];
    }

    // ── Settings: Get User by ID ──────────────────────────────────

    public function getUserById($player_id)
    {
        $stmt = $this->conn->prepare("SELECT player_id, username, email, role, total_xp, level, created_at FROM Player WHERE player_id = :id");
        $stmt->execute([':id' => $player_id]);
        return $stmt->fetch();
    }

    // Alias for backward compatibility
    public function getById($player_id)
    {
        return $this->getUserById($player_id);
    }
}
