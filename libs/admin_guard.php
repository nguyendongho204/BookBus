<?php
// libs/admin_guard.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session_bootstrap.php';

if (!isset($_SESSION['user'])) {
    $current  = $_SERVER['REQUEST_URI'] ?? (APP_BASE . '/');
    $redirect = APP_BASE . '/?show=login&redirect=' . urlencode($current);
    header('Location: ' . $redirect);
    exit;
}

$user = $_SESSION['user'];
// 0 = Admin, 1 = Nhân sự (chỉ 0 mới vào trang admin)
if (!array_key_exists('role', $user) || (int)$user['role'] !== 0) {
    header('Location: ' . APP_BASE . '/?show=forbidden');
    exit;
}
?>