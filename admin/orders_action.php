<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../libs/db_chuyenxe.php'; // $pdo

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'pending';

$allowed = ['pending','paid','success','failed','refunded'];
if ($id>0 && in_array($status,$allowed,true)) {
  $st = $pdo->prepare("UPDATE dat_ve SET payment_status=:s WHERE id=:id");
  $st->execute([':s'=>$status, ':id'=>$id]);
}
header("Location: orders.php");
