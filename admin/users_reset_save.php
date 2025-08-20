<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../libs/db.php';

$id  = (int)($_POST['id'] ?? 0);
$pw1 = (string)($_POST['password1'] ?? '');
$pw2 = (string)($_POST['password2'] ?? '');

if ($id <= 0 || $pw1 === '' || $pw1 !== $pw2) { header("Location: users.php"); exit; }

$hash = password_hash($pw1, PASSWORD_BCRYPT);

$st = $db->prepare("UPDATE daily_dangky SET password=:pw WHERE id=:id");
$st->bindValue(':pw', $hash, SQLITE3_TEXT);
$st->bindValue(':id', $id, SQLITE3_INTEGER);
$st->execute();

header("Location: users.php");
