<?php
require_once "../core/Database.php";

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // SANITIZE
    private function clean($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    public function register($username, $email, $password) {
        $username = $this->clean($username);
        $email = $this->clean($email);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Player(username, email, password) VALUES(:username, :email, :password)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":username"=>$username,
            ":email"=>$email,
            ":password"=>$password
        ]);
    }

    public function login($username, $password) {
        $username = $this->clean($username);

        $sql = "SELECT * FROM Player WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":username"=>$username]);

        if($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(password_verify($password,$user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function userExists($username) {
        $sql = "SELECT player_id FROM Player WHERE username=:username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":username"=>$username]);
        return $stmt->rowCount() > 0;
    }

    public function emailExists($email) {
        $sql = "SELECT player_id FROM Player WHERE email=:email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":email"=>$email]);
        return $stmt->rowCount() > 0;
    }
}