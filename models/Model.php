<?php
// ── Base Model Class ───────────────────────────────────────────
// Provides common functionality for all models

require_once __DIR__ . '/../core/Database.php';

abstract class Model {
    protected $conn;
    protected $table;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all records from the table
     */
    public function all($columns = '*') {
        $stmt = $this->conn->prepare("SELECT {$columns} FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a record by ID
     */
    public function find($id, $columns = '*') {
        $stmt = $this->conn->prepare("SELECT {$columns} FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find records by a specific column
     */
    public function findBy($column, $value, $columns = '*') {
        $stmt = $this->conn->prepare("SELECT {$columns} FROM {$this->table} WHERE {$column} = :value");
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single record by a specific column
     */
    public function findOneBy($column, $value, $columns = '*') {
        $stmt = $this->conn->prepare("SELECT {$columns} FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $stmt->execute([':value' => $value]);
        return $stmt->fetch();
    }

    /**
     * Check if a record exists
     */
    public function exists($column, $value) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$column} = :value");
        $stmt->execute([':value' => $value]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Count all records
     */
    public function count() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Delete a record by ID
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Begin a transaction
     */
    protected function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    protected function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback a transaction
     */
    protected function rollback() {
        return $this->conn->rollBack();
    }

    /**
     * Get the primary key column name (override in child classes)
     */
    protected function getPrimaryKey() {
        return 'id';
    }

    /**
     * Get the last insert ID
     */
    protected function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
