<?php
require_once __DIR__ . '/../libs/db.php';

$order_id = $_GET['order_id'] ?? '';
$status = $_GET['status'] ?? 'paid';

if (!$order_id) {
    die('Missing order_id');
}

try {
    // Cập nhật trạng thái thanh toán
    $newStatus = ($status === 'paid') ? 'success' : 'failed';
    
    $stmt = $pdo->prepare("UPDATE dat_ve SET 
        payment_status = ?, 
        txn_id = ?,
        updated_at = datetime('now')
        WHERE order_id = ?
    ");
    
    $txn_id = 'TXN_' . date('YmdHis') . '_' . substr(md5($order_id), 0, 8);
    $result = $stmt->execute([$newStatus, $txn_id, $order_id]);
    
    if ($result && $stmt->rowCount() > 0) {
        if ($status === 'paid') {
            // THANH TOÁN THÀNH CÔNG
            
            // Lấy thông tin đơn hàng để lưu vào localStorage
            $orderStmt = $pdo->prepare("
                SELECT 
                    dv.*, 
                    cx.ten_nhaxe, 
                    cx.diem_di, 
                    cx.diem_den, 
                    cx.ngay_di, 
                    cx.gio_di 
                FROM dat_ve dv 
                LEFT JOIN chuyenxe cx ON cx.id = dv.id_chuyen 
                WHERE dv.order_id = ?
            ");
            $orderStmt->execute([$order_id]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orderData) {
                $orderJson = json_encode($orderData, JSON_UNESCAPED_UNICODE);
                
                echo "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Thanh toán thành công</title>
                    <meta charset='UTF-8'>
                </head>
                <body>
                    <div style='text-align: center; padding: 50px;'>
                        <h2>⏳ Đang xử lý thanh toán...</h2>
                        <p>Vui lòng chờ trong giây lát.</p>
                    </div>
                    
                    <script>
                        console.log('💾 Saving order data to localStorage...');
                        
                        // Lưu thông tin đơn hàng vào localStorage
                        const orderData = " . $orderJson . ";
                        localStorage.setItem('lastBookingOrder', JSON.stringify(orderData));
                        
                        console.log('✅ Order data saved:', orderData);
                        
                        // Redirect về trang chủ với success flag
                        setTimeout(function() {
                            console.log('🚀 Redirecting to success page...');
                            window.location.href = '/src/index.php?booking_success=1';
                        }, 1000);
                    </script>
                </body>
                </html>
                ";
            } else {
                // Fallback
                header("Location: /src/index.php?booking_success=1");
            }
            
        } else {
            // THANH TOÁN THẤT BẠI
            header("Location: /src/index.php?booking_failed=1");
        }
    } else {
        echo "❌ Order not found or update failed!";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>