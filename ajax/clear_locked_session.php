<?php
// Xóa session thông báo tài khoản bị khóa
session_start();
$_SESSION['show_locked_modal'] = false;
echo json_encode(['success' => true]);
?>