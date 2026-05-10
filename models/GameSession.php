<?php
// ── GameSession Model ───────────────────────────────────────────
// Handles all game session-related database operations

require_once __DIR__ . '/Model.php';

class GameSession extends Model {
    protected $table = 'GameSession';

    protected function getPrimaryKey() {
        return 'session_id';
    }

    /**
     * Create a new game session
     */
    public function create($player_id, $score, $xp, $category = null, $difficulty = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO GameSession (player_id, total_score, xp_earned, category, difficulty)
             VALUES (:pid, :score, :xp, :cat, :diff)"
        );
        $result = $stmt->execute([
            ':pid' => $player_id,
            ':score' => $score,
            ':xp' => $xp,
            ':cat' => $category,
            ':diff' => $difficulty,
        ]);

        if ($result) {
            $session_id = $this->lastInsertId();
            $this->updatePlayerXP($player_id, $xp);
            return $session_id;
        }
        return false;
    }

    /**
     * Update player XP and level
     */
    private function updatePlayerXP($player_id, $xp) {
        $stmt = $this->conn->prepare(
            "UPDATE Player
             SET total_xp = total_xp + :xp,
                 level = GREATEST(1, FLOOR((total_xp + :xp) / 100) + 1)
             WHERE player_id = :pid"
        );
        $stmt->execute([':xp' => $xp, ':pid' => $player_id]);
    }

    /**
     * Get total count of game sessions
     */
    public function getTotalCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM GameSession");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get average score across all sessions
     */
    public function getAverageScore() {
        $stmt = $this->conn->prepare("SELECT COALESCE(AVG(total_score), 0) FROM GameSession");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Get sessions by player
     */
    public function getByPlayer($player_id, $limit = 10) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM GameSession
             WHERE player_id = :pid
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':pid', $player_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get distinct categories from sessions
     */
    public function getCategories() {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT category FROM GameSession
             WHERE category IS NOT NULL AND category != ''
             ORDER BY category ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get player stats from sessions
     */
    public function getPlayerStats($player_id) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(session_id) AS games_played,
                    COALESCE(AVG(total_score), 0) AS avg_score,
                    COALESCE(MAX(total_score), 0) AS best_score,
                    COALESCE(SUM(xp_earned), 0) AS total_xp_earned
             FROM GameSession
             WHERE player_id = :pid"
        );
        $stmt->execute([':pid' => $player_id]);
        return $stmt->fetch();
    }
}
