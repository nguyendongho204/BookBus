<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../header.php';

// Kết nối database
if (is_file(__DIR__ . '/../libs/db.php')) {
    require_once __DIR__ . '/../libs/db.php';
}

// Lấy thông tin user
$userId = (int)($user['id'] ?? 0);
$email = (string)($user['email'] ?? '');
$username = (string)($user['name'] ?? $user['username'] ?? '');

// Lấy lịch sử đặt vé
$bookings = [];
$debug_info = [];
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Query với JOIN để lấy thông tin đầy đủ
        $sql = "
            SELECT 
                dv.id, 
                dv.ngay_dat, 
                dv.so_luong, 
                dv.amount, 
                dv.payment_status,
                dv.order_id,
                cx.ten_nhaxe,
                cx.diem_di,
                cx.diem_den,
                cx.ngay_di,
                cx.gio_di,
                cx.gia_ve
            FROM dat_ve dv 
            LEFT JOIN chuyenxe cx ON cx.id = dv.id_chuyen 
            WHERE dv.user_id = :user_id 
            ORDER BY dv.id DESC 
            LIMIT 100
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug_info[] = "PDO connection OK";
        $debug_info[] = "Query executed successfully";
        $debug_info[] = "Found " . count($bookings) . " bookings for user ID {$userId}";
        
    } elseif (isset($conn) && $conn instanceof mysqli) {
        // MySQLi fallback
        $sql = "
            SELECT 
                dv.id, 
                dv.ngay_dat, 
                dv.so_luong, 
                dv.amount, 
                dv.payment_status,
                dv.order_id,
                cx.ten_nhaxe,
                cx.diem_di,
                cx.diem_den,
                cx.ngay_di,
                cx.gio_di,
                cx.gia_ve
            FROM dat_ve dv 
            LEFT JOIN chuyenxe cx ON cx.id = dv.id_chuyen 
            WHERE dv.user_id = ? 
            ORDER BY dv.id DESC 
            LIMIT 100
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $debug_info[] = "MySQLi connection OK";
        $debug_info[] = "Found " . count($bookings) . " bookings";
    } else {
        $debug_info[] = "No database connection available";
    }
} catch (Exception $e) {
    $bookings = [];
    $debug_info[] = "Database error: " . $e->getMessage();
}

