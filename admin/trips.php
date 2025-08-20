<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
$pageTitle = "Chuyến xe";
$active = "trips";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db_chuyenxe.php"; // $pdo
date_default_timezone_set('Asia/Ho_Chi_Minh');

$LOCATIONS = ["Cần Thơ","Phong Điền","Ô Môn","Vĩnh Long","Kinh Cùng","Cái Tắc","Thốt Nốt","Cờ Đỏ","Vĩnh Thạnh","Bình Tân"];

// ---- Filters ----
$diem_di  = trim($_GET['diem_di']  ?? '');
$diem_den = trim($_GET['diem_den'] ?? '');
$tu_ngay  = trim($_GET['tu_ngay']  ?? '');
$den_ngay = trim($_GET['den_ngay'] ?? '');
$q        = trim($_GET['q'] ?? '');
$perPage  = 10;

// Normalize date
if ($tu_ngay !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tu_ngay)) $tu_ngay = '';
if ($den_ngay !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $den_ngay)) $den_ngay = '';

// Build WHERE
$where = [];
$params = [];
if ($diem_di !== '') { $where[] = "diem_di = :diem_di"; $params[':diem_di'] = $diem_di; }
if ($diem_den !== '') { $where[] = "diem_den = :diem_den"; $params[':diem_den'] = $diem_den; }
if ($tu_ngay !== '') { $where[] = "ngay_di >= :tu"; $params[':tu'] = $tu_ngay; }
if ($den_ngay !== '') { $where[] = "ngay_di <= :den"; $params[':den'] = $den_ngay; }
if ($q !== '') {
  $where[] = "(ten_nhaxe LIKE :q OR loai_xe LIKE :q)";
  $params[':q'] = "%".$q."%";
}
$whereSql = $where ? ("WHERE ".implode(" AND ", $where)) : "";

// ---- Pagination ----
$page = max(1, (int)($_GET['p'] ?? 1));
$st = $pdo->prepare("SELECT COUNT(*) FROM chuyenxe $whereSql");
$st->execute($params);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) $page = $pages;
$offset = ($page - 1) * $perPage;

// Data
$sql = "SELECT id,ten_nhaxe,loai_xe,diem_di,diem_den,ngay_di,gio_di,gio_den,gia_ve,so_ghe,so_ghe_con
        FROM chuyenxe $whereSql
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset";
$st = $pdo->prepare($sql);
foreach ($params as $k=>$v) $st->bindValue($k, $v);
$st->bindValue(':limit', $perPage, PDO::PARAM_INT);
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

function seat_pill_class($left){
  if ($left <= 0) return "pill pill-red";
  if ($left <= 10) return "pill pill-amber";
  return "pill pill-green";
}
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function keep($extra = []){
  $base = [
    'diem_di' => $_GET['diem_di'] ?? '',
    'diem_den' => $_GET['diem_den'] ?? '',
    'tu_ngay' => $_GET['tu_ngay'] ?? '',
    'den_ngay' => $_GET['den_ngay'] ?? '',
    'q' => $_GET['q'] ?? '',
  ];
  foreach($extra as $k=>$v) $base[$k] = $v;
  return http_build_query($base);
}
?>
<style>
/* Pills, badges, buttons */
.pill{display:inline-flex;align-items:center;justify-content:center;
  min-width:34px;height:28px;padding:0 10px;border-radius:999px;font-weight:700;font-size:14px}
