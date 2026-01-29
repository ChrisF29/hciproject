# 💣 Decrypt - Word Scramble Bomb Game

A thrilling web-based word scramble game where players must unscramble words before the bomb explodes! Built with HTML, CSS, JavaScript, and PHP.

![Game Preview](https://img.shields.io/badge/Status-Active-success) ![PHP](https://img.shields.io/badge/PHP-7.4+-blue) ![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)

## 🎮 Game Features

### Core Gameplay
- **Word Scramble Challenge** - Unscramble words before time runs out
- **Bomb Timer** - Visual bomb with countdown timer that ticks and shakes
- **4 Difficulty Levels**:
  - 🟢 **Easy** - 30 seconds per word
  - 🟡 **Medium** - 20 seconds per word
  - 🔴 **Hard** - 12 seconds per word
  - 💀 **Extreme** - 7 seconds per word
- **Scoring System** - Points based on speed, difficulty, and streaks
- **Streak Bonuses** - Build combos for bonus points
- **Hints System** - Get help at a point cost
- **Skip Option** - Skip difficult words with penalty

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
├── game.html           # Game interface
├── style.css           # Game styles
├── script.js           # Game logic (WordBombGame class)
├── auth.php            # Authentication functions
├── auth-style.css      # Auth pages styles
├── login.php           # Login page
├── register.php        # Registration page
├── logout.php          # Logout handler
├── database.php        # Database setup & connection
├── game_api.php        # Game API endpoints
├── leaderboard.php     # Leaderboard page
├── words.php           # Word database
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

### `game_api.php`

| Action | Method | Description |
|--------|--------|-------------|
| `getWord` | GET | Get a random word for the game |
| `saveScore` | POST | Save game score |
| `getProfile` | GET | Get user profile data |
| `getLeaderboard` | GET | Get leaderboard data |
| `checkAuth` | GET | Check authentication status |

## 🎨 Theme Support

The game supports both **Dark Mode** and **Light Mode**:

- Click the 🌙/☀️ button in the top-right corner to toggle
- Theme preference is saved in localStorage
- Smooth transitions between themes

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
$words = [
    'category' => ['word1', 'word2', 'word3'],
    // Add more categories...
];
```

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

**Enjoy the game! 💣🎮**
