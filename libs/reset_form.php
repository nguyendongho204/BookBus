<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $otp = $_POST['otp'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Kiểm tra OTP hợp lệ
    $query = $db->prepare("SELECT phone FROM password_reset WHERE phone = :phone AND token = :otp");
    $query->bindParam(':phone', $phone);
    $query->bindParam(':otp', $otp);
    $result = $query->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        // Cập nhật mật khẩu mới
        $update = $db->prepare("UPDATE daily_dangky SET password = :password WHERE phone = :phone");
        $update->bindParam(':password', $new_password);
        $update->bindParam(':phone', $phone);
        $update->execute();

        // Xóa token sau khi sử dụng
        $delete = $db->prepare("DELETE FROM password_reset WHERE phone = :phone");
        $delete->bindParam(':phone', $phone);
        $delete->execute();

        echo "Mật khẩu đã được cập nhật!";
    } else {
        echo "Mã xác nhận không hợp lệ.";
    }
}
?>
