<?php
class Database {
    private $db_path;
    private $conn;

    public function __construct() {
        // Path ke file database SQLite
        $this->db_path = __DIR__ . '/../database/nusantaralingo.db';
        
        // Buat folder database jika belum ada
        $db_dir = dirname($this->db_path);
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("sqlite:" . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign key constraints untuk SQLite
            $this->conn->exec("PRAGMA foreign_keys = ON");
            
            // Buat tabel jika belum ada
            $this->createTables();
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    private function createTables() {
        try {
            // Tabel users
            $sql_users = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100) NOT NULL,
                    avatar VARCHAR(255) DEFAULT 'default.png',
                    total_points INTEGER DEFAULT 0,
                    level INTEGER DEFAULT 1,
                    experience INTEGER DEFAULT 0,
                    streak_days INTEGER DEFAULT 0,
                    last_login DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            
            // Tabel languages (bahasa daerah)
            $sql_languages = "
                CREATE TABLE IF NOT EXISTS languages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(50) NOT NULL,
                    region VARCHAR(100) NOT NULL,
                    description TEXT,
                    flag_icon VARCHAR(100),
                    total_words INTEGER DEFAULT 0,
                    difficulty_level VARCHAR(20) DEFAULT 'pemula',
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            
            // Tabel categories (kategori pembelajaran)
            $sql_categories = "
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(50) NOT NULL,
                    description TEXT,
                    icon VARCHAR(50),
                    order_index INTEGER DEFAULT 0,
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            
            // Tabel words (kosakata)
            $sql_words = "
                CREATE TABLE IF NOT EXISTS words (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    language_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL,
                    indonesian_word VARCHAR(100) NOT NULL,
                    local_word VARCHAR(100) NOT NULL,
                    pronunciation VARCHAR(150),
                    audio_file VARCHAR(255),
                    image_file VARCHAR(255),
                    difficulty_level VARCHAR(20) DEFAULT 'pemula',
                    usage_example TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
                )
            ";
            
            // Tabel quizzes
            $sql_quizzes = "
                CREATE TABLE IF NOT EXISTS quizzes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(100) NOT NULL,
                    description TEXT,
                    language_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL,
                    quiz_type VARCHAR(50) DEFAULT 'multiple_choice',
                    difficulty_level VARCHAR(20) DEFAULT 'pemula',
                    total_questions INTEGER DEFAULT 10,
                    time_limit INTEGER DEFAULT 300,
                    points_per_question INTEGER DEFAULT 10,
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
                )
            ";
            
            // Tabel quiz_questions
            $sql_quiz_questions = "
                CREATE TABLE IF NOT EXISTS quiz_questions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    quiz_id INTEGER NOT NULL,
                    word_id INTEGER NOT NULL,
                    question_text TEXT NOT NULL,
                    question_type VARCHAR(50) DEFAULT 'multiple_choice',
                    correct_answer VARCHAR(255) NOT NULL,
                    option_a VARCHAR(255),
                    option_b VARCHAR(255),
                    option_c VARCHAR(255),
                    option_d VARCHAR(255),
                    points INTEGER DEFAULT 10,
                    order_index INTEGER DEFAULT 0,
                    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
                    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
                )
            ";
            
            // Tabel quiz_attempts (riwayat kuis)
            $sql_quiz_attempts = "
                CREATE TABLE IF NOT EXISTS quiz_attempts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    quiz_id INTEGER NOT NULL,
                    score INTEGER DEFAULT 0,
                    total_questions INTEGER DEFAULT 0,
                    correct_answers INTEGER DEFAULT 0,
                    time_taken INTEGER DEFAULT 0,
                    percentage DECIMAL(5,2) DEFAULT 0.00,
                    status VARCHAR(20) DEFAULT 'completed',
                    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
                )
            ";
            
            // Tabel user_progress
            $sql_user_progress = "
                CREATE TABLE IF NOT EXISTS user_progress (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    language_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL,
                    words_learned INTEGER DEFAULT 0,
                    quizzes_completed INTEGER DEFAULT 0,
                    total_score INTEGER DEFAULT 0,
                    best_score INTEGER DEFAULT 0,
                    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
                    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                    UNIQUE(user_id, language_id, category_id)
                )
            ";
            
