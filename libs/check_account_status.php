<?php
// check_account_status.php - Middleware kiểm tra tài khoản bị khóa
if (!function_exists('checkAccountStatus')) {
    function checkAccountStatus() {
        // Nếu chưa đăng nhập thì bỏ qua
        if (!isset($_SESSION['user'])) {
            return;
        }

        // Include database connection
        require_once __DIR__ . '/db.php';

        $userId = $_SESSION['user']['id'];
        
        try {
            // Kiểm tra trạng thái tài khoản hiện tại
            $stmt = $pdo->prepare("
                SELECT COALESCE(status, 1) as status, deleted_at 
                FROM daily_dangky 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu không tìm thấy user hoặc bị xóa
            if (!$user || !empty($user['deleted_at'])) {
                session_destroy();
                
                // Dùng JavaScript redirect thay vì header() để tránh lỗi headers sent
                echo '<script>window.location.href = "../login.php?account_deleted=1";</script>';
                exit;
            }

            // Nếu tài khoản bị khóa
            if ((int)$user['status'] === 0) {
                // Lưu thông tin để hiện modal - SỬA CÁCH LẤY THÔNG TIN
                $_SESSION['account_locked'] = [
                    'name' => isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'User',
                    'email' => isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''
                ];
                
                // Xóa session đăng nhập
                session_destroy();
                
                // Dùng JavaScript redirect
                echo '<script>window.location.href = "../login.php?locked=1";</script>';
                exit;
            }

        } catch (Exception $e) {
            // Lỗi database, log và tiếp tục
            error_log("Check account status error: " . $e->getMessage());
        }
    }
}

// Chỉ gọi khi có session active và user đã đăng nhập
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
    checkAccountStatus();
}
?>