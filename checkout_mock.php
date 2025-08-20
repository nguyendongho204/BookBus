<?php
// Thêm vào đầu file payments/checkout_mock.php
session_start();

// Kiểm tra đăng nhập
if (empty($_SESSION['user']['id'])) {
    // Chuyển hướng về trang chủ với thông báo
    header('Location: /src/trangchu.php?show=login&error=login_required');
    exit;
}

// Tiếp tục code hiện tại...
?>
<?php
require_once __DIR__ . '/../libs/db_chuyenxe.php';

$id_chuyen = isset($_GET['id_chuyen']) ? (int)$_GET['id_chuyen'] : 0;
$so_khach  = isset($_GET['so_khach']) ? (int)$_GET['so_khach'] : 1;

$stmt = $pdo->prepare('SELECT * FROM chuyenxe WHERE id = ?');
$stmt->execute([$id_chuyen]);
$cx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cx) { die('Không tìm thấy chuyến xe'); }

$diem_di   = htmlspecialchars($cx['diem_di']);
$diem_den  = htmlspecialchars($cx['diem_den']);
$ngay_di   = htmlspecialchars($cx['ngay_di']);
$gio_di    = htmlspecialchars($cx['gio_di']);
$gio_den   = htmlspecialchars($cx['gio_den']);
$gia_ve    = (int)$cx['gia_ve'];
$so_ghe_con= (int)$cx['so_ghe_con'];
if ($so_khach <= 0) $so_khach = 1;
if ($so_ghe_con > 0 && $so_khach > $so_ghe_con) $so_khach = $so_ghe_con;

$total = $gia_ve * max($so_khach,1);
function vnd($n){ return number_format((int)$n,0,',','.').' VND'; }
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Thanh toán - BookBus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
  --momo:#d81b60; --momo-dark:#ad114b; --bg:#f7f7fb;
}
body{background:var(--bg);}
.card-pay{
  max-width: 860px; margin: 40px auto; background:#fff; border: 0;
  border-radius:18px; box-shadow:0 14px 40px rgba(0,0,0,.08);
}
.brand{
  display:flex; align-items:center; gap:12px; font-weight:700; color:var(--momo);
}
.brand .dot{width:28px;height:28px;border-radius:8px;background:var(--momo);}
.badge-momo{background:var(--momo); font-weight:600;}
.btn-momo{
  background:var(--momo); border-color:var(--momo);
}
.btn-momo:hover{background:var(--momo-dark); border-color:var(--momo-dark);}
.form-control:focus,.form-select:focus{
  border-color: var(--momo); box-shadow: 0 0 0 .2rem rgba(216,27,96,.15);
}
.summary li{display:flex; justify-content:space-between; padding:.6rem .2rem;}
.summary li span{color:#666;}
.summary li strong{color:#111;}
.hr-dashed{border-top:1px dashed #ddd;}
.sec-title{font-weight:700;color:#333;}
.small-muted{color:#8a8a8a; font-size:.9rem;}
</style>
</head>
<body>
<div class="card card-pay">
  <div class="card-body p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="brand"><span class="dot"></span> Thanh toán BookBus</div>
      <span class="badge badge-momo rounded-pill text-white px-3 py-2">Giao diện mô phỏng MoMo</span>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <h6 class="sec-title mb-3">Thông tin chuyến</h6>
        <ul class="list-unstyled summary">
          <li><span>Tuyến</span><strong><?= $diem_di ?> → <?= $diem_den ?></strong></li>
          <li><span>Ngày đi</span><strong><?= $ngay_di ?></strong></li>
          <li><span>Giờ đi / đến</span><strong><?= $gio_di ?> → <?= $gio_den ?></strong></li>
          <li><span>Giá vé</span><strong><?= vnd($gia_ve) ?> / khách</strong></li>
          <li><span>Ghế còn</span><strong><?= $so_ghe_con ?></strong></li>
        </ul>
        <hr class="hr-dashed">
        <ul class="list-unstyled summary">
          <li><span>Tạm tính</span><strong><?= vnd($total) ?></strong></li>
        </ul>
        <p class="small-muted mt-2 mb-0"></p>
      </div>

      <div class="col-lg-6">
        <h6 class="sec-title mb-3">Thông tin người thanh toán</h6>
        <form action="../libs/xuly_dat_ve.php" method="POST" class="row g-3 needs-validation" novalidate>
          <input type="hidden" name="id_chuyen" value="<?= $id_chuyen ?>">
          <div class="col-12">
            <label class="form-label">Họ và tên</label>
            <input type="text" class="form-control" name="ho_ten" required placeholder="Nguyễn Văn A">
            <div class="invalid-feedback">Vui lòng nhập họ tên</div>
          </div>
          <div class="col-12">
            <label class="form-label">Số điện thoại</label>
            <input type="tel" class="form-control" name="sdt" required pattern="0[0-9]{9,10}" placeholder="0xxxxxxxxx">
            <div class="invalid-feedback">SĐT 10–11 số, bắt đầu bằng 0</div>
          </div>
          <div class="col-12">
            <label class="form-label">Email (không bắt buộc)</label>
            <input type="email" class="form-control" name="email" placeholder="email@domain.com">
          </div>
          <div class="col-12">
            <label class="form-label">Số lượng vé</label>
            <input type="number" class="form-control" name="so_luong" min="1" max="<?= max($so_ghe_con,1) ?>" value="<?= max($so_khach,1) ?>" required>
            <div class="invalid-feedback">Số vé phải ≥ 1 và ≤ <?= $so_ghe_con ?></div>
          </div>
          <div class="d-grid mt-2">
            <button class="btn btn-momo btn-lg" type="submit">Thanh toán</button>
          </div>
          <div class="text-center mt-2">
            <a href="../timkiemchuyenxe.php" class="text-decoration-none small">← Quay lại tìm chuyến</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', evt => {
      if (!form.checkValidity()) { evt.preventDefault(); evt.stopPropagation(); }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
</body>
</html>
