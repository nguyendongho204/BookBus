<?php
// includes/whoami.php
require_once __DIR__ . '/session_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if (!empty($_SESSION['user'])) {
    $name = $_SESSION['user']['name'] ?? ($_SESSION['user']['email'] ?? 'Tài khoản');
    echo json_encode(['ok' => true, 'name' => $name], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
}
