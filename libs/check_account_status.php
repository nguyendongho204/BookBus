<?php
function checkAccountStatus() {
    // Kiểm tra session user có tồn tại không
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        return 'not_logged_in';
    }
    
    global $pdo;
    
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, email, 
                   COALESCE(status, 1) as status, 
                   deleted_at 
            FROM daily_dangky 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Tài khoản không tồn tại
            unset($_SESSION['user']);
            $_SESSION['login_error'] = 'Tài khoản không tồn tại.';
            return 'not_found';
        }
        
        if ($user['deleted_at'] !== null) {
            // Tài khoản bị xóa
            unset($_SESSION['user']);
            $_SESSION['login_error'] = 'Tài khoản đã bị xóa.';
            return 'deleted';
        }
        
        if ((int)$user['status'] !== 1) {
            // Tài khoản bị khóa
            $_SESSION['locked_account_info'] = [
                'name' => $user['name'],
                'email' => $user['email']
            ];
            
            unset($_SESSION['user']); // QUAN TRỌNG: Xóa session user
            $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
            
            error_log("🔒 FORCE LOGOUT: User {$user['email']} (ID: {$user['id']}) account is locked.");
            
            return 'locked';
        }
        
        return 'active';
        
    } catch (Exception $e) {
        error_log('checkAccountStatus error: ' . $e->getMessage());
        return 'error';
    }
}
?>