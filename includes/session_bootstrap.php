<?php
// includes/session_bootstrap.php
declare(strict_types=1);

// ---- App paths ----
if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/..'));
}
if (!defined('APP_BASE')) {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if ($base === '') $base = '/';
    define('APP_BASE', $base);
}

// ---- Session setup ----
if (session_status() === PHP_SESSION_NONE) {
    session_name('BOOKBUSSESSID');

    // Dọn cookie legacy có path hẹp (nếu còn sót lại)
    foreach (['/src/tai-khoan','/src/tai-khoan/','/tai-khoan','/tai-khoan/'] as $p) {
        setcookie(session_name(), '', time() - 3600, $p);
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_start();
}

// ---- Chuẩn hoá key session người dùng ----
// Một số nơi đăng nhập có thể lưu dưới tên khác, ta đồng bộ về $_SESSION['user']
if (!isset($_SESSION['user'])) {
    if (!empty($_SESSION['khach_hang'])) {
        $k = $_SESSION['khach_hang'];
        $_SESSION['user'] = [
            'id'       => (int)($k['id'] ?? 0),
            'username' => $k['ten_dang_nhap'] ?? ($k['username'] ?? ''),
            'email'    => $k['email'] ?? null,
            'role'     => $k['vai_tro'] ?? ($k['role'] ?? null),
        ];
    } elseif (!empty($_SESSION['customer'])) {
        $k = $_SESSION['customer'];
        $_SESSION['user'] = [
            'id'       => (int)($k['id'] ?? 0),
            'username' => $k['username'] ?? '',
            'email'    => $k['email'] ?? null,
            'role'     => $k['role'] ?? null,
        ];
    }
}

// Tạo helper nhỏ để các file khác dùng
if (!function_exists('app_current_user')) {
    function app_current_user(): ?array {
        return (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) ? $_SESSION['user'] : null;
    }
}
