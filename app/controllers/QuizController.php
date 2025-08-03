<?php
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/User.php'; // Include User model for session update

class QuizController {
    private $quiz;

    public function __construct() {
        // Require login untuk semua method di controller ini
        AuthController::requireLogin();
        
        $this->quiz = new Quiz();
    }

    // Tampilkan daftar kuis
    public function index() {
        $stmt = $this->quiz->readAll();
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../views/quiz/index.php';
    }

    // Tampilkan detail kuis sebelum mulai
    public function show() {
        if (isset($_GET['id'])) {
            $this->quiz->id = $_GET['id'];
            $quiz_data = $this->quiz->readOne();
            
            if ($quiz_data) {
                include __DIR__ . '/../views/quiz/show.php';
            } else {
                header("Location: /public/index.php?controller=quiz&action=index&error=quiz_not_found");
                exit();
            }
        }
    }

    // Mulai kuis (tampilkan soal-soal)
    public function start() {
        if (isset($_GET['id'])) {
            $this->quiz->id = $_GET['id'];
            $quiz_data = $this->quiz->readOne();
            
            if ($quiz_data) {
                // Get quiz questions
                $questions_stmt = $this->quiz->getQuestions();
                $questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($questions)) {
                    header("Location: /public/index.php?controller=quiz&action=index&error=no_questions");
                    exit();
                }
                
                // Shuffle questions for randomness
                shuffle($questions);
                
                // Store quiz start time in session
                $_SESSION['quiz_start_time'] = time();
                $_SESSION['quiz_id'] = $this->quiz->id;
                
                include __DIR__ . '/../views/quiz/start.php';
            } else {
                header("Location: /public/index.php?controller=quiz&action=index&error=quiz_not_found");
                exit();
            }
        }
    }

    // Submit kuis dan tampilkan hasil - OPTIMIZED
    public function submit() {
        // Set timeout untuk operasi database
        ini_set('max_execution_time', 30);
        
        if ($_POST && isset($_SESSION['quiz_id'])) {
            $quiz_id = $_SESSION['quiz_id'];
            $answers = $_POST['answers'] ?? [];
            $start_time = $_SESSION['quiz_start_time'] ?? time();
            $time_taken = time() - $start_time;

            // Validasi basic sebelum proses database
            if (empty($answers)) {
                header("Location: /public/index.php?controller=quiz&action=index&error=" . urlencode("Tidak ada jawaban yang dikirim"));
                exit();
            }

            $this->quiz->id = $quiz_id;
            if (!$this->quiz->readOne()) {
                header("Location: /public/index.php?controller=quiz&action=index&error=quiz_not_found");
                exit();
            }

            // Submit quiz attempt dengan timeout
            $result = $this->quiz->submitAttempt($_SESSION['user_id'], $answers, $time_taken);

            if ($result['success']) {
                // Clear session data IMMEDIATELY
                unset($_SESSION['quiz_start_time']);
                unset($_SESSION['quiz_id']);

                // Update session points, level, and experience by re-reading user data
                $user_model = new User(); 
                $user_model->id = $_SESSION['user_id'];
                $user_model->readOne(); // Re-read user data to get updated level/exp/points
                $_SESSION['total_points'] = $user_model->total_points;
                $_SESSION['level'] = $user_model->level;
                $_SESSION['experience'] = $user_model->experience;


                // Redirect langsung tanpa delay
                header("Location: /public/index.php?controller=quiz&action=result&attempt_id=" . $result['attempt_id']);
                exit();
            } else {
                $error = $result['message'] ?? 'Terjadi kesalahan saat menyimpan jawaban';
                header("Location: /public/index.php?controller=quiz&action=index&error=" . urlencode($error));
                exit();
            }
        } else {
            header("Location: /public/index.php?controller=quiz&action=index");
            exit();
        }
    }

    // Tampilkan hasil kuis
    public function result() {
        if (isset($_GET['attempt_id'])) {
            $attempt_id = $_GET['attempt_id'];
            
            // Get quiz attempt details
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "SELECT qa.*, q.title as quiz_title, q.description, l.name as language_name, c.name as category_name
                      FROM quiz_attempts qa
                      LEFT JOIN quizzes q ON qa.quiz_id = q.id
                      LEFT JOIN languages l ON q.language_id = l.id
                      LEFT JOIN categories c ON q.category_id = c.id
                      WHERE qa.id = :attempt_id AND qa.user_id = :user_id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":attempt_id", $attempt_id);
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->execute();
            
            $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attempt) {
                // Check for new achievements
                $new_achievements = $this->getNewAchievements();
                error_log("New achievements for user " . ($_SESSION['user_id'] ?? 'N/A') . " after quiz: " . json_encode($new_achievements)); // Debug log
                
                include __DIR__ . '/../views/quiz/result.php';
            } else {
                header("Location: /public/index.php?controller=quiz&action=index&error=result_not_found");
                exit();
            }
        } else {
            header("Location: /public/index.php?controller=quiz&action=index");
            exit();
        }
    }

    // Get user's quiz history
    public function history() {
        $stmt = $this->quiz->getUserHistory($_SESSION['user_id'], 50);
        $quiz_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Quiz History for user " . ($_SESSION['user_id'] ?? 'N/A') . ": " . json_encode($quiz_history)); // Debug log
        
        include __DIR__ . '/../views/quiz/history.php';
    }

    private function getNewAchievements() {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Get achievements earned in the last 5 minutes (recently earned)
        $query = "SELECT a.name, a.description, a.icon, a.badge_color, a.points_reward
                  FROM user_achievements ua
                  LEFT JOIN achievements a ON ua.achievement_id = a.id
                  WHERE ua.user_id = :user_id 
                  AND ua.earned_at > datetime('now', '-5 minutes')
                  ORDER BY ua.earned_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
