<?php
require_once __DIR__ . '/../../config/database.php';

class Quiz {
    private $conn;
    private $table_name = "quizzes";

    public $id;
    public $title;
    public $description;
    public $language_id;
    public $category_id;
    public $quiz_type;
    public $difficulty_level;
    public $total_questions;
    public $time_limit;
    public $points_per_question;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all quizzes with language and category info
    public function readAll() {
        $query = "SELECT q.*, l.name as language_name, l.region, c.name as category_name, c.icon as category_icon
                  FROM " . $this->table_name . " q
                  LEFT JOIN languages l ON q.language_id = l.id
                  LEFT JOIN categories c ON q.category_id = c.id
                  WHERE q.is_active = 1
                  ORDER BY q.difficulty_level, q.title";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get quiz by ID with details
    public function readOne() {
        $query = "SELECT q.*, l.name as language_name, l.region, c.name as category_name, c.icon as category_icon
                  FROM " . $this->table_name . " q
                  LEFT JOIN languages l ON q.language_id = l.id
                  LEFT JOIN categories c ON q.category_id = c.id
                  WHERE q.id = :id AND q.is_active = 1
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->language_id = $row['language_id'];
            $this->category_id = $row['category_id'];
            $this->quiz_type = $row['quiz_type'];
            $this->difficulty_level = $row['difficulty_level'];
            $this->total_questions = $row['total_questions'];
            $this->time_limit = $row['time_limit'];
            $this->points_per_question = $row['points_per_question'];
            return $row;
        }
        return false;
    }

    // Get quiz questions
    public function getQuestions() {
        $query = "SELECT qq.*, w.indonesian_word, w.local_word, w.pronunciation, w.usage_example
                  FROM quiz_questions qq
                  LEFT JOIN words w ON qq.word_id = w.id
                  WHERE qq.quiz_id = :quiz_id
                  ORDER BY qq.order_index";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quiz_id", $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Submit quiz attempt - OPTIMIZED
    public function submitAttempt($user_id, $answers, $time_taken) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
            
            // Get quiz questions dengan single query
            $questions_stmt = $this->getQuestions();
            $questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($questions)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tidak ada soal ditemukan'];
            }

            $correct_answers = 0;
            $total_questions = count($questions);
            $score = 0;

            // Check answers - optimized loop
            foreach ($questions as $question) {
                $user_answer = $answers[$question['id']] ?? '';
                if (!empty($user_answer) && strtolower(trim($user_answer)) === strtolower(trim($question['correct_answer']))) {
                    $correct_answers++;
                    $score += $question['points'];
                }
            }

