<?php
// Script untuk debug leaderboard
require_once __DIR__ . '/../config/database.php';

echo "<h2>🔍 DEBUG LEADERBOARD</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h3>1. Cek Koneksi Database</h3>";
    if ($conn) {
        echo "✅ Database connected successfully<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "<h3>2. Cek Total Users</h3>";
    $query = "SELECT COUNT(*) as total FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total users: <strong>$total_users</strong><br>";
    
    if ($total_users == 0) {
        echo "❌ No users found! Running seed data...<br>";
        $database->seedData();
        echo "✅ Seed data completed<br>";
    }
    
    echo "<h3>3. Cek Users dengan Poin</h3>";
    $query = "SELECT username, full_name, total_points, level FROM users ORDER BY total_points DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Username</th><th>Full Name</th><th>Points</th><th>Level</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . $user['total_points'] . "</td>";
        echo "<td>" . $user['level'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>4. Test Leaderboard Query</h3>";
    $query = "SELECT id, username, full_name, avatar, total_points, level, experience 
              FROM users 
              ORDER BY total_points DESC, level DESC, experience DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Leaderboard results: <strong>" . count($leaderboard) . "</strong> users<br>";
    
    if (empty($leaderboard)) {
        echo "❌ Leaderboard query returned empty results<br>";
        
        // Force update some users with points
        echo "<h3>5. Adding Points to Demo Users</h3>";
        $update_queries = [
            "UPDATE users SET total_points = 1500, level = 5, experience = 1500 WHERE username = 'maya_smart'",
            "UPDATE users SET total_points = 1200, level = 4, experience = 1200 WHERE username = 'sari_cantik'",
            "UPDATE users SET total_points = 850, level = 3, experience = 850 WHERE username = 'budi123'",
            "UPDATE users SET total_points = 650, level = 2, experience = 650 WHERE username = 'joko_gamer'",
            "UPDATE users SET total_points = 400, level = 1, experience = 400 WHERE username = 'andi_cool'"
        ];
        
        foreach ($update_queries as $update_query) {
            try {
                $conn->exec($update_query);
                echo "✅ Updated user points<br>";
            } catch (Exception $e) {
                echo "❌ Error updating: " . $e->getMessage() . "<br>";
            }
        }
        
        // Test again
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "After update - Leaderboard results: <strong>" . count($leaderboard) . "</strong> users<br>";
    }
    
    echo "<h3>6. Final Leaderboard Data</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Rank</th><th>Username</th><th>Full Name</th><th>Points</th><th>Level</th></tr>";
    
    foreach ($leaderboard as $index => $user) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . $user['total_points'] . "</td>";
        echo "<td>" . $user['level'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
