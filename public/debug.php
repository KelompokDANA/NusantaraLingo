<?php
// debug.php
// This script is for debugging database and seeding issues.
// Access it via http://localhost:8000/debug.php

session_start(); // Start session to simulate user context if needed

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Quiz.php';

echo "<h1>NusantaraLingo Debug Script</h1>";

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "<p>✅ Database connection successful!</p>";
    
    // Force re-seed data (including demo users, attempts, achievements)
    echo "<h2>Seeding Data:</h2>";
    $database->seedData();
    echo "<p>✅ Data seeding process completed. Check logs for details.</p>";

    // --- Verify User Data ---
    echo "<h2>User Data Verification:</h2>";
    $user_stmt = $conn->query("SELECT id, username, email, total_points, level, experience FROM users ORDER BY total_points DESC LIMIT 5");
    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($users)) {
        echo "<p>Found " . count($users) . " users (top 5 by points):</p>";
        echo "<pre>" . htmlspecialchars(json_encode($users, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p>❌ No users found in the database.</p>";
    }

    // --- Verify Quiz Attempts ---
    echo "<h2>Quiz Attempts Verification:</h2>";
    $attempt_stmt = $conn->query("SELECT qa.id, u.username, q.title as quiz_title, qa.score, qa.percentage, qa.completed_at FROM quiz_attempts qa JOIN users u ON qa.user_id = u.id JOIN quizzes q ON qa.quiz_id = q.id ORDER BY qa.completed_at DESC LIMIT 5");
    $attempts = $attempt_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($attempts)) {
        echo "<p>Found " . count($attempts) . " quiz attempts (most recent 5):</p>";
        echo "<pre>" . htmlspecialchars(json_encode($attempts, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p>❌ No quiz attempts found in the database.</p>";
    }

    // --- Verify User Achievements ---
    echo "<h2>User Achievements Verification:</h2>";
    $achievement_stmt = $conn->query("SELECT ua.id, u.username, a.name as achievement_name, ua.earned_at FROM user_achievements ua JOIN users u ON ua.user_id = u.id JOIN achievements a ON ua.achievement_id = a.id ORDER BY ua.earned_at DESC LIMIT 5");
    $user_achievements = $achievement_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($user_achievements)) {
        echo "<p>Found " . count($user_achievements) . " user achievements (most recent 5):</p>";
        echo "<pre>" . htmlspecialchars(json_encode($user_achievements, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p>❌ No user achievements found in the database.</p>";
    }

    // --- Test Leaderboard Query ---
    echo "<h2>Leaderboard Query Test:</h2>";
    $user_model = new User();
    $leaderboard_stmt = $user_model->getLeaderboard(10);
    $leaderboard_data = $leaderboard_stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($leaderboard_data)) {
        echo "<p>Leaderboard query returned " . count($leaderboard_data) . " users:</p>";
        echo "<pre>" . htmlspecialchars(json_encode($leaderboard_data, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p>❌ Leaderboard query returned no data.</p>";
    }

    // --- Test User Rank Query (for a demo user) ---
    echo "<h2>User Rank Test (for 'maya_smart'):</h2>";
    $maya_stmt = $conn->query("SELECT id FROM users WHERE username = 'maya_smart'");
    $maya_id = $maya_stmt->fetchColumn();
    if ($maya_id) {
        $_SESSION['user_id'] = $maya_id; // Temporarily set session for testing
        $dashboard_controller = new DashboardController(); // Re-instantiate to pick up session
        $maya_rank = $dashboard_controller->leaderboard(); // This will render the leaderboard view, but also calculate rank
        echo "<p>Maya Smart's ID: " . $maya_id . "</p>";
        // The rank is calculated inside the leaderboard method, check the logs for it.
        echo "<p>Check PHP error logs for 'User rank' to see Maya Smart's rank.</p>";
    } else {
        echo "<p>❌ Demo user 'maya_smart' not found. Cannot test user rank.</p>";
    }

} else {
    echo "<p>❌ Failed to connect to the database. Check your `config/database.php`.</p>";
}

echo "<p><strong>Important:</strong> After running this script, please delete `database/nusantaralingo.db` and restart your PHP server (`php -S localhost:8000`) to ensure a clean state for your application.</p>";
echo "<p>Then, try accessing your application pages:</p>";
echo "<ul>";
echo "<li><a href='/public/index.php?controller=dashboard&action=leaderboard'>Leaderboard</a></li>";
echo "<li><a href='/public/index.php?controller=quiz&action=history'>Riwayat Kuis</a></li>";
echo "<li><a href='/public/index.php?controller=dashboard&action=achievements'>Pencapaian</a></li>";
echo "</ul>";
?>