// Debug mode
$debug = isset($_GET['debug']);
$showHistory = isset($_GET['show']) && $_GET['show'] === 'history';
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
.bb-wrap { max-width: 1150px; margin: 32px auto 80px; padding: 0 16px; }
.bb-card { background: #fff; border: 1px solid var(--bb-border); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.07); }

/* HERO SECTION */
.bb-hero { 
    display: flex; 
    gap: 20px; 
    align-items: center; 
    padding: 28px 32px; 
    background: linear-gradient(135deg, #ff5a2c 0%, #ff7849 100%); 
    color: white;
    position: relative;
    overflow: hidden;
}

.bb-hero::before {
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

.bb-avatar { 
    width: 80px; 
    height: 80px; 
    border-radius: 50%; 
    display: grid; 
    place-items: center; 
    background: rgba(255,255,255,0.2); 
    color: white; 
    font-weight: 800; 
    font-size: 32px; 
    border: 3px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
    z-index: 2;
    position: relative;
}

.bb-title { font-weight: 800; font-size: 24px; color: white; margin-bottom: 8px; }
.bb-sub { color: rgba(255,255,255,0.9); font-size: 15px; margin-bottom: 16px; }
.bb-logout { 
    color: rgba(255,255,255,0.8); 
    text-decoration: none; 
    padding: 8px 16px;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.bb-logout:hover { 
    background: rgba(255,255,255,0.2);
    color: white;
    text-decoration: none;
}

.bb-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.bb-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
}

/* TABS */
.bb-tabs { padding: 0 24px; background: #fff; }
.bb-tabs .nav-pills { 
    border-bottom: 1px solid var(--bb-border); 
    padding-bottom: 0;
    margin-bottom: 0;
}

.bb-tabs .nav-link { 
    border: none;
    border-radius: 0;
    padding: 16px 20px;
    font-weight: 700; 
    color: var(--bb-muted);
    cursor: pointer;
    background: transparent;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.bb-tabs .nav-link:hover { 
    background: var(--bb-orange-light);
    color: var(--bb-orange);
}

.bb-tabs .nav-link.active { 
    background: transparent;
    color: var(--bb-orange);
    border-bottom-color: var(--bb-orange);
}

.bb-tabs .nav-link i {
    margin-right: 8px;
}

.tab-content { 
    min-height: 500px; 
    padding: 32px 24px;
}

.tab-pane { 
    display: none; 
}

.tab-pane.show.active { 
    display: block; 
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* PROFILE INFO CARDS - NEW DESIGN */
.profile-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--bb-dark);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
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
    font-size: 18px;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.profile-card {
    background: #fff;
    border: 1px solid var(--bb-border);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--bb-orange) 0%, var(--bb-blue) 50%, var(--bb-purple) 100%);
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    border-color: var(--bb-orange);
}

.profile-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.field-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.field-label i {
    width: 20px;
    height: 20px;
    background: var(--bb-orange-light);
    color: var(--bb-orange);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.field-value {
    font-weight: 700;
    font-size: 18px;
    color: var(--bb-dark);
    word-break: break-word;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
}

.role-admin {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.role-staff {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

/* PASSWORD FORM - NEW DESIGN */
.password-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-section {
    background: #fff;
    border: 1px solid var(--bb-border);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 24px;
    position: relative;
}

.form-label {
    font-weight: 600;
    color: var(--bb-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.form-label i {
    color: var(--bb-orange);
}

.form-control {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid var(--bb-border);
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #fff;
    position: relative;
}

.form-control:focus {
    outline: none;
    border-color: var(--bb-orange);
    box-shadow: 0 0 0 4px rgba(255, 90, 44, 0.1);
    transform: translateY(-2px);
}

.form-control:hover {
    border-color: var(--bb-orange);
}

.password-input-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--bb-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    background: var(--bb-orange-light);
    color: var(--bb-orange);
}

.form-actions {
    margin-top: 32px;
    text-align: center;
}

.btn-submit {
    background: linear-gradient(135deg, var(--bb-orange) 0%, var(--bb-orange-dark) 100%);
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 200px;
    justify-content: center;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 90, 44, 0.3);
    filter: brightness(1.05);
}

.btn-submit:active {
    transform: translateY(0);
}

.btn-submit i {
    font-size: 16px;
}

/* Security Tips */
.security-tips {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.security-tips h6 {
    color: #0369a1;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.security-tips ul {
    margin: 0;
    padding-left: 20px;
    color: #075985;
    font-size: 14px;
}

.security-tips li {
    margin-bottom: 6px;
}

/* Booking History Cards - EXISTING DESIGN */
.booking-card {
    background: #fff;
    border: 1px solid var(--bb-border);
    border-radius: 16px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--bb-orange);
}

.booking-header {
    background: linear-gradient(135deg, #ff5a2c 0%, #ff7849 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.booking-id {
    font-weight: 800;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.booking-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-success { background: rgba(16, 185, 129, 0.2); color: var(--bb-success); }
.status-warning { background: rgba(245, 158, 11, 0.2); color: var(--bb-warning); }
.status-danger { background: rgba(239, 68, 68, 0.2); color: var(--bb-danger); }

.booking-body {
    padding: 0;
}

.booking-route {
    background: #f8fafc;
    padding: 20px;
    border-bottom: 1px solid var(--bb-border);
}

.route-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 15px;
}

.route-point {
    text-align: center;
    flex: 1;
}

.route-city {
    font-weight: 800;
    font-size: 16px;
    color: var(--bb-dark);
    margin-bottom: 4px;
}

.route-time {
    font-size: 13px;
    color: var(--bb-muted);
}

.route-arrow {
    color: var(--bb-orange);
    font-size: 20px;
    font-weight: bold;
}

.bus-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    font-size: 14px;
    color: var(--bb-muted);
}

.booking-details {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.detail-item {
    text-align: center;
}

.detail-label {
    font-size: 12px;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.detail-value {
    font-weight: 700;
    color: var(--bb-dark);
    font-size: 15px;
}

.price-value {
    color: var(--bb-orange);
    font-size: 18px;
    font-weight: 800;
}

.booking-footer {
    background: #f8fafc;
    padding: 15px 20px;
    border-top: 1px solid var(--bb-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.booking-date {
    font-size: 13px;
    color: var(--bb-muted);
}

.booking-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid;
    transition: all 0.3s ease;
}

.btn-primary-action {
    background: var(--bb-orange);
    border-color: var(--bb-orange);
    color: white;
}

.btn-secondary-action {
    background: transparent;
    border-color: var(--bb-border);
    color: var(--bb-muted);
}

.btn-action:hover {
    transform: translateY(-1px);
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--bb-muted);
}

.empty-icon {
    font-size: 64px;
    opacity: 0.3;
    margin-bottom: 20px;
}

.summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: center;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 768px) { 
    .bb-hero { 
        flex-direction: column; 
        align-items: flex-start;
        text-align: center;
        padding: 24px 20px;
    }
    
    .bb-hero .bb-avatar {
        align-self: center;
        margin-bottom: 16px;
    }
    
    .profile-grid { 
        grid-template-columns: 1fr; 
    }
    
    .bb-tabs .nav-pills {
        flex-direction: column;
        gap: 0;
    }
    
    .bb-tabs .nav-link {
        text-align: center;
        border-bottom: 1px solid var(--bb-border);
        border-radius: 0;
    }
    
    .bb-tabs .nav-link.active {
        border-bottom: 3px solid var(--bb-orange);
    }
    
    .form-section {
        padding: 20px;
    }
    
    .route-info { 
        flex-direction: column; 
        gap: 10px; 
    }
    
    .route-arrow { 
        transform: rotate(90deg); 
    }
    
    .booking-details { 
        grid-template-columns: repeat(2, 1fr); 
        gap: 15px; 
    }
    
    .booking-header { 
        flex-direction: column; 
        gap: 10px; 
        text-align: center; 
    }
    
    .summary-stats { 
        grid-template-columns: repeat(2, 1fr); 
    }
    
    .bb-badges {
        justify-content: center;
    }
}

/* Loading Animation */
.form-control.loading {
    background-image: linear-gradient(90deg, transparent, rgba(255,90,44,0.1), transparent);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200px 0; }
    100% { background-position: 200px 0; }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--bb-orange);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--bb-orange-dark);
}
</style>

<div class="bb-wrap">
    <div class="bb-card">
        <!-- HERO -->
        <div class="bb-hero">
            <div class="bb-avatar">
                <?php echo strtoupper(mb_substr($username ?: $email ?: 'U', 0, 1, 'UTF-8')); ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="bb-title">Xin chào, <?php echo htmlspecialchars($username ?: 'Người dùng'); ?> 👋</div>
                    <a class="bb-logout" href="/src/dangxuat.php">
                        <i class="fa fa-sign-out"></i> Đăng xuất
                    </a>
                </div>
                <div class="bb-badges">
                    <?php if (($user['role'] ?? 1) === 0): ?>
                        <span class="bb-badge"><i class="fa fa-shield"></i> Admin</span>
                    <?php else: ?>
                        <span class="bb-badge"><i class="fa fa-user"></i> Nhân sự</span>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                        <span class="bb-badge"><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
                    <?php endif; ?>
                    <span class="bb-badge"><i class="fa fa-id-badge"></i> ID: <?php echo $userId; ?></span>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="bb-tabs">
            <ul class="nav nav-pills gap-0" role="tablist">
                <li class="nav-item">
                    <button class="nav-link <?php echo !$showHistory ? 'active' : ''; ?>" data-target="#bb-t1" type="button">
                        <i class="fa fa-id-card-o"></i> Thông tin cá nhân
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-target="#bb-t2" type="button">
                        <i class="fa fa-lock"></i> Đổi mật khẩu
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?php echo $showHistory ? 'active' : ''; ?>" data-target="#bb-t3" type="button">
                        <i class="fa fa-ticket"></i> Lịch sử đặt vé
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- TAB 1: Thông tin cá nhân - NEW DESIGN -->
                <div class="tab-pane <?php echo !$showHistory ? 'show active' : ''; ?>" id="bb-t1">
                    <div class="profile-section">
                        <div class="section-title">
                            <i class="fa fa-user-circle"></i>
                            Thông tin tài khoản
                        </div>
                        
                        <div class="profile-grid">
                            <div class="profile-card">
                                <div class="profile-field">
                                    <div class="field-label">
                                        <i class="fa fa-hashtag"></i>
                                        ID tài khoản
                                    </div>
                                    <div class="field-value">#<?php echo $userId; ?></div>
                                </div>
                            </div>

                            <div class="profile-card">
                                <div class="profile-field">
                                    <div class="field-label">
                                        <i class="fa fa-user"></i>
                                        Tên đăng nhập
                                    </div>
                                    <div class="field-value"><?php echo htmlspecialchars($username ?: 'Chưa cập nhật'); ?></div>
                                </div>
                            </div>

                            <div class="profile-card">
                                <div class="profile-field">
                                    <div class="field-label">
                                        <i class="fa fa-envelope"></i>
                                        Địa chỉ email
                                    </div>
                                    <div class="field-value"><?php echo htmlspecialchars($email ?: 'Chưa cập nhật'); ?></div>
                                </div>
                            </div>

                            <div class="profile-card">
                                <div class="profile-field">
                                    <div class="field-label">
                                        <i class="fa fa-shield"></i>
                                        Vai trò hệ thống
                                    </div>
                                    <div class="field-value">
                                        <?php if (($user['role'] ?? 1) === 0): ?>
                                            <span class="role-badge role-admin">
                                                <i class="fa fa-crown"></i> Quản trị viên
                                            </span>
                                        <?php else: ?>
                                            <span class="role-badge role-staff">
                                                <i class="fa fa-user-check"></i> Người dùng
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Đổi mật khẩu - NEW DESIGN -->
                <div class="tab-pane" id="bb-t2">
                    <div class="password-form">
                        <div class="section-title">
                            <i class="fa fa-key"></i>
                            Đổi mật khẩu
                        </div>
                        
                        <div class="form-section">
                            <form method="POST" action="/src/tai-khoan/doi-mat-khau.php" id="passwordForm">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fa fa-lock"></i>
                                        Mật khẩu hiện tại
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="old_password" class="form-control" required 
                                               placeholder="Nhập mật khẩu hiện tại của bạn">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fa fa-key"></i>
                                        Mật khẩu mới
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="new_password" minlength="8" class="form-control" required 
                                               placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fa fa-check-circle"></i>
                                        Xác nhận mật khẩu mới
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="confirm_password" minlength="8" class="form-control" required 
                                               placeholder="Nhập lại mật khẩu mới">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-submit">
                                        <i class="fa fa-save"></i>
                                        Cập nhật mật khẩu
                                    </button>
                                </div>
                            </form>

                            <div class="security-tips">
                                <h6><i class="fa fa-info-circle"></i> Lời khuyên bảo mật</h6>
                                <ul>
                                    <li>Sử dụng mật khẩu mạnh với ít nhất 8 ký tự</li>
                                    <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                                    <li>Không sử dụng thông tin cá nhân dễ đoán</li>
                                    <li>Thay đổi mật khẩu định kỳ để đảm bảo an toàn</li>
                                    <li>Không chia sẻ mật khẩu với người khác</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Lịch sử đặt vé - EXISTING DESIGN -->
                <div class="tab-pane <?php echo $showHistory ? 'show active' : ''; ?>" id="bb-t3">
                    <?php if ($debug): ?>
                        <div class="alert alert-info">
                            <h6>🔍 Debug Info:</h6>
                            <p><strong>User ID:</strong> <?php echo $userId; ?></p>
                            <p><strong>Email:</strong> <?php echo $email; ?></p>
                            <p><strong>Username:</strong> <?php echo $username; ?></p>
                            <p><strong>Số vé tìm thấy:</strong> <?php echo count($bookings); ?></p>
                            <p><strong>Show History:</strong> <?php echo $showHistory ? 'Yes' : 'No'; ?></p>
                            <?php if (!empty($debug_info)): ?>
                                <p><strong>Debug Log:</strong></p>
                                <ul>
                                    <?php foreach ($debug_info as $info): ?>
                                        <li><?php echo htmlspecialchars($info); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bookings)): ?>
                        <!-- Summary Card -->
                        <div class="summary-card">
                            <h5><i class="fa fa-chart-line"></i> Tổng quan đặt vé</h5>
                            <div class="summary-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo count($bookings); ?></div>
                                    <div class="stat-label">Tổng vé</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $totalAmount = 0;
                                        foreach ($bookings as $b) {
                                            $amount = (float)($b['amount'] ?? 0);
                                            if ($amount > 0) {
                                                $totalAmount += $amount;
                                            } else {
                                                $giaVe = (float)($b['gia_ve'] ?? 0);
                                                $soLuong = (int)($b['so_luong'] ?? 0);
                                                $totalAmount += ($giaVe * $soLuong);
                                            }
                                        }
                                        echo number_format($totalAmount / 1000, 0) . 'K';
                                        ?>
                                    </div>
                                    <div class="stat-label">Tổng tiền (VNĐ)</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $successCount = 0;
                                        foreach ($bookings as $b) {
                                            $status = strtolower($b['payment_status'] ?? '');
                                            if (in_array($status, ['success', 'completed', 'paid'])) {
                                                $successCount++;
                                            }
                                        }
                                        echo $successCount;
                                        ?>
                                    </div>
                                    <div class="stat-label">Thành công</div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Cards -->
                        <div class="booking-list">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card">
                                    <!-- Header -->
                                    <div class="booking-header">
                                        <div class="booking-id">
                                            <i class="fa fa-ticket"></i>
                                            #<?php echo (int)$booking['id']; ?>
                                        </div>
                                        <div class="booking-status <?php 
                                            $status = strtolower($booking['payment_status'] ?? '');
                                            switch ($status) {
                                                case 'success':
                                                case 'completed':
                                                case 'paid':
                                                    echo 'status-success';
                                                    break;
                                                case 'pending':
                                                case 'processing':
                                                    echo 'status-warning';
                                                    break;
                                                case 'failed':
                                                case 'cancelled':
                                                    echo 'status-danger';
                                                    break;
                                                default:
                                                    echo 'status-warning';
                                            }
                                        ?>">
                                            <?php 
                                            switch ($status) {
                                                case 'success':
                                                case 'completed':
                                                case 'paid':
                                                    echo 'Thành công';
                                                    break;
                                                case 'pending':
                                                case 'processing':
                                                    echo 'Đang xử lý';
                                                    break;
                                                case 'failed':
                                                case 'cancelled':
                                                    echo 'Thất bại';
                                                    break;
                                                default:
                                                    echo htmlspecialchars($booking['payment_status'] ?? 'Chưa xác định');
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div class="booking-body">
                                        <!-- Route Info -->
                                        <div class="booking-route">
                                            <div class="route-info">
                                                <div class="route-point">
                                                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_di'] ?? 'Chưa xác định'); ?></div>
                                                    <div class="route-time">Điểm đi</div>
                                                </div>
                                                <div class="route-arrow">→</div>
                                                <div class="route-point">
                                                    <div class="route-city"><?php echo htmlspecialchars($booking['diem_den'] ?? 'Chưa xác định'); ?></div>
                                                    <div class="route-time">Điểm đến</div>
                                                </div>
                                            </div>
                                            <div class="bus-info">
                                                <span><i class="fa fa-bus"></i> <?php echo htmlspecialchars($booking['ten_nhaxe'] ?? 'Chưa xác định'); ?></span>
                                                <?php if ($booking['ngay_di'] && $booking['gio_di']): ?>
                                                    <span><i class="fa fa-calendar"></i> 
                                                        <?php 
                                                        try {
                                                            echo date('d/m/Y', strtotime($booking['ngay_di'])) . ' - ' . htmlspecialchars($booking['gio_di']);
                                                        } catch (Exception $e) {
                                                            echo htmlspecialchars($booking['ngay_di']) . ' - ' . htmlspecialchars($booking['gio_di']);
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Details -->
                                        <div class="booking-details">
                                            <div class="detail-item">
                                                <div class="detail-label">Số lượng vé</div>
                                                <div class="detail-value"><?php echo (int)($booking['so_luong'] ?? 0); ?> vé</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Thành tiền</div>
                                                <div class="detail-value price-value">
                                                    <?php 
                                                    $amount = (float)($booking['amount'] ?? 0);
                                                    if ($amount > 0) {
                                                        echo number_format($amount, 0, ',', '.') . ' VNĐ';
                                                    } else {
                                                        $giaVe = (float)($booking['gia_ve'] ?? 0);
                                                        $soLuong = (int)($booking['so_luong'] ?? 0);
                                                        $calculated = $giaVe * $soLuong;
                                                        if ($calculated > 0) {
                                                            echo number_format($calculated, 0, ',', '.') . ' VNĐ';
                                                        } else {
                                                            echo '0 VNĐ';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($booking['order_id'])): ?>
                                            <div class="detail-item">
                                                <div class="detail-label">Mã đơn hàng</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($booking['order_id']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="booking-footer">
                                        <div class="booking-date">
                                            <i class="fa fa-clock-o"></i>
                                            Đặt lúc: 
                                            <?php 
                                            $ngayDat = $booking['ngay_dat'] ?? '';
                                            if ($ngayDat) {
                                                try {
                                                    echo date('d/m/Y H:i', strtotime($ngayDat));
                                                } catch (Exception $e) {
                                                    echo htmlspecialchars($ngayDat);
                                                }
                                            } else {
                                                echo 'Không xác định';
                                            }
                                            ?>
                                        </div>
                                        <div class="booking-actions">
                                            <a href="/src/tai-khoan/don.php?id=<?php echo $booking['id']; ?>" class="btn-action btn-secondary-action">
                                                <i class="fa fa-eye"></i> Chi tiết
                                            </a>
                                            <?php if (in_array(strtolower($booking['payment_status'] ?? ''), ['success', 'completed', 'paid'])): ?>
                                                <a href="/src/tai-khoan/in-ve.php?id=<?php echo $booking['id']; ?>" class="btn-action btn-primary-action">
                                                    <i class="fa fa-print"></i> In vé
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fa fa-ticket"></i>
                            </div>
                            <h5>Chưa có lịch sử đặt vé</h5>
                            <p>Bạn chưa đặt vé nào. Hãy <a href="/src/timkiemchuyenxe.php" class="text-decoration-none" style="color: var(--bb-orange);">đặt vé ngay</a>!</p>
                            <?php if ($debug): ?>
                                <div class="mt-3 text-start">
                                    <small class="text-muted">
                                        <strong>Kiểm tra:</strong><br>
                                        • User ID hiện tại: <?php echo $userId; ?><br>
                                        • Query: SELECT * FROM dat_ve WHERE user_id = <?php echo $userId; ?><br>
                                        • Database connection: <?php echo isset($pdo) ? 'PDO OK' : (isset($conn) ? 'MySQLi OK' : 'NONE'); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
    }
}

// Enhanced form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = this.querySelector('input[name="new_password"]').value;
    const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
        return false;
    }
    
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Mật khẩu mới phải có ít nhất 8 ký tự!');
        return false;
    }
    
    // Add loading state
    const submitBtn = this.querySelector('.btn-submit');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Add loading animation to form inputs
    this.querySelectorAll('.form-control').forEach(input => {
        input.classList.add('loading');
    });
});

