<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
// admin/dashboard.php
$pageTitle="Dashboard";
$active="dashboard";
require_once __DIR__."/_layout_top.php";
require_once __DIR__."/../libs/db.php";

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');

function qAll($db,$sql,$params){
  $stmt=$db->prepare($sql);
  foreach($params as $k=>$v){ $stmt->bindValue($k,$v); }
  $res=$stmt->execute(); $arr=[];
  while($r=$res->fetchArray(SQLITE3_ASSOC)) $arr[]=$r;
  return $arr;
}

$byRoute = qAll($db, "
SELECT t.id AS tuyen_id,
       t.diem_di || ' - ' || t.diem_den AS tuyen,
       ROUND(SUM(v.gia_ban),0) AS doanh_thu,
       COUNT(v.id) AS so_ve
FROM ve v
JOIN chuyen c ON c.id = v.chuyen_id
JOIN tuyen t  ON t.id = c.tuyen_id
WHERE v.trang_thai='paid' AND date(v.created_at) BETWEEN :from AND :to
GROUP BY t.id ORDER BY doanh_thu DESC
", [':from'=>$from, ':to'=>$to]);

$byDay = qAll($db, "
SELECT date(v.created_at) AS ngay, ROUND(SUM(v.gia_ban),0) AS doanh_thu, COUNT(v.id) AS so_ve
FROM ve v
WHERE v.trang_thai='paid' AND date(v.created_at) BETWEEN :from AND :to
GROUP BY date(v.created_at) ORDER BY ngay
", [':from'=>$from, ':to'=>$to]);

$byHour = qAll($db, "
SELECT strftime('%H', c.gio_khoi_hanh) AS gio, ROUND(SUM(v.gia_ban),0) AS doanh_thu, COUNT(v.id) AS so_ve
FROM ve v
JOIN chuyen c ON c.id=v.chuyen_id
WHERE v.trang_thai='paid' AND date(v.created_at) BETWEEN :from AND :to
GROUP BY strftime('%H', c.gio_khoi_hanh) ORDER BY gio
", [':from'=>$from, ':to'=>$to]);

$byBranch = qAll($db, "
SELECT cn.ten AS chi_nhanh, ROUND(SUM(v.gia_ban),0) AS doanh_thu, COUNT(v.id) AS so_ve
FROM ve v
JOIN chuyen c ON c.id=v.chuyen_id
JOIN tuyen  t ON t.id=c.tuyen_id
JOIN chi_nhanh cn ON cn.id=t.chi_nhanh_id
WHERE v.trang_thai='paid' AND date(v.created_at) BETWEEN :from AND :to
GROUP BY cn.id ORDER BY doanh_thu DESC
", [':from'=>$from, ':to'=>$to]);

$totalRevenue = array_sum(array_column($byRoute,'doanh_thu'));
$totalTickets = array_sum(array_column($byRoute,'so_ve'));
?>
<div class="container-fluid py-3">
  <h5>Dashboard doanh thu</h5>
  <form class="row g-2 mb-3" method="get">
    <div class="col-auto"><input type="date" name="from" value="<?=$from?>" class="form-control"></div>
    <div class="col-auto"><input type="date" name="to"   value="<?=$to?>"   class="form-control"></div>
    <div class="col-auto"><button class="btn btn-primary">Áp dụng</button></div>
  </form>

  <div class="row g-3">
    <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm">
      <div class="text-muted">Tổng doanh thu</div>
      <div class="fs-4 fw-bold"><?=number_format($totalRevenue)?></div>
    </div></div>
    <div class="col-md-3"><div class="p-3 bg-white rounded shadow-sm">
      <div class="text-muted">Số vé</div>
      <div class="fs-4 fw-bold"><?=number_format($totalTickets)?></div>
    </div></div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-lg-7">
      <div class="bg-white rounded shadow-sm p-3">
        <div class="fw-semibold mb-2">Doanh thu theo ngày</div>
        <canvas id="chartByDay" height="120"></canvas>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="bg-white rounded shadow-sm p-3 mb-3">
        <div class="fw-semibold mb-2">Doanh thu theo giờ khởi hành</div>
        <canvas id="chartByHour" height="120"></canvas>
      </div>
      <div class="bg-white rounded shadow-sm p-3">
        <div class="fw-semibold mb-2">Doanh thu theo chi nhánh</div>
        <canvas id="chartByBranch" height="120"></canvas>
      </div>
    </div>
  </div>

  <div class="bg-white rounded shadow-sm p-3 mt-3">
    <div class="fw-semibold mb-2">Top tuyến theo doanh thu</div>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>Tuyến</th><th class="text-end">Doanh thu</th><th class="text-end">Số vé</th></tr></thead>
        <tbody>
          <?php foreach($byRoute as $r): ?>
            <tr>
              <td><?=htmlspecialchars($r['tuyen'])?></td>
              <td class="text-end"><?=number_format($r['doanh_thu'])?></td>
              <td class="text-end"><?=number_format($r['so_ve'])?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const byDay   = <?=json_encode($byDay,   JSON_UNESCAPED_UNICODE)?>;
const byHour  = <?=json_encode($byHour,  JSON_UNESCAPED_UNICODE)?>;
const byBr    = <?=json_encode($byBranch,JSON_UNESCAPED_UNICODE)?>;

new Chart(document.getElementById('chartByDay'), {
  type:'line',
  data:{labels:byDay.map(r=>r.ngay),
        datasets:[{label:'Doanh thu', data:byDay.map(r=>r.doanh_thu), tension:.3}]},
  options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
new Chart(document.getElementById('chartByHour'), {
  type:'bar',
  data:{labels:byHour.map(r=>r.gio),
        datasets:[{label:'Doanh thu', data:byHour.map(r=>r.doanh_thu)}]},
  options:{plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
new Chart(document.getElementById('chartByBranch'), {
  type:'bar',
  data:{labels:byBr.map(r=>r.chi_nhanh),
        datasets:[{label:'Doanh thu', data:byBr.map(r=>r.doanh_thu)}]},
  options:{indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}}}
});
</script>

<?php require_once __DIR__."/_layout_bottom.php"; ?>
