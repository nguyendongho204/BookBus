<?php
require_once __DIR__ . '/libs/db.php';

echo "<h2>🔧 Sửa bảng dat_ve cho payments...</h2>";

try {
    // Kiểm tra cấu trúc hiện tại
    $result = $pdo->query("PRAGMA table_info(dat_ve)");
    $columns = $result->fetchAll();
    $existingCols = array_column($columns, 'name');
    
    echo "<p><strong>Cột hiện có:</strong> " . implode(', ', $existingCols) . "</p>";
    
    // Thêm các cột cần thiết cho payments
    $paymentCols = [
        'txn_id' => "ALTER TABLE dat_ve ADD COLUMN txn_id TEXT",
        'ipn_payload' => "ALTER TABLE dat_ve ADD COLUMN ipn_payload TEXT",
        'updated_at' => "ALTER TABLE dat_ve ADD COLUMN updated_at TEXT"
    ];
    
    foreach ($paymentCols as $colName => $sql) {
        if (!in_array($colName, $existingCols)) {
            try {
                $pdo->exec($sql);
                echo "✅ Đã thêm cột: <strong>{$colName}</strong><br>";
            } catch (Exception $e) {
                echo "❌ Lỗi thêm cột {$colName}: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "✅ Cột <strong>{$colName}</strong> đã tồn tại<br>";
        }
    }
    
    // Kiểm tra và thêm các cột cơ bản nếu thiếu
    $basicCols = [
        'payment_provider' => "ALTER TABLE dat_ve ADD COLUMN payment_provider TEXT DEFAULT 'mock'",
        'payment_status' => "ALTER TABLE dat_ve ADD COLUMN payment_status TEXT DEFAULT 'pending'",
        'amount' => "ALTER TABLE dat_ve ADD COLUMN amount INTEGER DEFAULT 0",
        'order_id' => "ALTER TABLE dat_ve ADD COLUMN order_id TEXT"
    ];
    
    foreach ($basicCols as $colName => $sql) {
        if (!in_array($colName, $existingCols)) {
            try {
                $pdo->exec($sql);
                echo "✅ Đã thêm cột cơ bản: <strong>{$colName}</strong><br>";
            } catch (Exception $e) {
                echo "❌ Lỗi thêm cột {$colName}: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<h3>📊 Cấu trúc bảng cuối cùng:</h3>";
    $result = $pdo->query("PRAGMA table_info(dat_ve)");
    $columns = $result->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Tên cột</th><th>Kiểu</th><th>NOT NULL</th><th>Mặc định</th></tr>";
    foreach ($columns as $col) {
        $notNull = $col['notnull'] ? 'YES' : 'NO';
        $default = $col['dflt_value'] ?? 'NULL';
        echo "<tr><td>{$col['cid']}</td><td><strong>{$col['name']}</strong></td><td>{$col['type']}</td><td>{$notNull}</td><td>{$default}</td></tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>🎉 Hoàn thành sửa bảng!</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='/src/search_routes.php' style='background: #ff5a2c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎫 Thử đặt vé lại</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</h2>";
}
?>