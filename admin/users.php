<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
$pageTitle = "Quản lý tài khoản";
$active = "users";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db.php";

/* ---------- Bộ lọc ---------- */
$keyword = trim($_GET['q'] ?? '');
$roleF   = $_GET['role'] ?? '';
$statusF = $_GET['status'] ?? '';

$where = [];
if ($keyword !== '') {
  $kw = SQLite3::escapeString($keyword);
  $where[] = "(name LIKE '%$kw%' OR email LIKE '%$kw%' OR phone LIKE '%$kw%')";
}
if ($roleF !== '' && ($roleF === '0' || $roleF === '1')) {
  $where[] = "COALESCE(role,0) = ".(int)$roleF;
}
if ($statusF !== '' && in_array($statusF, ['0','1','2'], true)) {
  // 1=Hoạt động, 0=Đã khóa, 2=Đã xoá (mềm)
  if ($statusF === '2')       $where[] = "deleted_at IS NOT NULL";
  elseif ($statusF === '1')  { $where[] = "deleted_at IS NULL AND COALESCE(status,1)=1"; }
  else                       { $where[] = "deleted_at IS NULL AND COALESCE(status,1)=0"; }
}

$sql = "
  SELECT id, name, email, phone,
         COALESCE(role,0)   AS role,
         COALESCE(status,1) AS status,
         deleted_at
  FROM daily_dangky
