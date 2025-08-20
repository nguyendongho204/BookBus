<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($pageTitle) ? $pageTitle : "Quản trị" ?></title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
  <header class="admin-header">
    <div class="brand">
      <a href="index.php" class="brand-link">#BookBus <span class="badge">Quản trị</span></a>
    </div>
    <nav class="admin-nav">
      <a class="nav-link <?= ($active ?? '')==='dashboard'?'active':'' ?>" href="index.php">Bảng điều khiển</a>
      <a class="nav-link <?= ($active ?? '')==='revenue'?'active':'' ?>" href="doanhthu.php">Doanh thu</a>
      <a class="nav-link <?= ($active ?? '')==='orders'?'active':'' ?>" href="orders.php">Đơn đặt</a>
      <a class="nav-link <?= ($active ?? '')==='trips'?'active':'' ?>" href="trips.php">Chuyến xe</a>
      <a class="nav-link <?= ($active ?? '')==='users'?'active':'' ?>" href="users.php">Người dùng</a>
      <a class="nav-link" href="../homepage.php">↩ Trang ngoài</a>
    </nav>
    <div class="admin-user">
      <span class="hello">Xin chào, <?= htmlspecialchars($_SESSION['user']['username'] ?? ($_SESSION['user']['email'] ?? 'admin')) ?></span>
    </div>
  </header>
  <main class="admin-main container-xl">
