<?php
require_once 'auth.php';
require_once 'database.php';

$currentUser = getCurrentUser();
$conn = getDBConnection();

// Get filter
$filter = $_GET['filter'] ?? 'all';
$validFilters = ['all', 'easy', 'medium', 'hard', 'extreme', 'weekly', 'monthly'];
if (!in_array($filter, $validFilters)) {
    $filter = 'all';
}

// Build query based on filter
$whereClause = "";
$params = [];

if (in_array($filter, ['easy', 'medium', 'hard', 'extreme'])) {
    // Get best scores by difficulty
    $sql = "SELECT u.id, u.username, u.avatar, 
            MAX(s.score) as best_score, 
            COUNT(s.id) as total_games,
            SUM(s.words_defused) as total_words
            FROM users u
            JOIN scores s ON u.id = s.user_id
            WHERE s.difficulty = ?
            GROUP BY u.id
            ORDER BY best_score DESC
            LIMIT 100";
    $params = [$filter];
} elseif ($filter === 'weekly') {
    $sql = "SELECT u.id, u.username, u.avatar,
            SUM(s.score) as best_score,
            COUNT(s.id) as total_games,
            SUM(s.words_defused) as total_words
            FROM users u
            JOIN scores s ON u.id = s.user_id
            WHERE s.played_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY u.id
            ORDER BY best_score DESC
            LIMIT 100";
} elseif ($filter === 'monthly') {
    $sql = "SELECT u.id, u.username, u.avatar,
            SUM(s.score) as best_score,
            COUNT(s.id) as total_games,
            SUM(s.words_defused) as total_words
            FROM users u
            JOIN scores s ON u.id = s.user_id
            WHERE s.played_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id
            ORDER BY best_score DESC
            LIMIT 100";
} else {
    // All time best scores
    $sql = "SELECT u.id, u.username, u.avatar, 
            l.best_score, l.total_games, l.total_words_defused as total_words
            FROM leaderboard l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.best_score DESC
            LIMIT 100";
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$leaderboard = $stmt->fetchAll();

// Get current user's rank if logged in
$userRank = null;
if ($currentUser) {
    foreach ($leaderboard as $index => $player) {
        if ($player['id'] == $currentUser['id']) {
            $userRank = $index + 1;
            break;
        }
    }
}

// Avatar options
$avatars = ['🎮', '💣', '🔥', '⭐', '🏆', '👑', '🎯', '⚡', '🌟', '💀'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Word Bomb</title>
    <link rel="stylesheet" href="auth-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
    <div class="leaderboard-container">
        <!-- Header -->
        <div class="leaderboard-header">
            <a href="index.html" class="logo">💣 WORD BOMB</a>
            <h1>🏆 LEADERBOARD</h1>
            <p>Top bomb defusers around the world</p>
        </div>

        <!-- User Profile Card (if logged in) -->
        <?php if ($currentUser): ?>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo $avatars[array_search($currentUser['avatar'], ['default', 'bomb', 'fire', 'star', 'trophy', 'crown', 'target', 'bolt', 'sparkle', 'skull']) ?: 0]; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($currentUser['username']); ?></h2>
                    <p>Rank: #<?php echo $userRank ?? 'Unranked'; ?></p>
                </div>
                <div style="margin-left: auto;">
                    <a href="auth.php?action=logout" class="guest-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($currentUser['best_score']); ?></div>
                    <div class="stat-label">Best Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($currentUser['total_score']); ?></div>
                    <div class="stat-label">Total Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($currentUser['total_games']); ?></div>
                    <div class="stat-label">Games Played</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">🔥 <?php echo number_format($currentUser['best_streak']); ?></div>
                    <div class="stat-label">Best Streak</div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="profile-card" style="text-align: center;">
            <p style="margin-bottom: 15px;">Login to track your scores and compete on the leaderboard!</p>
            <a href="login.php" class="auth-btn" style="display: inline-flex; width: auto;">
                <span class="btn-text">LOGIN</span>
                <span class="btn-icon">🔐</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="leaderboard-tabs">
            <a href="?filter=all" class="tab-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Time</a>
            <a href="?filter=weekly" class="tab-btn <?php echo $filter === 'weekly' ? 'active' : ''; ?>">Weekly</a>
            <a href="?filter=monthly" class="tab-btn <?php echo $filter === 'monthly' ? 'active' : ''; ?>">Monthly</a>
            <a href="?filter=easy" class="tab-btn <?php echo $filter === 'easy' ? 'active' : ''; ?>">🟢 Easy</a>
            <a href="?filter=medium" class="tab-btn <?php echo $filter === 'medium' ? 'active' : ''; ?>">🟡 Medium</a>
            <a href="?filter=hard" class="tab-btn <?php echo $filter === 'hard' ? 'active' : ''; ?>">🔴 Hard</a>
            <a href="?filter=extreme" class="tab-btn <?php echo $filter === 'extreme' ? 'active' : ''; ?>">💀 Extreme</a>
        </div>

        <!-- Leaderboard Table -->
        <div class="leaderboard-table">
            <div class="leaderboard-row header">
                <div class="rank">Rank</div>
                <div class="player-info">Player</div>
                <div class="score-value">Score</div>
                <div class="games-value">Games</div>
            </div>

            <?php if (empty($leaderboard)): ?>
            <div class="leaderboard-row" style="justify-content: center; grid-template-columns: 1fr;">
                <p style="text-align: center; color: var(--text-secondary); padding: 40px;">
                    No scores yet. Be the first to play!
                </p>
            </div>
            <?php else: ?>
                <?php foreach ($leaderboard as $index => $player): ?>
                <div class="leaderboard-row <?php echo ($currentUser && $player['id'] == $currentUser['id']) ? 'current-user' : ''; ?>">
                    <div class="rank <?php 
                        echo $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')); 
                    ?>">
                        <?php 
                        if ($index === 0) echo '🥇';
                        elseif ($index === 1) echo '🥈';
                        elseif ($index === 2) echo '🥉';
                        else echo '#' . ($index + 1);
                        ?>
                    </div>
                    <div class="player-info">
                        <div class="player-avatar">
                            <?php 
                            $avatarIndex = array_search($player['avatar'] ?? 'default', ['default', 'bomb', 'fire', 'star', 'trophy', 'crown', 'target', 'bolt', 'sparkle', 'skull']);
                            echo $avatars[$avatarIndex !== false ? $avatarIndex : 0];
                            ?>
                        </div>
                        <div>
                            <div class="player-name"><?php echo htmlspecialchars($player['username']); ?></div>
                            <div class="player-stats"><?php echo number_format($player['total_words'] ?? 0); ?> words defused</div>
                        </div>
                    </div>
                    <div class="score-value"><?php echo number_format($player['best_score']); ?></div>
                    <div class="games-value"><?php echo number_format($player['total_games']); ?> games</div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Back to Game -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="guest-btn">
                <span class="btn-icon">🎮</span>
                Back to Game
            </a>
        </div>
    </div>

    <div class="auth-decoration">
        <div class="floating-bomb bomb-1">💣</div>
        <div class="floating-bomb bomb-2">💥</div>
        <div class="floating-bomb bomb-3">🔥</div>
        <div class="floating-bomb bomb-4">⏰</div>
    </div>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle" title="Toggle Light/Dark Mode">
        <span class="theme-icon">🌙</span>
    </button>

    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('.theme-icon');
        
        // Check for saved theme preference or default to dark
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            themeIcon.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
    </script>
</body>
</html>
