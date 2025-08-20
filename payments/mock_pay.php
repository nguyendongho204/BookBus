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
<html lang="vi">
<head>
    <title>Thanh toán QR - BookBus</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bb-primary: #ff5722;
            --bb-primary-dark: #d84315;
            --bb-success: #4caf50;
            --bb-danger: #f44336;
            --bb-warning: #ff9800;
            --bb-dark: #263238;
            --bb-light: #f8fafc;
            --bb-gradient: linear-gradient(135deg, #ff5722 0%, #ff7043 100%);
            --bb-qr-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bb-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .payment-wrapper {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* HEADER */
        .payment-header {
            background: var(--bb-gradient);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            filter: blur(40px);
        }

        .payment-header .content {
            position: relative;
            z-index: 2;
        }

        .payment-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .payment-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        /* MAIN CONTENT */
        .payment-body {
            padding: 40px;
        }

        .qr-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .qr-container {
            position: relative;
            display: inline-block;
            margin: 30px 0;
        }

        .qr-code {
            width: 240px;
            height: 240px;
            background: var(--bb-qr-gradient);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
            border: 4px solid white;
            position: relative;
            overflow: hidden;
        }

        .qr-code::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(30deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(30deg); }
        }

        .qr-code-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .qr-icon {
            font-size: 60px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .qr-text {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 10px;
        }

        .qr-order-id {
            font-size: 12px;
            opacity: 0.7;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        /* ORDER INFO */
        .order-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .info-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--bb-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .info-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--bb-dark);
        }

        .info-value.highlight {
            color: var(--bb-primary);
            font-size: 20px;
        }

        /* COUNTDOWN */
        .countdown-section {
            background: var(--bb-warning);
            color: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .countdown-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: countdown-shine 2s infinite;
        }

        @keyframes countdown-shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .countdown-content {
            position: relative;
            z-index: 2;
        }

        .countdown-text {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .countdown-timer {
            font-size: 32px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            min-width: 180px;
            justify-content: center;
        }

        .btn-success-custom {
            background: var(--bb-success);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-success-custom:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }

        .btn-danger-custom {
            background: var(--bb-danger);
            color: white;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        }

        .btn-danger-custom:hover {
            background: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(244, 67, 54, 0.4);
            color: white;
        }

        /* NAVIGATION */
        .nav-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid;
        }

        .btn-outline-custom {
            background: transparent;
            border-color: #ddd;
            color: #666;
        }

        .btn-outline-custom:hover {
            background: var(--bb-primary);
            border-color: var(--bb-primary);
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .payment-container {
                padding: 0 10px;
            }

            .payment-body {
                padding: 30px 20px;
            }

            .payment-header {
                padding: 25px 20px;
            }

            .qr-code {
                width: 200px;
                height: 200px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .action-btn {
                width: 100%;
                max-width: 300px;
            }
        }

        /* ANIMATIONS */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-wrapper {
            animation: fadeInUp 0.6s ease-out;
        }

        .qr-container {
            animation: fadeInUp 0.8s ease-out;
        }

        .order-info {
            animation: fadeInUp 1s ease-out;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-wrapper">
            <!-- HEADER -->
            <div class="payment-header">
                <div class="content">
                    <div class="payment-title">
                        <i class="fas fa-qrcode"></i>
                        Thanh toán QR
                    </div>
                    <div class="payment-subtitle">
                        Quét mã QR bằng ứng dụng ngân hàng để thanh toán
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="payment-body">
                <!-- QR SECTION -->
                <div class="qr-section">
                    <div class="qr-container">
                        <div class="qr-code">
                            <div class="qr-code-content">
                                <div class="qr-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div class="qr-text">QR CODE</div>
                                <div class="qr-order-id"><?php echo htmlspecialchars($order_id); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>
                        Mở ứng dụng ngân hàng và quét mã QR để thanh toán
                    </p>
                </div>

                <!-- ORDER INFO -->
                <div class="order-info">
                    <div class="info-title">
                        <i class="fas fa-receipt"></i>
                        Thông tin đơn hàng
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-building me-1"></i>
                                Nhà cung cấp
                            </div>
                            <div class="info-value">BookBus</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-hashtag me-1"></i>
                                Mã đơn hàng
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($order_id); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-route me-1"></i>
                                Tuyến đường
                            </div>
                            <div class="info-value">
                                <?php if ($order['ten_nhaxe']): ?>
                                    <?php echo htmlspecialchars($order['diem_di'] . ' → ' . $order['diem_den']); ?>
                                <?php else: ?>
                                    Tuyến chưa xác định
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar me-1"></i>
                                Ngày khởi hành
                            </div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($order['ngay_di'])); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock me-1"></i>
                                Giờ khởi hành
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($order['gio_di']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-ticket-alt me-1"></i>
                                Số vé
                            </div>
                            <div class="info-value"><?php echo (int)$order['so_luong']; ?> vé</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user me-1"></i>
                                Khách hàng
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($order['ho_ten'] ?? 'Chưa cập nhật'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Tổng tiền
                            </div>
                            <div class="info-value highlight"><?php echo number_format($order['amount'], 0, ',', '.'); ?>₫</div>
                        </div>
                    </div>
                </div>

                <!-- COUNTDOWN -->
                <div class="countdown-section" id="countdown">
                    <div class="countdown-content">
                        <div class="countdown-text">
                            <i class="fas fa-hourglass-half me-2"></i>
                            Đơn hàng sẽ hết hạn sau
                        </div>
                        <div class="countdown-timer" id="timer">10:00</div>
                    </div>
                </div>

                <!-- TEST BUTTONS -->
                <div class="action-buttons">
                    <a href="mock_ipn.php?order_id=<?php echo urlencode($order_id); ?>&status=paid" 
                       class="action-btn btn-success-custom">
                        <i class="fas fa-check-circle"></i>
                        Thanh toán thành công
                    </a>
                    <a href="mock_ipn.php?order_id=<?php echo urlencode($order_id); ?>&status=failed" 
                       class="action-btn btn-danger-custom">
                        <i class="fas fa-times-circle"></i>
                        Thanh toán thất bại
                    </a>
                </div>

                <!-- NAVIGATION -->
                <div class="nav-buttons">
                    <a href="/src/index.php" class="nav-btn btn-outline-custom">
                        <i class="fas fa-arrow-left"></i>
                        Về trang chủ
                    </a>
                    <button class="nav-btn btn-outline-custom" onclick="cancelOrder()">
                        <i class="fas fa-ban"></i>
                        Hủy giao dịch
                    </button>
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
        
        if (timeLeft <= 60) {
            document.getElementById('countdown').style.background = 'var(--bb-danger)';
        }
        
        if (timeLeft <= 0) {
            window.location.href = '/src/index.php?payment_timeout=1';
            return;
        }
        
        timeLeft--;
    }
    
    function cancelOrder() {
        if (confirm('Bạn có chắc muốn hủy giao dịch này không?')) {
            window.location.href = '/src/index.php?payment_cancelled=1';
        }
    }
    
    // Bấm vào countdown để test timeout
    document.getElementById('countdown').addEventListener('click', function() {
        if (confirm('🧪 Test timeout? Sẽ redirect về trang chủ.')) {
            window.location.href = '/src/index.php?payment_timeout=1';
        }
    });
    
    // Cập nhật timer mỗi giây
    setInterval(updateTimer, 1000);
    updateTimer();

    // Prevent accidental page refresh
    window.addEventListener('beforeunload', function(e) {
        e.preventDefault();
        e.returnValue = 'Bạn có chắc muốn rời khỏi trang thanh toán?';
    });

    // Auto-refresh page every 30 seconds to check payment status
    setInterval(function() {
        // Optional: Add AJAX call to check payment status
        console.log('Checking payment status...');
    }, 30000);
    </script>
</body>
</html>