            $percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100, 2) : 0;

            // Single INSERT untuk quiz attempt
            $query = "INSERT INTO quiz_attempts 
                      (user_id, quiz_id, score, total_questions, correct_answers, time_taken, percentage, status, completed_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$user_id, $this->id, $score, $total_questions, $correct_answers, $time_taken, $percentage]);

            if (!$result) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Gagal menyimpan hasil kuis'];
            }

            $attempt_id = $this->conn->lastInsertId();

            // Update user points dengan single query
            $update_user_query = "UPDATE users SET 
                                 total_points = total_points + ?, 
                                 experience = experience + ?,
                                 level = CASE WHEN (experience + ?) >= (level * 100) THEN level + 1 ELSE level END
                                 WHERE id = ?";
            $stmt = $this->conn->prepare($update_user_query);
            $stmt->execute([$score, $score, $score, $user_id]);

            // Update user progress - simplified
            $this->updateUserProgressOptimized($user_id, $score, $percentage);

            // Check achievements - simplified (run in background)
            $this->checkAchievementsOptimized($user_id, $percentage, $total_questions);

            $this->conn->commit();
            
            return [
                'success' => true,
                'attempt_id' => $attempt_id,
                'score' => $score,
                'correct_answers' => $correct_answers,
                'total_questions' => $total_questions,
                'percentage' => $percentage
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Quiz submit error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }

    // Optimized user progress update
    private function updateUserProgressOptimized($user_id, $score, $percentage) {
        // Get current language_id and category_id from the quiz object
        $language_id = $this->language_id;
        $category_id = $this->category_id;

        $query = "INSERT INTO user_progress 
                  (user_id, language_id, category_id, total_score, best_score, quizzes_completed, completion_percentage, last_activity) 
                  VALUES (?, ?, ?, ?, ?, 1, ?, CURRENT_TIMESTAMP)
                  ON CONFLICT(user_id, language_id, category_id) DO UPDATE SET
                  total_score = total_score + excluded.total_score,
                  best_score = MAX(user_progress.best_score, excluded.best_score),
                  quizzes_completed = user_progress.quizzes_completed + 1,
                  completion_percentage = (user_progress.completion_percentage * user_progress.quizzes_completed + excluded.completion_percentage) / (user_progress.quizzes_completed + 1),
                  last_activity = CURRENT_TIMESTAMP";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $language_id, $category_id, $score, $score, $percentage]);
        } catch (Exception $e) {
            error_log("Error updating user progress: " . $e->getMessage());
        }
    }

    // Simplified achievement checking
    private function checkAchievementsOptimized($user_id, $percentage, $total_questions) {
        // Get user's current quiz count
        $stmt_quiz_count = $this->conn->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ?");
        $stmt_quiz_count->execute([$user_id]);
        $user_quiz_count = $stmt_quiz_count->fetchColumn();

        // Get user's current language count
        $stmt_lang_count = $this->conn->prepare("SELECT COUNT(DISTINCT language_id) FROM user_progress WHERE user_id = ?");
        $stmt_lang_count->execute([$user_id]);
        $user_language_count = $stmt_lang_count->fetchColumn();

        // Get user's streak days (assuming it's updated elsewhere or from session)
        // For this example, we'll fetch it from the user table
        $stmt_streak = $this->conn->prepare("SELECT streak_days FROM users WHERE id = ?");
        $stmt_streak->execute([$user_id]);
        $user_streak_days = $stmt_streak->fetchColumn();

        $achievements_to_check = [];
        
        // Define achievement requirements (matching seedAchievements in database.php)
        $all_achievements_query = $this->conn->query("SELECT id, requirement_type, requirement_value FROM achievements WHERE is_active = 1");
        $all_achievements = $all_achievements_query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_achievements as $achievement) {
            $earned = false;
            switch ($achievement['requirement_type']) {
                case 'first_quiz':
                    if ($user_quiz_count >= $achievement['requirement_value']) {
                        $earned = true;
                    }
                    break;
                case 'quiz_count':
                    if ($user_quiz_count >= $achievement['requirement_value']) {
                        $earned = true;
                    }
                    break;
                case 'perfect_score':
                    if ($percentage == 100 && $total_questions > 0) { // Only award if it was a perfect score
                        $earned = true;
                    }
                    break;
                case 'language_count':
                    if ($user_language_count >= $achievement['requirement_value']) {
                        $earned = true;
                    }
                    break;
                case 'streak_days':
                    if ($user_streak_days >= $achievement['requirement_value']) {
                        $earned = true;
                    }
                    break;
            }

            if ($earned) {
                $achievements_to_check[] = $achievement['id'];
            }
        }
        
        // Award achievements in single query (INSERT OR IGNORE to prevent duplicates)
        if (!empty($achievements_to_check)) {
            $insert_stmt = $this->conn->prepare("INSERT OR IGNORE INTO user_achievements (user_id, achievement_id, earned_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
            foreach ($achievements_to_check as $achievement_id) {
                $insert_stmt->execute([$user_id, $achievement_id]);
            }
            error_log("Awarded achievements for user " . $user_id . ": " . json_encode($achievements_to_check));
        }
    }

    // Get user's quiz history
    public function getUserHistory($user_id, $limit = 10) {
        $query = "SELECT qa.*, q.title as quiz_title, l.name as language_name, c.name as category_name
                  FROM quiz_attempts qa
                  LEFT JOIN quizzes q ON qa.quiz_id = q.id
                  LEFT JOIN languages l ON q.language_id = l.id
                  LEFT JOIN categories c ON q.category_id = c.id
                  WHERE qa.user_id = :user_id
                  ORDER BY qa.completed_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}

// Include User model for points update
require_once __DIR__ . '/User.php';
?>
