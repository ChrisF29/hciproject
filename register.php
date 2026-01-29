<?php
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $result = registerUser(
        $_POST['username'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );
    
    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Word Bomb</title>
    <link rel="stylesheet" href="auth-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="logo">💣 WORD BOMB</a>
                <h1>Join the Squad!</h1>
                <p>Create an account to save your scores and compete</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <?php echo implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="register-form">
                <div class="form-group">
                    <label for="username">
                        <span class="label-icon">👤</span>
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Choose a username"
                        required
                        minlength="3"
                        maxlength="20"
                        pattern="[a-zA-Z0-9_]+"
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                    <span class="input-hint">3-20 characters, letters, numbers, and underscores only</span>
                </div>

                <div class="form-group">
                    <label for="email">
                        <span class="label-icon">📧</span>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                            placeholder="Create a password"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            👁️
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <span class="label-icon">🔐</span>
                        Confirm Password
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm your password"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            👁️
                        </button>
                    </div>
                    <span class="input-hint" id="password-match"></span>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#" class="terms-link">Terms & Conditions</a>
                    </label>
                </div>

                <button type="submit" class="auth-btn">
                    <span class="btn-text">CREATE ACCOUNT</span>
                    <span class="btn-icon">🎮</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login Here</a></p>
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
    </div>

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

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthEl = document.getElementById('password-strength');
            let strength = 0;
            let text = '';
            let color = '';

            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    text = 'Weak';
                    color = '#ff4757';
                    break;
                case 2:
                case 3:
                    text = 'Medium';
                    color = '#ffa502';
                    break;
                case 4:
                case 5:
                    text = 'Strong';
                    color = '#2ed573';
                    break;
            }

            if (password.length > 0) {
                strengthEl.innerHTML = `<div class="strength-bar" style="width: ${strength * 20}%; background: ${color}"></div><span style="color: ${color}">${text}</span>`;
            } else {
                strengthEl.innerHTML = '';
            }
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirm = e.target.value;
            const matchEl = document.getElementById('password-match');

            if (confirm.length > 0) {
                if (password === confirm) {
                    matchEl.textContent = '✓ Passwords match';
                    matchEl.style.color = '#2ed573';
                } else {
                    matchEl.textContent = '✗ Passwords do not match';
                    matchEl.style.color = '#ff4757';
                }
            } else {
                matchEl.textContent = '';
            }
        });

        // Form validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }

            if (!terms) {
                e.preventDefault();
                alert('Please agree to the Terms & Conditions');
                return;
            }
        });
    </script>
</body>
</html>