// Tab switching logic
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing tabs...');
    
    // Get all tab buttons
    const tabButtons = document.querySelectorAll('.nav-link[data-target]');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    console.log('Found', tabButtons.length, 'tab buttons and', tabPanes.length, 'tab panes');
    
    // Add click event to each tab button
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            console.log('Tab clicked:', this.getAttribute('data-target'));
            
            // Remove active class from all buttons
            tabButtons.forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            // Hide all tab panes
            tabPanes.forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show target pane
            const targetId = this.getAttribute('data-target');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                console.log('Showing pane:', targetId);
            } else {
                console.error('Target pane not found:', targetId);
            }
        });
    });
    
    // Auto show history tab if requested
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show') === 'history') {
        console.log('Auto-showing history tab');
        const historyTab = document.querySelector('[data-target="#bb-t3"]');
        if (historyTab) {
            historyTab.click();
            setTimeout(function() {
                document.getElementById('bb-t3').scrollIntoView({behavior: 'smooth', block: 'start'});
            }, 200);
        }
    }
    
    // Smooth animations for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Apply animation to profile cards
    document.querySelectorAll('.profile-card').forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
    
    // Apply animation to booking cards
    document.querySelectorAll('.booking-card').forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
});

// Handle password change notifications
(function(){
    const params = new URLSearchParams(location.search);
    const pwd = params.get('pwd');
    if (!pwd) return;
    
    // Show password tab
    const trigger = document.querySelector('[data-target="#bb-t2"]');
    if (trigger) {
        trigger.click();
    }
    
    // Show notification
    const msgMap = {
        ok: 'Đổi mật khẩu thành công.',
        wrong_old: 'Mật khẩu hiện tại không đúng.',
        mismatch: 'Mật khẩu mới không khớp.',
        too_short: 'Mật khẩu mới tối thiểu 8 ký tự.',
        sys: 'Có lỗi hệ thống. Vui lòng thử lại.'
    };
    
    const alert = document.createElement('div');
    alert.className = 'alert ' + (pwd === 'ok' ? 'alert-success' : 'alert-danger') + ' mt-3';
    alert.textContent = msgMap[pwd] || 'Thao tác không hợp lệ.';
    alert.style.cssText = `
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    `;
    
    if (pwd === 'ok') {
        alert.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        alert.style.color = 'white';
        alert.innerHTML = '<i class="fa fa-check-circle"></i> ' + alert.textContent;
    } else {
        alert.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
        alert.style.color = 'white';
        alert.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + alert.textContent;
    }
    
    const passwordForm = document.querySelector('.password-form');
    if (passwordForm) {
        passwordForm.insertBefore(alert, passwordForm.firstChild);
    }
    
    // Remove param from URL
    params.delete('pwd');
    const newUrl = location.pathname + (params.toString() ? ('?' + params.toString()) : '');
    history.replaceState(null, '', newUrl);
    
    // Auto hide notification after 5 seconds
    setTimeout(function() {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(function() {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 500);
    }, 5000);
})();

// Add floating effect to profile cards
document.querySelectorAll('.profile-card').forEach(function(card) {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Password strength indicator
document.querySelector('input[name="new_password"]').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength');
    
    if (!strengthBar) {
        const bar = document.createElement('div');
        bar.id = 'password-strength';
        bar.style.cssText = `
            height: 4px;
            background: #f1f1f1;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        `;
        this.parentElement.appendChild(bar);
        
        const fill = document.createElement('div');
        fill.id = 'strength-fill';
        fill.style.cssText = `
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        `;
        bar.appendChild(fill);
    }
    
    const fill = document.getElementById('strength-fill');
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    
    fill.style.width = strength + '%';
    
    if (strength < 50) {
        fill.style.background = '#ef4444';
    } else if (strength < 75) {
        fill.style.background = '#f59e0b';
    } else {
        fill.style.background = '#10b981';
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>