<?php
// logout.php
require_once __DIR__ . '/includes/session_bootstrap.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: /src/trangchu.php?logout=1'); // đổi thành '/trangchu.php' nếu không dùng /src
exit;
