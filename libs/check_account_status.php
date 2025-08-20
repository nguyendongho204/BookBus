<?php
// libs/check_account_status.php - Kiểm tra tài khoản bị khóa
if (!function_exists('checkAccountStatus')) {
    function checkAccountStatus() {
        // Nếu chưa đăng nhập thì bỏ qua
        if (!isset($_SESSION['user'])) {
            return 'not_logged_in';
        }

        // Include database connection
        require_once __DIR__ . '/db.php';

        $userId = $_SESSION['user']['id'];
        
        try {
            // Kiểm tra trạng thái tài khoản hiện tại
            $stmt = $pdo->prepare("
                SELECT COALESCE(status, 1) as status, deleted_at, name, email 
                FROM daily_dangky 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu không tìm thấy user hoặc bị xóa
            if (!$user || !empty($user['deleted_at'])) {
                session_destroy();
                return 'deleted';
            }

            // Nếu tài khoản bị khóa (status = 0)
            if ((int)$user['status'] === 0) {
                $_SESSION['show_locked_modal'] = true;
                $_SESSION['locked_account_info'] = [
                    'name' => $user['name'],
                    'email' => $user['email']
                ];
                
                // Thêm dòng này để đăng xuất user
                unset($_SESSION['user']);
                
                return 'locked';
            }

        } catch (PDOException $e) {
            error_log("Lỗi kiểm tra trạng thái tài khoản: " . $e->getMessage());
        }
        
        return 'active';
    }
}
?>