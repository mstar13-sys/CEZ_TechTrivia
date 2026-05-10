<?php
// ── Question Model ─────────────────────────────────────────────
// Handles all question-related database operations

require_once __DIR__ . '/Model.php';

class Question extends Model {
    protected $table = 'Question';

    protected function getPrimaryKey() {
        return 'question_id';
    }

    /**
     * Get all questions with choice count
     */
    public function getAllWithChoiceCount() {
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

    /**
     * Get a question with its choices
     */
    public function getWithChoices($question_id) {
        $question = $this->find($question_id);
        if (!$question) return null;

        $stmt = $this->conn->prepare("SELECT * FROM Choice WHERE question_id = :id");
        $stmt->execute([':id' => $question_id]);
        $question['choices'] = $stmt->fetchAll();
        return $question;
    }

    /**
     * Add a new question with choices
     */
    public function add($text, $difficulty, $category, $choices, $correct_index) {
        $this->beginTransaction();
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO Question (question_text, difficulty, category) VALUES (:text, :diff, :cat)"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category]);
            $question_id = $this->lastInsertId();

            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO Choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
                );
                $stmt2->execute([
                    ':qid' => $question_id,
                    ':text' => $choice,
                    ':correct' => ($i == $correct_index ? 1 : 0)
                ]);
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Update a question with choices
     */
    public function update($id, $text, $difficulty, $category, $choices, $correct_index) {
        $this->beginTransaction();
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
                $stmt2->execute([
                    ':qid' => $id,
                    ':text' => $choice,
                    ':correct' => ($i == $correct_index ? 1 : 0)
                ]);
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Get all distinct categories
     */
    public function getCategories() {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT q.category
             FROM Question q
             INNER JOIN Choice c ON q.question_id = c.question_id
             ORDER BY q.category ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get difficulties available for a category
     */
    public function getDifficultiesByCategory($category) {
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

    /**
     * Count questions by category and difficulty
     */
    public function countByCategoryAndDifficulty($category, $difficulty) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT q.question_id)
             FROM Question q
             INNER JOIN Choice c ON q.question_id = c.question_id
             WHERE q.category = :cat AND q.difficulty = :diff"
        );
        $stmt->execute([':cat' => $category, ':diff' => $difficulty]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get filtered questions by category and/or difficulty
     */
    public function getFiltered($category = null, $difficulty = null, $limit = 10) {
        $where = ['1=1'];
        $params = [];

        if ($category !== null && $category !== '') {
            $where[] = "q.category = :cat";
            $params[':cat'] = $category;
        }
        if ($difficulty !== null && $difficulty !== '') {
            $where[] = "q.difficulty = :diff";
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
                    'question_id' => $qid,
                    'question_text' => $row['question_text'],
                    'difficulty' => $row['difficulty'],
                    'category' => $row['category'],
                    'choices' => [],
                ];
            }
            $questions[$qid]['choices'][] = [
                'choice_id' => $row['choice_id'],
                'choice_text' => $row['choice_text'],
                'is_correct' => $row['is_correct'],
            ];
        }

        $questions = array_values($questions);
        shuffle($questions);
        return array_slice($questions, 0, $limit);
    }

    /**
     * Get total count of questions
     */
    public function getTotalCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Question");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
