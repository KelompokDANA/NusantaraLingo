<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // Tampilkan halaman login
    public function login() {
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['user_id'])) {
            header("Location: /public/index.php?controller=dashboard&action=index");
            exit();
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }

    // Proses login
    public function authenticate() {
        if ($_POST) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // DEBUG: Log attempt
            error_log("=== LOGIN ATTEMPT ===");
            error_log("Username/Email: " . $username);
            error_log("Password length: " . strlen($password));
            
            if ($this->user->login($username, $password)) {
                // DEBUG: Login success
                error_log("✅ LOGIN SUCCESS for: " . $username);
                
                // Set session
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['full_name'] = $this->user->full_name;
                $_SESSION['avatar'] = $this->user->avatar;
                $_SESSION['level'] = $this->user->level;
                $_SESSION['total_points'] = $this->user->total_points;

                header("Location: /public/index.php?controller=dashboard&action=index&message=login_success");
                exit();
            } else {
                // DEBUG: Login failed
                error_log("❌ LOGIN FAILED for: " . $username);
                
                $error = "Username/email atau password salah!";
                include __DIR__ . '/../views/auth/login.php';
            }
        }
    }

    // Tampilkan halaman register
    public function register() {
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['user_id'])) {
            header("Location: /public/index.php?controller=dashboard&action=index");
            exit();
        }
        
        include __DIR__ . '/../views/auth/register.php';
    }

    // Proses register - DIPERBAIKI
    public function store() {
        if ($_POST) {
            // Trim semua input
            $this->user->username = trim($_POST['username'] ?? '');
            $this->user->email = trim($_POST['email'] ?? '');
            $this->user->password = $_POST['password'] ?? '';
            $this->user->full_name = trim($_POST['full_name'] ?? '');

            // DEBUG: Log registration attempt
            error_log("=== REGISTRATION ATTEMPT ===");
            error_log("Username: " . $this->user->username);
            error_log("Email: " . $this->user->email);
            error_log("Full Name: " . $this->user->full_name);
            error_log("Password length: " . strlen($this->user->password));

            // Validasi
            $errors = $this->user->validate('register');
            error_log("Validation errors: " . json_encode($errors));

            if (empty($errors)) {
                // Coba registrasi
                $register_result = $this->user->register();
                error_log("Registration result: " . ($register_result ? "SUCCESS" : "FAILED"));
                
                if ($register_result) {
                    // DEBUG: Registration success
                    error_log("✅ REGISTRATION SUCCESS for: " . $this->user->username);
                    
                    header("Location: /public/index.php?controller=auth&action=login&message=register_success");
                    exit();
                } else {
                    error_log("❌ REGISTRATION FAILED - Database error");
                    $errors[] = "Gagal mendaftar. Silakan coba lagi.";
                }
            }

            // Jika ada error, tampilkan form lagi dengan error
            include __DIR__ . '/../views/auth/register.php';
        }
    }

    // Logout
    public function logout() {
        session_destroy();
        header("Location: /public/index.php?controller=auth&action=login&message=logout_success");
        exit();
    }

    // Check if user is logged in
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /public/index.php?controller=auth&action=login&error=login_required");
            exit();
        }
    }
}
?>
