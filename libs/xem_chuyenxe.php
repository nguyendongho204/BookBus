<?php
include("db_chuyenxe.php");

// Danh sách điểm (đồng bộ như form thêm)
$ALL_POINTS = array(
  'Cần Thơ', 'Phong Điền', 'Ô Môn', 'Vĩnh Long', 'Kinh Cùng', 'Cái Tắc', 'Thốt Nốt', 'Cờ Đỏ', 'Vĩnh Thạnh', 'Bình Tân'
);


// Lấy query params
$diem_di   = isset($_GET['diem_di'])   ? trim($_GET['diem_di'])   : '';
$diem_den  = isset($_GET['diem_den'])  ? trim($_GET['diem_den'])  : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to   = isset($_GET['date_to'])   ? trim($_GET['date_to'])   : '';
$q         = isset($_GET['q'])         ? trim($_GET['q'])         : '';

$allowedSort = array(
  'id' => 'id',
  'ngay_di' => 'ngay_di',
  'gio_di' => 'gio_di',
  'gio_den' => 'gio_den',
  'gia_ve' => 'gia_ve',
  'so_ghe' => 'so_ghe',
  'so_ghe_con' => 'so_ghe_con',
  'ten_nhaxe' => 'ten_nhaxe',
  'loai_xe' => 'loai_xe',
  'diem_di' => 'diem_di',
  'diem_den' => 'diem_den'
);
$sort = (isset($_GET['sort']) && isset($allowedSort[$_GET['sort']])) ? $_GET['sort'] : 'ngay_di';
$dir  = (isset($_GET['dir'])  && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$pageSize = (int)(isset($_GET['page_size']) ? $_GET['page_size'] : 10);
if (!in_array($pageSize, array(10,20,50,100), true)) $pageSize = 10;
$offset = ($page - 1) * $pageSize;

// WHERE
$where = array();
$params = array();
if ($diem_di !== '') { $where[] = 'diem_di = :diem_di'; $params[':diem_di'] = $diem_di; }
if ($diem_den !== '') { $where[] = 'diem_den = :diem_den'; $params[':diem_den'] = $diem_den; }
if ($date_from !== '') { $where[] = 'ngay_di >= :date_from'; $params[':date_from'] = $date_from; }
if ($date_to !== '') { $where[] = 'ngay_di <= :date_to'; $params[':date_to'] = $date_to; }
if ($q !== '') { $where[] = '(ten_nhaxe LIKE :kw OR loai_xe LIKE :kw OR diem_di LIKE :kw OR diem_den LIKE :kw)'; $params[':kw'] = '%'.$q.'%'; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count
$sqlCount = 'SELECT COUNT(*) FROM chuyenxe ' . $whereSql;
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $pageSize));

