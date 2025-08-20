<?php
require_once __DIR__ . '/libs/db.php';

echo "<h2>🔧 Sửa bảng dat_ve để hỗ trợ thanh toán...</h2>";

try {
    // Kiểm tra cấu trúc bảng hiện tại
    echo "<h3>📋 Cấu trúc bảng dat_ve hiện tại:</h3>";
    $result = $pdo->query("PRAGMA table_info(dat_ve)");
    $columns = $result->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Tên cột</th><th>Kiểu</th><th>NOT NULL</th><th>Giá trị mặc định</th></tr>";
    $existingCols = [];
    foreach ($columns as $col) {
        $existingCols[] = $col['name'];
        $notNull = $col['notnull'] ? 'YES' : 'NO';
        $default = $col['dflt_value'] ?? 'NULL';
        echo "<tr><td>{$col['cid']}</td><td>{$col['name']}</td><td>{$col['type']}</td><td>{$notNull}</td><td>{$default}</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra các cột cần thiết
    $requiredCols = [
        'payment_provider' => "ALTER TABLE dat_ve ADD COLUMN payment_provider TEXT DEFAULT 'mock'",
        'payment_status' => "ALTER TABLE dat_ve ADD COLUMN payment_status TEXT DEFAULT 'pending'", 
        'amount' => "ALTER TABLE dat_ve ADD COLUMN amount INTEGER DEFAULT 0",
        'order_id' => "ALTER TABLE dat_ve ADD COLUMN order_id TEXT",
        'user_id' => "ALTER TABLE dat_ve ADD COLUMN user_id INTEGER"
    ];
    
    echo "<h3>🔄 Thêm cột thiếu...</h3>";
    
    foreach ($requiredCols as $colName => $sql) {
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
    
    // Cập nhật dữ liệu cũ (nếu có)
    echo "<h3>🔄 Cập nhật dữ liệu cũ...</h3>";
    
    // Cập nhật payment_provider cho records cũ
    $updated1 = $pdo->exec("UPDATE dat_ve SET payment_provider = 'mock' WHERE payment_provider IS NULL OR payment_provider = ''");
    echo "✅ Cập nhật payment_provider cho {$updated1} records<br>";
    
    // Cập nhật payment_status cho records cũ
    $updated2 = $pdo->exec("UPDATE dat_ve SET payment_status = 'success' WHERE payment_status IS NULL OR payment_status = ''");
    echo "✅ Cập nhật payment_status cho {$updated2} records<br>";
    
    // Cập nhật order_id cho records cũ (nếu thiếu)
    $updated3 = $pdo->exec("UPDATE dat_ve SET order_id = 'ORDER_' || id WHERE order_id IS NULL OR order_id = ''");
    echo "✅ Cập nhật order_id cho {$updated3} records<br>";
    
    echo "<h3>📊 Cấu trúc bảng sau khi sửa:</h3>";
    $result = $pdo->query("PRAGMA table_info(dat_ve)");
    $columns = $result->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Tên cột</th><th>Kiểu</th><th>NOT NULL</th><th>Giá trị mặc định</th></tr>";
    foreach ($columns as $col) {
        $notNull = $col['notnull'] ? 'YES' : 'NO';
        $default = $col['dflt_value'] ?? 'NULL';
        echo "<tr><td>{$col['cid']}</td><td>{$col['name']}</td><td>{$col['type']}</td><td>{$notNull}</td><td>{$default}</td></tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>🎉 Hoàn thành!</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='/src/search_routes.php' style='background: #ff5a2c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎫 Thử đặt vé</a>";
    echo "<a href='/src/tai-khoan/index.php?show=history' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Xem lịch sử</a>";
    echo "</div>";
    
    // Test INSERT
    echo "<h3>🧪 Test INSERT mới:</h3>";
    try {
        $testSQL = "INSERT INTO dat_ve (
            id_chuyen, ho_ten, sdt, email, so_luong, ngay_dat,
            payment_provider, payment_status, amount, order_id, user_id
        ) VALUES (
            1, 'Test User', '0123456789', 'test@example.com', 1, datetime('now'),
            'mock', 'pending', 150000, 'TEST_ORDER', 22
        )";
        
        $pdo->exec($testSQL);
        $testId = $pdo->lastInsertId();
        echo "✅ Test INSERT thành công! ID: {$testId}<br>";
        
        // Xóa test record
        $pdo->exec("DELETE FROM dat_ve WHERE id = {$testId}");
        echo "✅ Đã xóa test record<br>";
        
    } catch (Exception $e) {
        echo "❌ Test INSERT thất bại: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</h2>";
    echo "<p>File: " . $e->getFile() . " - Dòng: " . $e->getLine() . "</p>";
}
?>