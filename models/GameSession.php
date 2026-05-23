<?php
// ── GameSession Model ───────────────────────────────────────────
// Handles all game session-related database operations

require_once __DIR__ . '/Model.php';

class GameSession extends Model {
    protected $table = 'gamesession';
    private const XP_PER_LEVEL = 100;

    protected function getPrimaryKey() {
        return 'session_id';
    }

    /**
     * Create a new game session
     */
    public function create($player_id, $score, $xp, $category = null, $difficulty = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO gamesession (player_id, total_score, xp_earned, category, difficulty)
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
        $player_id = (int)$player_id;
        $xp = max(0, (int)$xp);

        $stmt = $this->conn->prepare(
            "UPDATE player
             SET total_xp = GREATEST(0, COALESCE(total_xp, 0) + :xp)
             WHERE player_id = :pid"
        );
        $stmt->execute([':xp' => $xp, ':pid' => $player_id]);

        $stmt = $this->conn->prepare("SELECT total_xp FROM player WHERE player_id = :pid");
        $stmt->execute([':pid' => $player_id]);
        $totalXp = (int)$stmt->fetchColumn();
        $level = max(1, intdiv(max(0, $totalXp), self::XP_PER_LEVEL) + 1);

        $stmt = $this->conn->prepare("UPDATE player SET level = :level WHERE player_id = :pid");
        $stmt->execute([':level' => $level, ':pid' => $player_id]);
    }

    /**
     * Get total count of game sessions
     */
    public function getTotalCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM gamesession");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get average score across all sessions
     */
    public function getAverageScore() {
        $stmt = $this->conn->prepare("SELECT COALESCE(AVG(total_score), 0) FROM gamesession");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Get sessions by player
     */
    public function getByPlayer($player_id, $limit = 10) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM gamesession
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
            "SELECT DISTINCT category FROM gamesession
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
             FROM gamesession
             WHERE player_id = :pid"
        );
        $stmt->execute([':pid' => $player_id]);
        return $stmt->fetch();
    }
}
