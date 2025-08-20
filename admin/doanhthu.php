<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
$pageTitle = "Doanh thu";
$active = "revenue";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db_chuyenxe.php"; // $pdo

$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

$where = "payment_status IN ('success','paid')";
$params = [];
if ($from !== '') { $where .= " AND date(ngay_dat) >= date(:from)"; $params[':from'] = $from; }
if ($to   !== '') { $where .= " AND date(ngay_dat) <= date(:to)";   $params[':to']   = $to;   }

$sqlSum = "SELECT COALESCE(SUM(amount),0) AS total FROM dat_ve WHERE $where";
$st = $pdo->prepare($sqlSum);
foreach ($params as $k=>$v) $st->bindValue($k, $v);
$st->execute();
$total = (int)$st->fetchColumn();

$sqlList = "SELECT id, id_chuyen, ho_ten, sdt, so_luong, amount, ngay_dat, payment_status
            FROM dat_ve WHERE $where ORDER BY id DESC LIMIT 50";
$st2 = $pdo->prepare($sqlList);
foreach ($params as $k=>$v) $st2->bindValue($k, $v);
$st2->execute();
$rows = $st2->fetchAll(PDO::FETCH_ASSOC);

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
?>
<div class="form-card" style="margin-bottom:20px;">
  <form class="row g-3" method="get">
    <div class="col-auto">
      <label class="form-label">Từ ngày</label>
      <input type="date" class="form-control" name="from" value="<?=htmlspecialchars($from)?>">
    </div>
    <div class="col-auto">
      <label class="form-label">Đến ngày</label>
      <input type="date" class="form-control" name="to" value="<?=htmlspecialchars($to)?>">
    </div>
    <div class="col-auto" style="padding-top:30px;">
      <button class="btn btn-brand">Lọc</button>
    </div>
  </form>
</div>

<div class="cardx" style="margin-bottom:20px;">
  <h5>Tổng doanh thu: <b><?= number_format($total) ?></b> đ</h5>
</div>

<div class="cardx">
  <h6>Đơn gần đây</h6>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
      <thead><tr>
        <th>ID</th><th>Chuyến</th><th>Khách</th><th>SĐT</th><th>SL</th><th>Số tiền</th><th>Ngày đặt</th><th>Trạng thái</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td>#<?=$r['id_chuyen']?></td>
          <td><?=htmlspecialchars($r['ho_ten'])?></td>
          <td><?=htmlspecialchars($r['sdt'])?></td>
          <td><?=$r['so_luong']?></td>
          <td><?=number_format((int)$r['amount'])?></td>
          <td><?=htmlspecialchars($r['ngay_dat'])?></td>
          <td><?=vn_status($r['payment_status'])?></td>
        </td>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
