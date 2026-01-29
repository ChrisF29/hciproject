<?php
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.html');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $result = loginUser(
        $_POST['username'] ?? '',
        $_POST['password'] ?? '',
        isset($_POST['remember'])
    );
    
    if ($result['success']) {
        header('Location: index.html');
        exit;
    } else {
        $error = implode('<br>', $result['errors']);
    }
}

// Check for registration success message
if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Word Bomb</title>
    <link rel="stylesheet" href="auth-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.html" class="logo">💣 WORD BOMB</a>
                <h1>Welcome Back!</h1>
                <p>Login to continue your bomb defusing journey</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="login-form">
                <div class="form-group">
                    <label for="username">
                        <span class="label-icon">👤</span>
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username or email"
                        required
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Password
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="auth-btn">
                    <span class="btn-text">LOGIN</span>
                    <span class="btn-icon">🚀</span>
                </button>
            </form>

            <div class="auth-divider">
                <span>OR</span>
            </div>

            <div class="guest-option">
                <a href="index.html" class="guest-btn">
                    <span class="btn-icon">🎮</span>
                    Play as Guest
                </a>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register Now</a></p>
            </div>
        </div>

        <div class="auth-decoration">
            <div class="floating-bomb bomb-1">💣</div>
            <div class="floating-bomb bomb-2">💥</div>
            <div class="floating-bomb bomb-3">🔥</div>
            <div class="floating-bomb bomb-4">⏰</div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Form validation
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>
