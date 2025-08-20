<?php
session_start();
require_once __DIR__ . '/src/libs/db.php';

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

    // Kiểm tra tài khoản có bị khóa không
    if ((int)$user['status'] === 0) {
        $_SESSION['account_locked'] = [
            'name' => $user['name'],
            'email' => $user['email']
        ];
        header('Location: login.php?locked=1');
        exit;
    }

    // Đăng nhập thành công
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => (int)$user['role']
    ];

    // Redirect based on role
    if ((int)$user['role'] === 1) {
        header('Location: src/admin/index.php');
    } else {
        header('Location: src/index.php');
    }
    exit;

} catch (Exception $e) {
    $_SESSION['login_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
    header('Location: login.php');
    exit;
}
?>