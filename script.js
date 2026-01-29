// Word Bomb Game - Main JavaScript

class WordBombGame {
    constructor() {
        // Game state
        this.score = 0;
        this.streak = 0;
        this.bestStreak = 0;
        this.wordsDefused = 0;
        this.currentWord = '';
        this.scrambledWord = '';
        this.category = '';
        this.hint = '';
        this.timeLeft = 30;
        this.maxTime = 30;
        this.difficulty = 'medium';
        this.isPlaying = false;
        this.hintsUsed = 0;
        this.timer = null;
        this.soundEnabled = true;
        
        // User state
        this.isLoggedIn = false;
        this.currentUser = null;

        // DOM Elements
        this.screens = {
            start: document.getElementById('start-screen'),
            game: document.getElementById('game-screen'),
            gameover: document.getElementById('gameover-screen')
        };

        this.elements = {
            score: document.getElementById('score'),
            streak: document.getElementById('streak'),
            wordsDefused: document.getElementById('words-defused'),
            timerDisplay: document.getElementById('timer-display'),
            scrambledWord: document.getElementById('scrambled-word'),
            category: document.getElementById('category'),
            wordLength: document.getElementById('word-length'),
            guessInput: document.getElementById('guess-input'),
            hintDisplay: document.getElementById('hint-display'),
            feedback: document.getElementById('feedback'),
            bomb: document.getElementById('bomb'),
            explosion: document.getElementById('explosion'),
            fuseSpark: document.getElementById('fuse-spark')
        };

        // Audio context for sound effects
        this.audioContext = null;

        // Initialize
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadHighScores();
        this.checkAuthStatus();
        
        // Select default difficulty
        document.querySelector('[data-difficulty="medium"]').classList.add('selected');
    }

    async checkAuthStatus() {
        try {
            const response = await fetch('game_api.php?action=checkAuth');
            const data = await response.json();
            
            this.isLoggedIn = data.logged_in;
            this.currentUser = data.logged_in ? { id: data.user_id, username: data.username } : null;
            
            this.updateUserUI();
        } catch (error) {
            console.error('Error checking auth:', error);
        }
    }

    updateUserUI() {
        const guestView = document.getElementById('guest-view');
        const loggedInView = document.getElementById('logged-in-view');
        const usernameDisplay = document.getElementById('username-display');
        
        if (this.isLoggedIn && this.currentUser) {
            guestView.classList.add('hidden');
            loggedInView.classList.remove('hidden');
            usernameDisplay.textContent = this.currentUser.username;
        } else {
            guestView.classList.remove('hidden');
            loggedInView.classList.add('hidden');
        }
    }

    async logout() {
        try {
            const formData = new FormData();
            formData.append('action', 'logout');
            
            await fetch('auth.php', {
                method: 'POST',
                body: formData
            });
            
            this.isLoggedIn = false;
            this.currentUser = null;
            this.updateUserUI();
        } catch (error) {
            console.error('Error logging out:', error);
        }
    }

