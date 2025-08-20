<?php
// tool/make_admin.php
// Cách dùng: 
//   - Theo SĐT:  http://localhost:8080/src/tool/make_admin.php?phone=1234567890
//   - Theo email: http://localhost:8080/src/tool/make_admin.php?email=admin@gmail.com
require_once(__DIR__ . '/../libs/db.php'); // $db = new SQLite3(...)

$phone = $_GET['phone'] ?? '';
$email = $_GET['email'] ?? '';

if ($phone === '' && $email === '') {
  http_response_code(400);
  exit("Truyền ?phone=... (chỉ chữ số) hoặc ?email=...");
}

try {
  if ($phone !== '') {
    $digits = preg_replace('/\D+/', '', $phone);
    $st = $db->prepare("UPDATE daily_dangky SET role=1 WHERE phone=:p");
    $st->bindValue(':p', $digits, SQLITE3_TEXT);
    $st->execute();
    echo "OK: set admin by phone={$digits}";
  } else {
    $st = $db->prepare("UPDATE daily_dangky SET role=1 WHERE lower(email)=lower(:e)");
    $st->bindValue(':e', $email, SQLITE3_TEXT);
    $st->execute();
    echo "OK: set admin by email={$email}";
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo "Lỗi: " . $e->getMessage();
}
