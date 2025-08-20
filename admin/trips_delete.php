<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../libs/db_chuyenxe.php'; // $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id > 0) {
    // Nếu muốn chặn xoá khi đã có đơn, bật kiểm tra này:
    // $has = (int)$pdo->query("SELECT COUNT(*) FROM dat_ve WHERE id_chuyen = {$id}")->fetchColumn();
    // if ($has > 0) { header("Location: trips.php?err=" . urlencode("Không thể xoá: đã có đơn đặt.")); exit; }
    $st = $pdo->prepare("DELETE FROM chuyenxe WHERE id=:id");
    $st->execute([':id'=>$id]);
  }
}
header("Location: trips.php");
