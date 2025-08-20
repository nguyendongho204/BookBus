<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
require_once __DIR__.'/../libs/db.php';

$id  = (int)($_POST['id'] ?? 0);
$pw1 = (string)($_POST['password1'] ?? '');
$pw2 = (string)($_POST['password2'] ?? '');

if ($id <= 0 || $pw1 === '' || $pw1 !== $pw2) { header("Location: users.php"); exit; }

$hash = password_hash($pw1, PASSWORD_BCRYPT);

$st = $pdo->prepare("UPDATE daily_dangky SET password=:pw WHERE id=:id");
$st->bindValue(':pw', $hash, PDO::PARAM_STR);
$st->bindValue(':id', $id, PDO::PARAM_INT);
$st->execute();

header("Location: users.php");
