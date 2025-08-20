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
        // Đã set session trong check_account_status.php và đã xóa session user
        error_log("User account is locked, modal will be shown");
        
        // Thêm redirect để tải lại trang
        // Sửa đường dẫn để đảm bảo chính xác
        if (strpos($_SERVER['PHP_SELF'], '/src/') !== false) {
            header('Location: /src/index.php?account_locked=1');
        } else {
            header('Location: /index.php?account_locked=1');
        }
        exit;
    }
    
    if ($account_status === 'deleted') {
        // Redirect về login nếu tài khoản bị xóa
        header('Location: /login.php?account_deleted=1');
        exit;
    }
}
?>