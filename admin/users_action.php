<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
// admin/users_action.php
require_once __DIR__ . '/../libs/db.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user'])) {
  http_response_code(403);
  exit('Forbidden');
}

$actorId   = $_SESSION['user']['id']   ?? null;
$actorName = $_SESSION['user']['name'] ?? 'unknown';

$id     = (int)($_POST['id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($id <= 0 || $action === '') {
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Thiếu tham số'];
  header('Location: users.php');
  exit;
}

// Lấy thông tin user hiện tại
$stmt = $pdo->prepare("SELECT id, name, email, COALESCE(role,0) as role, COALESCE(status,1) as status, deleted_at FROM daily_dangky WHERE id = ?");
$stmt->execute([$id]);
$cur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cur) {
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Không tìm thấy tài khoản'];
  header('Location: users.php');
  exit;
}

try {
  $pdo->beginTransaction();

  switch ($action) {
    case 'lock': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET status = 0 WHERE id = ? AND deleted_at IS NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'warning', 'msg'=>"Đã khóa tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'unlock': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET status = 1 WHERE id = ? AND deleted_at IS NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã mở khóa tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'promote': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET role = 1 WHERE id = ? AND deleted_at IS NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã nâng quyền quản trị cho #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'demote': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET role = 0 WHERE id = ? AND deleted_at IS NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'secondary', 'msg'=>"Đã hạ quyền tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'soft_delete': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET deleted_at = datetime('now','localtime') WHERE id = ? AND deleted_at IS NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Đã xóa (mềm) tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'restore': {
      $stmt = $pdo->prepare("UPDATE daily_dangky SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Đã khôi phục tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    case 'hard_delete': {
      $stmt = $pdo->prepare("DELETE FROM daily_dangky WHERE id = ?");
      $stmt->execute([$id]);

      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Đã xóa vĩnh viễn tài khoản #{$id} ({$cur['name']})"];
      break;
    }
    
    default:
      $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Hành động không hợp lệ"];
      break;
  }

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  $_SESSION['flash'] = ['type'=>'danger', 'msg'=>"Lỗi: ".$e->getMessage()];
}

// Quay lại danh sách
header('Location: users.php');
exit;
?>