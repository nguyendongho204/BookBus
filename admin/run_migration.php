<?php
// admin/run_migration.php
// Chạy migrations/001_audit_log.sql để tạo bảng audit_log trong SQLite.
// ⚠️ Nên xóa file này sau khi chạy xong.

session_start();
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../libs/db.php';

$sqlFile = __DIR__ . '/../migrations/001_audit_log.sql';
if (!file_exists($sqlFile)) {
    exit("❌ Không tìm thấy file: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    exit("❌ Không đọc được nội dung file SQL.\n");
}

// Tách câu lệnh theo dấu ; rồi chạy từng cái
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $st) {
    if ($st === '') continue;
    $ok = $db->exec($st);
    if ($ok === false) {
        echo "❌ Lỗi khi chạy: $st\n";
        echo "Lý do: " . $db->lastErrorMsg() . "\n";
    }
}

$check = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='audit_log'");
if ($check === 'audit_log') {
    echo "✅ Đã tạo (hoặc đã có) bảng audit_log.\n";
    $res = $db->query("PRAGMA table_info(audit_log)");
    echo "Cấu trúc bảng audit_log:\n";
    while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
        echo " - {$r['name']} ({$r['type']})\n";
    }
} else {
    echo "❌ Chưa thấy bảng audit_log.\n";
}
