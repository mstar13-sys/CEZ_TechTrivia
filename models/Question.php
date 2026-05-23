<?php
// ── Question Model ─────────────────────────────────────────────
// Handles all question-related database operations

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../core/SoftDeleteStore.php';

class Question extends Model {
    protected $table = 'question';
    private $softDeletes;

    public function __construct() {
        parent::__construct();
        $this->softDeletes = new SoftDeleteStore();
    }

    protected function getPrimaryKey() {
        return 'question_id';
    }

    /**
     * Get all questions with choice count
     */
    public function getAllWithChoiceCount() {
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

    /**
     * Get a question with its choices
     */
    public function getWithChoices($question_id) {
        if ($this->softDeletes->isDeleted('questions', $question_id)) {
            return null;
        }

        $question = $this->find($question_id);
        if (!$question) return null;

        $stmt = $this->conn->prepare("SELECT * FROM choice WHERE question_id = :id");
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
                "INSERT INTO question (question_text, difficulty, category) VALUES (:text, :diff, :cat)"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category]);
            $question_id = $this->lastInsertId();

            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
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
                "UPDATE question SET question_text=:text, difficulty=:diff, category=:cat WHERE question_id=:id"
            );
            $stmt->execute([':text' => $text, ':diff' => $difficulty, ':cat' => $category, ':id' => $id]);

            $this->conn->prepare("DELETE FROM choice WHERE question_id = :id")->execute([':id' => $id]);

            foreach ($choices as $i => $choice) {
                $stmt2 = $this->conn->prepare(
                    "INSERT INTO choice (question_id, choice_text, is_correct) VALUES (:qid, :text, :correct)"
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

    public function delete($id, $reason = '') {
        $stmt = $this->conn->prepare("SELECT question_id FROM question WHERE question_id = :id");
        $stmt->execute([':id' => (int)$id]);
        if (!$stmt->fetch()) {
            return false;
        }

        return $this->softDeletes->markDeleted('questions', (int)$id, $reason);
    }

    public function restore($id) {
        return $this->softDeletes->restore('questions', (int)$id);
    }

    public function getDeletedWithChoiceCount() {
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

    /**
     * Get all distinct categories
     */
    public function getCategories() {
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

    /**
     * Get difficulties available for a category
     */
    public function getDifficultiesByCategory($category) {
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

    /**
     * Count questions by category and difficulty
     */
    public function countByCategoryAndDifficulty($category, $difficulty) {
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
}
