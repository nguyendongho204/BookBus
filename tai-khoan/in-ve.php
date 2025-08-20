<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

// Kết nối database
if (is_file(__DIR__ . '/../libs/db.php')) {
    require_once __DIR__ . '/../libs/db.php';
}

// Lấy ID đơn hàng
$bookingId = (int)($_GET['id'] ?? 0);
$userId = (int)($user['id'] ?? 0);

if (!$bookingId) {
    header('Location: /src/tai-khoan/index.php');
    exit;
}

// Lấy thông tin đặt vé với xử lý lỗi tốt hơn
$booking = null;
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Đầu tiên lấy thông tin vé
        $sql = "SELECT * FROM dat_ve WHERE id = :booking_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            echo '<script>alert("Không tìm thấy thông tin vé hoặc bạn không có quyền truy cập!"); window.close();</script>';
            exit;
        }
        
        // Sau đó lấy thông tin chuyến xe (nếu có)
        $trip = null;
        if (!empty($booking['id_chuyen'])) {
            $sql2 = "SELECT * FROM chuyenxe WHERE id = :id_chuyen";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':id_chuyen', $booking['id_chuyen'], PDO::PARAM_INT);
            $stmt2->execute();
            $trip = $stmt2->fetch(PDO::FETCH_ASSOC);
        }
        
        // Lấy thông tin user
        // Lấy thông tin user một cách thông minh
        $user_info = [];

        // Ưu tiên 1: Thông tin từ đặt vé (nếu có)
        if (!empty($booking['ten_khach'])) {
            $user_info['name'] = $booking['ten_khach'];
        }

        if (!empty($booking['email'])) {
            $user_info['email'] = $booking['email'];
        }

        // Ưu tiên 2: Thông tin từ session user
        if (empty($user_info['name'])) {
            $user_info['name'] = $user['name'] ?? $user['username'] ?? 'Khách hàng';
        }

        if (empty($user_info['email'])) {
            $user_info['email'] = $user['email'] ?? 'customer@gmail.com';
        }
                            
        // Merge dữ liệu
        if ($trip) {
            $booking = array_merge($booking, $trip);
        } else {
            // Nếu không có thông tin chuyến xe, sử dụng giá trị mặc định
            $booking['ten_nhaxe'] = $booking['ten_nhaxe'] ?? 'BookBus';
            $booking['diem_di'] = $booking['diem_di'] ?? 'Chưa xác định';
            $booking['diem_den'] = $booking['diem_den'] ?? 'Chưa xác định';
            $booking['ngay_di'] = $booking['ngay_di'] ?? date('Y-m-d');
            $booking['gio_di'] = $booking['gio_di'] ?? '00:00';
            $booking['gia_ve'] = $booking['gia_ve'] ?? 0;
            $booking['loai_xe'] = $booking['loai_xe'] ?? 'Xe khách';
        }
        
        if ($user_info) {
            $booking['user_name'] = $user_info['name'];
            $booking['user_email'] = $user_info['email'];
        }
        
    } else {
        echo '<script>alert("Lỗi kết nối database!"); window.close();</script>';
        exit;
    }
} catch (Exception $e) {
    echo '<script>alert("Lỗi: ' . addslashes($e->getMessage()) . '"); window.close();</script>';
    exit;
}

// Tính toán thông tin bổ sung
$totalAmount = (float)($booking['amount'] ?? 0);
if ($totalAmount <= 0) {
    $totalAmount = (float)($booking['gia_ve'] ?? 0) * (int)($booking['so_luong'] ?? 0);
}

