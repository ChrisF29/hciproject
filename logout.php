<?php
/**
 * Word Bomb Game - Logout Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth.php';

// Perform logout
logoutUser();

// Redirect to login page
header('Location: login.php');
exit;
?>
