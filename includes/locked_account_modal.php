<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'libs/db.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, email, password, status FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Email hoặc mật khẩu không đúng.';
        header('Location: login.php');
        exit;
    }

    // Nếu tài khoản bị khóa
    if ((int)$user['status'] === 0) {
        $_SESSION['locked_account_info'] = [
            'name' => $user['name'],
            'email' => $user['email']
        ];
        $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
        header('Location: login.php?error=account_locked');
        exit;
    }

    $_SESSION['user'] = $user;
    header('Location: src/index.php');
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['login_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
    header('Location: login.php');
    exit;
}