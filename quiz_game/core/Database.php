<?php
require_once __DIR__ . '/../config/database.php';

// ── Database ──────────────────────────────────────────────────
// Single class that creates and holds the PDO connection.
// Usage: $db = new Database(); $conn = $db->getConnection();
class Database {
    private $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Never expose raw DB errors to the user
            die(json_encode(['error' => 'Database connection failed. Check config/database.php.']));
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
