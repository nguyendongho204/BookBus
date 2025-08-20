<?php
// /src/tai-khoan/doi-mat-khau.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session_bootstrap.php';
if (empty($_SESSION['user']['id'])) {
  header('Location: /src/homepage.php?show=login'); exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: /src/tai-khoan/index.php'); exit;
}

$uid   = (int)$_SESSION['user']['id'];
$old   = (string)($_POST['old_password'] ?? '');
$new   = (string)($_POST['new_password'] ?? '');
$cfm   = (string)($_POST['confirm_password'] ?? '');

if (strlen($new) < 8) { header('Location: /src/tai-khoan/index.php?pwd=too_short'); exit; }
if ($new !== $cfm)     { header('Location: /src/tai-khoan/index.php?pwd=mismatch');  exit; }

// Kết nối DB gốc
$mode = null;
if (is_file(__DIR__ . '/../libs/db.php')) require_once __DIR__ . '/../libs/db.php';
if (isset($pdo) && $pdo instanceof PDO) {
  $mode='pdo'; $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
} elseif (isset($conn) && $conn instanceof mysqli) {
  $mode='mysqli'; if (method_exists($conn,'set_charset')) $conn->set_charset('utf8mb4');
} elseif (isset($db) && $db instanceof SQLite3) {
  $mode='sqlite3';
} else {
  header('Location: /src/tai-khoan/index.php?pwd=sys'); exit;
}

// Helpers
function table_exists_pdo(PDO $pdo, string $name): bool {
  $drv = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
  if ($drv === 'sqlite') {
    $st=$pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?"); $st->execute([$name]);
    return (bool)$st->fetchColumn();
  }
  $st=$pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_name=? LIMIT 1");
  try { $st->execute([$name]); } catch(Throwable $e){ return false; }
  return (bool)$st->fetchColumn();
}
function table_exists_mysqli(mysqli $c, string $name): bool {
  $sql="SELECT 1 FROM information_schema.tables WHERE table_name=? LIMIT 1";
  if ($st=$c->prepare($sql)) { $st->bind_param('s',$name); $st->execute(); $r=$st->get_result(); $ok=$r && $r->fetch_row(); $st->close(); return (bool)$ok; }
  return false;
}
function table_exists_sqlite3(SQLite3 $db, string $name): bool {
  $st=$db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:n"); $st->bindValue(':n',$name,SQLITE3_TEXT);
  $r=$st->execute(); $row=$r?$r->fetchArray(SQLITE3_NUM):false; return (bool)$row;
}

// Xác định bảng/field dùng cho user hiện tại
$tbl1 = 'quan_tri_vien'; // fields: id, mat_khau
$tbl2 = 'daily_dangky'; // fields: id, password
$use  = null;
$row  = null;

try {
  if ($mode==='pdo') {
    if (table_exists_pdo($pdo,$tbl1)) {
      $st=$pdo->prepare("SELECT id, mat_khau AS pwd FROM $tbl1 WHERE id=? LIMIT 1"); $st->execute([$uid]); $row=$st->fetch();
      if ($row) $use=['table'=>$tbl1,'col'=>'mat_khau'];
    }
    if (!$row && table_exists_pdo($pdo,$tbl2)) {
      $st=$pdo->prepare("SELECT id, password AS pwd FROM $tbl2 WHERE id=? LIMIT 1"); $st->execute([$uid]); $row=$st->fetch();
      if ($row) $use=['table'=>$tbl2,'col'=>'password'];
    }
  } elseif ($mode==='mysqli') {
    if (table_exists_mysqli($conn,$tbl1)) {
      $st=$conn->prepare("SELECT id, mat_khau AS pwd FROM $tbl1 WHERE id=? LIMIT 1"); $st->bind_param('i',$uid); $st->execute(); $r=$st->get_result();
      if ($r && ($row=$r->fetch_assoc())) $use=['table'=>$tbl1,'col'=>'mat_khau']; $st->close();
    }
    if (!$row && table_exists_mysqli($conn,$tbl2)) {
      $st=$conn->prepare("SELECT id, password AS pwd FROM $tbl2 WHERE id=? LIMIT 1"); $st->bind_param('i',$uid); $st->execute(); $r=$st->get_result();
      if ($r && ($row=$r->fetch_assoc())) $use=['table'=>$tbl2,'col'=>'password']; $st->close();
    }
  } else { // sqlite3
    if (table_exists_sqlite3($db,$tbl1)) {
      $st=$db->prepare("SELECT id, mat_khau AS pwd FROM $tbl1 WHERE id=:i LIMIT 1"); $st->bindValue(':i',$uid,SQLITE3_INTEGER);
      $r=$st->execute(); $row=$r?$r->fetchArray(SQLITE3_ASSOC):null; if ($row) $use=['table'=>$tbl1,'col'=>'mat_khau'];
    }
    if (!$row && table_exists_sqlite3($db,$tbl2)) {
      $st=$db->prepare("SELECT id, password AS pwd FROM $tbl2 WHERE id=:i LIMIT 1"); $st->bindValue(':i',$uid,SQLITE3_INTEGER);
      $r=$st->execute(); $row=$r?$r->fetchArray(SQLITE3_ASSOC):null; if ($row) $use=['table'=>$tbl2,'col'=>'password'];
    }
  }
} catch (Throwable $e) {
  header('Location: /src/tai-khoan/index.php?pwd=sys'); exit;
}

if (!$row || !$use) { header('Location: /src/tai-khoan/index.php?pwd=sys'); exit; }

// Xác thực mật khẩu cũ (hỗ trợ hash hoặc plain)
$stored = (string)($row['pwd'] ?? '');
$ok = false;
if ($stored !== '') {
  $info = password_get_info($stored);
  $ok = !empty($info['algo']) ? password_verify($old, $stored) : hash_equals($stored, $old);
}
if (!$ok) { header('Location: /src/tai-khoan/index.php?pwd=wrong_old'); exit; }

// Cập nhật mật khẩu mới (hash)
$newHash = password_hash($new, PASSWORD_DEFAULT);

try {
  if ($mode==='pdo') {
    $st=$pdo->prepare("UPDATE {$use['table']} SET {$use['col']}=? WHERE id=?"); $st->execute([$newHash, $uid]);
  } elseif ($mode==='mysqli') {
    $sql="UPDATE {$use['table']} SET {$use['col']}=? WHERE id=?"; $st=$conn->prepare($sql); $st->bind_param('si',$newHash,$uid); $st->execute(); $st->close();
  } else {
    $st=$db->prepare("UPDATE {$use['table']} SET {$use['col']}=:p WHERE id=:i");
    $st->bindValue(':p',$newHash,SQLITE3_TEXT); $st->bindValue(':i',$uid,SQLITE3_INTEGER); $st->execute();
  }
} catch (Throwable $e) {
  header('Location: /src/tai-khoan/index.php?pwd=sys'); exit;
}

header('Location: /src/tai-khoan/index.php?pwd=ok');
exit;
