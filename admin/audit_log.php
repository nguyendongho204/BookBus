<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
// admin/audit_log.php
$pageTitle="Nhật ký hệ thống";
$active="audit";
require_once __DIR__."/_layout_top.php";
require_once __DIR__."/../libs/db.php";

$q       = trim($_GET['q'] ?? '');
$actionF = $_GET['action'] ?? '';
$typeF   = $_GET['object_type'] ?? '';
$from    = $_GET['from'] ?? '';
$to      = $_GET['to'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 30; $offset = ($page-1)*$limit;

$where=[];
if($q!==''){
  $kw = SQLite3::escapeString($q);
  $where[]="(username LIKE '%$kw%' OR details_json LIKE '%$kw%' OR object_id LIKE '%$kw%')";
}
if($actionF!=='') $where[]="action = '".SQLite3::escapeString($actionF)."'";
if($typeF!=='')   $where[]="object_type = '".SQLite3::escapeString($typeF)."'";
if($from!=='')    $where[]="date(created_at) >= date('".SQLite3::escapeString($from)."')";
if($to!=='')      $where[]="date(created_at) <= date('".SQLite3::escapeString($to)."')";
$w = $where ? "WHERE ".implode(" AND ", $where) : "";

$total = $db->querySingle("SELECT COUNT(*) FROM audit_log $w");
$rows  = $db->query("SELECT * FROM audit_log $w ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>
<div class="container-fluid py-3">
  <h5>Nhật ký hệ thống</h5>
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-3"><input name="q" value="<?=htmlspecialchars($q)?>" class="form-control" placeholder="Từ khóa (user, id, JSON)"></div>
    <div class="col-md-2">
      <select name="action" class="form-select">
        <option value="">-- Hành động --</option>
        <?php foreach(['login','create','update','delete','soft_delete','hard_delete','lock','unlock','promote','demote'] as $a): ?>
          <option value="<?=$a?>" <?=$actionF===$a?'selected':''?>><?=$a?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input name="object_type" value="<?=htmlspecialchars($typeF)?>" class="form-control" placeholder="Object type (users, trips, tickets...)">
    </div>
    <div class="col-md-2"><input type="date" name="from" value="<?=$from?>" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="to"   value="<?=$to?>"   class="form-control"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Lọc</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr>
        <th>Thời gian</th><th>User</th><th>Hành động</th><th>Đối tượng</th><th>Chi tiết</th><th>IP</th><th>UA</th>
      </tr></thead>
      <tbody>
      <?php while($r=$rows->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
          <td><?=htmlspecialchars($r['created_at'])?></td>
          <td>#<?= (int)$r['user_id'] ?> - <?=htmlspecialchars($r['username'])?></td>
          <td><span class="badge bg-secondary"><?=$r['action']?></span></td>
          <td><?=htmlspecialchars($r['object_type'])?>: <code><?=htmlspecialchars($r['object_id'])?></code></td>
          <td><pre class="mb-0 small" style="white-space:pre-wrap"><?=htmlspecialchars($r['details_json'])?></pre></td>
          <td><?=htmlspecialchars($r['ip_address'])?></td>
          <td class="small text-muted" style="max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=htmlspecialchars($r['user_agent'])?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php $pages = max(1, ceil($total/$limit)); if($pages>1): ?>
    <nav><ul class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <li class="page-item <?=$i===$page?'active':''?>"><a class="page-link" href="?<?=http_build_query(array_merge($_GET,['page'=>$i]))?>"><?=$i?></a></li>
      <?php endfor; ?>
    </ul></nav>
  <?php endif; ?>
</div>
<?php require_once __DIR__."/_layout_bottom.php"; ?>
