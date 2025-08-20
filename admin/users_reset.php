<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
$pageTitle = "Đặt lại mật khẩu";
$active = "users";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db.php";

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT id, name, email FROM daily_dangky WHERE id=:id");
$st->bindValue(':id',$id,PDO::PARAM_INT);
$st->execute();
$u = $st->fetch();
if (!$u) { header("Location: users.php"); exit; }
?>
<link rel="stylesheet" href="/assets/css/styleaccount.css">
<div class="acc-page">
  <div class="acc-card">
    <div class="acc-card-body">
      <h6 class="mb-3"><i class="bi bi-key"></i> Đặt lại mật khẩu cho: <?= htmlspecialchars($u['name']) ?> (ID #<?= $u['id'] ?>)</h6>
      <form method="post" action="users_reset_save.php" onsubmit="return confirm('Xác nhận đặt lại mật khẩu?');" class="row g-3">
        <input type="hidden" name="id" value="<?=$u['id']?>">
        <div class="col-md-6">
          <label class="form-label">Mật khẩu mới</label>
          <input type="password" class="form-control" name="password1" minlength="6" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nhập lại mật khẩu</label>
          <input type="password" class="form-control" name="password2" minlength="6" required>
        </div>
        <div class="col-12 d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-save"></i> Cập nhật</button>
          <a class="btn btn-secondary" href="users.php"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
