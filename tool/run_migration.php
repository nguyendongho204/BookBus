<?php
// CHỈNH lại đường dẫn DB nếu dự án của bạn đặt nơi khác
$dbPath = __DIR__ . '/../libs/database.db';
$db = new SQLite3($dbPath);
$sql = file_get_contents(__DIR__.'/migrate_20250816.sql');
if ($db->exec($sql)) {
  echo "Migration OK\n";
} else {
  echo "Migration FAILED: ".$db->lastErrorMsg()."\n";
}
