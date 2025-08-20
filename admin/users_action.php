<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
// admin/users_action.php
session_start();
require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../libs/audit.php';

// (tuỳ bạn) chặn nếu chưa đăng nhập
if (!isset($_SESSION['user'])) {
  http_response_code(403);
  exit('Forbidden');
}

$actorId   = $_SESSION['user']['id']   ?? null;        // người thực hiện
$actorName = $_SESSION['user']['name'] ?? 'unknown';

$id     = (int)($_POST['id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($id <= 0 || $action === '') {
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Thiếu tham số'];
  header('Location: users.php');
  exit;
}

// LẤY THÔNG TIN HIỆN TẠI CỦA USER (bảng đang dùng là daily_dangky)
$st = $db->prepare("SELECT id, name, email, COALESCE(role,0) as role, COALESCE(status,1) as status, deleted_at FROM daily_dangky WHERE id=:id");
$st->bindValue(':id', $id, SQLITE3_INTEGER);
$cur = $st->execute()->fetchArray(SQLITE3_ASSOC);

if (!$cur) {
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Không tìm thấy tài khoản'];
  header('Location: users.php');
  exit;
}

try {
  $db->exec('BEGIN');

  switch ($action) {
    case 'lock': {
      $q = $db->prepare("UPDATE daily_dangky SET status=0 WHERE id=:id AND deleted_at IS NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'lock', 'users', $id, [
        'target_name'   => $cur['name'],
        'before_status' => (int)$cur['status'],
        'after_status'  => 0
      ]);

      $_SESSION['flash'] = ['type'=>'warning', 'msg'=>"Đã khóa tài khoản #$id"];
      break;
    }
    case 'unlock': {
      $q = $db->prepare("UPDATE daily_dangky SET status=1 WHERE id=:id AND deleted_at IS NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'unlock', 'users', $id, [
        'target_name'   => $cur['name'],
        'before_status' => (int)$cur['status'],
        'after_status'  => 1
      ]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã mở khóa tài khoản #$id"];
      break;
    }
    case 'promote': {
      $q = $db->prepare("UPDATE daily_dangky SET role=1 WHERE id=:id AND deleted_at IS NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'promote', 'users', $id, [
        'target_name' => $cur['name'],
        'before_role' => (int)$cur['role'],
        'after_role'  => 1
      ]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã nâng quyền quản trị cho #$id"];
      break;
    }
    case 'demote': {
      $q = $db->prepare("UPDATE daily_dangky SET role=0 WHERE id=:id AND deleted_at IS NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'demote', 'users', $id, [
        'target_name' => $cur['name'],
        'before_role' => (int)$cur['role'],
        'after_role'  => 0
      ]);

      $_SESSION['flash'] = ['type'=>'secondary', 'msg'=>"Đã hạ quyền tài khoản #$id"];
      break;
    }
    case 'soft_delete': {
      $q = $db->prepare("UPDATE daily_dangky SET deleted_at = datetime('now','localtime') WHERE id=:id AND deleted_at IS NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'soft_delete', 'users', $id, [
        'target_name' => $cur['name']
      ]);

      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Đã xóa (mềm) tài khoản #$id"];
      break;
    }
    case 'restore': {
      $q = $db->prepare("UPDATE daily_dangky SET deleted_at = NULL WHERE id=:id AND deleted_at IS NOT NULL");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'restore', 'users', $id, [
        'target_name' => $cur['name']
      ]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã khôi phục tài khoản #$id"];
      break;
    }
    case 'hard_delete': {
      $q = $db->prepare("DELETE FROM daily_dangky WHERE id=:id");
      $q->bindValue(':id', $id, SQLITE3_INTEGER);
      $q->execute();

      audit_log($db, $actorId, $actorName, 'hard_delete', 'users', $id, [
        'target_name' => $cur['name'],
        'note'        => 'Xóa vĩnh viễn'
      ]);

      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Đã xóa vĩnh viễn tài khoản #$id"];
      break;
    }
    default:
      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Hành động không hợp lệ"];
      break;
  }

  $db->exec('COMMIT');
} catch (Throwable $e) {
  $db->exec('ROLLBACK');
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Lỗi: ".$e->getMessage()];
}

// quay lại danh sách
header('Location: users.php');
exit;
