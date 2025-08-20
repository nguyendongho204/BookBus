<?php
// Tạo file test_session.php trong thư mục src/
session_start();

echo "<h2>Session Test</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Cookie Path:</strong> " . ini_get('session.cookie_path') . "</p>";
echo "<p><strong>Session Data:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test set session
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 'Session hoạt động!';
    echo "<p style='color:green;'>✅ Session được thiết lập!</p>";
} else {
    echo "<p style='color:blue;'>✅ Session đã tồn tại: " . $_SESSION['test'] . "</p>";
}

echo "<p><a href='timkiemchuyenxe.php?debug=1'>Test trang tìm kiếm</a></p>";
echo "<p><a href='trangchu.php'>Về trang chủ</a></p>";
?>