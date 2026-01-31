<?php
/**
 * Word Bomb Game - API Handler
 * Handles word retrieval and game logic
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Include word database
require_once 'words.php';

/**
 * Scramble a word ensuring it's different from original
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
 * Get a random word based on difficulty and category
 */
function getRandomWord($difficulty = 'medium', $category = 'all') {
    global $wordDatabase;
    
    // Define word length ranges based on difficulty
    $lengthRanges = [
        'easy' => ['min' => 3, 'max' => 5],
        'medium' => ['min' => 5, 'max' => 7],
        'hard' => ['min' => 7, 'max' => 9],
        'extreme' => ['min' => 8, 'max' => 15]
    ];
    
    $range = $lengthRanges[$difficulty] ?? $lengthRanges['medium'];
    
    // Filter words by length and category
    $filteredWords = [];
    foreach ($wordDatabase as $wordData) {
        $wordLength = strlen($wordData['word']);
        $lengthMatch = $wordLength >= $range['min'] && $wordLength <= $range['max'];
        $categoryMatch = ($category === 'all' || strtolower($wordData['category']) === strtolower($category));
        
        if ($lengthMatch && $categoryMatch) {
            $filteredWords[] = $wordData;
        }
    }
    
    // If no words found with both filters, try category only
    if (empty($filteredWords) && $category !== 'all') {
        foreach ($wordDatabase as $wordData) {
            if (strtolower($wordData['category']) === strtolower($category)) {
                $filteredWords[] = $wordData;
            }
        }
    }
    
    // If still no words found, use all words
    if (empty($filteredWords)) {
        $filteredWords = $wordDatabase;
    }
    
    // Get random word
    $randomIndex = array_rand($filteredWords);
    $selectedWord = $filteredWords[$randomIndex];
    
    return [
        'word' => strtoupper($selectedWord['word']),
        'scrambled' => scrambleWord($selectedWord['word']),
        'category' => $selectedWord['category'],
        'hint' => $selectedWord['hint']
    ];
}

/**
 * Get all available categories
 */
function getCategories() {
    global $wordDatabase;
    $categories = array_unique(array_column($wordDatabase, 'category'));
    sort($categories);
    return $categories;
}

/**
 * Save high score
 */
function saveHighScore($score, $wordsDefused, $difficulty) {
    $scoresFile = 'highscores.json';
    
    $scores = [];
    if (file_exists($scoresFile)) {
        $content = file_get_contents($scoresFile);
        $scores = json_decode($content, true) ?? [];
    }
    
    $scores[] = [
        'score' => (int)$score,
        'words' => (int)$wordsDefused,
        'difficulty' => $difficulty,
        'date' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Sort by score descending and keep top 100
    usort($scores, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    $scores = array_slice($scores, 0, 100);
    
    file_put_contents($scoresFile, json_encode($scores, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Get high scores
 */
function getHighScores($limit = 10, $difficulty = null) {
    $scoresFile = 'highscores.json';
    
    if (!file_exists($scoresFile)) {
        return [];
    }
    
    $scores = json_decode(file_get_contents($scoresFile), true) ?? [];
    
    if ($difficulty) {
        $scores = array_filter($scores, function($s) use ($difficulty) {
            return $s['difficulty'] === $difficulty;
        });
    }
    
    return array_slice($scores, 0, $limit);
}

/**
 * Validate word guess
 */
function validateGuess($guess, $correctWord) {
    return strtoupper(trim($guess)) === strtoupper(trim($correctWord));
}

// Handle API requests
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'getWord':
        $difficulty = $_GET['difficulty'] ?? 'medium';
        $category = $_GET['category'] ?? 'all';
        $validDifficulties = ['easy', 'medium', 'hard', 'extreme'];
        
        if (!in_array($difficulty, $validDifficulties)) {
            $difficulty = 'medium';
        }
        
        $wordData = getRandomWord($difficulty, $category);
        
        echo json_encode([
            'success' => true,
            'word' => $wordData['word'],
            'scrambled' => $wordData['scrambled'],
            'category' => $wordData['category'],
            'hint' => $wordData['hint'],
            'difficulty' => $difficulty,
            'selectedCategory' => $category
        ]);
        break;
        
    case 'getCategories':
        $categories = getCategories();
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        break;
        
    case 'saveScore':
        $score = $_POST['score'] ?? 0;
        $wordsDefused = $_POST['words'] ?? 0;
        $difficulty = $_POST['difficulty'] ?? 'medium';
        
        $saved = saveHighScore($score, $wordsDefused, $difficulty);
        
        echo json_encode([
            'success' => $saved,
            'message' => $saved ? 'Score saved!' : 'Failed to save score'
        ]);
        break;
        
    case 'getHighScores':
        $limit = (int)($_GET['limit'] ?? 10);
        $difficulty = $_GET['difficulty'] ?? null;
        
        $scores = getHighScores($limit, $difficulty);
        
        echo json_encode([
            'success' => true,
            'scores' => $scores
        ]);
        break;
        
    case 'checkWord':
        $guess = $_POST['guess'] ?? '';
        $correct = $_POST['correct'] ?? '';
        
        $isCorrect = validateGuess($guess, $correct);
        
        echo json_encode([
            'success' => true,
            'correct' => $isCorrect
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action',
            'availableActions' => ['getWord', 'saveScore', 'getHighScores', 'checkWord']
        ]);
        break;
}
?>
