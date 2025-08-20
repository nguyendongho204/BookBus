<?php
// tai-khoan/don.php
require __DIR__ . '/_auth.php';
require __DIR__ . '/../libs/db_chuyenxe.php'; // $pdo (SQLite PDO)

$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { header('Location: /tai-khoan'); exit; }

$st = $pdo->prepare("
  SELECT dv.*,
         cx.ten_nhaxe, cx.loai_xe, cx.diem_di, cx.diem_den, cx.ngay_di, cx.gio_di, cx.gio_den, cx.gia_ve
  FROM dat_ve dv
  LEFT JOIN chuyenxe cx ON cx.id = dv.id_chuyen
  WHERE dv.id = ? AND dv.user_id = ?
  LIMIT 1
");
$st->execute([$id, $userId]);
$don = $st->fetch(PDO::FETCH_ASSOC);
if (!$don) { header('Location: /tai-khoan'); exit; }

$title = 'Chi tiết đơn #' . htmlspecialchars($don['order_id'] ?? (string)$id);
ob_start(); ?>
<div class="card tk-card"><div class="card-body">
  <h5 class="mb-3">Đơn hàng: <strong>#<?= htmlspecialchars($don['order_id'] ?? $id) ?></strong></h5>
  <div class="row g-3">
    <div class="col-md-6">
      <div><strong>Nhà xe:</strong> <?= htmlspecialchars($don['ten_nhaxe'] ?? '') ?></div>
      <div><strong>Tuyến:</strong> <?= htmlspecialchars(($don['diem_di'] ?? '').' → '.($don['diem_den'] ?? '')) ?></div>
      <div><strong>Khởi hành:</strong> <?= htmlspecialchars(($don['ngay_di'] ?? '').' '.$don['gio_di']) ?></div>
      <div><strong>Đến nơi (dự kiến):</strong> <?= htmlspecialchars($don['gio_den'] ?? '') ?></div>
    </div>
    <div class="col-md-6">
      <div><strong>Số vé:</strong> <?= (int)$don['so_luong'] ?></div>
      <div><strong>Tổng tiền:</strong> <?= number_format((float)($don['amount'] ?? 0),0,',','.') ?> ₫</div>
      <div><strong>Trạng thái:</strong>
        <?php
          $txt = ['paid'=>'Đã thanh toán','pending'=>'Chờ thanh toán','failed'=>'Thất bại'][$don['payment_status']] ?? $don['payment_status'];
          $cls = ['paid'=>'success','pending'=>'secondary','failed'=>'danger'][$don['payment_status']] ?? 'secondary';
        ?>
        <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($txt) ?></span>
      </div>
      <div><strong>Ngày đặt:</strong> <?= htmlspecialchars($don['ngay_dat'] ?? '') ?></div>
    </div>
  </div>

  <hr class="my-4">
  <!-- TODO: Nếu có bảng chi tiết hành khách/ghế thì render tại đây -->

  <div class="mt-3 d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/tai-khoan">← Lịch sử</a>
    <?php if (($don['payment_status'] ?? '') === 'paid'): ?>
      <a class="btn btn-primary disabled" title="Sắp có">Tải vé (PDF)</a>
    <?php endif; ?>
  </div>
</div></div>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';
