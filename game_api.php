<?php
/**
 * Word Bomb Game - Game API Handler
 * Handles game actions: saving scores, getting user data, etc.
 */

session_start();
require_once 'database.php';
require_once 'words.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Save game score
 */
function saveGameScore($userId, $score, $wordsDefused, $bestStreak, $difficulty, $hintsUsed = 0) {
    $conn = getDBConnection();
    
    try {
        // Insert score record
        $stmt = $conn->prepare("INSERT INTO scores (user_id, score, words_defused, best_streak, difficulty, hints_used) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $score, $wordsDefused, $bestStreak, $difficulty, $hintsUsed]);
        
        // Update user stats
        $stmt = $conn->prepare("UPDATE users SET 
                               total_games = total_games + 1,
                               total_score = total_score + ?,
                               best_score = GREATEST(best_score, ?),
                               total_words_defused = total_words_defused + ?,
                               best_streak = GREATEST(best_streak, ?)
                               WHERE id = ?");
        $stmt->execute([$score, $score, $wordsDefused, $bestStreak, $userId]);
        
        // Update leaderboard
        $stmt = $conn->prepare("UPDATE leaderboard SET 
                               total_score = total_score + ?,
                               best_score = GREATEST(best_score, ?),
                               total_games = total_games + 1,
                               total_words_defused = total_words_defused + ?,
                               best_streak = GREATEST(best_streak, ?),
                               average_score = (total_score + ?) / (total_games + 1)
                               WHERE user_id = ?");
        $stmt->execute([$score, $score, $wordsDefused, $bestStreak, $score, $userId]);
        
        // Update rankings
        updateRankings($conn);
        
        // Check achievements
        $achievements = checkAchievements($userId, $score, $wordsDefused, $bestStreak, $difficulty, $hintsUsed);
        
        return [
            'success' => true,
            'message' => 'Score saved!',
            'new_achievements' => $achievements
        ];
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Failed to save score'];
    }
}

/**
 * Update leaderboard rankings
 */
function updateRankings($conn) {
    $conn->exec("SET @rank = 0");
    $conn->exec("UPDATE leaderboard SET rank_position = (@rank := @rank + 1) ORDER BY best_score DESC, total_score DESC");
}

/**
 * Check and award achievements
 */
function checkAchievements($userId, $score, $wordsDefused, $streak, $difficulty, $hintsUsed) {
    $conn = getDBConnection();
    $newAchievements = [];
    
    // Get user's current stats
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Get count of games by difficulty
    $stmt = $conn->prepare("SELECT difficulty, COUNT(*) as count FROM scores WHERE user_id = ? GROUP BY difficulty");
    $stmt->execute([$userId]);
    $gamesByDifficulty = [];
    while ($row = $stmt->fetch()) {
        $gamesByDifficulty[$row['difficulty']] = $row['count'];
    }
    
    // Get all achievements
    $stmt = $conn->prepare("SELECT * FROM achievements");
    $stmt->execute();
    $achievements = $stmt->fetchAll();
    
    // Get user's existing achievements
    $stmt = $conn->prepare("SELECT achievement_id FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingAchievements = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($achievements as $achievement) {
        if (in_array($achievement['id'], $existingAchievements)) {
            continue; // Already has this achievement
        }
        
        $unlocked = false;
        
        switch ($achievement['requirement_type']) {
            case 'words_defused':
                $unlocked = $wordsDefused >= $achievement['requirement_value'];
                break;
            case 'total_words':
                $unlocked = $user['total_words_defused'] >= $achievement['requirement_value'];
                break;
            case 'single_score':
                $unlocked = $score >= $achievement['requirement_value'];
                break;
            case 'streak':
                $unlocked = $streak >= $achievement['requirement_value'];
                break;
            case 'total_games':
                $unlocked = $user['total_games'] >= $achievement['requirement_value'];
                break;
            case 'games_easy':
                $unlocked = ($gamesByDifficulty['easy'] ?? 0) >= $achievement['requirement_value'];
                break;
            case 'games_medium':
                $unlocked = ($gamesByDifficulty['medium'] ?? 0) >= $achievement['requirement_value'];
                break;
            case 'games_hard':
                $unlocked = ($gamesByDifficulty['hard'] ?? 0) >= $achievement['requirement_value'];
                break;
            case 'games_extreme':
                $unlocked = ($gamesByDifficulty['extreme'] ?? 0) >= $achievement['requirement_value'];
                break;
            case 'no_hints':
                $unlocked = $hintsUsed == 0 && $wordsDefused > 0;
                break;
        }
        
        if ($unlocked) {
            $stmt = $conn->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
            $stmt->execute([$userId, $achievement['id']]);
            $newAchievements[] = [
                'name' => $achievement['name'],
                'description' => $achievement['description'],
                'icon' => $achievement['icon'],
                'points' => $achievement['points']
            ];
        }
    }
    
    return $newAchievements;
}

/**
 * Get user profile data
 */
function getUserProfile($userId) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT u.*, l.rank_position 
                           FROM users u 
                           LEFT JOIN leaderboard l ON u.id = l.user_id 
                           WHERE u.id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return null;
    }
    
    // Get recent games
    $stmt = $conn->prepare("SELECT * FROM scores WHERE user_id = ? ORDER BY played_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $recentGames = $stmt->fetchAll();
    
    // Get achievements
    $stmt = $conn->prepare("SELECT a.*, ua.unlocked_at 
                           FROM achievements a 
                           JOIN user_achievements ua ON a.id = ua.achievement_id 
                           WHERE ua.user_id = ? 
                           ORDER BY ua.unlocked_at DESC");
    $stmt->execute([$userId]);
    $achievements = $stmt->fetchAll();
    
    return [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'avatar' => $user['avatar'],
            'total_games' => $user['total_games'],
            'total_score' => $user['total_score'],
            'best_score' => $user['best_score'],
            'total_words_defused' => $user['total_words_defused'],
            'best_streak' => $user['best_streak'],
            'rank' => $user['rank_position'],
            'created_at' => $user['created_at']
        ],
        'recent_games' => $recentGames,
        'achievements' => $achievements
    ];
}

/**
 * Get leaderboard
 */
function getLeaderboard($limit = 10, $filter = 'all') {
    $conn = getDBConnection();
    
    if (in_array($filter, ['easy', 'medium', 'hard', 'extreme'])) {
        $stmt = $conn->prepare("SELECT u.id, u.username, u.avatar, 
                               MAX(s.score) as best_score, 
                               COUNT(s.id) as total_games
                               FROM users u
                               JOIN scores s ON u.id = s.user_id
                               WHERE s.difficulty = ?
                               GROUP BY u.id
                               ORDER BY best_score DESC
                               LIMIT ?");
        $stmt->execute([$filter, $limit]);
    } else {
        $stmt = $conn->prepare("SELECT u.id, u.username, u.avatar, 
                               l.best_score, l.total_games, l.rank_position
                               FROM leaderboard l
                               JOIN users u ON l.user_id = u.id
                               ORDER BY l.best_score DESC
                               LIMIT ?");
        $stmt->execute([$limit]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Scramble a word
 */
function scrambleWord($word) {
    $word = strtoupper($word);
    $chars = str_split($word);
    $maxAttempts = 10;
    $attempts = 0;
    
    do {
        shuffle($chars);
        $scrambled = implode('', $chars);
        $attempts++;
    } while ($scrambled === $word && $attempts < $maxAttempts);
    
    return $scrambled;
}

/**
 * Get a random word
 */
function getRandomWord($difficulty = 'medium') {
    global $wordDatabase;
    
    $lengthRanges = [
        'easy' => ['min' => 4, 'max' => 5],
        'medium' => ['min' => 5, 'max' => 7],
        'hard' => ['min' => 7, 'max' => 9],
        'extreme' => ['min' => 8, 'max' => 12]
    ];
    
    $range = $lengthRanges[$difficulty] ?? $lengthRanges['medium'];
    
    $filteredWords = array_filter($wordDatabase, function($word) use ($range) {
        $len = strlen($word['word']);
        return $len >= $range['min'] && $len <= $range['max'];
    });
    
    if (empty($filteredWords)) {
        $filteredWords = $wordDatabase;
    }
    
    $randomIndex = array_rand($filteredWords);
    $selectedWord = $filteredWords[$randomIndex];
    
    return [
        'word' => strtoupper($selectedWord['word']),
        'scrambled' => scrambleWord($selectedWord['word']),
        'category' => $selectedWord['category'],
        'hint' => $selectedWord['hint']
    ];
}

// Handle API requests
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'getWord':
        $difficulty = $_GET['difficulty'] ?? 'medium';
        $wordData = getRandomWord($difficulty);
        echo json_encode([
            'success' => true,
            'word' => $wordData['word'],
            'scrambled' => $wordData['scrambled'],
            'category' => $wordData['category'],
            'hint' => $wordData['hint']
        ]);
        break;
        
    case 'saveScore':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Not logged in', 'guest' => true]);
            break;
        }
        
        $result = saveGameScore(
            getCurrentUserId(),
            (int)($_POST['score'] ?? 0),
            (int)($_POST['words_defused'] ?? 0),
            (int)($_POST['best_streak'] ?? 0),
            $_POST['difficulty'] ?? 'medium',
            (int)($_POST['hints_used'] ?? 0)
        );
        echo json_encode($result);
        break;
        
    case 'getProfile':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            break;
        }
        
        $profile = getUserProfile(getCurrentUserId());
        echo json_encode(['success' => true, 'profile' => $profile]);
        break;
        
    case 'getLeaderboard':
        $limit = (int)($_GET['limit'] ?? 10);
        $filter = $_GET['filter'] ?? 'all';
        $leaderboard = getLeaderboard($limit, $filter);
        echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
        break;
        
    case 'checkAuth':
        echo json_encode([
            'success' => true,
            'logged_in' => isLoggedIn(),
            'user_id' => getCurrentUserId(),
            'username' => $_SESSION['username'] ?? null
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action',
            'available_actions' => ['getWord', 'saveScore', 'getProfile', 'getLeaderboard', 'checkAuth']
        ]);
}
?>