";
if ($where) $sql .= " WHERE ".implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$rows = $db->query($sql);
?>
<style>
/* Chỉ áp dụng cho trang này để tránh ảnh hưởng chỗ khác */
.users-page{padding:16px}
.users-card{border:1px solid #e9ecef;border-radius:14px;background:#fff}
.users-card .body{padding:16px}
.filter-grid{display:grid;grid-template-columns:1.2fr .8fr .8fr auto;gap:10px}
@media(max-width:768px){.filter-grid{grid-template-columns:1fr}}
.table thead th{background:#f8f9fa;font-weight:600}
.badge-round{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.85rem;font-weight:600}
.badge-green{color:#1e7e34;background:#e6f4ea;border:1px solid #ccead6}
.badge-red{color:#b21f2d;background:#fde8ea;border:1px solid #f5c2c7}
.badge-muted{color:#495057;background:#f1f3f5;border:1px solid #e9ecef}
.actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.actions .btn{padding:.35rem .6rem;border-radius:.5rem;font-size:.875rem}
</style>

<div class="users-page">
  <div class="users-card">
    <div class="body">
      <h5 class="mb-3">Quản lý tài khoản</h5>

      <!-- Bộ lọc -->
      <form class="filter-grid mb-3" method="get">
        <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm theo tên / email / phone...">
        <select name="role" class="form-select">
          <option value="">Tất cả quyền</option>
          <option value="1" <?= $roleF==='1'?'selected':'' ?>>Quản trị viên</option>
          <option value="0" <?= $roleF==='0'?'selected':'' ?>>Người dùng</option>
        </select>
        <select name="status" class="form-select">
          <option value="">Tất cả trạng thái</option>
          <option value="1" <?= $statusF==='1'?'selected':'' ?>>Hoạt động</option>
          <option value="0" <?= $statusF==='0'?'selected':'' ?>>Ngưng hoạt động</option>
          <option value="2" <?= $statusF==='2'?'selected':'' ?>>Đã xoá</option>
        </select>
        <button class="btn btn-primary">Lọc</button>
      </form>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="width:80px">ID</th>
              <th>Tên</th>
              <th>Email</th>
              <th>Phone</th>
              <th style="width:140px">Quyền</th>
              <th style="width:140px">Trạng thái</th>
              <th style="min-width:560px">Thao tác</th>
            </tr>
          </thead>
          <tbody>
          <?php $hasRow = false; ?>
          <?php while ($u = $rows->fetchArray(SQLITE3_ASSOC)): $hasRow = true; ?>
            <tr>
              <td>#<?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['name'] ?? '') ?></td>
              <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
              <td><?= (int)$u['role']===1 ? 'Quản trị viên' : 'Người dùng' ?></td>
              <td>
                <?php if (!empty($u['deleted_at'])): ?>
                  <span class="badge-round badge-muted">Đã xoá</span>
                <?php else: ?>
                  <?php if ((int)$u['status']===1): ?>
                    <span class="badge-round badge-green">Hoạt động</span>
                  <?php else: ?>
                    <span class="badge-round badge-red">Ngưng hoạt động</span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>

              <td>
                <div class="actions">
                  <?php if (empty($u['deleted_at'])): ?>
                    <!-- Khóa / Mở khóa -->
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="Xác nhận <?= ((int)$u['status']===1?'KHÓA':'MỞ KHÓA') ?> tài khoản này?">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="<?= (int)$u['status']===1 ? 'lock' : 'unlock' ?>">
                      <button class="btn <?= (int)$u['status']===1 ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                        <?= (int)$u['status']===1?'Khóa':'Mở khóa' ?>
                      </button>
                    </form>

                    <!-- Đặt lại mật khẩu -->
                    <a class="btn btn-outline-primary" href="users_reset.php?id=<?=$u['id']?>">
                      Đặt lại MK
                    </a>

                    <!-- Xóa (mềm) -->
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="Xác nhận XOÁ tài khoản này (xoá mềm)?">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="soft_delete">
                      <button class="btn btn-outline-danger">
                        Xóa
                      </button>
                    </form>
                  <?php else: ?>
                    <!-- Khôi phục -->
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="Khôi phục tài khoản đã xoá này?">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="restore">
                      <button class="btn btn-outline-success">Khôi phục</button>
                    </form>

                    <!-- Xóa vĩnh viễn -->
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="XÓA VĨNH VIỄN tài khoản này? Hành động không thể hoàn tác.">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="hard_delete">
                      <button class="btn btn-danger">Xóa vĩnh viễn</button>
                    </form>
                  <?php endif; ?>

                  <!-- Nâng quyền / Hạ quyền -->
                  <?php if ((int)$u['role']===0): ?>
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="Nâng quyền quản trị cho tài khoản này?">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="promote">
                      <button class="btn btn-info text-white">
                        Nâng quyền
                      </button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="users_action.php" class="m-0 needs-confirm"
                          data-confirm="Hạ quyền xuống người dùng thường?">
                      <input type="hidden" name="id" value="<?=$u['id']?>">
                      <input type="hidden" name="action" value="demote">
                      <button class="btn btn-secondary">
                        Hạ quyền
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>

          <?php if (!$hasRow): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                Không có dữ liệu phù hợp.
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- Confirm Modal (Bootstrap 5) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xác nhận thao tác</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmMessage" class="mb-0">Bạn có chắc muốn thực hiện thao tác này?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Huỷ</button>
        <button type="button" id="confirmYes" class="btn btn-primary">Xác nhận</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  let pendingForm = null;
  const modalEl = document.getElementById('confirmModal');
  const msgEl   = document.getElementById('confirmMessage');
  const yesBtn  = document.getElementById('confirmYes');
  if(!modalEl) return;
  const bsModal = new bootstrap.Modal(modalEl);

  document.addEventListener('click', function(e){
    const btn = e.target.closest('button, a');
    if(!btn) return;
    const form = btn.closest('form.needs-confirm');
    if(!form) return; // Chỉ chặn các form cần xác nhận
    e.preventDefault();
    const msg = form.getAttribute('data-confirm') || 'Bạn có chắc muốn thực hiện thao tác này?';
    msgEl.textContent = msg;
    pendingForm = form;
    bsModal.show();
  });

  yesBtn.addEventListener('click', function(){
    if (pendingForm) {
      bsModal.hide();
      pendingForm.submit();
      pendingForm = null;
    }
  });
})();
</script>

<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
