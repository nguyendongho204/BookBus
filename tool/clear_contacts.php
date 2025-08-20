<?php
// Kết nối SQLite
$dbPath = __DIR__ . '/../libs/database.db';
if (!file_exists($dbPath)) {
    die("Không tìm thấy DB tại: $dbPath");
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

$mode = $_GET['mode'] ?? 'preview';
$confirm = isset($_GET['confirm']) && $_GET['confirm'] == 1;

// Tên bảng và cột
$table = 'daily_dangky';
$colPhone = 'phone';
$colEmail = 'email';

if ($mode === 'preview') {
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $dupes = $pdo->query("SELECT $colPhone, COUNT(*) as total FROM $table GROUP BY $colPhone HAVING total > 1")->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Preview dữ liệu</h2>";
    echo "Tổng số bản ghi: $count<br>";
    if ($dupes) {
        echo "<p><b>SĐT trùng:</b></p><pre>";
        print_r($dupes);
        echo "</pre>";
    } else {
        echo "<p>Không có SĐT trùng.</p>";
    }
    echo "<p><a href='?mode=clear_contacts&confirm=1'>XÓA email & phone</a></p>";
    echo "<p><a href='?mode=delete_all&confirm=1'>XÓA toàn bộ tài khoản</a></p>";
}

elseif ($mode === 'clear_contacts' && $confirm) {
    $pdo->exec("UPDATE $table SET $colPhone = '', $colEmail = ''");
    echo "Đã xóa toàn bộ email & SĐT (dữ liệu vẫn giữ nguyên tài khoản).";
}

elseif ($mode === 'delete_all' && $confirm) {
    $pdo->exec("DELETE FROM $table");
    echo "Đã xóa toàn bộ tài khoản.";
}

else {
    echo "Sai cú pháp hoặc thiếu confirm=1";
}
