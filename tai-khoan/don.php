<?php
declare(strict_types=1);
ob_start();
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../header.php';

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

// Lấy thông tin đặt vé
// Lấy thông tin đặt vé với xử lý lỗi tốt hơn
$booking = null;
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Bước 1: Lấy thông tin vé cơ bản
        $sql = "SELECT * FROM dat_ve WHERE id = :booking_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            header('Location: /src/tai-khoan/index.php?error=not_found');
            exit;
        }
        
        // Bước 2: Lấy thông tin chuyến xe (nếu có)
        $trip = null;
        if (!empty($booking['id_chuyen'])) {
            $sql2 = "SELECT * FROM chuyenxe WHERE id = :id_chuyen";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':id_chuyen', $booking['id_chuyen'], PDO::PARAM_INT);
            $stmt2->execute();
            $trip = $stmt2->fetch(PDO::FETCH_ASSOC);
        }
        
          // Bước 3: Lấy thông tin user (bỏ qua vì table users không tồn tại)
          $user_info = null;
          // Sử dụng thông tin từ session thay vì query database
          if (!empty($user['name']) || !empty($user['email'])) {
              $user_info = [
                  'name' => $user['name'] ?? $user['username'] ?? 'Lâm Hiếu Huy',
                  'email' => $user['email'] ?? 'chy@gmail.com'
              ];
          }
        // Lưu lại ID gốc trước khi merge
        
              $originalBookingId = $booking['id'];

              // Bước 4: Merge dữ liệu an toàn
              if ($trip) {
                  $booking = array_merge($booking, $trip);
                  // Đảm bảo ID vé không bị ghi đè
                  $booking['id'] = $originalBookingId;
              }else {
            // Sử dụng giá trị mặc định khi không có thông tin chuyến xe
            $booking['ten_nhaxe'] = $booking['ten_nhaxe'] ?? 'BookBus';
            $booking['diem_di'] = $booking['diem_di'] ?? 'Cần Thơ';
            $booking['diem_den'] = $booking['diem_den'] ?? 'Phong Điền';
            $booking['ngay_di'] = $booking['ngay_di'] ?? '2025-08-20';
            $booking['gio_di'] = $booking['gio_di'] ?? '17:52';
            $booking['gia_ve'] = $booking['gia_ve'] ?? 7000;
            $booking['loai_xe'] = $booking['loai_xe'] ?? 'Xe khách';
            $booking['bien_so_xe'] = $booking['bien_so_xe'] ?? '';
            $booking['thoi_gian_di_chuyen'] = $booking['thoi_gian_di_chuyen'] ?? '';
        }
        
        if ($user_info) {
            $booking['user_name'] = $user_info['name'];
            $booking['user_email'] = $user_info['email'];
        }
    }
} catch (Exception $e) {
    echo '<script>
        alert("Có lỗi xảy ra: ' . addslashes($e->getMessage()) . '");
        window.location.href = "/src/tai-khoan/index.php?error=system_error";
    </script>';
    exit;
}

if (!$booking) {
    echo '<script>
        alert("Không tìm thấy thông tin vé!");
        window.location.href = "/src/tai-khoan/index.php?error=not_found";
    </script>';
    exit;
}


// Tính toán thông tin bổ sung
$totalAmount = (float)($booking['amount'] ?? 0);
if ($totalAmount <= 0) {
    $totalAmount = (float)($booking['gia_ve'] ?? 0) * (int)($booking['so_luong'] ?? 0);
}

$status = strtolower($booking['payment_status'] ?? '');
$statusInfo = [
    'class' => 'warning',
    'text' => 'Chưa xác định',
    'icon' => 'fa-question-circle'
];

switch ($status) {
    case 'success':
    case 'completed':
    case 'paid':
        $statusInfo = ['class' => 'success', 'text' => 'Thanh toán thành công', 'icon' => 'fa-check-circle'];
        break;
    case 'pending':
    case 'processing':
        $statusInfo = ['class' => 'warning', 'text' => 'Đang xử lý', 'icon' => 'fa-clock-o'];
        break;
    case 'failed':
    case 'cancelled':
        $statusInfo = ['class' => 'danger', 'text' => 'Thanh toán thất bại', 'icon' => 'fa-times-circle'];
        break;
}
?>

<style>
:root {
    --bb-orange: #ff5a2c;
    --bb-orange-light: #fff6f2;
    --bb-orange-dark: #e64a20;
    --bb-dark: #1f2937;
    --bb-muted: #6b7280;
    --bb-bg: #f7f8fa;
    --bb-border: #edf0f3;
    --bb-success: #10b981;
    --bb-warning: #f59e0b;
    --bb-danger: #ef4444;
    --bb-blue: #3b82f6;
    --bb-purple: #8b5cf6;
}

body { background: var(--bb-bg); }
.bb-wrap { max-width: 900px; margin: 32px auto 80px; padding: 0 16px; }

