<?php /* tai-khoan/_layout.php */ ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Tài khoản') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Đặt base để tất cả link trỏ về /src/ -->
  <base href="/src/">

  <!-- CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
  <?php
  // Gắn header/footer sẵn có
  if (is_file(__DIR__ . '/../header.php')) include __DIR__ . '/../header.php';
  ?>
  <main class="container my-4"><?= $content ?? '' ?></main>
  <?php if (is_file(__DIR__ . '/../footer.php')) include __DIR__ . '/../footer.php'; ?>

  <!-- JS -->
  <script src="js/bootstrap.min.js"></script>
</body>
</html>
