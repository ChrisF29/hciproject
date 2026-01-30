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
    <title>Register - Decrypt</title>
    <link rel="stylesheet" href="auth-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="logo">💣 DECRYPT</a>
                <h1>Join the Squad!</h1>
                <p>Create an account to save your scores and compete</p>
            </div>

            <!-- Breadcrumb Navigation -->
            <div class="breadcrumb-container">
                <div class="breadcrumb-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Account Info</div>
                </div>
                <div class="breadcrumb-line"></div>
                <div class="breadcrumb-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Security</div>
                </div>
                <div class="breadcrumb-line"></div>
                <div class="breadcrumb-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirm</div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <?php echo implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="register-form">
                <!-- Step 1: Account Info -->
                <div class="form-step active" data-step="1">
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

                    <div class="step-navigation">
                        <button type="button" class="auth-btn btn-next" onclick="nextStep()">
                            <span class="btn-text">NEXT</span>
                            <span class="btn-icon">→</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Security -->
                <div class="form-step" data-step="2">
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
                                minlength="8"
                                autocomplete="new-password"
                                oninput="checkStrength(this.value)"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                👁️
                            </button>
                        </div>
                        <ul class="requirements-list" id="reqList">
                            <li id="len"><span class="req-icon">○</span> 8+ Characters</li>
                            <li id="up"><span class="req-icon">○</span> Uppercase (A-Z)</li>
                            <li id="num"><span class="req-icon">○</span> Number (0-9)</li>
                            <li id="spec"><span class="req-icon">○</span> Symbol (!@#$)</li>
                        </ul>
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

                    <div class="step-navigation">
                        <button type="button" class="auth-btn btn-back" onclick="prevStep()">
                            <span class="btn-icon">←</span>
                            <span class="btn-text">BACK</span>
                        </button>
                        <button type="button" class="auth-btn btn-next" onclick="nextStep()">
                            <span class="btn-text">NEXT</span>
                            <span class="btn-icon">→</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Confirm -->
                <div class="form-step" data-step="3">
                    <div class="summary-section">
                        <h3>Review Your Information</h3>
                        <div class="summary-item">
                            <span class="summary-label">👤 Username:</span>
                            <span class="summary-value" id="summary-username">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">📧 Email:</span>
                            <span class="summary-value" id="summary-email">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">🔒 Password:</span>
                            <span class="summary-value">●●●●●●●●</span>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="#" class="terms-link">Terms & Conditions</a>
                        </label>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="auth-btn btn-back" onclick="prevStep()">
                            <span class="btn-icon">←</span>
                            <span class="btn-text">BACK</span>
                        </button>
                        <button type="submit" class="auth-btn btn-submit">
                            <span class="btn-text">CREATE ACCOUNT</span>
                            <span class="btn-icon">🎮</span>
                        </button>
                    </div>
                </div>
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
        // Multi-step form state
        let currentStep = 1;
        const totalSteps = 3;

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

        // Step navigation functions
        function nextStep() {
            if (!validateCurrentStep()) {
                return;
            }

            if (currentStep < totalSteps) {
                // Hide current step
                document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.add('completed');

                currentStep++;

                // Show next step
                document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.add('active');

                // Update summary on final step
                if (currentStep === 3) {
                    updateSummary();
                }

                // Smooth scroll to top
                document.querySelector('.auth-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                // Hide current step
                document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.remove('active');

                currentStep--;

                // Show previous step
                document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.remove('completed');
                document.querySelector(`.breadcrumb-step[data-step="${currentStep}"]`).classList.add('active');

                // Smooth scroll to top
                document.querySelector('.auth-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function validateCurrentStep() {
            const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
            const inputs = currentStepEl.querySelectorAll('input[required]');
            
            for (let input of inputs) {
                if (!input.value.trim()) {
                    input.focus();
                    showError(`Please fill in ${input.placeholder}`);
                    return false;
                }

                // Validate specific fields
                if (input.id === 'username' && !input.checkValidity()) {
                    input.focus();
                    showError('Username must be 3-20 characters with letters, numbers, and underscores only');
                    return false;
                }

                if (input.id === 'email' && !input.checkValidity()) {
                    input.focus();
                    showError('Please enter a valid email address');
                    return false;
                }

                if (input.id === 'password') {
                    const hasLength = input.value.length >= 8;
                    const hasUpper = /[A-Z]/.test(input.value);
                    const hasNumber = /[0-9]/.test(input.value);
                    const hasSpecial = /[^a-zA-Z0-9]/.test(input.value);

                    if (!hasLength || !hasUpper || !hasNumber || !hasSpecial) {
                        input.focus();
                        showError('Password must meet all security requirements');
                        return false;
                    }
                }

                if (input.id === 'confirm_password') {
                    const password = document.getElementById('password').value;
                    if (input.value !== password) {
                        input.focus();
                        showError('Passwords do not match');
                        return false;
                    }
                }
            }

            return true;
        }

        function updateSummary() {
            document.getElementById('summary-username').textContent = document.getElementById('username').value;
            document.getElementById('summary-email').textContent = document.getElementById('email').value;
        }

        function showError(message) {
            // Create temporary error notification
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-error temp-alert';
            errorDiv.innerHTML = `<span class="alert-icon">⚠️</span>${message}`;
            
            const authCard = document.querySelector('.auth-card');
            const header = authCard.querySelector('.auth-header');
            
            // Remove any existing temp alerts
            const existingAlert = authCard.querySelector('.temp-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            header.insertAdjacentElement('afterend', errorDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 300);
            }, 3000);
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Password strength checker with requirements
        function checkStrength(password) {
            const requirements = {
                len: password.length >= 8,
                up: /[A-Z]/.test(password),
                num: /[0-9]/.test(password),
                spec: /[^a-zA-Z0-9]/.test(password)
            };

            let strength = 0;
            
            // Update each requirement indicator
            Object.keys(requirements).forEach(key => {
                const met = requirements[key];
                const element = document.getElementById(key);
                if (element) {
                    const icon = element.querySelector('.req-icon');
                    if (met) {
                        element.classList.add('met');
                        if (icon) {
                            icon.textContent = '●';
                            icon.style.color = '#2ed573';
                        }
                        strength++;
                    } else {
                        element.classList.remove('met');
                        if (icon) {
                            icon.textContent = '○';
                            icon.style.color = '';
                        }
                    }
                }
            });

        }

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

            // Check password requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^a-zA-Z0-9]/.test(password);

            if (!hasLength || !hasUpper || !hasNumber || !hasSpecial) {
                e.preventDefault();
                showError('Password must meet all requirements: 8+ characters, uppercase letter, number, and special symbol');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                showError('Passwords do not match');
                return;
            }

            if (!terms) {
                e.preventDefault();
                showError('Please agree to the Terms & Conditions');
                return;
            }
        });

        // Allow Enter key to advance steps
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && currentStep < totalSteps) {
                e.preventDefault();
                const activeInput = document.activeElement;
                if (activeInput.tagName === 'INPUT' && activeInput.type !== 'checkbox') {
                    nextStep();
                }
            }
        });
    </script>
</body>
</html>
