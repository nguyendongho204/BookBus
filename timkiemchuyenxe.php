
<?php
// Fix session configuration
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);

// Start session nếu chưa có
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Debug session info
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("Current URL: " . $_SERVER['REQUEST_URI']);

include __DIR__ . '/libs/db_chuyenxe.php';

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = false;

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    if (isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id'])) {
        $isLoggedIn = true;
    }
}

// Debug code - thêm vào URL ?debug=1 để xem
if (isset($_GET['debug'])) {
    echo "<div style='position:fixed;top:0;left:0;background:white;z-index:9999;padding:15px;border:2px solid red;max-width:500px;'>";
    echo "<strong>🔍 SESSION DEBUG:</strong><br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session Status: " . session_status() . "<br>";
    echo "Cookie Path: " . ini_get('session.cookie_path') . "<br>";
    echo "Cookie Domain: " . ini_get('session.cookie_domain') . "<br>";
    echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "<strong>Session Data:</strong><pre>";
    print_r($_SESSION);
    echo "</pre>";
    echo "<strong>Login Status:</strong> " . ($isLoggedIn ? '✅ LOGGED IN' : '❌ NOT LOGGED IN');
    echo "<br><button onclick='this.parentElement.style.display=\"none\"'>Close</button>";
    echo "</div>";
}


$diem_di = $_GET['diem_di'] ?? '';
$diem_den = $_GET['diem_den'] ?? '';
$ngay_khoi_hanh = $_GET['ngay_khoi_hanh'] ?? '';
$so_khach = $_GET['so_khach'] ?? 1;

$query = "SELECT * FROM chuyenxe WHERE diem_di LIKE :diem_di AND diem_den LIKE :diem_den AND ngay_di = :ngay_di";
$stmt = $pdo->prepare($query);
$stmt->execute([
    ':diem_di' => "%$diem_di%",
    ':diem_den' => "%$diem_den%",
    ':ngay_di' => $ngay_khoi_hanh
]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm chuyến xe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/owl.carousel.css" rel="stylesheet">
    <link href="css/awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap-utilities.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap-buttons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head> 
 <style>
        .trip-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .price {
            color: #ff4500;
            font-size: 22px;
            font-weight: bold;
        }
        .btn-book {
            background-color: #ff4500;
            color: white;
            font-weight: bold;
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
        }
        .time-info {
            font-weight: bold;
            font-size: 18px;
        }
        .sub-info {
            font-size: 14px;
            color: gray;
        }
        .header-custom {
            all: unset; 
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 5px 20px !important;
            background-color: white !important;
            font-family: Arial, sans-serif !important;
        }
        .header-custom ul {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            list-style: none !important;
            margin: 0 !important;
        }
    </style>
<body>
<!-- Lưu trạng thái đăng nhập cho JavaScript -->
<script>
    // Lưu trạng thái đăng nhập vào biến toàn cục để JavaScript sử dụng
    window.isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
</script>

<?php include 'header.php'; ?>
<?php if (!empty($_GET['success'])): ?>
  <div class="alert alert-success" style="max-width:960px;margin:16px auto;">
    ✅ Đặt vé thành công!
  </div>
<?php endif; ?>
<div class="container mt-4 content-timkiem">
<?php include 'timvexenhanh.php'; ?>
<h3 class="mb-4">Kết quả tìm kiếm chuyến xe</h3>

<?php if (count($results) > 0): ?>
    <?php foreach ($results as $row): 
        $gio_di = new DateTime($row['gio_di']);
        $gio_den = new DateTime($row['gio_den']);
        $diff = $gio_di->diff($gio_den);
        $duration = $diff->h . " giờ " . $diff->i . " phút";
    ?>
    <div class="trip-card row align-items-center">
        <!-- Bên trái: thông tin xe -->
        <div class="col-md-8">
            <h5 class="fw-bold"><?php echo htmlspecialchars($row['ten_nhaxe']); ?></h5>
            <p class="sub-info"><?php echo htmlspecialchars($row['loai_xe']); ?></p>
            <div class="d-flex align-items-center">
                <div class="me-4 text-center">
                    <div class="time-info"><?php echo $gio_di->format('H:i'); ?></div>
                    <div class="sub-info"><?php echo htmlspecialchars($row['diem_di']); ?></div>
                </div>
                <div class="mx-3 fs-4">→</div>
                <div class="me-4 text-center">
                    <div class="time-info"><?php echo $gio_den->format('H:i'); ?></div>
                    <div class="sub-info"><?php echo htmlspecialchars($row['diem_den']); ?></div>
                </div>
                <div class="ms-4">
                    <div class="fw-bold"><?php echo $duration; ?></div>
                    <div class="sub-info">Còn <?php echo $row['so_ghe_con']; ?> ghế</div>
                </div>
            </div>
        </div>

        <!-- Bên phải: giá và nút đặt -->
        <div class="col-md-4 text-end">
            <div class="price">
                <?php echo number_format($row['gia_ve'], 0, ',', '.'); ?> VND <small>/khách</small>
            </div>
            <div class="mt-2">
                <?php if ($isLoggedIn): ?>
                    <!-- Đã đăng nhập - cho phép đặt vé trực tiếp -->
                    <a href="payments/checkout_mock.php?id_chuyen=<?= $row['id'] ?>&so_khach=<?= $so_khach ?>" class="btn-book">Đặt Ngay</a>
                <?php else: ?>
                    <!-- Chưa đăng nhập - hiện modal -->
                    <a href="javascript:void(0)" onclick="showLoginRequiredModal(); return false;" class="btn-book">Đặt Ngay</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-warning">❌ Không tìm thấy chuyến xe nào phù hợp.</div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="includes/js/jquery-1.12.4.min.js"></script>
<script src="includes/js/script.js"></script>
<script>
// Lưu trạng thái đăng nhập cho JavaScript
window.isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

// Function để check trạng thái đăng nhập real-time
function checkLoginStatusRealtime() {
    return fetch('/src/includes/whoami.php', {
        credentials: 'same-origin',
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.ok && (data.id || data.user_id)) {
            window.isUserLoggedIn = true;
            return true;
        } else {
            window.isUserLoggedIn = false;
            return false;
        }
    })
    .catch(error => {
        console.log('Error checking login status:', error);
        window.isUserLoggedIn = false;
        return false;
    });
}

