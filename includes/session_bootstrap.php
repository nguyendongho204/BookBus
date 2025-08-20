<?php
// includes/session_bootstrap.php - FINAL VERSION

// Define APP_BASE if not exists
if (!defined('APP_BASE')) {
    define('APP_BASE', '/src');
}

// ONLY set ini if session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set session configuration BEFORE starting
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    
    // Now start session
    session_start();
}

// Session security (only once)
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
?>