<?php
// tai-khoan/api_orders.php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../libs/db_chuyenxe.php'; // $pdo (SQLite PDO)

header('Content-Type: application/json; charset=utf-8');

$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;

$email = (string)($user['email'] ?? '');
$sdt   = (string)($user['sdt'] ?? '');

// ---- Đếm tổng đơn ----
$c = $pdo->prepare("
  SELECT COUNT(*)
  FROM dat_ve dv
  WHERE (dv.user_id IS NOT NULL AND dv.user_id = :uid)
     OR (dv.user_id IS NULL AND dv.email <> '' AND dv.email = :email)
     OR (dv.user_id IS NULL AND (dv.email IS NULL OR dv.email = '') AND dv.sdt = :sdt)
");
$c->execute([
    ':uid'   => $userId,
    ':email' => $email,
    ':sdt'   => $sdt
]);
$total = (int)$c->fetchColumn();

// ---- Lấy danh sách đơn ----
$st = $pdo->prepare("
  SELECT dv.id, dv.order_id, dv.so_luong, dv.amount, dv.payment_status,
         (cx.diem_di || ' → ' || cx.diem_den) AS tuyen,
         (cx.ngay_di || ' ' || cx.gio_di)     AS thoigian
  FROM dat_ve dv
  LEFT JOIN chuyenxe cx ON cx.id = dv.id_chuyen
  WHERE (dv.user_id IS NOT NULL AND dv.user_id = :uid)
     OR (dv.user_id IS NULL AND dv.email <> '' AND dv.email = :email)
     OR (dv.user_id IS NULL AND (dv.email IS NULL OR dv.email = '') AND dv.sdt = :sdt)
  ORDER BY dv.id DESC
  LIMIT :lim OFFSET :off
");
$st->bindValue(':uid', $userId, PDO::PARAM_INT);
$st->bindValue(':email', $email, PDO::PARAM_STR);
$st->bindValue(':sdt', $sdt, PDO::PARAM_STR);
$st->bindValue(':lim', $per, PDO::PARAM_INT);
$st->bindValue(':off', $off, PDO::PARAM_INT);
$st->execute();

$items = $st->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'ok'          => true,
  'page'        => $page,
  'per_page'    => $per,
  'total'       => $total,
  'total_pages' => max(1, (int)ceil($total / $per)),
  'items'       => $items
], JSON_UNESCAPED_UNICODE);
