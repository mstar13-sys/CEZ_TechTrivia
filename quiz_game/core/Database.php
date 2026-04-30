<?php
require_once __DIR__ . "/../config/database.php";

class Database {
    private $conn;

    public function __construct() {
        $config = new Config();
        $this->conn = $config->connect();
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>