# 💣 Decrypt - Word Scramble Bomb Game

A thrilling web-based word scramble game where players must unscramble words before the bomb explodes! Built with HTML, CSS, JavaScript, and PHP.

![Game Preview](https://img.shields.io/badge/Status-Active-success) ![PHP](https://img.shields.io/badge/PHP-7.4+-blue) ![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)

## 🎮 Game Features

### Main Menu
- **Play Game** - Start a new game with customizable settings
- **Options** - Configure sound effects and theme preferences
- **Leaderboards** - View global rankings
- **How To Play** - Learn the game mechanics

### Game Setup (2-Step Process)
1. **Select Difficulty** - Choose your challenge level
2. **Select Category** - Pick a word category or play with all categories

### Core Gameplay
- **Word Scramble Challenge** - Unscramble words before time runs out
- **Bomb Timer** - Visual bomb with countdown timer that ticks and shakes
- **4 Difficulty Levels**:
  - 🟢 **Easy** - 30 seconds per word (short words)
  - 🟡 **Medium** - 20 seconds per word (balanced)
  - 🔴 **Hard** - 12 seconds per word (longer words)
  - 💀 **Extreme** - 7 seconds per word (ultimate challenge)
- **Scoring System** - Points based on speed, difficulty, and streaks
- **Streak Bonuses** - Build combos for bonus points
- **Hints System** - Get help at a point cost (-50 pts)
- **Skip Option** - Skip difficult words with penalty (-100 pts)

### 📚 Word Categories (22 Categories)
Play with words from specific categories:
- 🐾 **Animals** - From cats to hippopotamus
- 🍕 **Food** - Fruits, vegetables, dishes & more
- 💻 **Technology** - Gadgets, software & digital terms
- ⚽ **Sports** - From yoga to gymnastics
- 🌿 **Nature** - Mountains, oceans, weather phenomena
- 🔬 **Science** - Atoms, molecules & experiments
- 🎵 **Music** - Instruments & musical terms
- 🌍 **Countries** - Nations around the world
- 🐉 **Fantasy** - Dragons, wizards & mythical creatures
- 👨‍⚕️ **Professions** - Jobs and careers
- 🚀 **Space** - Planets, stars & cosmic objects
- 👕 **Clothing** - Apparel & accessories
- 🏰 **Buildings** - Structures & architecture
- 😊 **Emotions** - Feelings & moods
- 🎮 **Games** - Board games & entertainment
- 🚗 **Transportation** - Vehicles & travel
- 🌦️ **Weather** - Climate & meteorology
- 🏠 **Household** - Home items & furniture
- 🫀 **Body** - Anatomy & body parts
- 🎨 **Colors** - Color names
- 🔷 **Shapes** - Geometric shapes
- 🎲 **All Categories** - Random mix of everything

### Visual Effects
- 💥 Explosion animations when time runs out
- 🔥 Fuse spark effects on the bomb
- ✨ Screen shake on explosion
- 🎉 Success animations when word is guessed correctly
- 🌙/☀️ Dark/Light mode themes

### User System
- 🔐 User registration and login
- 📊 Score tracking and history
- 🏆 Global leaderboard
- 🎖️ Achievement system (18 achievements)
- 📈 Player statistics and profiles

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Fonts**: Google Fonts (Orbitron, Press Start 2P)
- **Audio**: Web Audio API for sound effects

## 📁 Project Structure

```
hciproject/
├── index.php           # Main entry point (auth wrapper)
├── game.html           # Game interface (all screens)
├── style.css           # Game styles (2200+ lines)
├── script.js           # Game logic (WordBombGame class)
├── api.php             # Word API endpoints
├── auth.php            # Authentication functions
├── auth-style.css      # Auth pages styles
├── login.php           # Login page
├── register.php        # Registration page
├── logout.php          # Logout handler
├── database.php        # Database setup & connection
├── game_api.php        # Game API endpoints
├── leaderboard.php     # Leaderboard page
├── words.php           # Word database (500+ words)
└── README.md           # This file
```

## 🚀 Installation

### Prerequisites
- XAMPP, WAMP, or similar PHP development environment
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone/Copy the project** to your web server directory:
   ```bash
   # For XAMPP
   cd C:\xampp\htdocs
   # Copy project folder here
   ```

2. **Start Apache and MySQL** from XAMPP Control Panel

3. **Initialize the database** by visiting:
   ```
   http://localhost/hciproject/database.php
   ```
   This will create the `wordbomb_game` database and all required tables.

4. **Access the game**:
   ```
   http://localhost/hciproject/
   ```

5. **Register an account** to start playing!

## 📊 Database Schema

### Tables
- **users** - User accounts and statistics
- **scores** - Individual game scores
- **leaderboard** - Aggregated player rankings
- **achievements** - Available achievements
- **user_achievements** - Unlocked achievements per user
- **daily_challenges** - Daily challenge system
- **sessions** - Remember me tokens

## 🎯 API Endpoints

### `api.php` (Word API)

| Action | Method | Parameters | Description |
|--------|--------|------------|-------------|
| `getWord` | GET | `difficulty`, `category` | Get a random word for the game |
| `getCategories` | GET | - | Get list of all categories |
| `saveScore` | POST | `score`, `words`, `difficulty` | Save game score |
| `getHighScores` | GET | `limit`, `difficulty` | Get high scores |
| `checkWord` | POST | `guess`, `correct` | Validate a guess |

### `game_api.php` (User API)

| Action | Method | Description |
|--------|--------|-------------|
| `getProfile` | GET | Get user profile data |
| `getLeaderboard` | GET | Get leaderboard data |
| `checkAuth` | GET | Check authentication status |
| `saveScore` | POST | Save score with achievements |

## 🎨 Theme Support

The game supports both **Dark Mode** and **Light Mode**:

- Access via **Options** menu from the main menu
- Theme preference is saved in localStorage
- Smooth transitions between themes
- All screens fully styled for both themes

## 🔊 Options

Configure your game experience:
- **Sound Effects** - Toggle game sounds ON/OFF
- **Dark Mode** - Toggle between dark and light themes

## 🏆 Achievements

Players can unlock 18 achievements including:
- 🎯 **First Blood** - Defuse your first word
- 🔥 **On Fire** - Get a 5 word streak
- ⚡ **Speed Demon** - Defuse a word in under 3 seconds
- 💯 **Century** - Score 100 points in a single game
- 👑 **Word Master** - Defuse 100 words total
- And many more!

## 🔧 Configuration

### Database Connection
Edit `database.php` to modify database credentials:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'wordbomb_game';
```

### Adding Words
Edit `words.php` to add new words to the game:
```php
$wordDatabase = [
    ['word' => 'example', 'category' => 'Category', 'hint' => 'A hint for the word'],
    // Add more words with category and hint...
];
```

Each word entry requires:
- `word` - The word to scramble
- `category` - Category for filtering
- `hint` - Hint shown when player uses hint button

## 🎵 Sound Effects

The game uses Web Audio API for procedurally generated sounds:
- ✅ Correct answer sound
- ❌ Wrong answer sound
- 💥 Explosion sound
- ⏰ Tick-tock timer sound
- 🎉 Achievement unlock sound

## 📱 Responsive Design

The game is fully responsive and works on:
- 🖥️ Desktop computers
- 💻 Laptops
- 📱 Tablets
- 📱 Mobile phones

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

Created as an HCI (Human-Computer Interaction) project.

---

## 📸 Screenshots

### Main Menu
- Clean 4-button interface
- Play Game, Options, Leaderboards, How To Play

### Game Setup
- Step 1: Choose difficulty (Easy/Medium/Hard/Extreme)
- Step 2: Choose category (22 options or All Categories)

### Gameplay
- Scrambled word display
- Bomb with countdown timer
- Score, streak, and words defused tracking
- Hint and skip buttons

### Game Over
- Final score display
- Words defused count
- Best streak achieved
- Play Again or return to Main Menu

---

**Enjoy the game! 💣🎮**
