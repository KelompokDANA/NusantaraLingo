<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/AuthController.php';

class DashboardController {
    private $user;
    private $quiz;

    public function __construct() {
        // Require login untuk semua method di controller ini
        AuthController::requireLogin();
        
        $this->user = new User();
        $this->quiz = new Quiz();
    }

    // Dashboard utama
    public function index() {
        // Get current user data
        $this->user->id = $_SESSION['user_id'];
        $this->user->readOne();

        // Get user statistics
        $stats = $this->user->getStatistics();

        // Get recent quiz attempts
        $recent_attempts_stmt = $this->quiz->getUserHistory($_SESSION['user_id'], 5);
        $recent_attempts = $recent_attempts_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Dashboard recent attempts for user " . ($_SESSION['user_id'] ?? 'N/A') . ": " . json_encode($recent_attempts)); // Debug log

        // Get available quizzes
        $available_quizzes_stmt = $this->quiz->readAll();
        $available_quizzes = $available_quizzes_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get leaderboard
        $leaderboard_stmt = $this->user->getLeaderboard(5);
        $leaderboard = $leaderboard_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get user achievements
        $achievements = $this->getUserAchievements();
        error_log("Dashboard achievements for user " . ($_SESSION['user_id'] ?? 'N/A') . ": " . json_encode($achievements)); // Debug log


        include __DIR__ . '/../views/dashboard/index.php';
    }

    // Profile page
    public function profile() {
        // Get current user data
        $this->user->id = $_SESSION['user_id'];
        $this->user->readOne();

        // Get detailed statistics
        $stats = $this->user->getStatistics();

        // Get all quiz history
        $quiz_history_stmt = $this->quiz->getUserHistory($_SESSION['user_id'], 20);
        $quiz_history = $quiz_history_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get user achievements
        $achievements = $this->getUserAchievements();

        // Get user progress by language
        $progress = $this->getUserProgress();

        include __DIR__ . '/../views/dashboard/profile.php';
    }

    // Leaderboard page - FIXED
    public function leaderboard() {
        try {
            // Get full leaderboard
            $leaderboard_stmt = $this->user->getLeaderboard(50);
            $leaderboard = $leaderboard_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get current user rank
            $user_rank = $this->getUserRank($_SESSION['user_id']);

            // Debug: Log leaderboard data
            error_log("=== LEADERBOARD DEBUG ===");
            error_log("Leaderboard count: " . count($leaderboard));
            error_log("User rank: " . $user_rank);
            error_log("User ID: " . ($_SESSION['user_id'] ?? 'N/A'));

            include __DIR__ . '/../views/dashboard/leaderboard.php';
        } catch (Exception $e) {
            error_log("Leaderboard error: " . $e->getMessage());
            
            // Fallback: empty leaderboard
            $leaderboard = [];
            $user_rank = null;
            
            include __DIR__ . '/../views/dashboard/leaderboard.php';
        }
    }

    // Achievements page
    public function achievements() {
        // Get all achievements with user status
        $achievements = $this->getAllAchievements();
        error_log("Achievements page data for user " . ($_SESSION['user_id'] ?? 'N/A') . ": " . json_encode($achievements)); // Debug log

        include __DIR__ . '/../views/dashboard/achievements.php';
    }

    private function getUserAchievements() {
        $database = new Database();
        $conn = $database->getConnection();

        $query = "SELECT a.*, ua.earned_at
                  FROM achievements a
                  LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = :user_id
                  WHERE a.is_active = 1
                  ORDER BY ua.earned_at DESC, a.points_reward DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Raw User Achievements from DB for user " . ($_SESSION['user_id'] ?? 'N/A') . ": " . json_encode($result)); // Debug log
        return $result;
    }

    private function getUserProgress() {
        $database = new Database();
        $conn = $database->getConnection();

        $query = "SELECT up.*, l.name as language_name, l.region, c.name as category_name, c.icon
                  FROM user_progress up
                  LEFT JOIN languages l ON up.language_id = l.id
                  LEFT JOIN categories c ON up.category_id = c.id
                  WHERE up.user_id = :user_id
                  ORDER BY up.last_activity DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserRank($user_id) {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Get user's total points first
            $user_query = "SELECT total_points FROM users WHERE id = :user_id";
            $stmt = $conn->prepare($user_query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user_data) {
                return null;
            }

            $user_points = $user_data['total_points'];

            // Count how many users have more points
            $rank_query = "SELECT COUNT(*) + 1 as rank
                          FROM users 
                          WHERE total_points > :user_points";

            $stmt = $conn->prepare($rank_query);
            $stmt->bindParam(":user_points", $user_points);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['rank'];

        } catch (Exception $e) {
            error_log("Error getting user rank: " . $e->getMessage());
            return null;
        }
    }

    private function getAllAchievements() {
        $database = new Database();
        $conn = $database->getConnection();

        $query = "SELECT a.*, 
                         CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as is_earned,
                         ua.earned_at
                  FROM achievements a
                  LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = :user_id
                  WHERE a.is_active = 1
                  ORDER BY is_earned DESC, a.points_reward DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