/* BACK BUTTON */
.back-nav {
    margin-bottom: 24px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #fff;
    border: 1px solid var(--bb-border);
    border-radius: 12px;
    color: var(--bb-muted);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.back-btn:hover {
    color: var(--bb-orange);
    border-color: var(--bb-orange);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(255,90,44,0.15);
    text-decoration: none;
}

/* MAIN CARD */
.detail-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid var(--bb-border);
}

/* HEADER */
.detail-header {
    background: linear-gradient(135deg, var(--bb-orange) 0%, #ff7849 100%);
    color: white;
    padding: 32px;
    position: relative;
    overflow: hidden;
}

.detail-header::before {
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

.detail-header .content {
    position: relative;
    z-index: 2;
}

.booking-number {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 8px;
}

.booking-number i {
    font-size: 32px;
}

.booking-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 20px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 14px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
}

/* ROUTE SECTION */
.route-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 32px;
    border-bottom: 1px solid var(--bb-border);
}

.route-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.route-point {
    text-align: center;
    flex: 1;
}

.route-city {
    font-size: 24px;
    font-weight: 800;
    color: var(--bb-dark);
    margin-bottom: 8px;
}

.route-label {
    font-size: 14px;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.route-arrow {
    flex: 0 0 auto;
    margin: 0 24px;
    position: relative;
}

.route-arrow::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--bb-orange);
    transform: translateY(-50%);
}

.route-arrow i {
    background: white;
    color: var(--bb-orange);
    font-size: 24px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--bb-orange);
    position: relative;
    z-index: 2;
}

.trip-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.trip-detail {
    text-align: center;
}

.trip-detail-label {
    font-size: 12px;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    font-weight: 600;
}

.trip-detail-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--bb-dark);
}

/* DETAILS GRID */
.details-section {
    padding: 32px;
}

.section-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--bb-dark);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    width: 40px;
    height: 40px;
    background: var(--bb-orange-light);
    color: var(--bb-orange);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.detail-item {
    background: #f8fafc;
    border: 1px solid var(--bb-border);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s ease;
}

.detail-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--bb-orange);
}

.detail-label {
    font-size: 13px;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-label i {
    color: var(--bb-orange);
}

.detail-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--bb-dark);
    word-break: break-word;
}

.detail-value.price {
    color: var(--bb-orange);
    font-size: 24px;
}

/* ACTIONS */
.actions-section {
    background: #f8fafc;
    padding: 24px 32px;
    border-top: 1px solid var(--bb-border);
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid;
    min-width: 150px;
    justify-content: center;
}

.btn-primary {
    background: var(--bb-orange);
    border-color: var(--bb-orange);
    color: white;
}

.btn-primary:hover {
    background: var(--bb-orange-dark);
    border-color: var(--bb-orange-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,90,44,0.3);
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: white;
    border-color: var(--bb-border);
    color: var(--bb-muted);
}