    bindEvents() {
        // Start button
        document.getElementById('start-btn').addEventListener('click', () => this.startGame());

        // Difficulty buttons
        document.querySelectorAll('.diff-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                this.difficulty = btn.dataset.difficulty;
                this.maxTime = parseInt(btn.dataset.time);
            });
        });

        // Submit guess
        document.getElementById('submit-btn').addEventListener('click', () => this.checkGuess());
        this.elements.guessInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.checkGuess();
        });

        // Hint button
        document.getElementById('hint-btn').addEventListener('click', () => this.showHint());

        // Skip button
        document.getElementById('skip-btn').addEventListener('click', () => this.skipWord());

        // Game over buttons
        document.getElementById('play-again-btn').addEventListener('click', () => this.startGame());
        document.getElementById('menu-btn').addEventListener('click', () => this.showScreen('start'));

        // Sound toggle
        document.getElementById('sound-toggle').addEventListener('click', () => this.toggleSound());
        
        // Logout button
        document.getElementById('logout-btn').addEventListener('click', () => this.logout());
    }

    showScreen(screenName) {
        Object.values(this.screens).forEach(screen => screen.classList.remove('active'));
        this.screens[screenName].classList.add('active');
    }

    async startGame() {
        this.score = 0;
        this.streak = 0;
        this.bestStreak = 0;
        this.wordsDefused = 0;
        this.hintsUsed = 0;
        this.isPlaying = true;

        this.updateDisplay();
        this.showScreen('game');
        this.elements.hintDisplay.classList.add('hidden');
        this.elements.feedback.textContent = '';
        this.elements.feedback.className = 'feedback';

        await this.loadNewWord();
        this.startTimer();
        this.elements.guessInput.focus();
        
        this.playSound('start');
    }

    async loadNewWord() {
        try {
            const response = await fetch(`api.php?action=getWord&difficulty=${this.difficulty}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentWord = data.word.toUpperCase();
                this.scrambledWord = data.scrambled.toUpperCase();
                this.category = data.category;
                this.hint = data.hint;
                
                this.elements.scrambledWord.textContent = this.scrambledWord;
                this.elements.category.textContent = `Category: ${this.category}`;
                this.elements.wordLength.textContent = `Word length: ${this.currentWord.length} letters`;
                this.elements.guessInput.value = '';
                this.elements.hintDisplay.classList.add('hidden');
                this.elements.feedback.textContent = '';
                this.elements.feedback.className = 'feedback';
                
                // Reset timer for new word
                this.timeLeft = this.maxTime;
                this.elements.timerDisplay.textContent = this.timeLeft;
                this.elements.bomb.classList.remove('danger');
            }
        } catch (error) {
            console.error('Error loading word:', error);
            // Fallback to local word if API fails
            this.loadLocalWord();
        }
    }

    loadLocalWord() {
        // Fallback words if PHP backend isn't available
        const fallbackWords = [
            { word: 'PUZZLE', category: 'Games', hint: 'A game that tests your mind' },
            { word: 'ROCKET', category: 'Space', hint: 'Takes astronauts to space' },
            { word: 'JUNGLE', category: 'Nature', hint: 'A dense tropical forest' },
            { word: 'CASTLE', category: 'Buildings', hint: 'Where kings and queens live' },
            { word: 'DRAGON', category: 'Fantasy', hint: 'A fire-breathing creature' },
            { word: 'PLANET', category: 'Space', hint: 'Earth is one of these' },
            { word: 'GUITAR', category: 'Music', hint: 'A stringed instrument' },
            { word: 'ZOMBIE', category: 'Horror', hint: 'The undead' },
            { word: 'WIZARD', category: 'Fantasy', hint: 'A magical person' },
            { word: 'PIRATE', category: 'Adventure', hint: 'Sails the seven seas' }
        ];

        const wordObj = fallbackWords[Math.floor(Math.random() * fallbackWords.length)];
        this.currentWord = wordObj.word;
        this.scrambledWord = this.scrambleWord(wordObj.word);
        this.category = wordObj.category;
        this.hint = wordObj.hint;

        this.elements.scrambledWord.textContent = this.scrambledWord;
        this.elements.category.textContent = `Category: ${this.category}`;
        this.elements.wordLength.textContent = `Word length: ${this.currentWord.length} letters`;
        this.elements.guessInput.value = '';
        this.elements.hintDisplay.classList.add('hidden');
        
        this.timeLeft = this.maxTime;
        this.elements.timerDisplay.textContent = this.timeLeft;
        this.elements.bomb.classList.remove('danger');
    }

    scrambleWord(word) {
        const arr = word.split('');
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        // Make sure scrambled word is different from original
        if (arr.join('') === word) {
            return this.scrambleWord(word);
        }
        return arr.join('');
    }

    startTimer() {
        this.elements.bomb.classList.add('ticking');
        
        this.timer = setInterval(() => {
            this.timeLeft--;
            this.elements.timerDisplay.textContent = this.timeLeft;

            // Danger mode when time is low
            if (this.timeLeft <= 5) {
                this.elements.bomb.classList.add('danger');
                this.playSound('tick');
            }

            // Time's up!
            if (this.timeLeft <= 0) {
                this.explode();
            }
        }, 1000);
    }

    stopTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        this.elements.bomb.classList.remove('ticking', 'danger');
    }

    checkGuess() {
        if (!this.isPlaying) return;

        const guess = this.elements.guessInput.value.toUpperCase().trim();
        
        if (!guess) return;

        if (guess === this.currentWord) {
            this.correctGuess();
        } else {
            this.wrongGuess();
        }
    }

    async correctGuess() {
        this.stopTimer();
        
        // Calculate score based on time left and streak
        const timeBonus = this.timeLeft * 10;
        const streakBonus = this.streak * 25;
        const basePoints = 100;
        const totalPoints = basePoints + timeBonus + streakBonus;
        
        this.score += totalPoints;
        this.streak++;
        this.wordsDefused++;
        
        if (this.streak > this.bestStreak) {
            this.bestStreak = this.streak;
        }

        // Visual feedback
        this.elements.guessInput.classList.add('correct');
        this.elements.feedback.textContent = `✓ DEFUSED! +${totalPoints} points`;
        this.elements.feedback.className = 'feedback correct';
        this.elements.bomb.classList.add('defused');
        
        this.playSound('correct');
        this.updateDisplay();

        // Wait and load next word
        setTimeout(async () => {
            this.elements.guessInput.classList.remove('correct');
            this.elements.bomb.classList.remove('defused');
            await this.loadNewWord();
            this.startTimer();
            this.elements.guessInput.focus();
        }, 1500);
    }

    wrongGuess() {
        this.elements.guessInput.classList.add('wrong');
        this.elements.feedback.textContent = '✗ Wrong! Try again!';
        this.elements.feedback.className = 'feedback wrong';
        
        this.playSound('wrong');

        setTimeout(() => {
            this.elements.guessInput.classList.remove('wrong');
            this.elements.guessInput.value = '';
            this.elements.guessInput.focus();
        }, 500);
    }

    showHint() {
        if (!this.isPlaying || this.hintsUsed >= 3) return;

        this.hintsUsed++;
        this.score = Math.max(0, this.score - 50);
        this.updateDisplay();

        // Progressive hints
        let hintText = '';
        if (this.hintsUsed === 1) {
            hintText = `💡 Hint: ${this.hint}`;
        } else if (this.hintsUsed === 2) {
            hintText = `💡 First letter: ${this.currentWord[0]}`;
        } else {
            hintText = `💡 Last letter: ${this.currentWord[this.currentWord.length - 1]}`;
        }

        this.elements.hintDisplay.textContent = hintText;
        this.elements.hintDisplay.classList.remove('hidden');
        
        this.playSound('hint');
    }

    async skipWord() {
        if (!this.isPlaying) return;

        this.score = Math.max(0, this.score - 100);
        this.streak = 0;
        this.stopTimer();
        this.updateDisplay();

        this.elements.feedback.textContent = `Skipped! The word was: ${this.currentWord}`;
        this.elements.feedback.className = 'feedback wrong';

        setTimeout(async () => {
            await this.loadNewWord();
            this.startTimer();
            this.elements.guessInput.focus();
        }, 2000);
    }

    explode() {
        this.stopTimer();
        this.isPlaying = false;

        // Explosion effects
        this.elements.bomb.style.display = 'none';
        this.elements.explosion.classList.remove('hidden');
        this.elements.explosion.classList.add('active');
        document.querySelector('.game-container').classList.add('screen-shake');
        
        this.playSound('explosion');

        // Show game over after explosion
        setTimeout(() => {
            this.elements.bomb.style.display = 'block';
            this.elements.explosion.classList.add('hidden');
            this.elements.explosion.classList.remove('active');
            document.querySelector('.game-container').classList.remove('screen-shake');
            this.showGameOver(false);
        }, 1500);
    }

    async showGameOver(survived = false) {
        const gameoverTitle = document.getElementById('gameover-title');
        const gameoverMessage = document.getElementById('gameover-message');
        
        if (survived) {
            gameoverTitle.textContent = '🎉 SURVIVED! 🎉';
            gameoverTitle.className = 'survived';
            gameoverMessage.textContent = 'Incredible! You defused all the bombs!';
        } else {
            gameoverTitle.textContent = '💥 BOOM! 💥';
            gameoverTitle.className = 'exploded';
            gameoverMessage.textContent = 'The bomb exploded!';
        }

        document.getElementById('final-score').textContent = this.score;
        document.getElementById('final-words').textContent = this.wordsDefused;
        document.getElementById('final-streak').textContent = `🔥 ${this.bestStreak}`;
        document.getElementById('correct-word').textContent = this.currentWord;

        // Save score to server if logged in
        await this.saveScoreToServer();
        
        this.saveHighScore();
        this.displayHighScores();
        this.showScreen('gameover');
    }

    async saveScoreToServer() {
        if (!this.isLoggedIn) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'saveScore');
            formData.append('score', this.score);
            formData.append('words_defused', this.wordsDefused);
            formData.append('best_streak', this.bestStreak);
            formData.append('difficulty', this.difficulty);
            formData.append('hints_used', this.hintsUsed);
            
            const response = await fetch('game_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.new_achievements && data.new_achievements.length > 0) {
                this.showAchievements(data.new_achievements);
            }
        } catch (error) {
            console.error('Error saving score:', error);
        }
    }

    showAchievements(achievements) {
        const popup = document.getElementById('achievement-popup');
        const content = document.getElementById('achievement-content');
        
        content.innerHTML = achievements.map(a => `
            <div class="achievement-item">
                <span class="achievement-icon">${a.icon}</span>
                <div class="achievement-info">
                    <div class="achievement-name">${a.name}</div>
                    <div class="achievement-desc">${a.description}</div>
                    <div class="achievement-points">+${a.points} points</div>
                </div>
            </div>
        `).join('');
        
        popup.classList.remove('hidden');
        this.playSound('correct');
    }

    updateDisplay() {
        this.elements.score.textContent = this.score;
        this.elements.streak.textContent = `🔥 ${this.streak}`;
        this.elements.wordsDefused.textContent = this.wordsDefused;
    }

    saveHighScore() {
        const highScores = JSON.parse(localStorage.getItem('wordBombHighScores') || '[]');
        
        highScores.push({
            score: this.score,
            words: this.wordsDefused,
            difficulty: this.difficulty,
            date: new Date().toLocaleDateString()
        });

        // Sort and keep top 5
        highScores.sort((a, b) => b.score - a.score);
        highScores.splice(5);

        localStorage.setItem('wordBombHighScores', JSON.stringify(highScores));
    }

    loadHighScores() {
        return JSON.parse(localStorage.getItem('wordBombHighScores') || '[]');
    }

    displayHighScores() {
        const highScores = this.loadHighScores();
        const container = document.getElementById('highscores-list');
        
        if (highScores.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No high scores yet!</p>';
            return;
        }

        container.innerHTML = highScores.map((hs, index) => `
            <div class="highscore-item">
                <span class="highscore-rank">#${index + 1}</span>
                <span class="highscore-score">${hs.score} pts</span>
                <span class="highscore-difficulty">${hs.difficulty}</span>
            </div>
        `).join('');
    }

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        const btn = document.getElementById('sound-toggle');
        btn.textContent = this.soundEnabled ? '🔊' : '🔇';
    }

    playSound(type) {
        if (!this.soundEnabled) return;

        // Initialize audio context on first sound
        if (!this.audioContext) {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }

        const ctx = this.audioContext;
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);

        switch(type) {
            case 'start':
                oscillator.frequency.setValueAtTime(440, ctx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.1);
                gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.2);
                break;
                
            case 'correct':
                oscillator.frequency.setValueAtTime(523, ctx.currentTime);
                oscillator.frequency.setValueAtTime(659, ctx.currentTime + 0.1);
                oscillator.frequency.setValueAtTime(784, ctx.currentTime + 0.2);
                gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.3);
                break;
                
            case 'wrong':
                oscillator.frequency.setValueAtTime(200, ctx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(100, ctx.currentTime + 0.2);
                gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.2);
                break;
                
            case 'tick':
                oscillator.frequency.setValueAtTime(800, ctx.currentTime);
                gainNode.gain.setValueAtTime(0.1, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.05);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.05);
                break;
                
            case 'explosion':
                // Create noise for explosion
                const bufferSize = ctx.sampleRate * 0.5;
                const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                const output = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) {
                    output[i] = Math.random() * 2 - 1;
                }
                const noise = ctx.createBufferSource();
                noise.buffer = buffer;
                
                const noiseGain = ctx.createGain();
                noise.connect(noiseGain);
                noiseGain.connect(ctx.destination);
                
                noiseGain.gain.setValueAtTime(0.5, ctx.currentTime);
                noiseGain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                
                noise.start(ctx.currentTime);
                noise.stop(ctx.currentTime + 0.5);
                return;
                
            case 'hint':
                oscillator.frequency.setValueAtTime(600, ctx.currentTime);
                oscillator.frequency.setValueAtTime(800, ctx.currentTime + 0.1);
                gainNode.gain.setValueAtTime(0.2, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.15);
                break;
        }
    }
}

// Initialize game when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.game = new WordBombGame();
});
