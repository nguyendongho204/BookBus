<?php
require_once __DIR__ . '/../libs/admin_guard.php';
require_once __DIR__ . '/../libs/db.php';

// Kiểm tra và xử lý form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $password1 = $_POST['password1'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Kiểm tra mật khẩu nhập lại có khớp không
    if ($password1 !== $password2) {
        header("Location: users_reset.php?id=$id&error=Mật khẩu nhập lại không khớp");
        exit;
    }

    // Mã hóa mật khẩu và cập nhật
    $hashed_password = password_hash($password1, PASSWORD_DEFAULT);
    
    // Sử dụng $pdo thay vì $db (từ file db.php)
    $st = $pdo->prepare("UPDATE daily_dangky SET password = :password WHERE id = :id");
    $st->bindValue(':password', $hashed_password, PDO::PARAM_STR);
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

    // Chuyển hướng về trang users với thông báo thành công
    header("Location: users.php?msg=Đặt lại mật khẩu thành công");
    exit;
} else {
    // Nếu không phải POST request, chuyển hướng về trang users
    header("Location: users.php");
    exit;
}
?>