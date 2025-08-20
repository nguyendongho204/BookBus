<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
$pageTitle = "Người dùng - Thêm/Sửa";
$active = "users";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db.php"; // $db (SQLite3)

$id = (int)($_GET['id'] ?? 0);
$user = ['name'=>'','email'=>'','phone'=>'','role'=>0];
if ($id>0) {
  $st = $db->prepare("SELECT id,name,email,phone,COALESCE(role,0) role FROM daily_dangky WHERE id=:id");
  $st->bindValue(':id',$id,SQLITE3_INTEGER);
  $rs = $st->execute(); $row = $rs->fetchArray(SQLITE3_ASSOC);
  if ($row) $user = $row;
}
?>
<div class="form-card">
  <h5><?= $id>0 ? 'Cập nhật người dùng' : 'Thêm người dùng' ?></h5>
  <form method="post" action="users_save.php">
    <input type="hidden" name="id" value="<?=$id?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Họ tên</label>
        <input class="form-control" name="name" required value="<?=htmlspecialchars($user['name'])?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required value="<?=htmlspecialchars($user['email'])?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Số điện thoại</label>
        <input class="form-control" name="phone" required value="<?=htmlspecialchars($user['phone'])?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Quyền</label>
        <select class="form-select" name="role">
          <option value="0" <?= (int)$user['role']===0?'selected':'' ?>>User</option>
          <option value="1" <?= (int)$user['role']===1?'selected':'' ?>>Admin</option>
        </select>
      </div>
      <?php if ($id===0): ?>
      <div class="col-md-6">
        <label class="form-label">Mật khẩu</label>
        <input type="password" class="form-control" name="password" minlength="4" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Nhập lại mật khẩu</label>
        <input type="password" class="form-control" name="password2" minlength="4" required>
      </div>
      <?php endif; ?>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-brand"><?= $id>0?'Lưu thay đổi':'Tạo tài khoản' ?></button>
      <a class="btn btn-ghost" href="users.php">Hủy</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
