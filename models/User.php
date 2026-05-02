<?php
require_once __DIR__ . '/../core/Database.php';

// ── User Model ────────────────────────────────────────────────
// Handles all database operations for players and questions.
class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // ── Auth ──────────────────────────────────────────────────

    public function register($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->conn->prepare(
            "INSERT INTO Player (username, email, password) VALUES (:username, :email, :password)"
        );
        return $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hash]);
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM Player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function userExists($username) {
        $stmt = $this->conn->prepare("SELECT player_id FROM Player WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT player_id FROM Player WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    // ── Admin: Players ────────────────────────────────────────

    public function getAllPlayers() {
        $stmt = $this->conn->prepare(
            "SELECT player_id, username, email, role, total_xp, level, created_at
             FROM Player ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deletePlayer($id) {
        $stmt = $this->conn->prepare("DELETE FROM Player WHERE player_id = :id AND role != 'admin'");
        return $stmt->execute([':id' => (int)$id]);
    }

    // ── Admin: Stats ──────────────────────────────────────────

    public function getStats() {
        return [
            'total_players'   => $this->conn->query("SELECT COUNT(*) FROM Player WHERE role='player'")->fetchColumn(),
            'total_sessions'  => $this->conn->query("SELECT COUNT(*) FROM GameSession")->fetchColumn(),
            'total_questions' => $this->conn->query("SELECT COUNT(*) FROM Question")->fetchColumn(),
            'avg_score'       => $this->conn->query("SELECT COALESCE(AVG(total_score),0) FROM GameSession")->fetchColumn(),
        ];
    }

    // ── Admin: Questions ──────────────────────────────────────

    public function getAllQuestions() {
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

    public function getQuestionWithChoices($question_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Question WHERE question_id = :id");
        $stmt->execute([':id' => $question_id]);
        $question = $stmt->fetch();
        if (!$question) return null;

        $stmt2 = $this->conn->prepare("SELECT * FROM Choice WHERE question_id = :id");
        $stmt2->execute([':id' => $question_id]);
        $question['choices'] = $stmt2->fetchAll();
        return $question;
    }

    public function addQuestion($text, $difficulty, $category, $choices, $correct_index) {
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

    public function updateQuestion($id, $text, $difficulty, $category, $choices, $correct_index) {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "UPDATE Question SET question_text=:text, difficulty=:diff, category=:cat WHERE question_id=:id"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category, ':id' => $id]);

            // Delete old choices and re-insert
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

    public function deleteQuestion($id) {
        $stmt = $this->conn->prepare("DELETE FROM Question WHERE question_id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    // ── Leaderboard ───────────────────────────────────────────

    public function getLeaderboard($limit = 10) {
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

    // ── Quiz / Game Session ───────────────────────────────────

    public function getRandomQuestions($limit = 10) {
        $stmt = $this->conn->prepare(
            "SELECT q.question_id, q.question_text, q.difficulty, q.category,
                    c.choice_id, c.choice_text, c.is_correct
             FROM Question q
             JOIN Choice c ON q.question_id = c.question_id
             ORDER BY RAND()"
        );
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

        // Shuffle and limit
        $questions = array_values($questions);
        shuffle($questions);
        return array_slice($questions, 0, $limit);
    }

    public function saveGameSession($player_id, $score, $xp) {
        $stmt = $this->conn->prepare(
            "INSERT INTO GameSession (player_id, total_score, xp_earned) VALUES (:pid, :score, :xp)"
        );
        $stmt->execute([':pid' => $player_id, ':score' => $score, ':xp' => $xp]);
        $session_id = $this->conn->lastInsertId();

        // Update player XP
        $stmt2 = $this->conn->prepare(
            "UPDATE Player SET total_xp = total_xp + :xp,
             level = GREATEST(1, FLOOR((total_xp + :xp) / 100) + 1)
             WHERE player_id = :pid"
        );
        $stmt2->execute([':xp' => $xp, ':pid' => $player_id]);

        return $session_id;
    }

    public function getPlayerStats($player_id) {
        $stmt = $this->conn->prepare(
            "SELECT p.username, p.total_xp, p.level,
                    COUNT(gs.session_id) AS games_played,
                    COALESCE(AVG(gs.total_score), 0) AS avg_score,
                    COALESCE(MAX(gs.total_score), 0) AS best_score
             FROM Player p
             LEFT JOIN GameSession gs ON p.player_id = gs.player_id
             WHERE p.player_id = :pid
             GROUP BY p.player_id"
        );
        $stmt->execute([':pid' => $player_id]);
        return $stmt->fetch();
    }
}
