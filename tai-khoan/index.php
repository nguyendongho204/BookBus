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
    --bb-dark: #1f2937;
    --bb-muted: #6b7280;
    --bb-bg: #f7f8fa;
    --bb-border: #edf0f3;
}

body { background: var(--bb-bg); }
.bb-wrap { max-width: 1150px; margin: 32px auto 80px; padding: 0 16px; }
.bb-card { background: #fff; border: 1px solid var(--bb-border); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.07); }
.bb-hero { display: flex; gap: 20px; align-items: center; padding: 22px 26px; background: linear-gradient(180deg,#fbfcff 0%, #fff 100%); border-bottom: 1px solid var(--bb-border); }
.bb-avatar { width: 70px; height: 70px; border-radius: 50%; display: grid; place-items: center; background: #fff6f2; color: var(--bb-orange); font-weight: 800; font-size: 28px; border: 1px solid #ffe3d9; }
.bb-title { font-weight: 800; font-size: 22px; color: var(--bb-dark); }
.bb-sub { color: var(--bb-muted); font-size: 14px; }
.bb-logout { color: #64748b; text-decoration: none; }
.bb-logout:hover { color: #ef4444; }

.bb-tabs { padding: 0 18px; }
.bb-tabs .nav-link { border: 1px solid transparent; border-radius: 999px; padding: .5rem .9rem; font-weight: 700; color: #374151; cursor: pointer; }
.bb-tabs .nav-link:hover { background: #f6f7f9; }
.bb-tabs .nav-link.active { background: var(--bb-orange); border-color: var(--bb-orange); color: #fff; box-shadow: 0 6px 16px rgba(255,90,44,.25); }
.bb-pane { padding: 22px 8px 26px; }

.bb-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
.bb-info { background: #fff; border: 1px solid var(--bb-border); border-radius: 14px; padding: 14px 16px; }
.bb-info .lbl { font-size: 12px; color: var(--bb-muted); letter-spacing: .02em; margin-bottom: 6px; text-transform: uppercase; }
.bb-info .val { font-weight: 800; color: #111827; }

.bb-form .form-control { border-radius: 12px; border: 1px solid var(--bb-border); }
.bb-form .form-control:focus { border-color: var(--bb-orange); box-shadow: 0 0 0 .2rem rgba(255,90,44,.15); }
.bb-form .btn { border-radius: 12px; padding: .7rem 1.1rem; }
.btn-bb { background: var(--bb-orange); border-color: var(--bb-orange); color: white; }
.btn-bb:hover { filter: brightness(.95); color: white; }

.bb-table { border: 1px solid var(--bb-border); border-radius: 14px; overflow: hidden; }
.bb-empty { display: flex; align-items: center; justify-content: center; gap: 10px; color: var(--bb-muted); background: #fff; border: 1px dashed var(--bb-border); border-radius: 14px; padding: 28px; text-align: center; }

/* Tab content - đảm bảo hiển thị đúng */
.tab-content { min-height: 400px; }
.tab-pane { display: none; }
.tab-pane.show.active { display: block; }

@media (max-width: 768px) { 
    .bb-hero { flex-direction: column; align-items: flex-start; } 
    .bb-grid { grid-template-columns: 1fr; } 
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
                    <a class="bb-logout small" href="/src/dangxuat.php"><i class="fa fa-sign-out"></i> Đăng xuất</a>
                </div>
                <div class="bb-sub mt-1">Quản lý thông tin cá nhân, đổi mật khẩu và xem lịch sử đặt vé của bạn.</div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <?php if (($user['role'] ?? 1) === 0): ?>
                        <span class="badge text-bg-primary"><i class="fa fa-shield"></i> Admin</span>
                    <?php else: ?>
                        <span class="badge text-bg-success"><i class="fa fa-user"></i> Nhân sự</span>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                        <span class="badge text-bg-light"><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($email); ?></span>
                    <?php endif; ?>
                    <span class="badge text-bg-light"><i class="fa fa-id-badge"></i> ID: <?php echo $userId; ?></span>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="bb-tabs">
            <ul class="nav nav-pills gap-2 mt-3" role="tablist">
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

            <div class="tab-content bb-pane">
                <!-- TAB 1: Thông tin cá nhân -->
                <div class="tab-pane <?php echo !$showHistory ? 'show active' : ''; ?>" id="bb-t1">
                    <div class="bb-grid">
                        <div class="bb-info">
                            <span class="lbl">ID</span>
                            <div class="val"><?php echo $userId; ?></div>
                        </div>
                        <div class="bb-info">
                            <span class="lbl">Tên đăng nhập</span>
                            <div class="val"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        <div class="bb-info">
                            <span class="lbl">Email</span>
                            <div class="val"><?php echo htmlspecialchars($email); ?></div>
                        </div>
                        <div class="bb-info">
                            <span class="lbl">Vai trò</span>
                            <div class="val"><?php echo (($user['role'] ?? 1) === 0) ? 'Admin' : 'Nhân sự'; ?></div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Đổi mật khẩu -->
                <div class="tab-pane" id="bb-t2">
                    <form class="bb-form row g-3" method="POST" action="/src/tai-khoan/doi-mat-khau.php">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="new_password" minlength="8" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhập lại mật khẩu mới</label>
                            <input type="password" name="confirm_password" minlength="8" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-bb">
                                <i class="fa fa-save"></i> Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: Lịch sử đặt vé -->
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
                        <div class="table-responsive">
                            <table class="table bb-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Ngày đặt</th>
                                        <th>Nhà xe</th>
                                        <th>Tuyến đường</th>
                                        <th>Ngày đi</th>
                                        <th>SL</th>
                                        <th>Thành tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><strong>#<?php echo (int)$booking['id']; ?></strong></td>
                                            <td>
                                                <?php 
                                                $ngayDat = $booking['ngay_dat'] ?? '';
                                                if ($ngayDat) {
                                                    try {
                                                        echo date('d/m/Y H:i', strtotime($ngayDat));
                                                    } catch (Exception $e) {
                                                        echo htmlspecialchars($ngayDat);
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['ten_nhaxe'] ?? 'Chưa xác định'); ?></td>
                                            <td>
                                                <?php 
                                                $diemDi = $booking['diem_di'] ?? '';
                                                $diemDen = $booking['diem_den'] ?? '';
                                                if ($diemDi && $diemDen) {
                                                    echo htmlspecialchars($diemDi) . ' → ' . htmlspecialchars($diemDen);
                                                } else {
                                                    echo 'Chưa xác định';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $ngayDi = $booking['ngay_di'] ?? '';
                                                $gioDi = $booking['gio_di'] ?? '';
                                                if ($ngayDi) {
                                                    try {
                                                        echo date('d/m/Y', strtotime($ngayDi));
                                                        if ($gioDi) {
                                                            echo '<br><small class="text-muted">' . htmlspecialchars($gioDi) . '</small>';
                                                        }
                                                    } catch (Exception $e) {
                                                        echo htmlspecialchars($ngayDi);
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo (int)($booking['so_luong'] ?? 0); ?></td>
                                            <td>
                                                <strong>
                                                    <?php 
                                                    $amount = (float)($booking['amount'] ?? 0);
                                                    if ($amount > 0) {
                                                        echo number_format($amount, 0, ',', '.') . ' VNĐ';
                                                    } else {
                                                        // Fallback: tính từ giá vé * số lượng
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
                                                </strong>
                                            </td>
                                            <td>
                                                <?php 
                                                $status = $booking['payment_status'] ?? '';
                                                $badgeClass = 'text-bg-secondary';
                                                $statusText = 'Chưa xác định';
                                                
                                                switch (strtolower($status)) {
                                                    case 'success':
                                                    case 'completed':
                                                    case 'paid':
                                                        $badgeClass = 'text-bg-success';
                                                        $statusText = 'Thành công';
                                                        break;
                                                    case 'pending':
                                                    case 'processing':
                                                        $badgeClass = 'text-bg-warning';
                                                        $statusText = 'Đang xử lý';
                                                        break;
                                                    case 'failed':
                                                    case 'cancelled':
                                                        $badgeClass = 'text-bg-danger';
                                                        $statusText = 'Thất bại';
                                                        break;
                                                    default:
                                                        if ($status) {
                                                            $statusText = htmlspecialchars($status);
                                                        }
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-center">
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                Tổng cộng: <strong><?php echo count($bookings); ?> vé</strong>
                                | Tổng tiền: <strong>
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
                                    echo number_format($totalAmount, 0, ',', '.') . ' VNĐ';
                                    ?>
                                </strong>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="bb-empty">
                            <div>
                                <i class="fa fa-ticket" style="font-size: 48px; opacity: 0.3;"></i>
                                <h5 class="mt-3">Chưa có lịch sử đặt vé</h5>
                                <p class="text-muted">Bạn chưa đặt vé nào. Hãy <a href="/src/" class="text-decoration-none">đặt vé ngay</a>!</p>
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
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
    
    const tabsContainer = document.querySelector('.bb-tabs');
    if (tabsContainer) {
        tabsContainer.insertBefore(alert, tabsContainer.firstChild);
    }
    
    // Remove param from URL
    params.delete('pwd');
    const newUrl = location.pathname + (params.toString() ? ('?' + params.toString()) : '');
    history.replaceState(null, '', newUrl);
})();
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>