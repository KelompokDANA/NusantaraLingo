<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $avatar;
    public $total_points;
    public $level;
    public $experience;
    public $streak_days;
    public $last_login;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Register user baru - DIPERBAIKI
    public function register() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (username, email, password, full_name, avatar, total_points, level, experience, streak_days) 
                      VALUES (:username, :email, :password, :full_name, :avatar, :total_points, :level, :experience, :streak_days)";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $clean_username = htmlspecialchars(strip_tags(trim($this->username)));
            $clean_email = htmlspecialchars(strip_tags(trim($this->email)));
            $clean_full_name = htmlspecialchars(strip_tags(trim($this->full_name)));
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            
            // Default values
            $default_avatar = 'default.png';
            $default_points = 0;
            $default_level = 1;
            $default_experience = 0;
            $default_streak = 0;

            // DEBUG: Log what we're inserting
            error_log("Inserting user:");
            error_log("- Username: " . $clean_username);
            error_log("- Email: " . $clean_email);
            error_log("- Full Name: " . $clean_full_name);
            error_log("- Password Hash: " . substr($hashed_password, 0, 20) . "...");

            // Bind values
            $stmt->bindParam(":username", $clean_username);
            $stmt->bindParam(":email", $clean_email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":full_name", $clean_full_name);
            $stmt->bindParam(":avatar", $default_avatar);
            $stmt->bindParam(":total_points", $default_points);
            $stmt->bindParam(":level", $default_level);
            $stmt->bindParam(":experience", $default_experience);
            $stmt->bindParam(":streak_days", $default_streak);

            $result = $stmt->execute();
            
            if ($result) {
                $this->id = $this->conn->lastInsertId();
                error_log("✅ User inserted with ID: " . $this->id);
                
                // Verify the user was actually inserted
                $verify_query = "SELECT id, username, email FROM users WHERE id = :id";
                $verify_stmt = $this->conn->prepare($verify_query);
                $verify_stmt->bindParam(":id", $this->id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($verify_result) {
                    error_log("✅ User verification SUCCESS: " . json_encode($verify_result));
                    return true;
                } else {
                    error_log("❌ User verification FAILED");
                    return false;
                }
            } else {
                error_log("❌ Insert query failed");
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("❌ Registration error: " . $e->getMessage());
            return false;
        }
    }

    // Login user - DIPERBAIKI
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password, full_name, avatar, total_points, level, experience, streak_days 
                      FROM " . $this->table_name . " 
                      WHERE (username = :username OR email = :username) 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $clean_username = trim($username);
            $stmt->bindParam(":username", $clean_username);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // DEBUG: Log query result
            error_log("Database query for: " . $clean_username);
            error_log("User found: " . ($row ? "YES" : "NO"));
            
            if ($row) {
                error_log("Found user data:");
                error_log("- ID: " . $row['id']);
                error_log("- Username: " . $row['username']);
                error_log("- Email: " . $row['email']);
                error_log("- Full Name: " . $row['full_name']);
                error_log("- Password Hash: " . substr($row['password'], 0, 20) . "...");
                
                $password_check = password_verify($password, $row['password']);
                error_log("Password verify result: " . ($password_check ? "SUCCESS" : "FAILED"));
                
                if ($password_check) {
                    // Set object properties
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    $this->email = $row['email'];
                    $this->full_name = $row['full_name'];
                    $this->avatar = $row['avatar'] ?? 'default.png';
                    $this->total_points = $row['total_points'] ?? 0;
                    $this->level = $row['level'] ?? 1;
                    $this->experience = $row['experience'] ?? 0;
                    $this->streak_days = $row['streak_days'] ?? 0;
                    
                    // Update last login
                    $this->updateLastLogin();
                    
                    return true;
                }
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("❌ Login error: " . $e->getMessage());
            return false;
        }
    }

    // Update last login
    private function updateLastLogin() {
        try {
            $query = "UPDATE " . $this->table_name . " SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Warning: Could not update last login: " . $e->getMessage());
        }
    }

    // Get user by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->avatar = $row['avatar'];
            $this->total_points = $row['total_points'];
            $this->level = $row['level'];
            $this->experience = $row['experience'];
            $this->streak_days = $row['streak_days'];
            $this->last_login = $row['last_login'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Get leaderboard - FIXED
    public function getLeaderboard($limit = 10) {
        try {
            $query = "SELECT id, username, full_name, avatar, total_points, level, experience 
                      FROM " . $this->table_name . " 
                      WHERE total_points > 0
                      ORDER BY total_points DESC, level DESC, experience DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            // Debug: Log query result
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Leaderboard query returned " . count($result) . " users");
            
            // If no results, create a new statement and return it
            if (empty($result)) {
                error_log("No users found for leaderboard, checking all users...");
                
                // Check if there are any users at all
                $check_query = "SELECT COUNT(*) as user_count FROM " . $this->table_name;
                $check_stmt = $this->conn->prepare($check_query);
                $check_stmt->execute();
                $user_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['user_count'];
                
                error_log("Total users in database: " . $user_count);
                
                if ($user_count > 0) {
                    // Get all users regardless of points
                    $query = "SELECT id, username, full_name, avatar, total_points, level, experience 
                              FROM " . $this->table_name . " 
                              ORDER BY total_points DESC, level DESC, experience DESC
                              LIMIT :limit";
                    
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Error in getLeaderboard: " . $e->getMessage());
            
            // Return empty result set
            $stmt = $this->conn->prepare("SELECT id, username, full_name, avatar, total_points, level, experience FROM " . $this->table_name . " WHERE 1=0");
            $stmt->execute();
            return $stmt;
        }
    }

    // Update points and experience
    public function updatePoints($points) {
        $new_points = $this->total_points + $points;
        $new_experience = $this->experience + $points;
        $new_level = floor($new_experience / 100) + 1; // Level up every 100 exp

        $query = "UPDATE " . $this->table_name . " 
                  SET total_points = :points, experience = :experience, level = :level 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":points", $new_points);
        $stmt->bindParam(":experience", $new_experience);
        $stmt->bindParam(":level", $new_level);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            $this->total_points = $new_points;
            $this->experience = $new_experience;
            $this->level = $new_level;
            return true;
        }
        return false;
    }

    // Get user statistics
    public function getStatistics() {
        $stats = [];
        
        // Total quizzes completed
        $query = "SELECT COUNT(*) as total_quizzes FROM quiz_attempts WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['total_quizzes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_quizzes'];
        
        // Average score
        $query = "SELECT AVG(percentage) as avg_score FROM quiz_attempts WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['avg_score'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_score'], 1);
        
        // Languages learned
        $query = "SELECT COUNT(DISTINCT language_id) as languages_count FROM user_progress WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['languages_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['languages_count'];
        
        // Achievements count
        $query = "SELECT COUNT(*) as achievements_count FROM user_achievements WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['achievements_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['achievements_count'];
        
        return $stats;
    }

    // Validasi input - DIPERBAIKI
    public function validate($type = 'register') {
        $errors = [];
        
        // Trim inputs
        $this->username = trim($this->username);
        $this->email = trim($this->email);
        $this->full_name = trim($this->full_name);
        
        if(empty($this->username)) {
            $errors[] = "Username tidak boleh kosong";
        } elseif(strlen($this->username) < 3) {
            $errors[] = "Username minimal 3 karakter";
        } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $this->username)) {
            $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore";
        } elseif($this->isUsernameExists() && $type == 'register') {
            $errors[] = "Username sudah digunakan";
        }
        
        if(empty($this->email)) {
            $errors[] = "Email tidak boleh kosong";
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        } elseif($this->isEmailExists() && $type == 'register') {
            $errors[] = "Email sudah digunakan";
        }
        
        if($type == 'register') {
            if(empty($this->full_name)) {
                $errors[] = "Nama lengkap tidak boleh kosong";
            } elseif(strlen($this->full_name) < 2) {
                $errors[] = "Nama lengkap minimal 2 karakter";
            }
            
            if(empty($this->password)) {
                $errors[] = "Password tidak boleh kosong";
            } elseif(strlen($this->password) < 6) {
                $errors[] = "Password minimal 6 karakter";
            }
        }
        
        return $errors;
    }

    private function isUsernameExists() {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $clean_username = trim($this->username);
            $stmt->bindParam(":username", $clean_username);
            $stmt->execute();
            $exists = $stmt->rowCount() > 0;
            error_log("Username '" . $clean_username . "' exists: " . ($exists ? "YES" : "NO"));
            return $exists;
        } catch (PDOException $e) {
            error_log("Error checking username: " . $e->getMessage());
            return false;
        }
    }

    private function isEmailExists() {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $clean_email = trim($this->email);
            $stmt->bindParam(":email", $clean_email);
            $stmt->execute();
            $exists = $stmt->rowCount() > 0;
            error_log("Email '" . $clean_email . "' exists: " . ($exists ? "YES" : "NO"));
            return $exists;
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }
}
?>
