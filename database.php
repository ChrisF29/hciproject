<?php
/**
 * Word Bomb Game - Database Configuration and Setup
 * Run this file once to create all necessary tables
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wordbomb_game');

/**
 * Get database connection
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // If database doesn't exist, create it
            if ($e->getCode() == 1049) {
                createDatabase();
                return getDBConnection();
            }
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

/**
 * Create the database if it doesn't exist
 */
function createDatabase() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database created successfully.<br>";
    } catch (PDOException $e) {
        die("Error creating database: " . $e->getMessage());
    }
}

/**
 * Create all necessary tables
 */
function createTables() {
    $conn = getDBConnection();
    
    // Users table
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `avatar` VARCHAR(50) DEFAULT 'default',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_login` TIMESTAMP NULL,
        `total_games` INT DEFAULT 0,
        `total_score` INT DEFAULT 0,
        `best_score` INT DEFAULT 0,
        `total_words_defused` INT DEFAULT 0,
        `best_streak` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        INDEX `idx_username` (`username`),
        INDEX `idx_email` (`email`),
        INDEX `idx_best_score` (`best_score` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Scores table (individual game records)
    $sql_scores = "CREATE TABLE IF NOT EXISTS `scores` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `score` INT NOT NULL DEFAULT 0,
        `words_defused` INT NOT NULL DEFAULT 0,
        `best_streak` INT NOT NULL DEFAULT 0,
        `difficulty` ENUM('easy', 'medium', 'hard', 'extreme') NOT NULL DEFAULT 'medium',
        `time_played` INT DEFAULT 0,
        `hints_used` INT DEFAULT 0,
        `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_score` (`score` DESC),
        INDEX `idx_difficulty` (`difficulty`),
        INDEX `idx_played_at` (`played_at` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Leaderboard table (aggregated stats for quick queries)
    $sql_leaderboard = "CREATE TABLE IF NOT EXISTS `leaderboard` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL UNIQUE,
        `username` VARCHAR(50) NOT NULL,
        `total_score` INT DEFAULT 0,
        `best_score` INT DEFAULT 0,
        `total_games` INT DEFAULT 0,
        `total_words_defused` INT DEFAULT 0,
        `best_streak` INT DEFAULT 0,
        `average_score` DECIMAL(10,2) DEFAULT 0,
        `rank_position` INT DEFAULT 0,
        `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        INDEX `idx_best_score` (`best_score` DESC),
        INDEX `idx_total_score` (`total_score` DESC),
        INDEX `idx_rank` (`rank_position`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Daily challenges table
    $sql_daily = "CREATE TABLE IF NOT EXISTS `daily_challenges` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `challenge_date` DATE NOT NULL,
        `score` INT DEFAULT 0,
        `completed` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_daily` (`user_id`, `challenge_date`),
        INDEX `idx_date` (`challenge_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Achievements table
    $sql_achievements = "CREATE TABLE IF NOT EXISTS `achievements` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `description` VARCHAR(255) NOT NULL,
        `icon` VARCHAR(50) NOT NULL,
        `requirement_type` VARCHAR(50) NOT NULL,
        `requirement_value` INT NOT NULL,
        `points` INT DEFAULT 10
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // User achievements table
    $sql_user_achievements = "CREATE TABLE IF NOT EXISTS `user_achievements` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `achievement_id` INT NOT NULL,
        `unlocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_user_achievement` (`user_id`, `achievement_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Session tokens table
    $sql_sessions = "CREATE TABLE IF NOT EXISTS `sessions` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `token` VARCHAR(255) NOT NULL UNIQUE,
        `expires_at` TIMESTAMP NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        INDEX `idx_token` (`token`),
        INDEX `idx_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $conn->exec($sql_users);
        echo "✓ Users table created.<br>";
        
        $conn->exec($sql_scores);
        echo "✓ Scores table created.<br>";
        
        $conn->exec($sql_leaderboard);
        echo "✓ Leaderboard table created.<br>";
        
        $conn->exec($sql_daily);
        echo "✓ Daily challenges table created.<br>";
        
        $conn->exec($sql_achievements);
        echo "✓ Achievements table created.<br>";
        
        $conn->exec($sql_user_achievements);
        echo "✓ User achievements table created.<br>";
        
        $conn->exec($sql_sessions);
        echo "✓ Sessions table created.<br>";
        
        // Insert default achievements
        insertDefaultAchievements($conn);
        
        echo "<br><strong>All tables created successfully!</strong>";
        
    } catch (PDOException $e) {
        die("Error creating tables: " . $e->getMessage());
    }
}

/**
 * Insert default achievements
 */
function insertDefaultAchievements($conn) {
    $achievements = [
        ['First Defuse', 'Defuse your first word', '🎯', 'words_defused', 1, 10],
        ['Bomb Squad Rookie', 'Defuse 10 words total', '🔰', 'total_words', 10, 25],
        ['Bomb Squad Pro', 'Defuse 100 words total', '⭐', 'total_words', 100, 50],
        ['Bomb Squad Elite', 'Defuse 500 words total', '🏆', 'total_words', 500, 100],
        ['First Century', 'Score 100 points in a single game', '💯', 'single_score', 100, 15],
        ['High Scorer', 'Score 500 points in a single game', '🔥', 'single_score', 500, 30],
        ['Score Master', 'Score 1000 points in a single game', '👑', 'single_score', 1000, 75],
        ['Streak Starter', 'Get a 3 word streak', '3️⃣', 'streak', 3, 15],
        ['On Fire', 'Get a 5 word streak', '🔥', 'streak', 5, 25],
        ['Unstoppable', 'Get a 10 word streak', '💪', 'streak', 10, 50],
        ['Speed Demon', 'Defuse a word in under 3 seconds', '⚡', 'speed', 3, 40],
        ['Easy Mode Master', 'Complete 10 games on Easy', '🟢', 'games_easy', 10, 20],
        ['Medium Mode Master', 'Complete 10 games on Medium', '🟡', 'games_medium', 10, 30],
        ['Hard Mode Master', 'Complete 10 games on Hard', '🔴', 'games_hard', 10, 50],
        ['Extreme Survivor', 'Complete 5 games on Extreme', '💀', 'games_extreme', 5, 100],
        ['Dedicated Player', 'Play 50 games total', '🎮', 'total_games', 50, 50],
        ['No Hints Needed', 'Complete a game without using hints', '🧠', 'no_hints', 1, 35],
        ['Daily Warrior', 'Complete 7 daily challenges', '📅', 'daily_challenges', 7, 50]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO achievements (name, description, icon, requirement_type, requirement_value, points) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($achievements as $achievement) {
        $stmt->execute($achievement);
    }
    
    echo "✓ Default achievements inserted.<br>";
}

/**
 * Update leaderboard rankings
 */
function updateLeaderboardRankings() {
    $conn = getDBConnection();
    
    // Update rank positions based on best score
    $sql = "SET @rank = 0;
            UPDATE leaderboard 
            SET rank_position = (@rank := @rank + 1)
            ORDER BY best_score DESC, total_score DESC";
    
    $conn->exec($sql);
}

// If this file is accessed directly, create tables
if (basename($_SERVER['PHP_SELF']) == 'database.php') {
    echo "<h2>Word Bomb Database Setup</h2>";
    echo "<hr>";
    createTables();
    echo "<hr>";
    echo "<p><a href='index.html'>Go to Game</a> | <a href='login.php'>Login</a> | <a href='register.php'>Register</a></p>";
}
?>
