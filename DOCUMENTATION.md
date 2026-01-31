# 📖 Decrypt (Word Bomb) - Complete Codebase Documentation

This document provides an exhaustive, in-depth explanation of every file in the Decrypt (Word Bomb) game project. Each file is thoroughly documented with its purpose, complete functionality breakdown, code explanations, and relationships with other files.

---

## 📋 Table of Contents

1. [Project Overview](#-project-overview)
2. [File Structure](#-file-structure)
3. [Core Game Files](#-core-game-files)
   - [index.php](#indexphp)
   - [game.html](#gamehtml)
   - [script.js](#scriptjs)
4. [API Files](#-api-files)
   - [api.php](#apiphp)
   - [game_api.php](#game_apiphp)
   - [words.php](#wordsphp)
5. [Authentication Files](#-authentication-files)
   - [auth.php](#authphp)
   - [login.php](#loginphp)
   - [register.php](#registerphp)
   - [logout.php](#logoutphp)
6. [Database Files](#-database-files)
   - [database.php](#databasephp)
   - [leaderboard.php](#leaderboardphp)
7. [Styling Files](#-styling-files)
   - [style.css](#stylecss)
   - [auth-style.css](#auth-stylecss)
8. [File Interactions & Data Flow](#-file-interactions--data-flow)
9. [Database Schema](#-database-schema)
10. [Security Considerations](#-security-considerations)
11. [Development Guide](#-development-guide)

---

## 🎮 Project Overview

**Decrypt (Word Bomb)** is a full-stack, real-time word scramble game built with modern web technologies. Players race against a ticking bomb timer to unscramble words, earning points for speed and accuracy while building streaks for bonus multipliers.

### Core Features
| Feature | Description |
|---------|-------------|
| **User Authentication** | Complete registration, login, logout with session management and "Remember Me" cookies |
| **4 Difficulty Levels** | Easy (30s, 3-5 letters), Medium (20s, 5-7 letters), Hard (12s, 7-9 letters), Extreme (7s, 8-15 letters) |
| **22 Word Categories** | Animals, Food, Technology, Sports, Nature, Science, Music, Countries, Fantasy, Professions, Space, Clothing, Buildings, Emotions, Games, Transportation, Weather, Household, Body, Colors, Shapes |
| **500+ Words** | Comprehensive word database with hints for each word |
| **Global Leaderboard** | Rankings with filtering by All Time, Weekly, Monthly, and by difficulty |
| **Achievement System** | 18 achievements tracking various accomplishments |
| **Dark/Light Themes** | Persistent theme preference with smooth transitions |
| **Sound Effects** | Web Audio API-generated sounds with toggle control |
| **Responsive Design** | Optimized for desktop, tablet, and mobile devices |

### Technology Stack
```
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND                                │
├─────────────────────────────────────────────────────────────┤
│  HTML5        │ Semantic markup, accessible forms           │
│  CSS3         │ CSS Variables, Flexbox, Grid, Animations    │
│  JavaScript   │ ES6+ Classes, Async/Await, Web Audio API    │
│  Fonts        │ Google Fonts (Orbitron, Press Start 2P)     │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                       BACKEND                                │
├─────────────────────────────────────────────────────────────┤
│  PHP 7.4+     │ Session management, PDO, JSON APIs          │
│  MySQL/MariaDB│ Relational database via XAMPP               │
│  Apache       │ Web server via XAMPP                        │
└─────────────────────────────────────────────────────────────┘
```

### Architecture Pattern
The application follows an **MVC-like** separation of concerns:
- **Model Layer**: `database.php`, `words.php` - Data structures and database operations
- **View Layer**: `game.html`, `login.php`, `register.php`, `leaderboard.php` - User interfaces
- **Controller Layer**: `api.php`, `game_api.php`, `auth.php` - Business logic and request handling

---

## 📁 File Structure

```
hciproject/
├── index.php           # Application entry point & authentication gate
├── game.html           # Complete game interface (7 screens)
├── script.js           # WordBombGame class - all game logic (693 lines)
├── style.css           # Complete game styling (2284 lines)
├── api.php             # Public word/game API (235 lines)
├── game_api.php        # Authenticated user/score API (386 lines)
├── words.php           # Word database array (674 lines, 500+ words)
├── auth.php            # Authentication functions (275 lines)
├── auth-style.css      # Auth pages styling
├── login.php           # Login page with form
├── register.php        # Multi-step registration wizard (496 lines)
├── logout.php          # Session termination handler
├── database.php        # Database config & table creation (262 lines)
├── leaderboard.php     # Global rankings page (248 lines)
├── README.md           # Project overview for GitHub
└── DOCUMENTATION.md    # This comprehensive guide
```

---

## 🎮 Core Game Files

---

### `index.php`

**Purpose:** Main entry point and authentication gateway for the game application.

**File Size:** ~25 lines

**What This File Does:**

This is the first file executed when a user visits the game. It acts as a security checkpoint, ensuring only authenticated users can access the game.

**Complete Code Breakdown:**

```php
<?php
/**
 * Word Bomb Game - Main Entry Point
 * Requires authentication to play
 */

// Start PHP session if not already started
// This is necessary for maintaining user login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include authentication functions
require_once 'auth.php';

// Security Check: Verify user is logged in
if (!isLoggedIn()) {
    // User not authenticated - redirect to login
    // The ?redirect=game parameter tells login.php where to go after successful login
    header('Location: login.php?redirect=game');
    exit;  // IMPORTANT: Always exit after header redirect
}

// User is authenticated - get their profile data
$currentUser = getCurrentUser();

// Load the game interface
// Using include (not include_once) allows game.html to access $currentUser
include 'game.html';
?>
```

**Key Concepts:**

1. **Session Management**: 
   - `session_status()` returns `PHP_SESSION_NONE`, `PHP_SESSION_DISABLED`, or `PHP_SESSION_ACTIVE`
   - Checking before `session_start()` prevents "session already started" warnings

2. **require_once vs include**:
   - `require_once 'auth.php'` - Fatal error if file missing (correct for dependencies)
   - `include 'game.html'` - Warning only (allows game.html to access PHP variables)

3. **Redirect with Exit**:
   - Always call `exit` after `header('Location: ...')` 
   - Without exit, PHP continues executing, potentially showing restricted content

**File Dependencies:**
- **Requires**: `auth.php` (for `isLoggedIn()` and `getCurrentUser()` functions)
- **Includes**: `game.html` (the actual game interface)

**Data Flow:**
```
User Request → index.php
                  │
                  ├─► Check session (auth.php)
                  │         │
                  │         ├─► Not logged in → Redirect to login.php
                  │         │
                  │         └─► Logged in → Get user data
                  │                              │
                  └─────────────────────────────►│
                                                 ▼
                                            Load game.html
                                        (with $currentUser data)
```

---

### `game.html`

**Purpose:** The complete game interface containing all visual elements and screen layouts.

**File Size:** 383 lines

**What This File Does:**

This HTML file defines the entire user interface for the game. It contains 7 distinct "screens" that are shown/hidden via JavaScript, creating a single-page application experience.

**Complete Structure Breakdown:**

#### Document Head
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Bomb - Defuse the Scramble!</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
```

**Key Elements:**
- `viewport` meta tag enables responsive design on mobile
- Two Google Fonts loaded: **Orbitron** (futuristic, for headings) and **Press Start 2P** (pixel font, for accents)

#### User Bar
```html
<div id="user-bar" class="user-bar">
    <div id="logged-in-view" class="user-section">
        <span id="user-welcome" class="user-welcome">
            👤 Welcome, <span id="username-display"><?php echo htmlspecialchars($currentUser['username']); ?></span>!
        </span>
        <a href="logout.php" class="user-btn logout-btn">🚪 Logout</a>
    </div>
</div>
```

**Key Concepts:**
- **PHP Embedded in HTML**: The `<?php echo ... ?>` displays the username from `$currentUser` (set in index.php)
- **XSS Prevention**: `htmlspecialchars()` escapes HTML characters to prevent injection attacks
- **Direct Logout Link**: Clicking logout navigates to `logout.php` which destroys the session

#### Screen 1: Main Menu
```html
<div id="main-menu-screen" class="screen active">
    <div class="title-container">
        <h1 class="game-title">💣 Decrypt 💣</h1>
        <p class="subtitle">Defuse the scramble before it's too late!</p>
    </div>
    
    <div class="main-menu-buttons">
        <button id="play-game-btn" class="menu-btn play-btn">
            <span class="menu-icon">🎮</span>
            <span class="menu-text">Play Game</span>
        </button>
        <button id="options-btn" class="menu-btn options-btn">
            <span class="menu-icon">⚙️</span>
            <span class="menu-text">Options</span>
        </button>
        <button id="leaderboards-btn" class="menu-btn leaderboards-btn" onclick="window.location.href='leaderboard.php'">
            <span class="menu-icon">🏆</span>
            <span class="menu-text">Leaderboards</span>
        </button>
        <button id="how-to-play-btn" class="menu-btn howto-btn">
            <span class="menu-icon">❓</span>
            <span class="menu-text">How To Play</span>
        </button>
    </div>
</div>
```

**Key Concepts:**
- **Active Class**: `class="screen active"` - Only one screen has "active" at a time, controlled by CSS `display: none/block`
- **Button IDs**: Each button has a unique ID for JavaScript event binding
- **Inline onclick vs Event Listeners**: Leaderboards uses inline `onclick` for simple navigation, others use JS event listeners for complex actions

#### Screen 2: Difficulty Selection (Step 1 of 2)
```html
<div id="difficulty-screen" class="screen">
    <div class="setup-header">
        <button id="diff-back-btn" class="back-btn">← Back</button>
        <h2 class="setup-title">Select Difficulty</h2>
        <div class="step-indicator">Step 1 of 2</div>
    </div>
    
    <div class="difficulty-selector">
        <div class="difficulty-buttons">
            <button class="diff-btn" data-difficulty="easy" data-time="30">
                <span class="diff-icon">🟢</span>
                <span class="diff-name">Easy</span>
                <span class="diff-time">30 seconds</span>
                <span class="diff-desc">Short words, more time</span>
            </button>
            <!-- Medium, Hard, Extreme buttons follow same pattern -->
        </div>
    </div>
    
    <div class="setup-nav-buttons">
        <button id="diff-next-btn" class="main-btn">NEXT →</button>
    </div>
</div>
```

**Key Concepts:**
- **Data Attributes**: `data-difficulty="easy"` and `data-time="30"` store configuration without extra JavaScript variables
- **Selected State**: JavaScript adds `.selected` class when user clicks a button
- **Step Indicator**: Visual progress through the 2-step setup process

#### Screen 3: Category Selection (Step 2 of 2)

Contains 22 category buttons with icons for Animals, Food, Technology, Sports, Nature, Science, Music, Countries, Fantasy, Professions, Space, Clothing, Buildings, Emotions, Games, Transportation, Weather, Household, Body, Colors, Shapes, and "All Categories".

#### Screen 4: Options
```html
<div id="options-screen" class="screen">
    <div class="options-container">
        <div class="option-item">
            <span class="option-label">🔊 Sound Effects</span>
            <button id="sound-option-toggle" class="option-toggle active">ON</button>
        </div>
        <div class="option-item">
            <span class="option-label">🌙 Dark Mode</span>
            <button id="theme-option-toggle" class="option-toggle active">ON</button>
        </div>
    </div>
</div>
```

#### Screen 5: How To Play

Contains 6 instruction cards explaining: Unscramble Words, Type & Submit, Use Hints, Skip Words, Build Streaks, Speed Bonus.

#### Screen 6: Active Game
```html
<div id="game-screen" class="screen">
    <!-- Score Header -->
    <div class="game-header">
        <div class="score-display"><span id="score">0</span></div>
        <div class="streak-display"><span id="streak">🔥 0</span></div>
        <div class="words-display"><span id="words-defused">0</span></div>
    </div>

    <!-- Bomb Visual with Timer -->
    <div class="bomb-container">
        <div id="bomb" class="bomb">
            <div class="bomb-timer-display"><span id="timer-display">30</span></div>
        </div>
        <div id="explosion" class="explosion hidden"></div>
    </div>

    <!-- Word Display -->
    <div class="word-area">
        <span id="category">Category: Loading...</span>
        <div id="scrambled-word" class="scrambled-word">LOADING</div>
        <span id="word-length">Word length: 0 letters</span>
    </div>

    <!-- Input and Controls -->
    <input type="text" id="guess-input" placeholder="Type your guess...">
    <button id="submit-btn">DEFUSE!</button>
    <button id="hint-btn">💡 HINT (-50 pts)</button>
    <button id="skip-btn">⏭️ SKIP (-100 pts)</button>
</div>
```

#### Screen 7: Game Over

Shows final score, words defused, best streak, the correct word, achievement popups, local high scores, and Play Again/Main Menu buttons.

**File Dependencies:**
- **style.css** - All visual styling
- **script.js** - Game logic and event handling
- **Google Fonts** - External font resources
- **index.php** - Provides `$currentUser` variable

---

### `script.js`

**Purpose:** Core game engine implementing the `WordBombGame` class with all game logic.

**File Size:** 693 lines

**What This File Does:**

This JavaScript file contains the entire game logic organized as an ES6 class. It handles game state management, screen navigation, API communication, timer system, scoring system, sound generation, and user preferences.

**Class Properties:**

```javascript
class WordBombGame {
    constructor() {
        // === GAME STATE ===
        this.score = 0;              // Current game score
        this.streak = 0;             // Current consecutive correct answers
        this.bestStreak = 0;         // Best streak this game
        this.wordsDefused = 0;       // Total words solved this game
        this.currentWord = '';       // The correct answer (uppercase)
        this.scrambledWord = '';     // The scrambled version shown to player
        this.category = '';          // Current word's category
        this.hint = '';              // Current word's hint text
        this.timeLeft = 30;          // Seconds remaining
        this.maxTime = 30;           // Max time based on difficulty
        this.difficulty = 'medium';  // Selected difficulty
        this.selectedCategory = 'all'; // Selected category filter
        this.isPlaying = false;      // Is game currently active
        this.hintsUsed = 0;          // Hints used this game (max 3)
        this.timer = null;           // Reference to setInterval
        this.soundEnabled = true;    // Sound effects toggle
        
        // === USER STATE ===
        this.isLoggedIn = false;
        this.currentUser = null;

        // === DOM ELEMENT REFERENCES ===
        this.screens = { /* 7 screen elements */ };
        this.elements = { /* All interactive elements */ };

        // === AUDIO ===
        this.audioContext = null;  // Web Audio API context
    }
}
```

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `init()` | Initialize game, bind events, load preferences |
| `bindEvents()` | Attach click/keypress handlers to all buttons |
| `showScreen(name)` | Switch between the 7 game screens |
| `startGame()` | Reset state, load first word, start timer |
| `loadNewWord()` | Fetch word from api.php with difficulty/category |
| `checkGuess()` | Validate user's answer |
| `correctGuess()` | Handle correct answer (+points, +streak, next word) |
| `wrongGuess()` | Handle incorrect answer (shake, clear input) |
| `showHint()` | Reveal hint (-50 points, max 3 per word) |
| `skipWord()` | Skip to next word (-100 points, reset streak) |
| `startTimer()` | Begin countdown with danger mode at 5 seconds |
| `stopTimer()` | Pause countdown |
| `explode()` | Handle time running out (explosion animation) |
| `showGameOver()` | Display results, save score, show achievements |
| `saveScoreToServer()` | POST score to game_api.php |
| `playSound(type)` | Generate sound using Web Audio API |

**Scoring System:**
```javascript
// Points per correct answer:
const basePoints = 100;                    // Base points
const timeBonus = this.timeLeft * 10;      // +10 per second remaining
const streakBonus = this.streak * 25;      // +25 per streak count
const totalPoints = basePoints + timeBonus + streakBonus;

// Penalties:
// Hint: -50 points (max 3 hints per word)
// Skip: -100 points + streak reset
```

**Sound System (Web Audio API):**
```javascript
playSound(type) {
    if (!this.soundEnabled) return;
    
    // Lazy initialize AudioContext
    if (!this.audioContext) {
        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    
    // Create oscillator and gain nodes
    const oscillator = ctx.createOscillator();
    const gainNode = ctx.createGain();
    
    switch(type) {
        case 'correct':   // Rising three-note chord
        case 'wrong':     // Descending buzz
        case 'tick':      // Short high beep
        case 'explosion': // White noise burst
        case 'hint':      // Two-tone blip
        case 'start':     // Rising tone
    }
}
```

**File Dependencies:**
- **game.html** - Provides all DOM elements
- **api.php** - Word retrieval endpoint
- **game_api.php** - Score saving endpoint
- **localStorage** - Theme/sound preferences, local high scores

---

## 🔌 API Files

---

### `api.php`

**Purpose:** Public API for word retrieval and basic game operations.

**File Size:** 235 lines

**Endpoints:**

| Action | Method | Parameters | Response |
|--------|--------|------------|----------|
| `getWord` | GET | `difficulty`, `category` | `{word, scrambled, category, hint}` |
| `getCategories` | GET | - | `{categories: [...]}` |
| `saveScore` | POST | `score`, `words`, `difficulty` | `{success: bool}` |
| `getHighScores` | GET | `limit`, `difficulty` | `{scores: [...]}` |
| `checkWord` | POST | `guess`, `correct` | `{correct: bool}` |

**Word Filtering Logic:**
```php
function getRandomWord($difficulty = 'medium', $category = 'all') {
    global $wordDatabase;
    
    // Word length requirements per difficulty
    $lengthRanges = [
        'easy' => ['min' => 3, 'max' => 5],     // 3-5 letters
        'medium' => ['min' => 5, 'max' => 7],   // 5-7 letters
        'hard' => ['min' => 7, 'max' => 9],     // 7-9 letters
        'extreme' => ['min' => 8, 'max' => 15]  // 8-15 letters
    ];
    
    // Filter by BOTH length AND category
    // Falls back to category only, then all words
}
```

**Word Scrambling:**
```php
function scrambleWord($word) {
    $word = strtoupper($word);
    $chars = str_split($word);
    
    // Shuffle until different from original
    do {
        shuffle($chars);
        $scrambled = implode('', $chars);
    } while ($scrambled === $word);
    
    return $scrambled;
}
```

---

### `game_api.php`

**Purpose:** Authenticated API for user-related game operations.

**File Size:** 386 lines

**What This File Does:**

This API requires user authentication and handles all database operations related to user progress, including saving scores, checking achievements, and updating leaderboards.

**Endpoints:**

| Action | Method | Auth Required | Description |
|--------|--------|---------------|-------------|
| `checkAuth` | GET | No | Check login status |
| `saveScore` | POST | Yes | Save score + check achievements |
| `getProfile` | GET | Yes | Get user profile data |
| `getLeaderboard` | GET | No | Get rankings |

**Score Saving Process:**
1. Insert new record into `scores` table
2. Update `users` table statistics (total_games, best_score, etc.)
3. Update `leaderboard` table aggregates
4. Recalculate all player rankings
5. Check and award any new achievements
6. Return success + any new achievements

**Achievement Checking:**
```php
function checkAchievements($userId, $score, $wordsDefused, $streak, $difficulty, $hintsUsed) {
    // Check each achievement type:
    // - words_defused: Words in THIS game
    // - total_words: Lifetime words
    // - single_score: Score in THIS game
    // - streak: Streak in THIS game
    // - total_games: Lifetime games
    // - games_easy/medium/hard/extreme: Games at difficulty
    // - no_hints: Completed without hints
    
    // Award and return newly unlocked achievements
}
```

---

### `words.php`

**Purpose:** Word database containing 500+ words with categories and hints.

**File Size:** 674 lines

**Structure:**
```php
$wordDatabase = [
    // Each word has: word, category, hint
    ['word' => 'cat', 'category' => 'Animals', 'hint' => 'A furry pet that meows'],
    ['word' => 'elephant', 'category' => 'Animals', 'hint' => 'Large animal with a trunk'],
    ['word' => 'pizza', 'category' => 'Food', 'hint' => 'Italian dish with toppings'],
    // ... 500+ more words
];
```

**22 Categories:**
Animals, Food, Technology, Sports, Nature, Science, Music, Countries, Fantasy, Professions, Space, Clothing, Buildings, Emotions, Games, Transportation, Weather, Household, Body, Colors, Shapes

---

## 🔐 Authentication Files

---

### `auth.php`

**Purpose:** Core authentication logic and user management functions.

**File Size:** 275 lines

**Key Functions:**

| Function | Purpose |
|----------|---------|
| `registerUser($username, $email, $password, $confirmPassword)` | Create new account with validation |
| `loginUser($username, $password, $remember)` | Authenticate and create session |
| `logoutUser()` | Destroy session and cookies |
| `isLoggedIn()` | Check session or remember token |
| `getCurrentUser()` | Get logged-in user's profile |

**Password Security:**
```php
// Registration: Hash password with bcrypt
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Login: Verify against stored hash
if (password_verify($password, $user['password'])) {
    // Valid password
}
```

**Remember Me Implementation:**
```php
// On login with remember=true:
$token = bin2hex(random_bytes(32));  // 64-char secure token
// Store in database with 30-day expiry
// Set HTTP-only cookie

// On page load:
// Check for remember_token cookie
// Look up in database
// Restore session if valid and not expired
```

---

### `login.php`

**Purpose:** User login page with form and validation.

**Features:**
- Username OR email login
- Password field with show/hide toggle
- Remember me checkbox
- Redirect back to game after login
- Error/success message display
- Link to registration
- Theme toggle

---

### `register.php`

**Purpose:** Multi-step registration wizard.

**File Size:** 496 lines

**3-Step Process:**
1. **Account Info** - Username (3-20 chars, alphanumeric), Email
2. **Security** - Password with strength indicator (8+ chars, uppercase, number, symbol)
3. **Confirm** - Review and submit

**Real-time Validation:**
- Username availability check (AJAX)
- Email format validation
- Password strength meter with visual indicators
- Password match confirmation

---

### `logout.php`

**Purpose:** Session termination handler.

**File Size:** 17 lines

**What It Does:**
1. Start session (to access it)
2. Call `logoutUser()` (clears session, cookies, database token)
3. Redirect to login page

---

## 🗄️ Database Files

---

### `database.php`

**Purpose:** Database configuration, connection management, and schema creation.

**File Size:** 262 lines

**Configuration:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty by default in XAMPP
define('DB_NAME', 'wordbomb_game');
```

**Connection (Singleton Pattern):**
```php
function getDBConnection() {
    static $conn = null;  // Persists across calls
    
    if ($conn === null) {
        $conn = new PDO(/* connection string */);
        // Auto-create database if not exists
    }
    
    return $conn;
}
```

**Database Tables:**

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | id, username, email, password, best_score, total_games |
| `scores` | Individual game records | user_id, score, words_defused, difficulty, played_at |
| `leaderboard` | Aggregated rankings | user_id, best_score, rank_position, average_score |
| `achievements` | Achievement definitions | name, description, icon, requirement_type, requirement_value |
| `user_achievements` | Earned achievements | user_id, achievement_id, unlocked_at |
| `sessions` | Remember me tokens | user_id, token, expires_at |
| `daily_challenges` | Daily challenge system | user_id, challenge_date, score, completed |

**18 Default Achievements:**
- First Defuse, Bomb Squad Rookie/Pro/Elite
- First Century, High Scorer, Score Master
- Streak Starter, On Fire, Unstoppable
- Speed Demon, Easy/Medium/Hard/Extreme Mode Master
- Dedicated Player, No Hints Needed, Daily Warrior

**Auto-Setup:**
Visit `database.php` directly to create all tables:
```
http://localhost/hciproject/database.php
```

---

### `leaderboard.php`

**Purpose:** Global leaderboard page with multiple filter options.

**File Size:** 248 lines

**Filter Options:**
| Filter | Query Type |
|--------|------------|
| All Time | Best scores from leaderboard table |
| Weekly | Sum of scores in last 7 days |
| Monthly | Sum of scores in last 30 days |
| Easy/Medium/Hard/Extreme | Best scores at specific difficulty |

**Features:**
- User profile card (if logged in) showing rank and stats
- Filter tab buttons with active state
- Top 100 players with gold/silver/bronze styling for top 3
- Current user row highlighting
- Back to game button
- Theme toggle

---

## 🎨 Styling Files

---

### `style.css`

**Purpose:** Complete visual styling for the game interface.

**File Size:** 2284 lines

**Structure:**
1. **CSS Variables** - 50+ custom properties for colors, shadows, transitions
2. **Dark Mode** (default) and **Light Mode** themes
3. **Animated Background** - Gradient overlays, floating particles
4. **Screen System** - Show/hide screens with fade transitions
5. **Main Menu** - Button grid with hover effects
6. **Setup Screens** - Difficulty and category selectors
7. **Game Screen** - Bomb visual, timer, input area
8. **Animations** - 15+ keyframe animations
9. **Responsive Design** - Breakpoints at 768px and 480px

**Key Animations:**
```css
@keyframes titleGlow { /* Pulsing text shadow */ }
@keyframes bombShake { /* Danger shake effect */ }
@keyframes explosionExpand { /* Explosion scaling */ }
@keyframes fuseGlow { /* Fuse spark effect */ }
@keyframes fadeSlideIn { /* Screen transitions */ }
```

---

### `auth-style.css`

**Purpose:** Styling for authentication pages (login, register, leaderboard).

**Key Components:**
- Centered auth card with glassmorphism effect
- Form inputs with icon labels
- Multi-step wizard breadcrumbs
- Alert messages (error/success)
- Floating decorative bomb emojis
- Password strength indicator bars
- Responsive layouts

---

## 🔄 File Interactions & Data Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              USER BROWSER                                │
│  ┌─────────────────────────────────────────────────────────────────────┐│
│  │  game.html + script.js + style.css                                  ││
│  │  (Frontend: HTML structure, game logic, visual styling)             ││
│  └─────────────────────────────────────────────────────────────────────┘│
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐       ┌───────────────┐       ┌───────────────┐
│   api.php     │       │ game_api.php  │       │   auth.php    │
│               │       │               │       │               │
│ • getWord     │       │ • saveScore   │       │ • login       │
│ • getCategory │       │ • getProfile  │       │ • register    │
│ • checkWord   │       │ • achievements│       │ • logout      │
└───────┬───────┘       └───────┬───────┘       └───────┬───────┘
        │                       │                       │
        ▼                       ▼                       ▼
┌───────────────┐       ┌───────────────────────────────────────┐
│  words.php    │       │            database.php               │
│               │       │                                       │
│ 500+ words    │       │ MySQL Connection + Tables:            │
│ 22 categories │       │ • users • scores • leaderboard        │
│               │       │ • achievements • user_achievements    │
└───────────────┘       │ • sessions • daily_challenges         │
                        └───────────────────────────────────────┘
```

**Request Flow - Starting a Game:**
```
1. User clicks "Play Game" → script.js: showScreen('difficulty')
2. User selects difficulty → script.js: this.difficulty = 'hard'
3. User clicks "Next" → script.js: showScreen('category')
4. User selects category → script.js: this.selectedCategory = 'Animals'
5. User clicks "Start Game" → script.js: startGame()
   └─► loadNewWord()
       └─► fetch('api.php?action=getWord&difficulty=hard&category=Animals')
           └─► api.php: getRandomWord() → words.php filter
           └─► Returns: {word: 'ELEPHANT', scrambled: 'TNHPEALE', ...}
       └─► Display word, start timer
```

**Request Flow - Saving Score:**
```
1. Timer reaches 0 → script.js: explode()
2. Show explosion animation → script.js: showGameOver()
3. Save score → fetch('game_api.php', {action: 'saveScore', ...})
   └─► game_api.php: saveGameScore()
       ├─► INSERT INTO scores
       ├─► UPDATE users (stats)
       ├─► UPDATE leaderboard (rankings)
       └─► checkAchievements() → Return new achievements
4. Display achievements → script.js: showAchievements()
```

---

## 🔒 Security Considerations

| Security Measure | Implementation |
|------------------|----------------|
| **Password Hashing** | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt) |
| **SQL Injection** | PDO prepared statements with parameter binding |
| **XSS Prevention** | `htmlspecialchars()` on all user output |
| **Session Security** | HTTP-only cookies, proper session handling |
| **Input Validation** | Server-side validation for all inputs |
| **Remember Me** | Cryptographically secure tokens (`random_bytes(32)`) |

---

## 🛠️ Development Guide

### Adding New Words
Edit `words.php`:
```php
['word' => 'newword', 'category' => 'Category', 'hint' => 'A helpful hint'],
```

### Adding New Categories
1. Add words with new category name in `words.php`
2. Add button in `game.html` (category-screen):
```html
<button class="cat-btn" data-category="NewCategory">
    <span class="cat-icon">🆕</span>
    <span class="cat-name">New Category</span>
</button>
```

### Adding New Achievements
1. Insert in `database.php` `insertDefaultAchievements()`:
```php
['Achievement Name', 'Description', '🏅', 'requirement_type', value, points],
```
2. Add logic in `game_api.php` `checkAchievements()` if new requirement type

### Modifying Difficulty
1. `game.html`: Update button `data-time` attribute
2. `api.php`: Update `$lengthRanges` array

### Testing Without Database
The game includes fallback words in `script.js` `loadLocalWord()` that work without database connection.

---

## 📝 Version History

| Version | Changes |
|---------|---------|
| 1.0 | Basic gameplay with timer and scoring |
| 1.1 | Added category selection system |
| 1.2 | Main menu with Options and How To Play |
| 1.3 | 500+ words, 22 categories, full documentation |

---

*Documentation last updated: January 31, 2026*
*Total project files: 16*
*Total lines of code: ~6,000*