$status = strtolower($booking['payment_status'] ?? '');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé xe #<?php echo $booking['id']; ?> - BookBus</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            line-height: 1.6;
        }

        .ticket-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            position: relative;
        }

        /* TICKET DESIGN */
        .ticket-header {
            background: linear-gradient(135deg, #ff5a2c 0%, #ff7849 100%);
            color: white;
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
        }

        .ticket-header::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            filter: blur(60px);
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            filter: blur(40px);
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .company-logo {
            font-size: 32px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .company-logo i {
            background: rgba(255,255,255,0.2);
            padding: 12px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .ticket-number {
            text-align: right;
            font-size: 18px;
            font-weight: 700;
            opacity: 0.9;
        }

        .ticket-title {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            margin: 20px 0;
            position: relative;
            z-index: 2;
        }

        /* ROUTE SECTION */
        .route-section {
            padding: 40px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 2px dashed #cbd5e1;
        }

        .route-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .route-point {
            text-align: center;
            flex: 1;
        }

        .route-city {
            font-size: 28px;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .route-time {
            font-size: 16px;
            color: #6b7280;
            font-weight: 600;
        }

        .route-arrow {
            flex: 0 0 auto;
            margin: 0 30px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .route-arrow::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -30px;
            right: 30px;
            height: 3px;
            background: linear-gradient(90deg, #ff5a2c, #ff7849);
            transform: translateY(-50%);
            border-radius: 2px;
        }

        .route-arrow i {
            background: white;
            color: #ff5a2c;
            font-size: 32px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #ff5a2c;
            position: relative;
            z-index: 2;
            box-shadow: 0 8px 20px rgba(255,90,44,0.3);
        }

        .trip-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 24px;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .trip-item {
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .trip-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .trip-value {
            font-size: 16px;
            font-weight: 800;
            color: #1f2937;
        }

        /* PASSENGER INFO */
        .passenger-section {
            padding: 40px;
            background: white;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .section-title i {
            width: 40px;
            height: 40px;
            background: #fff6f2;
            color: #ff5a2c;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .passenger-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .passenger-item {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #ff5a2c;
        }

        .passenger-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .passenger-value {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            word-break: break-word;
        }

        .passenger-value.price {
            color: #ff5a2c;
            font-size: 20px;
        }

        /* WARNING NOTICE */
        .warning-notice {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .warning-notice i {
            font-size: 20px;
        }

        /* QR CODE SECTION */
        .qr-section {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .qr-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            filter: blur(30px);
        }

        .qr-placeholder {
            width: 120px;
            height: 120px;
            border: 3px dashed rgba(255,255,255,0.4);
            border-radius: 12px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
        }

        .qr-placeholder i {
            font-size: 48px;
            opacity: 0.6;
        }

        .qr-text {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .booking-code {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
        }

        /* FOOTER */
        .ticket-footer {
            background: #f8fafc;
            padding: 25px 40px;
            text-align: center;
            border-top: 2px dashed #cbd5e1;
        }

        .footer-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .contact-info {
            color: #ff5a2c;
            font-weight: 700;
            font-size: 16px;
        }

        /* PRINT BUTTON */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 12px;
        }

        .print-btn {
            background: #ff5a2c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #e64a20;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,90,44,0.3);
            color: white;
            text-decoration: none;
        }

        .print-btn.secondary {
            background: #6b7280;
        }

        .print-btn.secondary:hover {
            background: #4b5563;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .ticket-container {
                margin: 10px;
                border-radius: 12px;
            }

            .ticket-header,
            .route-section,
            .passenger-section,
            .qr-section,
            .ticket-footer {
                padding: 20px;
            }

            .route-container {
                flex-direction: column;
                gap: 20px;
            }

            .route-arrow {
                margin: 0;
                transform: rotate(90deg);
            }

            .route-city {
                font-size: 22px;
            }

            .trip-summary {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .passenger-grid {
                grid-template-columns: 1fr;
            }

            .print-actions {
                position: static;
                margin: 20px;
                justify-content: center;
            }
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: white !important;
            }

            .ticket-container {
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: none !important;
            }

            .print-actions {
                display: none !important;
            }

            .warning-notice {
                display: none !important;
            }

            .ticket-header {
                background: #f5f5f5 !important;
                color: #333 !important;
            }

            .route-arrow i {
                color: #333 !important;
                border-color: #333 !important;
            }

            .qr-section {
                background: #f5f5f5 !important;
                color: #333 !important;
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

        .ticket-container {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <!-- PRINT ACTIONS -->
    <div class="print-actions">
        <button class="print-btn" onclick="window.print()">
            <i class="fa fa-print"></i>
            In vé
        </button>
        <a href="javascript:window.close()">
            <i class="fa fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <!-- MAIN TICKET -->
    <div class="ticket-container">
        <!-- WARNING cho trường hợp thiếu thông tin chuyến xe -->
        <?php if (empty($booking['id_chuyen']) || !isset($trip)): ?>
        <div class="warning-notice">
            <i class="fa fa-exclamation-triangle"></i>
            Lưu ý: Một số thông tin chuyến xe có thể không được cập nhật đầy đủ
        </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="ticket-header">
            <div class="company-info">
                <div class="company-logo">
                    <i class="fa fa-bus"></i>
                    BookBus
                </div>
                <div class="ticket-number">
                    VÉ SỐ: #<?php echo $booking['id']; ?>
                </div>
            </div>
            <div class="ticket-title">
                VÉ XE KHÁCH ĐIỆN TỬ
            </div>
        </div>

        <!-- ROUTE SECTION -->
        <div class="route-section">
            <div class="route-container">
                <div class="route-point">
                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_di'] ?? 'Cần Thơ'); ?></div>
                    <div class="route-time">Điểm đi</div>
                </div>
                
                <div class="route-arrow">
                    <i class="fa fa-long-arrow-right"></i>
                </div>
                
                <div class="route-point">
                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_den'] ?? 'Phong Điền'); ?></div>
                    <div class="route-time">Điểm đến</div>
                </div>
            </div>

            <div class="trip-summary">
                <div class="trip-item">
                    <div class="trip-label">Nhà xe</div>
                    <div class="trip-value"><?php echo htmlspecialchars($booking['ten_nhaxe'] ?? 'BookBus'); ?></div>
                </div>
                <div class="trip-item">
                    <div class="trip-label">Ngày đi</div>
                    <div class="trip-value">
                        <?php 
                        if (!empty($booking['ngay_di'])) {
                            echo date('d/m/Y', strtotime($booking['ngay_di']));
                        } else {
                            echo date('d/m/Y'); // Sử dụng ngày hiện tại
                        }
                        ?>
                    </div>
                </div>
                <div class="trip-item">
                    <div class="trip-label">Giờ khởi hành</div>
                    <div class="trip-value"><?php echo htmlspecialchars($booking['gio_di'] ?? '17:52'); ?></div>
                </div>
                <div class="trip-item">
                    <div class="trip-label">Số ghế</div>
                    <div class="trip-value"><?php echo (int)($booking['so_luong'] ?? 0); ?> ghế</div>
                </div>
            </div>
        </div>

        <!-- PASSENGER INFO -->
        <div class="passenger-section">
            <div class="section-title">
                <i class="fa fa-user"></i>
                Thông tin hành khách
            </div>

            <div class="passenger-grid">
                <div class="passenger-item">
                    <div class="passenger-label">Họ và tên</div>
                    <div class="passenger-value"><?php echo htmlspecialchars($booking['ho_ten'] ?? $user_info['name'] ?? 'Chưa cập nhật'); ?></div>
                </div>
                
                <div class="passenger-item">
                    <div class="passenger-label">Số điện thoại</div>
                    <div class="passenger-value"><?php echo htmlspecialchars($booking['sdt'] ?? 'Chưa cập nhật'); ?></div>
                </div>
                
                <div class="passenger-item">
                    <div class="passenger-label">Email</div>
                    <div class="passenger-value"><?php echo htmlspecialchars($booking['booking_email'] ?? $booking['user_email'] ?? 'chy@gmail.com'); ?></div>
                </div>
                
                <div class="passenger-item">
                    <div class="passenger-label">Thành tiền</div>
                    <div class="passenger-value price"><?php echo number_format($totalAmount > 0 ? $totalAmount : 14000, 0, ',', '.'); ?> VNĐ</div>
                </div>
                
                <div class="passenger-item">
                    <div class="passenger-label">Ngày đặt vé</div>
                    <div class="passenger-value">
                        <?php 
                        if (!empty($booking['ngay_dat'])) {
                            echo date('d/m/Y H:i', strtotime($booking['ngay_dat']));
                        } else {
                            echo date('d/m/Y H:i');
                        }
                        ?>
                    </div>
                </div>
                
                <div class="passenger-item">
                    <div class="passenger-label">Trạng thái</div>
                    <div class="passenger-value">
                        <?php 
                        switch ($status) {
                            case 'success':
                            case 'completed':
                            case 'paid':
                                echo 'Đã thanh toán';
                                break;
                            case 'pending':
                            case 'processing':
                                echo 'Đang xử lý';
                                break;
                            default:
                                echo 'Thành công';
                        }
                        ?>
                    </div>
                </div>

                <?php if (!empty($booking['loai_xe'])): ?>
                <div class="passenger-item">
                    <div class="passenger-label">Loại xe</div>
                    <div class="passenger-value"><?php echo htmlspecialchars($booking['loai_xe']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($booking['bien_so_xe'])): ?>
                <div class="passenger-item">
                    <div class="passenger-label">Biển số xe</div>
                    <div class="passenger-value"><?php echo htmlspecialchars($booking['bien_so_xe']); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($booking['ghi_chu'])): ?>
            <div class="passenger-item" style="margin-top: 20px; grid-column: 1 / -1;">
                <div class="passenger-label">Ghi chú</div>
                <div class="passenger-value"><?php echo nl2br(htmlspecialchars($booking['ghi_chu'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- QR CODE SECTION -->
        <div class="qr-section">
            <div class="qr-placeholder">
                <i class="fa fa-qrcode"></i>
            </div>
            <div class="qr-text">
                Quét mã QR để xác thực vé
            </div>
            <div class="booking-code">
                <?php echo $booking['order_id'] ?? 'DV' . date('Ymd') . str_pad((string)$booking['id'], 4, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="ticket-footer">
            <div class="footer-text">
                Vé điện tử này được phát hành bởi BookBus
            </div>
            <div class="footer-text">
                Vui lòng xuất trình vé khi lên xe
            </div>
            <div class="contact-info">
                Hotline: 1900-909-090 | Website: bookbus.com
            </div>
        </div>
    </div>

    <script>
        // Enhanced print functionality
        function enhancedPrint() {
            document.querySelector('.print-actions').style.display = 'none';
            document.body.style.background = 'white';
            window.print();
            setTimeout(() => {
                document.querySelector('.print-actions').style.display = 'flex';
                document.body.style.background = '#f8fafc';
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.passenger-item');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>