// Data
$orderBy = $allowedSort[$sort] . ' ' . $dir . ', id DESC';
$sql = 'SELECT * FROM chuyenxe ' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper build query string giữ tham số
function buildQS($overrides = array()) {
  $params = array_merge($_GET, $overrides);
  foreach ($params as $k=>$v) { if ($v === '' || $v === null) unset($params[$k]); }
  return http_build_query($params);
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Danh sách chuyến xe</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background:#f6f7fb; padding:24px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
    .page-title { color:#ff3c3c; font-weight:700; letter-spacing:.3px; }
    .filter-card { background:#fff; border-radius:12px; box-shadow:0 6px 16px rgba(0,0,0,.06); padding:16px; margin-bottom:16px; }
    th a { text-decoration:none; color:inherit; }
    th a .sort { opacity:.35; font-size:12px; margin-left:4px; }
    th a.active { color:#0d6efd; }
    .table-wrap { background:#fff; border-radius:12px; box-shadow:0 6px 16px rgba(0,0,0,.06); overflow:hidden; }
    .table thead th { position:sticky; top:0; background:#f9fafb; z-index:1; }
    .pagination { margin:0; }
  </style>
</head>
<body>
  <div class="container-fluid">
    <h2 class="page-title mb-3">DANH SÁCH CHUYẾN XE</h2>

    <!-- Bộ lọc -->
    <form class="filter-card" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-2">
          <label class="form-label">Điểm đi</label>
          <select name="diem_di" class="form-select">
            <option value="">-- Tất cả --</option>
            <?php foreach ($ALL_POINTS as $opt): ?>
              <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($opt===$diem_di?'selected':''); ?>><?php echo htmlspecialchars($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Điểm đến</label>
          <select name="diem_den" class="form-select">
            <option value="">-- Tất cả --</option>
            <?php foreach ($ALL_POINTS as $opt): ?>
              <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($opt===$diem_den?'selected':''); ?>><?php echo htmlspecialchars($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Từ ngày</label>
          <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Đến ngày</label>
          <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Từ khóa</label>
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Nhà xe, loại xe...">
        </div>
        <div class="col-6 col-md-1">
          <label class="form-label">Hiển thị</label>
          <select name="page_size" class="form-select">
            <?php foreach (array(10,20,50,100) as $ps): ?>
              <option value="<?php echo $ps; ?>" <?php echo ($ps===$pageSize?'selected':''); ?>><?php echo $ps; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-1 d-grid">
          <button class="btn btn-primary">Lọc</button>
        </div>
        <div class="col-12 col-md-1 d-grid">
          <a class="btn btn-outline-secondary" href="?">Xóa lọc</a>
        </div>
      </div>
    </form>

    <div class="table-wrap">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <?php
                function sortLink($label, $key, $currentSort, $currentDir) {
                  $nextDir = ($currentSort === $key && $currentDir === 'ASC') ? 'DESC' : 'ASC';
                  $qs = buildQS(array('sort'=>$key, 'dir'=>$nextDir, 'page'=>1));
                  $active = $currentSort === $key ? 'active' : '';
                  $arrow = '';
                  if ($currentSort === $key) { $arrow = $currentDir==='ASC'?'▲':'▼'; }
                  echo '<th><a class="'.$active.'" href="?'.$qs.'">'.htmlspecialchars($label).'<span class="sort">'.$arrow.'</span></a></th>';
                }
              ?>
              <?php sortLink('ID', 'id', $sort, $dir); ?>
              <?php sortLink('Nhà xe', 'ten_nhaxe', $sort, $dir); ?>
              <?php sortLink('Loại xe', 'loai_xe', $sort, $dir); ?>
              <?php sortLink('Điểm đi', 'diem_di', $sort, $dir); ?>
              <?php sortLink('Điểm đến', 'diem_den', $sort, $dir); ?>
              <?php sortLink('Ngày đi', 'ngay_di', $sort, $dir); ?>
              <?php sortLink('Giờ đi', 'gio_di', $sort, $dir); ?>
              <?php sortLink('Giờ đến', 'gio_den', $sort, $dir); ?>
              <?php echo '<th class="text-end"><a class="'.($sort==='gia_ve'?'active':'').'" href="?'.buildQS(array('sort'=>'gia_ve','dir'=>($sort==='gia_ve' && $dir==='ASC'?'DESC':'ASC'),'page'=>1)).'">Giá vé<span class="sort">'.($sort==='gia_ve'?($dir==='ASC'?'▲':'▼'):'').'</span></a></th>'; ?>
              <?php echo '<th class="text-end"><a class="'.($sort==='so_ghe'?'active':'').'" href="?'.buildQS(array('sort'=>'so_ghe','dir'=>($sort==='so_ghe' && $dir==='ASC'?'DESC':'ASC'),'page'=>1)).'">Số ghế<span class="sort">'.($sort==='so_ghe'?($dir==='ASC'?'▲':'▼'):'').'</span></a></th>'; ?>
              <?php echo '<th class="text-end"><a class="'.($sort==='so_ghe_con'?'active':'').'" href="?'.buildQS(array('sort'=>'so_ghe_con','dir'=>($sort==='so_ghe_con' && $dir==='ASC'?'DESC':'ASC'),'page'=>1)).'">Còn<span class="sort">'.($sort==='so_ghe_con'?($dir==='ASC'?'▲':'▼'):'').'</span></a></th>'; ?>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($rows): foreach ($rows as $row): ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['ten_nhaxe']); ?></td>
                <td><?php echo htmlspecialchars($row['loai_xe']); ?></td>
                <td><?php echo htmlspecialchars($row['diem_di']); ?></td>
                <td><?php echo htmlspecialchars($row['diem_den']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['ngay_di'])); ?></td>
                <td><?php echo date('H:i', strtotime($row['gio_di'])); ?></td>
                <td><?php echo date('H:i', strtotime($row['gio_den'])); ?></td>
                <td class="text-end"><?php echo number_format((float)$row['gia_ve'], 0, ',', '.'); ?>đ</td>
                <td class="text-end"><?php echo (int)$row['so_ghe']; ?></td>
                <td class="text-end">
                  <?php $con = (int)$row['so_ghe_con']; $badge = $con < 5 ? 'bg-warning text-dark' : 'bg-success'; ?>
                  <span class="badge <?php echo $badge; ?>"><?php echo $con; ?></span>
                </td>
                <td>
                  <a href="sua_chuyenxe.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1">Sửa</a>
                  <a href="xoa_chuyenxe.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa chuyến này?')">Xóa</a>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr>
                <td colspan="12" class="text-center py-4 text-muted">Không có dữ liệu.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Phân trang -->
      <div class="d-flex justify-content-between align-items-center p-3">
        <div class="text-muted">Tổng: <?php echo $total; ?> chuyến • Trang <?php echo $page; ?>/<?php echo $totalPages; ?></div>
        <nav>
          <ul class="pagination mb-0">
            <?php
              function pageItem($label, $p, $disabled=false, $active=false) {
                $cls = 'page-item';
                if ($disabled) $cls .= ' disabled';
                if ($active) $cls .= ' active';
                $qs = buildQS(array('page'=>$p));
                echo '<li class="'.$cls.'"><a class="page-link" href="?'.$qs.'">'.$label.'</a></li>';
              }
              pageItem('«', 1, $page<=1, false);
              pageItem('‹', max(1,$page-1), $page<=1, false);
              $start = max(1, $page-2);
              $end   = min($totalPages, $page+2);
              for ($i=$start; $i<=$end; $i++) {
                pageItem((string)$i, $i, false, $i===$page);
              }
              pageItem('›', min($totalPages,$page+1), $page>=$totalPages, false);
              pageItem('»', $totalPages, $page>=$totalPages, false);
            ?>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</body>
</html>