            // Tabel achievements
            $sql_achievements = "
                CREATE TABLE IF NOT EXISTS achievements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    icon VARCHAR(100),
                    badge_color VARCHAR(20) DEFAULT 'primary',
                    requirement_type VARCHAR(50),
                    requirement_value INTEGER DEFAULT 0,
                    points_reward INTEGER DEFAULT 0,
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            
            // Tabel user_achievements
            $sql_user_achievements = "
                CREATE TABLE IF NOT EXISTS user_achievements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    achievement_id INTEGER NOT NULL,
                    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
                    UNIQUE(user_id, achievement_id)
                )
            ";
            
            // Execute semua SQL
            $this->conn->exec($sql_users);
            $this->conn->exec($sql_languages);
            $this->conn->exec($sql_categories);
            $this->conn->exec($sql_words);
            $this->conn->exec($sql_quizzes);
            $this->conn->exec($sql_quiz_questions);
            $this->conn->exec($sql_quiz_attempts);
            $this->conn->exec($sql_user_progress);
            $this->conn->exec($sql_achievements);
            $this->conn->exec($sql_user_achievements);
            
            // Buat index untuk performa
            $this->createIndexes();
            
        } catch(PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
        }
    }
    
    private function createIndexes() {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
            "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
            "CREATE INDEX IF NOT EXISTS idx_words_language ON words(language_id)",
            "CREATE INDEX IF NOT EXISTS idx_words_category ON words(category_id)",
            "CREATE INDEX IF NOT EXISTS idx_quiz_attempts_user ON quiz_attempts(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_quiz_attempts_quiz ON quiz_attempts(quiz_id)",
            "CREATE INDEX IF NOT EXISTS idx_user_progress_user ON user_progress(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_user_achievements_user ON user_achievements(user_id)"
        ];
        
        foreach ($indexes as $index) {
            $this->conn->exec($index);
        }
    }

    // Method untuk seed data awal - IMPROVED
    public function seedData() {
        try {
            // Cek apakah sudah ada data languages (indikator data awal)
            $stmt = $this->conn->query("SELECT COUNT(*) FROM languages");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                error_log("🌱 Seeding initial data (languages, categories, words, quizzes, achievements)...");
                $this->seedLanguages();
                $this->seedCategories();
                $this->seedWords();
                $this->seedQuizzes();
                $this->seedAchievements();
                error_log("✅ Initial data seeded.");
            }
            
            // Always check and seed demo users if none exist
            $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
            $user_count = $stmt->fetchColumn();
            
            if ($user_count == 0) {
                error_log("👥 Seeding demo users (including attempts and achievements)...");
                $this->seedDemoUsers(); // This calls seedDemoQuizAttempts, seedDemoUserProgress, seedDemoUserAchievements
                error_log("✅ Demo users seeded.");
            } else {
                // If users already exist, ensure demo user points are updated
                error_log("🔄 Updating demo user points...");
                $this->updateDemoUserPoints();
                error_log("✅ Demo user points updated.");

                // And ensure demo quiz attempts, progress, and achievements are present for existing demo users
                // These should use INSERT OR IGNORE to avoid duplicates if they were already added
                error_log("🔄 Seeding/updating demo quiz attempts, progress, and achievements for existing demo users...");
                $this->seedDemoQuizAttempts();
                $this->seedDemoUserProgress();
                $this->seedDemoUserAchievements();
                error_log("✅ Demo quiz attempts, progress, and achievements seeded/updated.");
            }
            
        } catch(PDOException $e) {
            error_log("Error seeding data: " . $e->getMessage());
        }
    }
    
    // NEW: Update demo user points
    private function updateDemoUserPoints() {
        $updates = [
            ['username' => 'maya_smart', 'points' => 1500, 'level' => 5, 'exp' => 1500],
            ['username' => 'sari_cantik', 'points' => 1200, 'level' => 4, 'exp' => 1200],
            ['username' => 'budi123', 'points' => 850, 'level' => 3, 'exp' => 850],
            ['username' => 'joko_gamer', 'points' => 650, 'level' => 2, 'exp' => 650],
            ['username' => 'andi_cool', 'points' => 400, 'level' => 1, 'exp' => 400]
        ];
        
        $stmt = $this->conn->prepare("UPDATE users SET total_points = ?, level = ?, experience = ? WHERE username = ?");
        
        foreach ($updates as $update) {
            $stmt->execute([$update['points'], $update['level'], $update['exp'], $update['username']]);
        }
    }
    
    private function seedLanguages() {
        $languages = [
            ['Bahasa Jawa', 'Jawa Tengah & Jawa Timur', 'Bahasa daerah dengan penutur terbanyak di Indonesia', 'jawa.png', 150, 'pemula'],
            ['Bahasa Sunda', 'Jawa Barat', 'Bahasa daerah kedua terbesar di Indonesia', 'sunda.png', 120, 'pemula'],
            ['Bahasa Batak', 'Sumatera Utara', 'Bahasa daerah dari tanah Batak', 'batak.png', 100, 'menengah'],
            ['Bahasa Minang', 'Sumatera Barat', 'Bahasa daerah dari ranah Minang', 'minang.png', 90, 'menengah'],
            ['Bahasa Bali', 'Bali', 'Bahasa daerah dari pulau dewata', 'bali.png', 80, 'menengah'],
            ['Bahasa Bugis', 'Sulawesi Selatan', 'Bahasa daerah dari tanah Bugis', 'bugis.png', 70, 'mahir']
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO languages (name, region, description, flag_icon, total_words, difficulty_level) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($languages as $lang) {
            $stmt->execute($lang);
        }
    }
    
    private function seedCategories() {
        $categories = [
            ['Keluarga', 'Kata-kata tentang anggota keluarga', 'users', 1],
            ['Makanan', 'Nama-nama makanan dan minuman', 'utensils', 2],
            ['Hewan', 'Nama-nama hewan', 'paw', 3],
            ['Warna', 'Nama-nama warna', 'palette', 4],
            ['Angka', 'Angka dan bilangan', 'calculator', 5],
            ['Alam', 'Benda-benda di alam', 'tree', 6],
            ['Tubuh', 'Bagian-bagian tubuh', 'user', 7],
            ['Waktu', 'Kata-kata tentang waktu', 'clock', 8]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO categories (name, description, icon, order_index) VALUES (?, ?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
    }
    
    private function seedWords() {
        // Sample words untuk Bahasa Jawa
        $jawa_words = [
            // Keluarga (category_id = 1)
            [1, 1, 'Ayah', 'Bapak', 'ba-pak', 'Bapak tindak pasar', 'pemula'],
            [1, 1, 'Ibu', 'Ibu', 'i-bu', 'Ibu masak ing pawon', 'pemula'],
            [1, 1, 'Kakak', 'Mbak', 'mbak', 'Mbak lagi sinau', 'pemula'],
            [1, 1, 'Adik', 'Adhi', 'a-dhi', 'Adhi lagi dolanan', 'pemula'],
            [1, 1, 'Kakek', 'Mbah Kakung', 'mbah ka-kung', 'Mbah Kakung cerita dongeng', 'pemula'],
            
            // Makanan (category_id = 2)
            [1, 2, 'Nasi', 'Sega', 'se-ga', 'Sega putih enak banget', 'pemula'],
            [1, 2, 'Air', 'Banyu', 'ba-nyu', 'Banyu iki resik', 'pemula'],
            [1, 2, 'Gula', 'Gula', 'gu-la', 'Gula jawa legi', 'pemula'],
            [1, 2, 'Garam', 'Uyah', 'u-yah', 'Uyah kanggo masak', 'pemula'],
            [1, 2, 'Teh', 'Teh', 'teh', 'Teh anget enak', 'pemula'],
            
            // Hewan (category_id = 3)
            [1, 3, 'Kucing', 'Kucing', 'ku-cing', 'Kucing iki lucu', 'pemula'],
            [1, 3, 'Anjing', 'Asu', 'a-su', 'Asu njaga omah', 'pemula'],
            [1, 3, 'Ayam', 'Pitik', 'pi-tik', 'Pitik ngluruk', 'pemula'],
            [1, 3, 'Sapi', 'Sapi', 'sa-pi', 'Sapi mangan suket', 'pemula'],
            [1, 3, 'Burung', 'Manuk', 'ma-nuk', 'Manuk mabur ing langit', 'pemula']
        ];
        
        // Sample words untuk Bahasa Sunda
        $sunda_words = [
            // Keluarga (category_id = 1)
            [2, 1, 'Ayah', 'Bapa', 'ba-pa', 'Bapa daek ka pasar', 'pemula'],
            [2, 1, 'Ibu', 'Indung', 'in-dung', 'Indung keur masak', 'pemula'],
            [2, 1, 'Kakak', 'Akang', 'a-kang', 'Akang keur diajar', 'pemula'],
            [2, 1, 'Adik', 'Adi', 'a-di', 'Adi keur ulin', 'pemula'],
            
            // Makanan (category_id = 2)
            [2, 2, 'Nasi', 'Sangu', 'sa-ngu', 'Sangu bodas hade pisan', 'pemula'],
            [2, 2, 'Air', 'Cai', 'cai', 'Cai ieu beresih', 'pemula'],
            [2, 2, 'Gula', 'Gula', 'gu-la', 'Gula kawung amis', 'pemula'],
            
            // Hewan (category_id = 3)
            [2, 3, 'Kucing', 'Ucing', 'u-cing', 'Ucing ieu lucu', 'pemula'],
            [2, 3, 'Anjing', 'Anjing', 'an-jing', 'Anjing ngajaga imah', 'pemula']
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO words (language_id, category_id, indonesian_word, local_word, pronunciation, usage_example, difficulty_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($jawa_words as $word) {
            $stmt->execute($word);
        }
        
        foreach ($sunda_words as $word) {
            $stmt->execute($word);
        }
    }
    
    private function seedQuizzes() {
        $quizzes = [
            ['Kuis Keluarga Jawa', 'Tes pengetahuan tentang anggota keluarga dalam bahasa Jawa', 1, 1, 'multiple_choice', 'pemula', 5, 300, 20],
            ['Kuis Makanan Jawa', 'Tes pengetahuan tentang makanan dalam bahasa Jawa', 1, 2, 'multiple_choice', 'pemula', 5, 300, 20],
            ['Kuis Keluarga Sunda', 'Tes pengetahuan tentang anggota keluarga dalam bahasa Sunda', 2, 1, 'multiple_choice', 'pemula', 5, 300, 20]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO quizzes (title, description, language_id, category_id, quiz_type, difficulty_level, total_questions, time_limit, points_per_question) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($quizzes as $quiz) {
            $stmt->execute($quiz);
        }
        
        // Generate quiz questions
        $this->generateQuizQuestions();
    }
    
    private function generateQuizQuestions() {
        // Get words for quiz questions
        $stmt = $this->conn->query("SELECT * FROM words ORDER BY id");
        $words = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $question_stmt = $this->conn->prepare("INSERT OR IGNORE INTO quiz_questions (quiz_id, word_id, question_text, correct_answer, option_a, option_b, option_c, option_d, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $quiz_counter = 1;
        $question_counter = 1;
        
        foreach ($words as $word) {
            if ($question_counter <= 5) { // 5 questions per quiz
                $question_text = "Apa arti dari '{$word['local_word']}' dalam bahasa Indonesia?";
                $correct_answer = $word['indonesian_word'];
                
                // Generate wrong options
                $wrong_options = $this->getWrongOptions($word['indonesian_word'], $word['category_id']);
                
                $question_stmt->execute([
                    $quiz_counter,
                    $word['id'],
                    $question_text,
                    $correct_answer,
                    $correct_answer, // option_a is correct
                    $wrong_options[0] ?? 'Pilihan B',
                    $wrong_options[1] ?? 'Pilihan C',
                    $wrong_options[2] ?? 'Pilihan D',
                    $question_counter
                ]);
                
                $question_counter++;
                
                if ($question_counter > 5) {
                    $question_counter = 1;
                    $quiz_counter++;
                    if ($quiz_counter > 3) break; // Only 3 quizzes for now
                }
            }
        }
    }
    
    private function getWrongOptions($correct_answer, $category_id) {
        $stmt = $this->conn->prepare("SELECT indonesian_word FROM words WHERE category_id = ? AND indonesian_word != ? LIMIT 3");
        $stmt->execute([$category_id, $correct_answer]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // If not enough options, add generic ones
        $generic_options = ['Pilihan A', 'Pilihan B', 'Pilihan C'];
        while (count($results) < 3) {
            $results[] = array_pop($generic_options);
        }
        
        return $results;
    }
    
    private function seedAchievements() {
        $achievements = [
            ['Pemula Sejati', 'Menyelesaikan kuis pertama', 'trophy', 'success', 'first_quiz', 1, 50],
            ['Pembelajar Aktif', 'Menyelesaikan 5 kuis', 'star', 'primary', 'quiz_count', 5, 100],
            ['Master Bahasa', 'Menyelesaikan 10 kuis', 'crown', 'warning', 'quiz_count', 10, 200],
            ['Penjelajah Nusantara', 'Belajar 3 bahasa daerah', 'globe', 'info', 'language_count', 3, 150],
            ['Skor Sempurna', 'Mendapat skor 100% dalam kuis', 'medal', 'danger', 'perfect_score', 1, 100],
            ['Konsisten', 'Login 7 hari berturut-turut', 'calendar', 'secondary', 'streak_days', 7, 75]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO achievements (name, description, icon, badge_color, requirement_type, requirement_value, points_reward) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($achievements as $achievement) {
            $stmt->execute($achievement);
        }
    }
    
    private function seedDemoUsers() {
        // Create demo users with realistic data
        $demo_users = [
            ['budi123', 'budi@email.com', password_hash('password123', PASSWORD_DEFAULT), 'Budi Santoso', 'default.png', 850, 3, 850, 5],
            ['sari_cantik', 'sari@email.com', password_hash('password123', PASSWORD_DEFAULT), 'Sari Dewi', 'default.png', 1200, 4, 1200, 8],
            ['joko_gamer', 'joko@email.com', password_hash('password123', PASSWORD_DEFAULT), 'Joko Widodo', 'default.png', 650, 2, 650, 3],
            ['maya_smart', 'maya@email.com', password_hash('password123', PASSWORD_DEFAULT), 'Maya Sari', 'default.png', 1500, 5, 1500, 12],
            ['andi_cool', 'andi@email.com', password_hash('password123', PASSWORD_DEFAULT), 'Andi Pratama', 'default.png', 400, 1, 400, 1]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, avatar, total_points, level, experience, streak_days) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($demo_users as $user) {
            $stmt->execute($user);
        }
        
        // Add some quiz attempts for demo users
        $this->seedDemoQuizAttempts();
        $this->seedDemoUserProgress();
        $this->seedDemoUserAchievements();
    }
    
    private function seedDemoQuizAttempts() {
        // Get user IDs for demo users
        $stmt_users = $this->conn->query("SELECT id, username FROM users WHERE username IN ('budi123', 'sari_cantik', 'joko_gamer', 'maya_smart', 'andi_cool')");
        $demo_user_ids = $stmt_users->fetchAll(PDO::FETCH_KEY_PAIR); // [username => id]

        // Get quiz IDs
        $stmt_quizzes = $this->conn->query("SELECT id, title FROM quizzes");
        $quiz_titles_to_ids = array_flip($stmt_quizzes->fetchAll(PDO::FETCH_KEY_PAIR)); // [title => id]

        if (empty($demo_user_ids) || empty($quiz_titles_to_ids)) {
            error_log("Skipping seedDemoQuizAttempts: No demo users or quizzes found.");
            return;
        }

        $attempts_data = [
            // user_id, quiz_id, score, total_questions, correct_answers, time_taken, percentage, status
            [$demo_user_ids['budi123'], $quiz_titles_to_ids['Kuis Keluarga Jawa'], 80, 5, 4, 240, 80.00, 'completed'],
            [$demo_user_ids['budi123'], $quiz_titles_to_ids['Kuis Makanan Jawa'], 100, 5, 5, 180, 100.00, 'completed'],
            [$demo_user_ids['sari_cantik'], $quiz_titles_to_ids['Kuis Keluarga Jawa'], 60, 5, 3, 300, 60.00, 'completed'],
            [$demo_user_ids['sari_cantik'], $quiz_titles_to_ids['Kuis Keluarga Sunda'], 80, 5, 4, 220, 80.00, 'completed'],
            [$demo_user_ids['joko_gamer'], $quiz_titles_to_ids['Kuis Keluarga Jawa'], 40, 5, 2, 280, 40.00, 'completed'],
            [$demo_user_ids['maya_smart'], $quiz_titles_to_ids['Kuis Keluarga Jawa'], 100, 5, 5, 150, 100.00, 'completed'],
            [$demo_user_ids['maya_smart'], $quiz_titles_to_ids['Kuis Makanan Jawa'], 100, 5, 5, 160, 100.00, 'completed'],
            [$demo_user_ids['andi_cool'], $quiz_titles_to_ids['Kuis Keluarga Jawa'], 20, 5, 1, 300, 20.00, 'completed']
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO quiz_attempts (user_id, quiz_id, score, total_questions, correct_answers, time_taken, percentage, status, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        foreach ($attempts_data as $attempt) {
            $stmt->execute($attempt);
        }
        error_log("Seeded " . count($attempts_data) . " demo quiz attempts.");
    }
    
    private function seedDemoUserProgress() {
        // Get user IDs for demo users
        $stmt_users = $this->conn->query("SELECT id, username FROM users WHERE username IN ('budi123', 'sari_cantik', 'joko_gamer', 'maya_smart', 'andi_cool')");
        $demo_user_ids = $stmt_users->fetchAll(PDO::FETCH_KEY_PAIR); // [username => id]

        // Get language and category IDs
        $stmt_lang = $this->conn->query("SELECT id, name FROM languages");
        $lang_names_to_ids = array_flip($stmt_lang->fetchAll(PDO::FETCH_KEY_PAIR));
        $stmt_cat = $this->conn->query("SELECT id, name FROM categories");
        $cat_names_to_ids = array_flip($stmt_cat->fetchAll(PDO::FETCH_KEY_PAIR));

        if (empty($demo_user_ids) || empty($lang_names_to_ids) || empty($cat_names_to_ids)) {
            error_log("Skipping seedDemoUserProgress: Missing demo users, languages, or categories.");
            return;
        }

        $progress_data = [
            // user_id, language_id, category_id, words_learned, quizzes_completed, total_score, best_score, completion_percentage
            [$demo_user_ids['budi123'], $lang_names_to_ids['Bahasa Jawa'], $cat_names_to_ids['Keluarga'], 5, 2, 180, 80, 80.00],
            [$demo_user_ids['budi123'], $lang_names_to_ids['Bahasa Jawa'], $cat_names_to_ids['Makanan'], 5, 2, 200, 100, 100.00],
            [$demo_user_ids['sari_cantik'], $lang_names_to_ids['Bahasa Jawa'], $cat_names_to_ids['Keluarga'], 3, 2, 140, 80, 60.00],
            [$demo_user_ids['sari_cantik'], $lang_names_to_ids['Bahasa Sunda'], $cat_names_to_ids['Keluarga'], 4, 1, 80, 80, 80.00],
            [$demo_user_ids['maya_smart'], $lang_names_to_ids['Bahasa Jawa'], $cat_names_to_ids['Keluarga'], 5, 2, 200, 100, 100.00],
            [$demo_user_ids['maya_smart'], $lang_names_to_ids['Bahasa Jawa'], $cat_names_to_ids['Makanan'], 5, 2, 200, 100, 100.00]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO user_progress (user_id, language_id, category_id, words_learned, quizzes_completed, total_score, best_score, completion_percentage, last_activity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        foreach ($progress_data as $prog) {
            $stmt->execute($prog);
        }
        error_log("Seeded " . count($progress_data) . " demo user progress entries.");
    }
    
    private function seedDemoUserAchievements() {
        // Get user IDs for demo users
        $stmt_users = $this->conn->query("SELECT id, username FROM users WHERE username IN ('budi123', 'sari_cantik', 'joko_gamer', 'maya_smart', 'andi_cool')");
        $demo_user_ids = $stmt_users->fetchAll(PDO::FETCH_KEY_PAIR); // [username => id]

        // Get achievement IDs
        $stmt_achievements = $this->conn->query("SELECT id, name FROM achievements");
        $achievement_names_to_ids = array_flip($stmt_achievements->fetchAll(PDO::FETCH_KEY_PAIR)); // [name => id]

        if (empty($demo_user_ids) || empty($achievement_names_to_ids)) {
            error_log("Skipping seedDemoUserAchievements: Missing demo users or achievements.");
            return;
        }

        $user_achievements_data = [
            // user_id, achievement_id
            [$demo_user_ids['budi123'], $achievement_names_to_ids['Pemula Sejati']], 
            [$demo_user_ids['budi123'], $achievement_names_to_ids['Pembelajar Aktif']], 
            [$demo_user_ids['budi123'], $achievement_names_to_ids['Skor Sempurna']], 
            
            [$demo_user_ids['sari_cantik'], $achievement_names_to_ids['Pemula Sejati']], 
            [$demo_user_ids['sari_cantik'], $achievement_names_to_ids['Pembelajar Aktif']],  
            
            [$demo_user_ids['joko_gamer'], $achievement_names_to_ids['Pemula Sejati']], 
            
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Pemula Sejati']], 
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Pembelajar Aktif']], 
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Master Bahasa']], 
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Penjelajah Nusantara']], 
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Skor Sempurna']], 
            [$demo_user_ids['maya_smart'], $achievement_names_to_ids['Konsisten']], 
            
            [$demo_user_ids['andi_cool'], $achievement_names_to_ids['Pemula Sejati']]
        ];
        
        $stmt = $this->conn->prepare("INSERT OR IGNORE INTO user_achievements (user_id, achievement_id, earned_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        foreach ($user_achievements_data as $ua) {
            $stmt->execute($ua);
        }
        error_log("Seeded " . count($user_achievements_data) . " demo user achievements.");
    }
}
?>
