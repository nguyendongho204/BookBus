<?php
// config.php - FIXED VERSION

// Define APP_BASE first
if (!defined('APP_BASE')) {
    define('APP_BASE', '/src');
}

// ⚠️ QUAN TRỌNG: Set session config TRƯỚC khi start session
if (session_status() === PHP_SESSION_NONE) {
    // Chỉ set ini nếu session chưa start
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_lifetime', 0);
    
    // Bây giờ mới start session
    session_start();
}

// Regenerate session ID để tránh session fixation (chỉ 1 lần)
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
?>