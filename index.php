<?php
/**
 * Word Bomb Game - Main Entry Point
 * Requires authentication to play
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php?redirect=game');
    exit;
}

// Get current user data
$currentUser = getCurrentUser();

// Include the game HTML
include 'game.html';
?>
