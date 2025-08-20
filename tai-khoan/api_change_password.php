<?php
// tai-khoan/api_change_password.php
require __DIR__ . '/_auth.php';
require __DIR__ . '/../libs/db_chuyenxe.php'; // $pdo (SQLite PDO)
header('Content-Type: application/json; charset=utf-8');

$old = $_POST['old'] ?? '';
$n1  = $_POST['new1'] ?? '';
$n2  = $_POST['new2'] ?? '';
if ($n1==='' || $n1!==$n2) { echo json_encode(['ok'=>false,'message'=>'Mật khẩu mới không khớp']); exit; }
if (strlen($n1) < 6) { echo json_encode(['ok'=>false,'message'=>'Tối thiểu 6 ký tự']); exit; }

$st = $pdo->prepare("SELECT password FROM daily_dangky WHERE id=?");
$st->execute([$userId]);
$hash = $st->fetchColumn();

if (!$hash || !password_verify($old, $hash)) {
  echo json_encode(['ok'=>false,'message'=>'Mật khẩu hiện tại không đúng']); exit;
}
$newHash = password_hash($n1, PASSWORD_DEFAULT);
$ok = $pdo->prepare("UPDATE daily_dangky SET password=? WHERE id=?")->execute([$newHash, $userId]);

echo json_encode(['ok'=>$ok,'message'=>$ok?'Đã cập nhật mật khẩu':'Không thể cập nhật']);
