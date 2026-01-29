<?php
/**
 * Word Bomb Game - Authentication Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $confirmPassword) {
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be 3-20 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Confirm password
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    $conn = getDBConnection();
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Username or email already exists']];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);
        
        $userId = $conn->lastInsertId();
        
        // Create leaderboard entry
        $stmt = $conn->prepare("INSERT INTO leaderboard (user_id, username) VALUES (?, ?)");
        $stmt->execute([$userId, $username]);
        
        return ['success' => true, 'message' => 'Registration successful! Please login.', 'user_id' => $userId];
        
    } catch (PDOException $e) {
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
}

/**
 * Login user
 */
function loginUser($username, $password, $remember = false) {
    if (empty($username) || empty($password)) {
        return ['success' => false, 'errors' => ['Please fill in all fields']];
    }
    
    $conn = getDBConnection();
    
    // Get user by username or email
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'errors' => ['Invalid username or password']];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'errors' => ['Account is deactivated']];
    }
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['logged_in'] = true;
    
    // Remember me token
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $conn->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expires]);
        
        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
    }
    
    return ['success' => true, 'message' => 'Login successful!', 'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'avatar' => $user['avatar'],
        'best_score' => $user['best_score'],
        'total_games' => $user['total_games']
    ]];
}

/**
 * Logout user
 */
function logoutUser() {
    // Clear session
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    // Clear remember token
    if (isset($_COOKIE['remember_token'])) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    return ['success' => true, 'message' => 'Logged out successfully'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    
    // Check remember token
    if (isset($_COOKIE['remember_token'])) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT s.user_id, u.username FROM sessions s 
                                JOIN users u ON s.user_id = u.id 
                                WHERE s.token = ? AND s.expires_at > NOW()");
        $stmt->execute([$_COOKIE['remember_token']]);
        $session = $stmt->fetch();
        
        if ($session) {
            $_SESSION['user_id'] = $session['user_id'];
            $_SESSION['username'] = $session['username'];
            $_SESSION['logged_in'] = true;
            return true;
        }
    }
    
    return false;
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, avatar, created_at, total_games, total_score, best_score, total_words_defused, best_streak FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch();
}

/**
 * Get user stats
 */
function getUserStats($userId) {
    $conn = getDBConnection();
    
    // Get recent games
    $stmt = $conn->prepare("SELECT * FROM scores WHERE user_id = ? ORDER BY played_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $recentGames = $stmt->fetchAll();
    
    // Get leaderboard position
    $stmt = $conn->prepare("SELECT rank_position FROM leaderboard WHERE user_id = ?");
    $stmt->execute([$userId]);
    $rank = $stmt->fetch();
    
    // Get achievements
    $stmt = $conn->prepare("SELECT a.* FROM achievements a 
                           JOIN user_achievements ua ON a.id = ua.achievement_id 
                           WHERE ua.user_id = ? ORDER BY ua.unlocked_at DESC");
    $stmt->execute([$userId]);
    $achievements = $stmt->fetchAll();
    
    return [
        'recent_games' => $recentGames,
        'rank' => $rank ? $rank['rank_position'] : 0,
        'achievements' => $achievements
    ];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'register':
            $result = registerUser(
                $_POST['username'] ?? '',
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );
            echo json_encode($result);
            break;
            
        case 'login':
            $result = loginUser(
                $_POST['username'] ?? '',
                $_POST['password'] ?? '',
                isset($_POST['remember'])
            );
            echo json_encode($result);
            break;
            
        case 'logout':
            $result = logoutUser();
            echo json_encode($result);
            break;
            
        case 'check':
            echo json_encode([
                'logged_in' => isLoggedIn(),
                'user' => getCurrentUser()
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>
