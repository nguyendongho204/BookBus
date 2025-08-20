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
            
            // Lấy thông tin đơn hàng để hiển thị
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
                ?>
                <!DOCTYPE html>
                <html lang="vi">
                <head>
                    <title>Thanh toán thành công - BookBus</title>
                    <meta charset='UTF-8'>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <style>
                        :root {
                            --bb-primary: #ff5722;
                            --bb-success: #4caf50;
                            --bb-dark: #263238;
                            --bb-light: #f8fafc;
                        }

                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }

                        body {
                            background: rgba(0, 0, 0, 0.8);
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 20px;
                            backdrop-filter: blur(5px);
                        }

                        @keyframes slideInUp {
                            from {
                                opacity: 0;
                                transform: translateY(50px) scale(0.9);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0) scale(1);
                            }
                        }

                        @keyframes bounce {
                            0%, 20%, 50%, 80%, 100% {
                                transform: translateY(0);
                            }
                            40% {
                                transform: translateY(-10px);
                            }
                            60% {
                                transform: translateY(-5px);
                            }
                        }

                        .success-modal {
                            background: white;
                            border-radius: 24px;
                            padding: 0;
                            max-width: 500px;
                            width: 100%;
                            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
                            animation: slideInUp 0.6s ease-out;
                            overflow: hidden;
                            position: relative;
                        }

                        .modal-header {
                            background: linear-gradient(135deg, var(--bb-success) 0%, #66bb6a 100%);
                            color: white;
                            padding: 40px 30px;
                            text-align: center;
                            position: relative;
                            overflow: hidden;
                        }

                        .modal-header::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -20%;
                            width: 200px;
                            height: 200px;
                            background: rgba(255, 255, 255, 0.1);
                            border-radius: 50%;
                            filter: blur(40px);
                        }

                        .success-icon {
                            width: 80px;
                            height: 80px;
                            background: rgba(255, 255, 255, 0.2);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 20px;
                            font-size: 40px;
                            animation: bounce 1s ease-in-out;
                            position: relative;
                            z-index: 2;
                        }

                        .modal-title {
                            font-size: 32px;
                            font-weight: 800;
                            margin-bottom: 8px;
                            position: relative;
                            z-index: 2;
                        }

                        .modal-subtitle {
                            font-size: 16px;
                            opacity: 0.9;
                            position: relative;
                            z-index: 2;
                        }

                        .modal-body {
                            padding: 40px 30px;
                        }

                        .booking-info {
                            background: #f8fafc;
                            border-radius: 16px;
                            padding: 25px;
                            margin-bottom: 30px;
                            border: 1px solid #e2e8f0;
                        }

                        .info-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-bottom: 16px;
                            padding: 12px 0;
                            border-bottom: 1px solid #e2e8f0;
                        }

                        .info-row:last-child {
                            border-bottom: none;
                            margin-bottom: 0;
                        }

                        .info-label {
                            font-size: 14px;
                            color: #666;
                            font-weight: 600;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            flex: 1;
                        }

                        .info-value {
                            font-size: 16px;
                            font-weight: 700;
                            color: var(--bb-dark);
                            text-align: right;
                            flex: 1;
                        }

                        .info-value.highlight {
                            color: var(--bb-primary);
                            font-size: 20px;
                        }

                        .action-buttons {
                            display: flex;
                            gap: 15px;
                            flex-wrap: wrap;
                        }

                        .action-btn {
                            flex: 1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 10px;
                            padding: 16px 20px;
                            border-radius: 12px;
                            font-weight: 700;
                            text-decoration: none;
                            transition: all 0.3s ease;
                            border: none;
                            cursor: pointer;
                            font-size: 15px;
                            min-height: 54px;
                            min-width: 140px;
                        }

                        .btn-primary-custom {
                            background: var(--bb-primary);
                            color: white;
                            box-shadow: 0 4px 15px rgba(255, 87, 34, 0.3);
                        }

                        .btn-primary-custom:hover {
                            background: #e64a19;
                            transform: translateY(-2px);
                            box-shadow: 0 8px 25px rgba(255, 87, 34, 0.4);
                            color: white;
                            text-decoration: none;
                        }

                        .btn-secondary-custom {
                            background: white;
                            color: #666;
                            border: 2px solid #e2e8f0;
                        }

                        .btn-secondary-custom:hover {
                            background: #f8fafc;
                            border-color: var(--bb-primary);
                            color: var(--bb-primary);
                            transform: translateY(-2px);
                            text-decoration: none;
                        }

                        /* Mobile responsive */
                        @media (max-width: 480px) {
                            .success-modal {
                                margin: 10px;
                                width: calc(100% - 20px);
                            }

                            .modal-header {
                                padding: 30px 20px;
                            }

                            .modal-title {
                                font-size: 28px;
                            }

                            .modal-body {
                                padding: 30px 20px;
                            }

                            .action-buttons {
                                flex-direction: column;
                            }

                            .action-btn {
                                width: 100%;
                                min-width: auto;
                            }

                            .info-row {
                                flex-direction: column;
                                gap: 8px;
                                align-items: flex-start;
                            }

                            .info-value {
                                text-align: left;
                            }
                        }
                    </style>
                </head>
                <body>
                    <!-- SUCCESS MODAL -->
                    <div class="success-modal">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <div class="success-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="modal-title">Đặt vé thành công!</div>
                            <div class="modal-subtitle">Cảm ơn bạn đã sử dụng dịch vụ BookBus</div>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <!-- Booking Information -->
                            <div class="booking-info">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-hashtag"></i>
                                        Mã đơn hàng
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($order_id); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-route"></i>
                                        Tuyến đường
                                    </span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($orderData['diem_di'] . ' → ' . $orderData['diem_den']); ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-calendar"></i>
                                        Ngày khởi hành
                                    </span>
                                    <span class="info-value"><?php echo date('d/m/Y', strtotime($orderData['ngay_di'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-clock"></i>
                                        Giờ khởi hành
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($orderData['gio_di']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-ticket-alt"></i>
                                        Số vé
                                    </span>
                                    <span class="info-value"><?php echo (int)$orderData['so_luong']; ?> vé</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Tổng tiền
                                    </span>
                                    <span class="info-value highlight"><?php echo number_format($orderData['amount'], 0, ',', '.'); ?>₫</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <a href="/src/tai-khoan/index.php?show=history" class="action-btn btn-primary-custom">
                                    <i class="fas fa-history"></i>
                                    Xem lịch sử đặt vé
                                </a>
                                <a href="/src/index.php" class="action-btn btn-secondary-custom">
                                    <i class="fas fa-home"></i>
                                    Về trang chủ
                                </a>
                            </div>
                        </div>
                    </div>

                    <script>
                    // Play success sound (optional)
                    function playSuccessSound() {
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRvQDAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YdADAAC4uLi4QEBAQEBAuLi4uEBAQEBAQLi4uLhAQEBAQEC4uLi4QEBAQEBA');
                            audio.volume = 0.2;
                            audio.play().catch(() => {});
                        } catch (e) {}
                    }

                    // Auto-redirect after 8 seconds
                    let countdown = 8;
                    function startAutoRedirect() {
                        const timer = setInterval(() => {
                            countdown--;
                            if (countdown <= 0) {
                                clearInterval(timer);
                                window.location.href = '/src/tai-khoan/index.php?show=history';
                            }
                        }, 1000);
                    }

                    // Initialize
                    document.addEventListener('DOMContentLoaded', function() {
                        playSuccessSound();
                        
                        // Start auto-redirect countdown after 3 seconds
                        setTimeout(startAutoRedirect, 3000);
                        
                        // Save order data to localStorage
                        const orderData = <?php echo json_encode($orderData, JSON_UNESCAPED_UNICODE); ?>;
                        localStorage.setItem('lastBookingOrder', JSON.stringify(orderData));
                        localStorage.setItem('lastBookingSuccess', Date.now());
                    });

                    // Handle button clicks with loading states
                    document.querySelectorAll('.action-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            const originalText = this.innerHTML;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang chuyển hướng...';
                            this.style.pointerEvents = 'none';
                            
                            // Allow navigation after brief delay
                            setTimeout(() => {
                                window.location.href = this.href;
                            }, 800);
                        });
                    });

                    // Keyboard navigation
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            document.querySelector('.btn-primary-custom').click();
                        } else if (e.key === 'Escape') {
                            window.location.href = '/src/index.php';
                        }
                    });
                    </script>
                </body>
                </html>
                <?php
            } else {
                // Fallback nếu không có thông tin đơn hàng
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