function showLoginRequiredModal() {
    // Kiểm tra real-time trước khi hiện modal
    checkLoginStatusRealtime().then(function(isLoggedIn) {
        if (isLoggedIn) {
            console.log('User is actually logged in, reloading page...');
            // Nếu user đã đăng nhập thực sự, reload trang
            window.location.reload();
            return;
        }
        
        // Nếu chưa đăng nhập, hiện modal
        const modalHTML = `
            <div id="loginRequiredModal" class="modal" style="display:block; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
                <div style="background:#fff; width:430px; margin:8% auto; padding:0; border-radius:10px; position:relative; box-shadow: 0 5px 25px rgba(0,0,0,0.15); overflow:hidden; animation: modalFadeIn 0.3s ease;">
                    <!-- Header với gradient -->
                    <div style="background: linear-gradient(135deg, #f4511e, #ff7043); padding: 18px 25px; text-align: left; position: relative;">
                        <h3 style="color:#fff; margin:0; font-size:20px; font-weight:500; display: flex; align-items: center;">
                            <i class="fa fa-lock" style="margin-right:10px; font-size:18px;"></i>
                            Yêu cầu đăng nhập
                        </h3>
                        <span onclick="closeLoginRequiredModal()" style="position:absolute; top:15px; right:18px; font-size:22px; cursor:pointer; color:white;">&times;</span>
                    </div>
                    
                    <!-- Body -->
                    <div style="padding:25px 30px; text-align:center;">
                        <!-- Icon -->
                        <div style="margin:5px auto 20px; width:75px; height:75px; background:#fff8f0; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-user-circle" style="font-size:45px; color:#f4511e;"></i>
                        </div>
                        
                        <!-- Nội dung -->
                        <p style="color:#444; line-height:1.5; font-size:16px; margin-bottom:8px;">
                            Vui lòng đăng nhập để tiếp tục đặt vé.
                        </p>
                        <p style="color:#28a745; font-size:15px; margin-bottom:25px;">
                            Nếu chưa có tài khoản, vui lòng đăng ký.
                        </p>
                        
                        <!-- Các nút -->
                        <div style="display:flex; gap:15px; justify-content:center; margin:25px 0 20px;">
                            <button onclick="openLoginFromModal()" style="padding:12px 5px; background:#f4511e; color:white; border:none; border-radius:25px; cursor:pointer; font-weight:500; font-size:15px; width:48%; transition: all 0.2s; box-shadow: 0 3px 10px rgba(244, 81, 30, 0.2);">
                                <i class="fa fa-sign-in" style="margin-right:8px;"></i> Đăng nhập
                            </button>
                            <button onclick="openRegisterFromModal()" style="padding:12px 5px; background:#ffffff; color:#f4511e; border:1px solid #f4511e; border-radius:25px; cursor:pointer; font-weight:500; font-size:15px; width:48%; transition: all 0.2s;">
                                <i class="fa fa-user-plus" style="margin-right:8px;"></i> Đăng ký
                            </button>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div style="background:#f8f9fa; padding:12px; text-align:center; border-top:1px solid #eee;">
                        <small style="color:#777; font-size:13px;">
                            <i class="fa fa-info-circle" style="margin-right:5px;"></i> 
                            Sẽ tự động chuyển về trang chủ sau <span id="countdown" style="font-weight:bold; color:#f4511e;">5</span> giây
                        </small>
                    </div>
                </div>
            </div>
            
            <style>
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                #loginRequiredModal button {
                    transition: all 0.25s ease;
                }
                
                #loginRequiredModal button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                
                #loginRequiredModal button:first-child:hover {
                    background: #e64a19;
                }
                
                #loginRequiredModal button:last-child:hover {
                    background: #fff8f6;
                }
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Đếm ngược
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            countdown--;
            if (countdownElement) countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                closeLoginRequiredModal();
                window.location.href = '/src/trangchu.php?show=login';
            }
        }, 1000);
        
        // Lưu interval ID để có thể clear khi đóng modal
        window.loginModalCountdownInterval = countdownInterval;
    });
}

function closeLoginRequiredModal() {
    const modal = document.getElementById('loginRequiredModal');
    if (modal) {
        if (window.loginModalCountdownInterval) {
            clearInterval(window.loginModalCountdownInterval);
        }
        
        const modalContent = modal.querySelector('div > div');
        if (modalContent) {
            modalContent.style.transition = 'all 0.3s ease';
            modalContent.style.transform = 'translateY(10px)';
            modalContent.style.opacity = '0';
        }
        
        setTimeout(() => {
            modal.remove();
        }, 250);
    }
}

function openLoginFromModal() {
    closeLoginRequiredModal();
    window.location.href = '/src/trangchu.php?show=login';
}

function openRegisterFromModal() {
    closeLoginRequiredModal();
    window.location.href = '/src/trangchu.php?show=register';
}

// Debug log khi trang load
console.log('Initial login status:', window.isUserLoggedIn);
console.log('Session data check...');
checkLoginStatusRealtime().then(function(status) {
    console.log('Real-time login status:', status);
});
</script>
</body>
</html>
