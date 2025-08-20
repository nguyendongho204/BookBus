<?php
// /src/tai-khoan/_auth.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session_bootstrap.php';

// Luôn chuẩn hoá base về gốc project (/src)
$SITE_BASE = APP_BASE;
if (preg_match('#/(tai-khoan|admin|libs|includes)(/.*)?$#', $SITE_BASE)) {
    $SITE_BASE = rtrim(dirname($SITE_BASE), '/');
}
if ($SITE_BASE === '' || $SITE_BASE === '.') { $SITE_BASE = '/'; }

$user = app_current_user();

// Nếu chưa có user -> về trang chủ và mở popup đăng nhập
if (!$user) {
    $home = (is_file(dirname(__DIR__) . '/trangchu.php'))
        ? $SITE_BASE . '/trangchu.php'
        : $SITE_BASE . '/index.php';

    $back = $_SERVER['REQUEST_URI'] ?? ($SITE_BASE . '/tai-khoan/index.php');
    header('Location: ' . $home . '?show=login&redirect=' . urlencode($back) . '&reason=no_session');
    exit;
}

// Cho các file khác dùng sẵn
$userId = (int)$user['id'];