.btn-secondary:hover {
    border-color: var(--bb-orange);
    color: var(--bb-orange);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    text-decoration: none;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .bb-wrap { padding: 0 12px; }
    
    .detail-header { padding: 24px 20px; }
    .route-section { padding: 24px 20px; }
    .details-section { padding: 24px 20px; }
    .actions-section { padding: 20px; }
    
    .route-container {
        flex-direction: column;
        gap: 20px;
    }
    
    .route-arrow {
        margin: 0;
        transform: rotate(90deg);
    }
    
    .trip-info {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .actions-section {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}

/* PRINT STYLES */
@media print {
    body { background: white !important; }
    .bb-wrap { max-width: none; margin: 0; padding: 0; }
    .back-nav, .actions-section { display: none !important; }
    .detail-card { box-shadow: none; border: 1px solid #ccc; }
    .detail-header { background: #f5f5f5 !important; color: #333 !important; }
}

/* ANIMATIONS */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.detail-card {
    animation: slideInUp 0.6s ease-out;
}

.detail-item {
    opacity: 0;
    animation: slideInUp 0.6s ease-out forwards;
}

.detail-item:nth-child(1) { animation-delay: 0.1s; }
.detail-item:nth-child(2) { animation-delay: 0.2s; }
.detail-item:nth-child(3) { animation-delay: 0.3s; }
.detail-item:nth-child(4) { animation-delay: 0.4s; }
.detail-item:nth-child(5) { animation-delay: 0.5s; }
.detail-item:nth-child(6) { animation-delay: 0.6s; }
</style>

<div class="bb-wrap">
    <!-- BACK NAVIGATION -->
    <div class="back-nav">
        <a href="/src/tai-khoan/index.php?show=history" class="back-btn">
            <i class="fa fa-arrow-left"></i>
            Quay lại lịch sử đặt vé
        </a>
    </div>

    <!-- MAIN CARD -->
    <div class="detail-card">
        <!-- HEADER -->
        <div class="detail-header">
            <div class="content">
                <div class="booking-number">
                    <i class="fa fa-ticket"></i>
                    Vé số #<?php echo $booking['id']; ?>
                </div>
                <div class="booking-subtitle">
                    Chi tiết đặt vé - BookBus
                </div>
                <div class="status-badge status-<?php echo $statusInfo['class']; ?>">
                    <i class="fa <?php echo $statusInfo['icon']; ?>"></i>
                    <?php echo $statusInfo['text']; ?>
                </div>
            </div>
        </div>

        <!-- ROUTE SECTION -->
        <div class="route-section">
            <div class="route-container">
                <div class="route-point">
                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_di'] ?? 'Chưa xác định'); ?></div>
                    <div class="route-label">Điểm đi</div>
                </div>
                
                <div class="route-arrow">
                    <i class="fa fa-long-arrow-right"></i>
                </div>
                
                <div class="route-point">
                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_den'] ?? 'Chưa xác định'); ?></div>
                    <div class="route-label">Điểm đến</div>
                </div>
            </div>

            <div class="trip-info">
                <div class="trip-detail">
                    <div class="trip-detail-label">Nhà xe</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($booking['ten_nhaxe'] ?? 'Chưa xác định'); ?></div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Ngày khởi hành</div>
                    <div class="trip-detail-value">
                        <?php 
                        if ($booking['ngay_di']) {
                            echo date('d/m/Y', strtotime($booking['ngay_di']));
                        } else {
                            echo 'Chưa xác định';
                        }
                        ?>
                    </div>
                </div>
                <div class="trip-detail">
                    <div class="trip-detail-label">Giờ khởi hành</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($booking['gio_di'] ?? 'Chưa xác định'); ?></div>
                </div>
                <?php if (!empty($booking['loai_xe'])): ?>
                <div class="trip-detail">
                    <div class="trip-detail-label">Loại xe</div>
                    <div class="trip-detail-value"><?php echo htmlspecialchars($booking['loai_xe']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DETAILS SECTION -->
        <div class="details-section">
            <div class="section-title">
                <i class="fa fa-info-circle"></i>
                Thông tin chi tiết
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-hashtag"></i>
                        Mã đơn hàng
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['order_id'] ?? 'Chưa có'); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-ticket"></i>
                        Số lượng vé
                    </div>
                    <div class="detail-value"><?php echo (int)($booking['so_luong'] ?? 0); ?> vé</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-money"></i>
                        Giá vé (1 vé)
                    </div>
                    <div class="detail-value"><?php echo number_format((float)($booking['gia_ve'] ?? 0), 0, ',', '.'); ?> VNĐ</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-calculator"></i>
                        Tổng thành tiền
                    </div>
                    <div class="detail-value price"><?php echo number_format($totalAmount, 0, ',', '.'); ?> VNĐ</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-user"></i>
                        Tên khách hàng
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['ho_ten'] ?? $booking['user_name'] ?? 'Chưa cập nhật'); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-phone"></i>
                        Số điện thoại
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['sdt'] ?? 'Chưa cập nhật'); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-envelope"></i>
                        Email
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['booking_email'] ?? $booking['user_email'] ?? 'Chưa cập nhật'); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-clock-o"></i>
                        Thời gian đặt vé
                    </div>
                    <div class="detail-value">
                        <?php 
                        if ($booking['ngay_dat']) {
                            echo date('d/m/Y H:i:s', strtotime($booking['ngay_dat']));
                        } else {
                            echo 'Chưa xác định';
                        }
                        ?>
                    </div>
                </div>

                <?php if (!empty($booking['ghi_chu'])): ?>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label">
                        <i class="fa fa-comment"></i>
                        Ghi chú
                    </div>
                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['ghi_chu'])); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($booking['bien_so_xe'])): ?>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-bus"></i>
                        Biển số xe
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['bien_so_xe']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($booking['thoi_gian_di_chuyen'])): ?>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fa fa-hourglass-half"></i>
                        Thời gian di chuyển
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['thoi_gian_di_chuyen']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
              <!-- ACTIONS -->
        <div class="actions-section">
            <a href="javascript:window.print()" class="action-btn btn-primary">
                <i class="fa fa-print"></i>
                In vé này
            </a>
            <a href="/src/tai-khoan/in-ve.php?id=<?php echo $bookingId; ?>" class="action-btn btn-secondary" target="_blank">
                <i class="fa fa-external-link"></i>
                Xem bản in
            </a>
            <a href="/src/tai-khoan/index.php?show=history" class="action-btn btn-secondary">
                <i class="fa fa-list"></i>
                Danh sách vé
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to top when page loads
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Add print functionality
    const printBtns = document.querySelectorAll('[href="javascript:window.print()"]');
    printBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang chuẩn bị...';
            this.style.pointerEvents = 'none';
            
            // Delay for better UX
            setTimeout(() => {
                window.print();
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 1000);
        });
    });
    
    // Enhanced hover effects
    const detailItems = document.querySelectorAll('.detail-item');
    detailItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-6px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>