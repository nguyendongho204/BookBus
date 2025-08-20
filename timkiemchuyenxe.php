<?php
session_start();
include __DIR__ . '/libs/db_chuyenxe.php';
$isLoggedIn = !empty($_SESSION['user']['id']);

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
                <a href="payments/checkout_mock.php?id_chuyen=<?= $row['id'] ?>&so_khach=<?= $so_khach ?>" class="btn-book">Đặt Ngay</a>
                </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra trạng thái đăng nhập từ PHP
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    
    // Tìm tất cả nút "Đặt Ngay"
    const bookingButtons = document.querySelectorAll('.btn-book, a[href*="checkout"]');
    
    bookingButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!isLoggedIn) {
                e.preventDefault(); // Ngăn chuyển hướng
                showLoginRequiredModal();
                return false;
            }
            // Nếu đã đăng nhập, tiếp tục bình thường
        });
    });
});

function showLoginRequiredModal() {
    const modalHTML = `
        <div id="loginRequiredModal" class="modal" style="display:block; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div style="background:#fff; width:420px; margin:8% auto; padding:30px; border-radius:15px; text-align:center; position:relative; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                <span onclick="closeLoginRequiredModal()" style="position:absolute; top:15px; right:20px; font-size:28px; cursor:pointer; color:#999; font-weight:bold;">&times;</span>
                
                <div style="margin-bottom:25px;">
                    <i class="fa fa-exclamation-triangle" style="font-size:60px; color:#ff9800; margin-bottom:20px; animation: pulse 2s infinite;"></i>
                    <h3 style="color:#333; margin:15px 0; font-size:24px;">Yêu cầu đăng nhập</h3>
                    <p style="color:#666; line-height:1.6; font-size:16px;">
                        Vui lòng đăng nhập để tiếp tục đặt vé.<br>
                        <span style="color:#28a745; font-weight:500;">Nếu chưa có tài khoản, vui lòng đăng ký.</span>
                    </p>
                </div>
                
                <div style="display:flex; gap:15px; justify-content:center; margin-top:20px;">
                    <button onclick="openLoginFromModal()" style="padding:14px 28px; background:#0d6efd; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:16px; transition: all 0.3s;">
                        <i class="fa fa-sign-in"></i> Đăng nhập
                    </button>
                    <button onclick="openRegisterFromModal()" style="padding:14px 28px; background:#28a745; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:16px; transition: all 0.3s;">
                        <i class="fa fa-user-plus"></i> Đăng ký
                    </button>
                </div>
                
                <div style="margin-top:20px; padding-top:15px; border-top:1px solid #eee;">
                    <small style="color:#999;">
                        <i class="fa fa-info-circle"></i> Sẽ tự động chuyển về trang chủ sau 3 giây
                    </small>
                </div>
            </div>
        </div>
        
        <style>
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            
            #loginRequiredModal button:hover {
                opacity: 0.9;
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Tự động chuyển về trang chủ sau 3 giây
    setTimeout(function() {
        closeLoginRequiredModal();
        window.location.href = '/src/trangchu.php?show=login';
    }, 3000);
}

function closeLoginRequiredModal() {
    const modal = document.getElementById('loginRequiredModal');
    if (modal) {
        modal.remove();
    }
}

function openLoginFromModal() {
    closeLoginRequiredModal();
    // Chuyển về trang chủ và mở login modal
    window.location.href = '/src/trangchu.php?show=login';
}

function openRegisterFromModal() {
    closeLoginRequiredModal();
    // Chuyển về trang chủ và mở register modal
    window.location.href = '/src/trangchu.php?show=register';
}
</script>
</body>
</html>

