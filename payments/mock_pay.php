<?php
require_once __DIR__ . '/../libs/db.php';

$order_id = $_GET['order_id'] ?? '';
if (!$order_id) {
    die('Missing order_id');
}

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("
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
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quét mã QR để thanh toán</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .payment-container { max-width: 800px; margin: 2rem auto; padding: 2rem; }
        .qr-section { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .order-info { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .countdown { background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; padding: 1rem; border-radius: 10px; text-align: center; margin: 1rem 0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="row">
            <div class="col-md-6">
                <div class="qr-section text-center">
                    <h4>Quét mã QR để thanh toán</h4>
                    <p class="text-muted">Sử dụng ứng dụng hỗ trợ quét QR để thực hiện thanh toán</p>
                    
                    <!-- QR Code placeholder -->
                    <div class="qr-code my-4">
                        <div style="width: 200px; height: 200px; background: #000; margin: 0 auto; display: grid; place-items: center; color: white; font-size: 14px;">
                            QR CODE<br>
                            <small><?php echo htmlspecialchars($order_id); ?></small>
                        </div>
                    </div>
                    
                    <!-- Test buttons -->
                    <div class="mt-4">
                        <a href="mock_ipn.php?order_id=<?php echo urlencode($order_id); ?>&status=paid" 
                           class="btn btn-success btn-lg me-2">
                            <i class="fa fa-check"></i> Thanh toán thành công
                        </a>
                        <a href="mock_ipn.php?order_id=<?php echo urlencode($order_id); ?>&status=failed" 
                           class="btn btn-danger btn-lg">
                            <i class="fa fa-times"></i> Thanh toán thất bại
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="order-info">
                    <h5>Thông tin đơn hàng</h5>
                    
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nhà cung cấp</strong></td>
                            <td>BookBus (Demo)</td>
                        </tr>
                        <tr>
                            <td><strong>Mã đơn hàng</strong></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($order_id); ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Thông tin đơn</strong></td>
                            <td>
                                <?php if ($order['ten_nhaxe']): ?>
                                    <?php echo htmlspecialchars($order['diem_di'] . ' → ' . $order['diem_den']); ?>
                                <?php else: ?>
                                    Tuyến chưa xác định
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Số vé</strong></td>
                            <td><?php echo (int)$order['so_luong']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Số tiền</strong></td>
                            <td><strong><?php echo number_format($order['amount'], 0, ',', '.'); ?>đ</strong></td>
                        </tr>
                    </table>
                    
                    <!-- Countdown -->
                    <div class="countdown" id="countdown">
                        Đơn hàng sẽ hết hạn sau: <span id="timer">10:00</span>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="/src/index.php" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> Quay về
                        </a>
                        <button class="btn btn-outline-danger" onclick="cancelOrder()">
                            <i class="fa fa-times"></i> Hủy giao dịch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Countdown timer
    let timeLeft = 600; // 10 minutes
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        
        if (timeLeft <= 0) {
            // Hết thời gian - redirect về trang chủ (không phải success)
            window.location.href = '/src/index.php?payment_timeout=1';
            return;
        }
        
        timeLeft--;
    }
    
    // Bấm vào countdown để test timeout
    document.getElementById('countdown').addEventListener('click', function() {
        if (confirm('Test timeout? Sẽ redirect về trang chủ không có modal success.')) {
            window.location.href = '/src/index.php?payment_timeout=1';
        }
    });
    
    function cancelOrder() {
        if (confirm('Bạn có chắc muốn hủy giao dịch?')) {
            window.location.href = '/src/index.php?payment_cancelled=1';
        }
    }
    
    // Cập nhật timer mỗi giây
    setInterval(updateTimer, 1000);
    updateTimer();
    </script>
</body>
</html>