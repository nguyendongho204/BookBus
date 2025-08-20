<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $otp = $_POST['otp'];

    $stmt = $db->prepare("SELECT phone FROM password_reset WHERE phone = :phone AND token = :otp");
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':otp', $otp);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        header("Location: reset_password.php?phone=$phone"); // Điều hướng đặt lại mật khẩu
    } else {
        echo "Mã OTP không hợp lệ!";
    }
}
?>
