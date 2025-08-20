\
<?php
// libs/audit.php
// Hàm tiện ích ghi nhật ký hệ thống (Audit log).
// Gọi: audit_log($db, $userId, $username, 'action', 'objectType', $objectId, ['k'=>'v']);

if (!function_exists('audit_log')) {
  function audit_log($db, $userId, $username, $action, $objectType, $objectId, $details = []) {
    $stmt = $db->prepare("
      INSERT INTO audit_log (user_id, username, action, object_type, object_id, details_json, ip_address, user_agent)
      VALUES (:uid, :uname, :action, :otype, :oid, :json, :ip, :ua)
    ");
    $stmt->bindValue(':uid',    $userId);
    $stmt->bindValue(':uname',  $username);
    $stmt->bindValue(':action', $action);
    $stmt->bindValue(':otype',  $objectType);
    $stmt->bindValue(':oid',    (string)$objectId);
    $stmt->bindValue(':json',   json_encode($details, JSON_UNESCAPED_UNICODE));
    $stmt->bindValue(':ip',     $_SERVER['REMOTE_ADDR'] ?? '');
    $stmt->bindValue(':ua',     $_SERVER['HTTP_USER_AGENT'] ?? '');
    $stmt->execute();
  }
}
