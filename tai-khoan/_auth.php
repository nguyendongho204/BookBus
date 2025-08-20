<?php
// tai-khoan/auth.php - SIMPLE FIX

// Start session if not started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Define app_current_user function
function app_current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Get current user
$current_user = app_current_user();

// Redirect if not logged in
if (!$current_user) {
    header('Location: /src/trangchu.php?show=login&auth_required=1');
    exit;
}

// User is logged in, continue...
?>