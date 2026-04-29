<?php
session_start();
require_once "../models/User.php";

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    private function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    private function redirectWithError($error, $redirectTo = "../views/register.php") {
        header("Location: " . $redirectTo . "?error=" . urlencode($error));
        exit;
    }

    public function register() {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->redirectWithError("Invalid request. Please try again.");
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Server-side validation
        if(empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->redirectWithError("All fields are required.");
        }

        // Email validation
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError("Please enter a valid email address.");
        }

        // Username validation
        if(strlen($username) < 3 || strlen($username) > 20) {
            $this->redirectWithError("Username must be 3-20 characters.");
        }

        if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->redirectWithError("Username can only contain letters, numbers, and underscores.");
        }

        // Password validation
        if(strlen($password) < 6) {
            $this->redirectWithError("Password must be at least 6 characters.");
        }

        // Confirm password
        if($password !== $confirmPassword) {
            $this->redirectWithError("Passwords do not match.");
        }

        // Check if user exists
        if($this->user->userExists($username)) {
            $this->redirectWithError("Username already exists. Please choose another.");
        }

        // Check if email exists
        if($this->user->emailExists($email)) {
            $this->redirectWithError("Email already registered. Please use another email.");
        }

        // Register user
        if($this->user->register($username, $email, $password)) {
            header("Location: ../index.php?registered=1");
            exit;
        } else {
            $this->redirectWithError("Registration failed. Please try again.");
        }
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input
        if(empty($username) || empty($password)) {
            $this->redirectWithError("All fields are required.", "../../index.php");
        }

        $user = $this->user->login($username, $password);

        if($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['player_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            
            header("Location: ../views/dashboard.php");
            exit;
        } else {
            $this->redirectWithError("Invalid username or password.", "../../index.php");
        }
    }

    public function logout() {
        // Clear session
        $_SESSION = array();
        
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$auth = new AuthController();

if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch($action) {
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
            header("Location: ../index.php");
            exit;
    }
}