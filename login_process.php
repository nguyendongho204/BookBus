<?php
session_start();
require_once __DIR__ . '/libs/db.php';
require_once __DIR__ . '/libs/check_account_status.php'; // THÊM DÒNG NÀY

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    header('Location: login.php');
    exit;
}

try {
    // Kiểm tra user trong database
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, 
               COALESCE(role, 0) as role, 
               COALESCE(status, 1) as status, 
               deleted_at 
        FROM daily_dangky 
        WHERE email = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = 'Email hoặc mật khẩu không đúng.';
        header('Location: login.php');
        exit;
    }

    // Kiểm tra mật khẩu
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Email hoặc mật khẩu không đúng.';
        header('Location: login.php');
        exit;
    }

    // ===== KIỂM TRA KỸ TÀI KHOẢN BỊ KHÓA =====
    // Kiểm tra tài khoản có bị khóa không
    if ((int)$user['status'] === 0) {
        // Lưu thông tin tài khoản bị khóa vào session để hiển thị modal
        $_SESSION['account_locked'] = [
            'name' => $user['name'],
            'email' => $user['email']
        ];
        
        // QUAN TRỌNG: Log cho mục đích debug
        error_log("🔒 ACCOUNT LOCKED: User {$user['email']} (ID: {$user['id']}) attempted login but account is locked.");
        
        // QUAN TRỌNG: KHÔNG set $_SESSION['user'] cho tài khoản bị khóa
        header('Location: login.php?locked=1');
        exit;
    }

    // KIỂM TRA THÊM LẦN NỮA tài khoản không bị khóa
    if ((int)$user['status'] !== 1) {
        $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
        header('Location: login.php?error=account_locked');
        exit;
    }

    // ĐẢM BẢO KHÔNG ĐĂNG NHẬP ĐƯỢC NẾU TÀI KHOẢN BỊ XÓA
    if ($user['deleted_at'] !== null) {
        $_SESSION['login_error'] = 'Tài khoản không tồn tại.';
        header('Location: login.php?error=account_deleted');
        exit;
    }

    // Chỉ đăng nhập khi tài khoản active (status = 1) và chưa bị xóa (deleted_at = NULL)
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => (int)$user['role']
    ];

    // GHI LOG để debug
    error_log("✅ LOGIN SUCCESS: User {$user['email']} (ID: {$user['id']}) logged in successfully.");

    // Redirect based on role
    if ((int)$user['role'] === 1) {
        header('Location: src/admin/index.php');
    } else {
        header('Location: src/index.php');
    }
    exit;

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
    header('Location: login.php');
    exit;
}
?>