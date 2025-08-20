<?php
/**
 * XỬ LÝ ĐĂNG KÝ (SQLite)
 * - Chuẩn hoá SĐT về digits-only trước khi kiểm tra/lưu
 * - Kiểm tra trùng email/SĐT
 * - Redirect hash cho UI: /#?show=register&err=...
 */
session_start();

$redirectBase = '/src/#';

try {
  // ===== Input =====
  $name      = trim($_POST['ten_dang_nhap'] ?? $_POST['fullname'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $phone_in  = isset($_POST['sdt']) ? $_POST['sdt'] : (isset($_POST['phone']) ? $_POST['phone'] : '');
  $password  = trim($_POST['mat_khau'] ?? $_POST['password'] ?? '');

  // ===== Validate =====
  if ($name === '' || $email === '' || $phone_in === '' || $password === '') {
    header('Location: ' . $redirectBase . '?show=register&err=missing'); exit;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirectBase . '?show=register&err=invalid_email'); exit;
  }

  // Chuẩn hoá SĐT: chỉ còn chữ số
  $phone = preg_replace('/\D+/', '', $phone_in);
  if ($phone === '' || strlen($phone) < 9) {
    header('Location: ' . $redirectBase . '?show=register&err=invalid_phone'); exit;
  }

  // ===== DB =====
  $dbPath = __DIR__ . '/database.db';
  $pdo = new PDO('sqlite:' . $dbPath);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Đảm bảo index unique cho phone (idempotent)
  $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_daily_dangky_phone_unique ON daily_dangky(phone)');

  // Check trùng email (không phân biệt hoa/thường)
  $st = $pdo->prepare('SELECT 1 FROM daily_dangky WHERE LOWER(email) = LOWER(:e) LIMIT 1');
  $st->execute([':e' => $email]);
  if ($st->fetchColumn()) {
    header('Location: ' . $redirectBase . '?show=register&err=email_exists'); exit;
  }

  // Check trùng phone (so sánh dạng digits-only)
  $st = $pdo->prepare('SELECT 1 FROM daily_dangky WHERE phone = :p LIMIT 1');
  $st->execute([':p' => $phone]);
  if ($st->fetchColumn()) {
    header('Location: ' . $redirectBase . '?show=register&err=phone_exists'); exit;
  }

  // Hash & insert
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $ins = $pdo->prepare('INSERT INTO daily_dangky (name, email, phone, password) VALUES (:n, :e, :p, :pw)');
  $ok  = $ins->execute([':n' => $name, ':e' => $email, ':p' => $phone, ':pw' => $hash]);

  if (!$ok) {
    header('Location: ' . $redirectBase . '?show=register&err=server_error'); exit;
  }

  // Thành công
  header('Location: ' . $redirectBase . '?regsucc=1'); exit;

} catch (Throwable $e) {
  $msg = $e->getMessage();
  // Map lỗi UNIQUE để trả về err tương ứng
  if (stripos($msg, 'UNIQUE') !== false) {
    if (stripos($msg, 'email') !== false) { header('Location: ' . $redirectBase . '?show=register&err=email_exists'); exit; }
    if (stripos($msg, 'phone') !== false) { header('Location: ' . $redirectBase . '?show=register&err=phone_exists'); exit; }
  }
  header('Location: ' . $redirectBase . '?show=register&err=server_error'); exit;
}