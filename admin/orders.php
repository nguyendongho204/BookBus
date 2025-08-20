<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
$pageTitle = "Đơn đặt";
$active = "orders";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db_chuyenxe.php"; // $pdo

$rows = $pdo->query("SELECT id,id_chuyen,ho_ten,sdt,so_luong,amount,ngay_dat,payment_status FROM dat_ve ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

function vn_status($s){
  $map = [
    'pending'  => 'Chờ thanh toán',
    'paid'     => 'Đã thanh toán',
    'success'  => 'Thành công',
    'failed'   => 'Thất bại',
    'refunded' => 'Đã hoàn tiền',
  ];
  return $map[$s] ?? $s;
}
function badge_class($s){
  if ($s==='failed') return 'badge-status badge-failed';
  if ($s==='pending') return 'badge-status badge-pending';
  if ($s==='refunded') return 'badge-status badge-pending';
  return 'badge-status badge-success';
}
$opts = ['pending','paid','success','failed','refunded'];
?>
<div class="cardx">
  <div class="table-responsive">
  <table class="table table-sm table-striped align-middle">
    <thead><tr>
      <th>ID</th><th>Chuyến</th><th>Khách</th><th>SĐT</th><th>Số Vé</th><th>Tiền</th><th>Ngày</th><th>Trạng thái</th><th>Cập nhật</th>
    </tr></thead>
    <tbody>
    <?php foreach($rows as $r): $st=$r['payment_status']; ?>
    <tr>
      <td><?=$r['id']?></td>
      <td>#<?=$r['id_chuyen']?></td>
      <td><?=htmlspecialchars($r['ho_ten'])?></td>
      <td><?=htmlspecialchars($r['sdt'])?></td>
      <td><?=$r['so_luong']?></td>
      <td><?=number_format((int)$r['amount'])?></td>
      <td><?=htmlspecialchars($r['ngay_dat'])?></td>
      <td><span class="<?=badge_class($st)?>"><?=vn_status($st)?></span></td>
      <td>
        <form method="post" action="orders_action.php" class="d-flex gap-2 align-items-center">
          <input type="hidden" name="id" value="<?=$r['id']?>">
          <select name="status" class="form-select form-select-sm" style="width:auto;">
            <?php foreach($opts as $o): ?>
              <option value="<?=$o?>" <?=$st===$o?'selected':''?>><?=vn_status($o)?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-brand">Cập nhật</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
