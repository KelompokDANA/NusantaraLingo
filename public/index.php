<?php
session_start();

// Auto-seed data jika database kosong
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
$db->seedData();

// Router sederhana untuk aplikasi MVC
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// Default redirect ke dashboard jika sudah login
if ($controller == 'auth' && $action == 'login' && isset($_SESSION['user_id'])) {
    $controller = 'dashboard';
    $action = 'index';
}

// Routing
switch($controller) {
    case 'auth':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        
        switch($action) {
            case 'login':
                $authController->login();
                break;
            case 'authenticate':
                $authController->authenticate();
                break;
            case 'register':
                $authController->register();
                break;
            case 'store':
                $authController->store();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                $authController->login();
                break;
        }
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $dashboardController = new DashboardController();
        
        switch($action) {
            case 'index':
                $dashboardController->index();
                break;
            case 'profile':
                $dashboardController->profile();
                break;
            case 'leaderboard':
                $dashboardController->leaderboard();
                break;
            case 'achievements':
                $dashboardController->achievements();
                break;
            default:
                $dashboardController->index();
                break;
        }
        break;
        
    case 'quiz':
        require_once __DIR__ . '/../app/controllers/QuizController.php';
        $quizController = new QuizController();
        
        switch($action) {
            case 'index':
                $quizController->index();
                break;
            case 'show':
                $quizController->show();
                break;
            case 'start':
                $quizController->start();
                break;
            case 'submit':
                $quizController->submit();
                break;
            case 'result':
                $quizController->result();
                break;
            case 'history':
                $quizController->history();
                break;
            default:
                $quizController->index();
                break;
        }
        break;
        
    default:
        // Default ke auth jika belum login, dashboard jika sudah login
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../app/controllers/DashboardController.php';
            $dashboardController = new DashboardController();
            $dashboardController->index();
        } else {
            require_once __DIR__ . '/../app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->login();
        }
        break;
}
?>
