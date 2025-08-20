<?php
/**
 * File: booking.php
 * Mô tả: Trang đặt vé xe buýt trực tuyến
 * Chức năng: Hiển thị form đặt vé, xử lý thông tin khách hàng, tích hợp thanh toán
 * Tác giả: @nguyendongho204
 * Ngày cập nhật: 2025-08-20
 */
require_once __DIR__ . '/libs/db_chuyenxe.php'; // tạo $pdo (SQLite)

$id_chuyen = isset($_GET['id_chuyen']) ? (int)$_GET['id_chuyen'] : 0;
$so_khach  = isset($_GET['so_khach']) ? (int)$_GET['so_khach'] : 1;
if ($id_chuyen <= 0) { die('Thiếu tham số id_chuyen'); }

$stmt = $pdo->prepare('SELECT * FROM chuyenxe WHERE id = ?');
$stmt->execute([$id_chuyen]);
$cx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cx) { die('Không tìm thấy chuyến xe'); }

$diem_di = htmlspecialchars($cx['diem_di']);
$diem_den= htmlspecialchars($cx['diem_den']);
$ngay_di = htmlspecialchars($cx['ngay_di']);
$gio_di  = htmlspecialchars($cx['gio_di']);
$gio_den = htmlspecialchars($cx['gio_den']);
$gia_ve  = (int)$cx['gia_ve'];
$gia_ve_fmt = number_format($gia_ve, 0, ',', '.');
$so_ghe_con = (int)$cx['so_ghe_con'];
if ($so_khach <= 0) $so_khach = 1;
if ($so_ghe_con > 0 && $so_khach > $so_ghe_con) $so_khach = $so_ghe_con;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Đặt vé - <?php echo $diem_di . ' → ' . $diem_den; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f8f9fa}
    .wrap{max-width:720px;margin:40px auto;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
    .wrap .hd{font-weight:700;font-size:26px;color:#e53935}
  </style>
</head>
<body>
<div class="wrap p-4 p-md-5">
  <h2 class="hd text-center mb-4">ĐẶT VÉ XE</h2>
  <div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Điểm đi</label><input class="form-control" value="<?php echo $diem_di; ?>" readonly></div>
    <div class="col-md-6"><label class="form-label">Điểm đến</label><input class="form-control" value="<?php echo $diem_den; ?>" readonly></div>
    <div class="col-md-4"><label class="form-label">Ngày đi</label><input class="form-control" value="<?php echo $ngay_di; ?>" readonly></div>
    <div class="col-md-4"><label class="form-label">Giờ đi</label><input class="form-control" value="<?php echo $gio_di; ?>" readonly></div>
    <div class="col-md-4"><label class="form-label">Giờ đến</label><input class="form-control" value="<?php echo $gio_den; ?>" readonly></div>
    <div class="col-md-6"><label class="form-label">Giá vé</label><input class="form-control" value="<?php echo $gia_ve_fmt; ?> VND / khách" readonly></div>
    <div class="col-md-6"><label class="form-label">Ghế còn lại</label><input class="form-control" value="<?php echo $so_ghe_con; ?>" readonly></div>
  </div>

  <form action="libs/xuly_dat_ve.php" method="POST" class="row g-3">
    <input type="hidden" name="id_chuyen" value="<?php echo $id_chuyen; ?>">
    <div class="col-md-6">
      <label class="form-label">Họ và tên</label>
      <input type="text" name="ho_ten" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Số điện thoại</label>
      <input type="text" name="sdt" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Số lượng vé</label>
      <input type="number" name="so_luong" class="form-control" min="1" max="<?php echo max($so_ghe_con,1); ?>" value="<?php echo max($so_khach,1); ?>" required>
    </div>
    <div class="col-12">
      <label class="form-label">Chọn cổng thanh toán</label>
      <select name="gateway" class="form-select">
        <option value="momo">MoMo</option>
        <option value="zalopay">ZaloPay</option>
      </select>
    </div>
    <div class="col-12 d-grid pt-2">
      <button type="submit" class="btn btn-danger py-2">Thanh toán</button>
    </div>
  </form>
</div>
</body>
</html>
