<?php
// src/includes/auth_middleware.php
if (!session_id()) {
    session_start();
}

// Kiểm tra trạng thái tài khoản nếu đã đăng nhập
if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/../libs/check_account_status.php';
    $account_status = checkAccountStatus();
    
    if ($account_status === 'locked') {
        // Đã set session trong check_account_status.php
        error_log("User account is locked, modal will be shown");
    }
}
?>