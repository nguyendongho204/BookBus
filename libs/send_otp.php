<?php
require 'db.php';
c:\Users\LEGION\AppData\Local\Temp\febc5c41-4de4-48e0-98a3-09b1b6787dd5_compressed.zip.dd5\Ưu Đãi Vượt Trội Cho Sinh Viên_cleanup (1)_cleanup (1).png
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];

    // Kiểm tra số điện thoại trong database
    $stmt = $db->prepare("SELECT phone FROM daily_dangky WHERE phone = :phone");
    $stmt->bindParam(':phone', $phone);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        $otp = rand(100000, 999999); // Tạo mã OTP ngẫu nhiên

        // Lưu OTP vào database
        $stmt = $db->prepare("INSERT INTO password_reset (phone, token) VALUES (:phone, :otp)");
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':otp', $otp);
        $stmt->execute();

        // Trả về JSON thay vì chuyển hướng
        echo json_encode(["success" => true, "phone" => $phone]);
    } else {
        echo json_encode(["success" => false, "message" => "Số điện thoại chưa được đăng ký!"]);
    }
    exit;
}
?>
