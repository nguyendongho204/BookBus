<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $confirm_password = password_hash($_POST['confirm_password'], PASSWORD_DEFAULT);

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        echo "Mật khẩu không trùng khớp!";
        exit();
    }

    // Cập nhật mật khẩu mới
    $stmt = $db->prepare("UPDATE daily_dangky SET password = :password WHERE phone = :phone");
    $stmt->bindParam(':password', $new_password);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();

    // Quay về trang đăng nhập
    header("Location: index.php");
}
?>
