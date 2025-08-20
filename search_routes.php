<?php
/**
 * File: search_routes.php
 * Mô tả: Trang tìm kiếm chuyến xe theo tuyến đường
 * Chức năng: Tìm kiếm chuyến xe theo điểm đi, điểm đến, ngày khởi hành
 * Tác giả: @nguyendongho204
 * Ngày cập nhật: 2025-08-20
 */

include __DIR__ . '/libs/db_chuyenxe.php';


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
<?php include 'quick_booking.php'; ?>
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
</body>
</html>

