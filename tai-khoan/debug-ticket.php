<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

// Kết nối database
if (is_file(__DIR__ . '/../libs/db.php')) {
    require_once __DIR__ . '/../libs/db.php';
}

// Lấy thông tin debug
$bookingId = (int)($_GET['id'] ?? 0);
$userId = (int)($user['id'] ?? 0);

echo "<h2>🔍 DEBUG THÔNG TIN VÉ</h2>";
echo "<p><strong>Booking ID từ URL:</strong> {$bookingId}</p>";
echo "<p><strong>User ID hiện tại:</strong> {$userId}</p>";
echo "<p><strong>User login:</strong> " . htmlspecialchars($user['name'] ?? $user['username'] ?? 'N/A') . "</p>";

// Kiểm tra kết nối database
if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p>✅ <strong>Database PDO:</strong> Kết nối OK</p>";
    
    try {
        // 1. Kiểm tra vé có tồn tại không
        $sql = "SELECT * FROM dat_ve WHERE id = :booking_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ticket) {
            echo "<p>✅ <strong>Vé tồn tại:</strong> Có</p>";
            echo "<p><strong>User ID của vé:</strong> " . ($ticket['user_id'] ?? 'NULL') . "</p>";
            echo "<p><strong>Khớp user:</strong> " . (($ticket['user_id'] == $userId) ? '✅ Có' : '❌ Không') . "</p>";
            
            echo "<h3>📋 Chi tiết vé:</h3>";
            echo "<pre>";
            print_r($ticket);
            echo "</pre>";
            
            // 2. Kiểm tra thông tin chuyến xe
            if (!empty($ticket['id_chuyen'])) {
                $sql2 = "SELECT * FROM chuyenxe WHERE id = :id_chuyen";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindParam(':id_chuyen', $ticket['id_chuyen'], PDO::PARAM_INT);
                $stmt2->execute();
                $trip = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($trip) {
                    echo "<h3>🚌 Thông tin chuyến xe:</h3>";
                    echo "<pre>";
                    print_r($trip);
                    echo "</pre>";
                } else {
                    echo "<p>❌ <strong>Chuyến xe:</strong> Không tìm thấy với ID " . $ticket['id_chuyen'] . "</p>";
                }
            }
            
        } else {
            echo "<p>❌ <strong>Vé tồn tại:</strong> Không tìm thấy vé với ID {$bookingId}</p>";
            
            // Kiểm tra các vé của user hiện tại
            $sql3 = "SELECT id, ngay_dat, so_luong, payment_status FROM dat_ve WHERE user_id = :user_id ORDER BY id DESC";
            $stmt3 = $pdo->prepare($sql3);
            $stmt3->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt3->execute();
            $userTickets = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>🎫 Danh sách vé của user hiện tại:</h3>";
            if ($userTickets) {
                echo "<table border='1' cellpadding='5' cellspacing='0'>";
                echo "<tr><th>ID</th><th>Ngày đặt</th><th>Số lượng</th><th>Trạng thái</th></tr>";
                foreach ($userTickets as $t) {
                    echo "<tr>";
                    echo "<td><a href='?id={$t['id']}'>{$t['id']}</a></td>";
                    echo "<td>{$t['ngay_dat']}</td>";
                    echo "<td>{$t['so_luong']}</td>";
                    echo "<td>{$t['payment_status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Không có vé nào cho user này.</p>";
            }
        }
        
        // 3. Kiểm tra tất cả vé trong hệ thống
        $sql4 = "SELECT id, user_id, ngay_dat, payment_status FROM dat_ve ORDER BY id DESC LIMIT 10";
        $stmt4 = $pdo->prepare($sql4);
        $stmt4->execute();
        $allTickets = $stmt4->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>🗂️ 10 vé gần nhất trong hệ thống:</h3>";
        if ($allTickets) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Ngày đặt</th><th>Trạng thái</th></tr>";
            foreach ($allTickets as $t) {
                $highlight = ($t['id'] == $bookingId) ? ' style="background: yellow;"' : '';
                echo "<tr{$highlight}>";
                echo "<td>{$t['id']}</td>";
                echo "<td>{$t['user_id']}</td>";
                echo "<td>{$t['ngay_dat']}</td>";
                echo "<td>{$t['payment_status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Lỗi database:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} elseif (isset($conn) && $conn instanceof mysqli) {
    echo "<p>✅ <strong>Database MySQLi:</strong> Kết nối OK</p>";
    // Tương tự với MySQLi nếu cần
} else {
    echo "<p>❌ <strong>Database:</strong> Không có kết nối</p>";
}

// 4. Kiểm tra session user
echo "<h3>👤 Thông tin session user:</h3>";
echo "<pre>";
print_r($user);
echo "</pre>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th { background: #f0f0f0; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
a { color: #0066cc; }
</style>