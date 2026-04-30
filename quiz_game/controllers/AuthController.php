<?php
require_once __DIR__ . "/../models/user.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    private function redirectWithError($error, $redirectTo = "../views/register.php") {
        $_SESSION['flash_error'] = $error;
        header("Location: " . $redirectTo);
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/register.php");
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->redirectWithError("Invalid request. Please try again.");
        }

        $username        = $_POST['username'] ?? '';
        $email           = $_POST['email'] ?? '';
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->redirectWithError("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError("Please enter a valid email address.");
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->redirectWithError("Username must be 3-20 characters.");
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->redirectWithError("Username can only contain letters, numbers, and underscores.");
        }

        if (strlen($password) < 6) {
            $this->redirectWithError("Password must be at least 6 characters.");
        }

        if ($password !== $confirmPassword) {
            $this->redirectWithError("Passwords do not match.");
        }

        if ($this->user->userExists($username)) {
            $this->redirectWithError("Username already exists. Please choose another.");
        }

        if ($this->user->emailExists($email)) {
            $this->redirectWithError("Email already registered. Please use another email.");
        }

        if ($this->user->register($username, $email, $password)) {
            $_SESSION['flash_registered'] = true;
            header("Location: ../../index.php");
            exit;
        } else {
            $this->redirectWithError("Registration failed. Please try again.");
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../index.php");
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->redirectWithError("Invalid request. Please try again.", "../../index.php");
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->redirectWithError("All fields are required.", "../../index.php");
        }

        $user = $this->user->login($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['player_id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['logged_in']  = true;

            header("Location: ../views/dashboard.php");
            exit;
        } else {
            $this->redirectWithError("Invalid username or password.", "../../index.php");
        }
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../index.php");
            exit;
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        header("Location: ../../index.php");
        exit;
    }
}

$auth = new AuthController();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $auth->register();
        break;
    case 'login':
        $auth->login();
        break;
    case 'logout':
        $auth->logout();
        break;
    default:
        header("Location: ../../index.php");
        exit;
}
