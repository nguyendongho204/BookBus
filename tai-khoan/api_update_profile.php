<?php
// tai-khoan/api_update_profile.php
require __DIR__ . '/_auth.php';
require __DIR__ . '/../libs/db_chuyenxe.php'; // $pdo (SQLite PDO)
header('Content-Type: application/json; charset=utf-8');

$name = trim($_POST['name'] ?? '');
$phone= trim($_POST['phone'] ?? '');
if ($name === '') { echo json_encode(['ok'=>false,'message'=>'Vui lòng nhập họ tên']); exit; }

$st = $pdo->prepare("UPDATE daily_dangky SET name=?, phone=? WHERE id=?");
$ok = $st->execute([$name, $phone, $userId]);

echo json_encode(['ok'=>$ok, 'message'=>$ok?'Đã lưu thay đổi':'Không thể lưu']);
