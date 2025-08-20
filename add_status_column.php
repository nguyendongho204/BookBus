<?php
require_once __DIR__ . '/libs/db.php';

echo "<h2>🔧 Thêm cột status vào bảng daily_dangky...</h2>";

try {
    // Kiểm tra cấu trúc hiện tại
    $result = $pdo->query("PRAGMA table_info(daily_dangky)");
    $columns = $result->fetchAll();
    $existingCols = array_column($columns, 'name');
    
    echo "<p><strong>Cột hiện có:</strong> " . implode(', ', $existingCols) . "</p>";
    
    // Thêm cột status nếu chưa có
    if (!in_array('status', $existingCols)) {
        try {
            $pdo->exec("ALTER TABLE daily_dangky ADD COLUMN status INTEGER NOT NULL DEFAULT 1");
            echo "✅ Đã thêm cột: <strong>status</strong><br>";
            
            // Cập nhật tất cả user hiện có thành trạng thái active (1)
            $updated = $pdo->exec("UPDATE daily_dangky SET status = 1 WHERE status IS NULL");
            echo "✅ Cập nhật status cho {$updated} users hiện có<br>";
            
        } catch (Exception $e) {
            echo "❌ Lỗi thêm cột status: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "✅ Cột <strong>status</strong> đã tồn tại<br>";
    }
    
    echo "<h3>📊 Cấu trúc bảng sau khi thêm cột:</h3>";
    $result = $pdo->query("PRAGMA table_info(daily_dangky)");
    $columns = $result->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Tên cột</th><th>Kiểu</th><th>NOT NULL</th><th>Mặc định</th></tr>";
    foreach ($columns as $col) {
        $notNull = $col['notnull'] ? 'YES' : 'NO';
        $default = $col['dflt_value'] ?? 'NULL';
        echo "<tr><td>{$col['cid']}</td><td><strong>{$col['name']}</strong></td><td>{$col['type']}</td><td>{$notNull}</td><td>{$default}</td></tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>🎉 Hoàn thành thêm cột status!</h3>";
    echo "<p><strong>Ý nghĩa:</strong></p>";
    echo "<ul>";
    echo "<li><strong>status = 1:</strong> Tài khoản hoạt động bình thường</li>";
    echo "<li><strong>status = 0:</strong> Tài khoản bị khóa</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Lỗi:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>