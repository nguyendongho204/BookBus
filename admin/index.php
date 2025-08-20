<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>
<?php
$pageTitle = "Bảng điều khiển";
$active = "dashboard";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db_chuyenxe.php"; // $pdo

// --- KPIs ---
// Sửa tên bảng revenue bị gãy "dat..._ve" -> "dat_ve" (đổi lại nếu dự án bạn dùng tên khác)
$revStmt = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM dat_ve WHERE payment_status IN ('success','paid')");
$rev = (int)$revStmt->fetchColumn();

// Nếu là SQLite dùng date('now','localtime'), nếu MySQL hãy đổi thành CURDATE()
$ordersToday = (int)$pdo->query("SELECT COUNT(*) FROM dat_ve WHERE date(ngay_dat)=date('now','localtime')")->fetchColumn();
$trips = (int)$pdo->query("SELECT COUNT(*) FROM chuyenxe")->fetchColumn();
// Đổi bảng users theo schema của bạn (giữ daily_dangky như file gốc nếu đúng)
$users = (int)$pdo->query("SELECT COUNT(*) FROM daily_dangky")->fetchColumn();
?>
<div class="kpi-grid">
  <div class="kpi"><div class="label">Tổng doanh thu</div><div class="value"><?=number_format($rev)?> đ</div></div>
  <div class="kpi"><div class="label">Đơn hôm nay</div><div class="value"><?=$ordersToday?></div></div>
  <div class="kpi"><div class="label">Chuyến xe</div><div class="value"><?=$trips?></div></div>
  <div class="kpi"><div class="label">Người dùng</div><div class="value"><?=$users?></div></div>
</div>
<?php require_once __DIR__ . "/_layout_bottom.php"; ?>
