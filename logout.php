<?php
/**
 * File: logout.php
 * Mô tả: Xử lý đăng xuất khỏi hệ thống
 * Chức năng: Xóa session, cookie và chuyển hướng về trang chủ
 * Tác giả: @nguyendongho204
 * Ngày cập nhật: 2025-08-20
 */
require_once __DIR__ . '/includes/session_bootstrap.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: homepage.php?logout=1');
exit;