.pill-green{background:#e7f9ee;color:#0f8a3a}
.pill-amber{background:#fff7e6;color:#a16207}
.pill-red{background:#fdecec;color:#b91c1c}
.btn-chip{display:inline-block;padding:6px 12px;border-radius:10px;font-weight:600;
  text-decoration:none;border:1px solid transparent;cursor:pointer}
.btn-chip.edit{background:#e6f0ff;border-color:#7aa2ff;color:#1d4ed8}
.btn-chip.delete{background:#fff1f2;border-color:#fda4af;color:#b91c1c}
.btn-chip.delete:hover{background:#ffe4e6}
.table thead th{font-weight:700;color:#374151}
.badge-type{display:inline-block;padding:4px 8px;border-radius:999px;background:#f3f4f6;color:#111827}
.paginationx{display:flex;gap:6px;justify-content:center;margin-top:14px}
.paginationx a,.paginationx span{padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px;text-decoration:none;color:#111827;background:#fff}
.paginationx .active{background:#2563eb;color:#fff;border-color:#1d4ed8}
.paginationx .muted{color:#9ca3af;background:#f9fafb}

/* Filter bar – compact keyword */
.filterbar{display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;margin-bottom:16px}
.filterbar .group{min-width:210px}
.filterbar .group.g-date{width:220px}
.filterbar .group.g-search{flex:0 0 auto}
.filterbar .group.g-search .search-row{display:flex;gap:10px;align-items:center}
.filterbar .group.g-search .search-row input{width:280px} /* rút ngắn hộp từ khoá */
.filterbar .btn-primary{background:#1e40af;border:none;color:#fff;border-radius:10px;padding:8px 14px;font-weight:700}
.filterbar .btn-secondary{background:#f3f4f6;border:none;border-radius:10px;padding:8px 14px;font-weight:600}

/* Modal */
.hidden{display:none !important}
.modal-backdropx{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1050}
.modalx{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);min-width:360px;max-width:92vw;
  background:#fff;border-radius:16px;box-shadow:0 20px 80px rgba(0,0,0,.25);z-index:1060}
.modalx .hd{padding:16px 18px;border-bottom:1px solid #f1f5f9;font-weight:700}
.modalx .bd{padding:16px 18px;color:#111827}
.modalx .ft{padding:14px 18px;border-top:1px solid #f1f5f9;display:flex;gap:10px;justify-content:flex-end}
.btn-dangerx{background:#ef4444;border:none;color:#fff;border-radius:10px;padding:8px 14px;font-weight:700}
.btn-lightx{background:#f3f4f6;border:none;color:#111827;border-radius:10px;padding:8px 14px;font-weight:600}
</style>

<div class="cardx">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="m-0">Danh sách chuyến</h5>
    <a href="trips_edit.php" class="btn btn-brand">+ Thêm chuyến</a>
  </div>

  <form method="get" class="filterbar">
    <div class="group">
      <label class="form-label">Điểm đi</label>
      <select class="form-select" name="diem_di">
        <option value="">-- Tất cả --</option>
        <?php foreach($LOCATIONS as $l): ?>
          <option value="<?=h($l)?>" <?=$diem_di===$l?'selected':''?>><?=h($l)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="group">
      <label class="form-label">Điểm đến</label>
      <select class="form-select" name="diem_den">
        <option value="">-- Tất cả --</option>
        <?php foreach($LOCATIONS as $l): ?>
          <option value="<?=h($l)?>" <?=$diem_den===$l?'selected':''?>><?=h($l)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="group g-date">
      <label class="form-label">Từ ngày</label>
      <input type="date" class="form-control" name="tu_ngay" value="<?=h($tu_ngay)?>">
    </div>
    <div class="group g-date">
      <label class="form-label">Đến ngày</label>
      <input type="date" class="form-control" name="den_ngay" value="<?=h($den_ngay)?>">
    </div>
    <div class="group g-search">
      <label class="form-label">Từ khoá</label>
      <div class="search-row">
        <input type="text" class="form-control" name="q" placeholder="Nhà xe, loại xe..." value="<?=h($q)?>">
        <button class="btn-primary" type="submit">Lọc</button>
        <a class="btn-secondary" href="trips.php">Xoá lọc</a>
      </div>
    </div>
  </form>

  <div class="table-responsive">
  <table class="table align-middle table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nhà xe</th>
        <th>Loại</th>
        <th>Điểm đi</th>
        <th>Điểm đến</th>
        <th>Ngày</th>
        <th>Giờ đi</th>
        <th>Giờ đến</th>
        <th>Giá vé</th>
        <th>Ghế</th>
        <th>Còn</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td><?=$r['id']?></td>
        <td><?=h($r['ten_nhaxe'])?></td>
        <td><span class="badge-type"><?=h($r['loai_xe'])?></span></td>
        <td><?=h($r['diem_di'])?></td>
        <td><?=h($r['diem_den'])?></td>
        <td><?=h($r['ngay_di'])?></td>
        <td><?=h($r['gio_di'])?></td>
        <td><?=h($r['gio_den'])?></td>
        <td><?=number_format((int)$r['gia_ve'])?></td>
        <td><?=$r['so_ghe']?></td>
        <td><span class="<?=seat_pill_class((int)$r['so_ghe_con'])?>"><?=$r['so_ghe_con']?></span></td>
        <td class="d-flex gap-2">
          <a class="btn-chip edit" href="trips_edit.php?id=<?=$r['id']?>">Sửa</a>
          <form id="del-<?=$r['id']?>" method="post" action="trips_delete.php?<?=keep(['p'=>$page])?>" style="display:inline-block;margin:0;">
            <input type="hidden" name="id" value="<?=$r['id']?>">
            <button type="button" class="btn-chip delete js-del" data-id="<?=$r['id']?>">Xóa</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="paginationx">
    <?php if ($page > 1): ?>
      <a href="?<?=keep(['p'=>1])?>">&laquo;</a>
      <a href="?<?=keep(['p'=>$page-1])?>">&lsaquo;</a>
    <?php else: ?>
      <span class="muted">&laquo;</span>
      <span class="muted">&lsaquo;</span>
    <?php endif; ?>
    <span class="active"><?=$page?></span>
    <?php if ($page < $pages): ?>
      <a href="?<?=keep(['p'=>$page+1])?>">&rsaquo;</a>
      <a href="?<?=keep(['p'=>$pages])?>">&raquo;</a>
    <?php else: ?>
      <span class="muted">&rsaquo;</span>
      <span class="muted">&raquo;</span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal confirm delete -->
<div class="modal-backdropx hidden" id="delBack"></div>
<div class="modalx hidden" id="delModal" role="dialog" aria-modal="true" aria-labelledby="delTitle" aria-describedby="delDesc">
  <div class="hd" id="delTitle">Xác nhận xoá</div>
  <div class="bd" id="delDesc">Bạn có chắc muốn xoá chuyến <b>#<span id="delId"></span></b>?</div>
  <div class="ft">
    <button class="btn-lightx" id="delCancel" type="button">Huỷ</button>
    <button class="btn-dangerx" id="delOk" type="button">Xoá</button>
  </div>
</div>

<script>
(function(){
  var openId = null;
  var back = document.getElementById('delBack');
  var modal = document.getElementById('delModal');
  var idSpan = document.getElementById('delId');
  var okBtn = document.getElementById('delOk');
  var cancelBtn = document.getElementById('delCancel');

  function show(id){
    openId = id;
    idSpan.textContent = id;
    back.classList.remove('hidden');
    modal.classList.remove('hidden');
    okBtn.focus();
  }
  function hide(){
    back.classList.add('hidden');
    modal.classList.add('hidden');
    openId = null;
  }

  document.addEventListener('click', function(e){
    var btn = e.target.closest('.js-del');
    if (btn){
      e.preventDefault();
      var id = btn.getAttribute('data-id');
      show(id);
    }
  });
  back.addEventListener('click', hide);
  cancelBtn.addEventListener('click', function(e){ e.preventDefault(); hide(); });
  okBtn.addEventListener('click', function(e){
    e.preventDefault();
    if (!openId) return hide();
    var f = document.getElementById('del-' + openId);
    if (f) f.submit();
    hide();
  });
  document.addEventListener('keydown', function(e){
    if (!modal.classList.contains('hidden')){
      if (e.key === 'Escape') hide();
      if (e.key === 'Enter') { okBtn.click(); }
    }
  });
})();
</script>

<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
