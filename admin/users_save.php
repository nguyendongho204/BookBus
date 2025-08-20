<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../libs/db.php'; // $db

$id   = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email= trim($_POST['email'] ?? '');
$phone= preg_replace('/\D+/', '', $_POST['phone'] ?? '');
$role = (int)($_POST['role'] ?? 0);

if ($name==='' || $email==='' || $phone==='') { header("Location: users.php"); exit; }

if ($id>0) {
  $st = $db->prepare("UPDATE daily_dangky SET name=:n,email=:e,phone=:p,role=:r WHERE id=:id");
  $st->bindValue(':n',$name,SQLITE3_TEXT);
  $st->bindValue(':e',$email,SQLITE3_TEXT);
  $st->bindValue(':p',$phone,SQLITE3_TEXT);
  $st->bindValue(':r',$role,SQLITE3_INTEGER);
  $st->bindValue(':id',$id,SQLITE3_INTEGER);
  $st->execute();
} else {
  $pw1 = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');
  if ($pw1==='' || $pw1!==$pw2) { header("Location: users_edit.php"); exit; }
  $hash = password_hash($pw1, PASSWORD_BCRYPT);
  // unique checks
  $chk = $db->prepare("SELECT 1 FROM daily_dangky WHERE lower(email)=lower(:e) OR phone=:p LIMIT 1");
  $chk->bindValue(':e',$email,SQLITE3_TEXT); $chk->bindValue(':p',$phone,SQLITE3_TEXT);
  $rs = $chk->execute();
  if ($rs->fetchArray()) { header("Location: users_edit.php"); exit; }
  $ins = $db->prepare("INSERT INTO daily_dangky(name,email,phone,password,role) VALUES(:n,:e,:p,:pw,:r)");
  $ins->bindValue(':n',$name,SQLITE3_TEXT);
  $ins->bindValue(':e',$email,SQLITE3_TEXT);
  $ins->bindValue(':p',$phone,SQLITE3_TEXT);
  $ins->bindValue(':pw',$hash,SQLITE3_TEXT);
  $ins->bindValue(':r',$role,SQLITE3_INTEGER);
  $ins->execute();
}
header("Location: users.php");
