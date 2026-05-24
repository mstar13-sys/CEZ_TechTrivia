<?php
// ── Auth Controller ───────────────────────────────────────────
// Handles login, register, and logout POST actions.
// All redirects use relative paths — no base_url() needed.

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // ── Register ──────────────────────────────────────────────
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to('../views/register.php');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid request. Please try again.');
            redirect_to('../views/register.php');
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email']?? '');
        $password = $_POST['password']?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Server-side validation (client side already checked, but never trust client)
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            set_flash('error', 'All fields are required.');
            redirect_to('../views/register.php');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Please enter a valid email address.');
            redirect_to('../views/register.php');
        }
        if (strlen($username) < 3 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            set_flash('error', 'Username must be 3-20 characters (letters, numbers, underscores).');
            redirect_to('../views/register.php');
        }
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            set_flash('error', 'Password must be 8+ chars with an uppercase letter and a number.');
            redirect_to('../views/register.php');
        }
        if ($password !== $confirmPassword) {
            set_flash('error', 'Passwords do not match.');
            redirect_to('../views/register.php');
        }
        if ($this->user->userExists($username)) {
            set_flash('error', 'Username is already taken.');
            redirect_to('../views/register.php');
        }
        if ($this->user->emailExists($email)) {
            set_flash('error', 'Email is already registered.');
            redirect_to('../views/register.php');
        }

        if ($this->user->register($username, $email, $password)) {
            set_flash('registered', true);
            redirect_to('../login.php');
        } else {
            set_flash('error', 'Registration failed. Please try again.');
            redirect_to('../views/register.php');
        }
    }

    // Login 
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to('../login.php');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid request. Please try again.');
            redirect_to('../login.php');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            set_flash('error', 'Please enter your username and password.');
            redirect_to('../login.php');
        }

        $userData = $this->user->login($username, $password);

        if ($userData) {
            session_regenerate_id(true); // Security: regenerate after login

            $_SESSION['user_id']   = $userData['player_id'];
            $_SESSION['username']  = $userData['username'];
            $_SESSION['role']      = $userData['role'] ?? 'player';
            $_SESSION['total_xp']  = $userData['total_xp'] ?? 0;
            $_SESSION['level']     = $userData['level'] ?? 1;
            $_SESSION['logged_in'] = true;

            if ($_SESSION['role'] === 'admin') {
                redirect_to('../views/admin.php');
            } else {
                redirect_to('../views/dashboard.php');
            }
        } else {
            $deletedAccount = $this->user->getDeletedLoginDetails($username, $password);
            if ($deletedAccount) {
                $meta = $deletedAccount['deleted_meta'] ?? [];
                set_flash('deleted_account', [
                    'username' => $deletedAccount['username'] ?? $username,
                    'reason' => $meta['reason'] ?? 'No reason was recorded.',
                ]);
                redirect_to('../login.php');
            }

            set_flash('error', 'Incorrect username or password.');
            redirect_to('../login.php');
        }
    }

    public function requestRecovery() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to('../index.php');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('recovery_error', 'Invalid request. Please try again.');
            redirect_to('../index.php#recovery');
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $reason = normalize_recovery_reason($_POST['recovery_reason'] ?? '');

        if ($username === '' || $email === '' || $password === '' || $reason === '') {
            set_flash('recovery_error', 'Fill in your username, email, password, and return reason.');
            redirect_to('../index.php#recovery');
        }
        if (strlen($username) < 3 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            set_flash('recovery_error', 'Enter valid account credentials.');
            redirect_to('../index.php#recovery');
        }
        if (strlen($reason) > 500) {
            set_flash('recovery_error', 'Return reason must be 500 characters or fewer.');
            redirect_to('../index.php#recovery');
        }

        $account = $this->user->getDeletedRecoveryAccount($username, $email, $password);
        if (!$account) {
            set_flash('recovery_error', 'No deleted account matched those credentials.');
            redirect_to('../index.php#recovery');
        }

        $notifications = new NotificationStore();
        $meta = $account['deleted_meta'] ?? [];
        $stored = $notifications->add(
            'recovery_request',
            'Account recovery request',
            $account['username'] . ' wants to recover a deleted account.',
            [
                'player_id' => (int)$account['player_id'],
                'username' => $account['username'],
                'email' => $account['email'],
                'deleted_reason' => $meta['reason'] ?? '',
                'return_reason' => $reason,
            ]
        );

        set_flash(
            $stored ? 'recovery_success' : 'recovery_error',
            $stored ? 'Your recovery request was sent to the admin.' : 'Could not save your recovery request. Please try again.'
        );
        redirect_to('../index.php#recovery');
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to('../views/forgot_password.php');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Invalid request. Please try again.');
            redirect_to('../views/forgot_password.php');
        }

        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            set_flash('error', 'All fields are required.');
            redirect_to('../views/forgot_password.php');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Please enter a valid email address.');
            redirect_to('../views/forgot_password.php');
        }
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            set_flash('error', 'Password must be 8+ chars with an uppercase letter and a number.');
            redirect_to('../views/forgot_password.php');
        }
        if ($password !== $confirmPassword) {
            set_flash('error', 'Passwords do not match.');
            redirect_to('../views/forgot_password.php');
        }

        $result = $this->user->resetPasswordByUsernameEmail($username, $email, $password);
        if ($result['success']) {
            set_flash('password_reset', $result['message']);
            redirect_to('../login.php');
        }

        set_flash('error', $result['message']);
        redirect_to('../views/forgot_password.php');
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout() {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            redirect_to('../index.php');
        }

        // Clear the session data
        $_SESSION = [];

        // Expire the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        redirect_to('../index.php');
    }
}

// ── Route the action ──────────────────────────────────────────
$auth   = new AuthController();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register': $auth->register(); break;
    case 'login':    $auth->login();    break;
    case 'request_recovery': $auth->requestRecovery(); break;
    case 'forgot_password': $auth->forgotPassword(); break;
    case 'logout':   $auth->logout();   break;
    case 'check_availability':
        header('Content-Type: application/json');
        $user  = new User();
        $field = $_GET['field'] ?? '';
        $value = trim($_GET['value'] ?? '');
        if (empty($value) || !in_array($field, ['username', 'email'], true)) {
            echo json_encode(['available' => false, 'message' => 'Invalid request']);
            exit;
        }
        if ($field === 'username') {
            $exists = $user->userExists($value);
            echo json_encode([
                'available' => !$exists,
                'message'   => $exists ? 'Username is already taken' : 'Username is available',
            ]);
        } else {
            $exists = $user->emailExists($value);
            echo json_encode([
                'available' => !$exists,
                'message'   => $exists ? 'Email is already registered' : 'Email is available',
            ]);
        }
        exit;
    default:
        redirect_to('../index.php');
}
