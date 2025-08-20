<?php
session_start();
header('Content-Type: application/json');

$response = ['ok' => false];

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    $user = $_SESSION['user'];
    
    if (isset($user['id']) && !empty($user['id'])) {
        $response = [
            'ok' => true,
            'id' => $user['id'],
            'user_id' => $user['id'],
            'name' => $user['username'] ?? ($user['name'] ?? ($user['email'] ?? 'User')),
            'email' => $user['email'] ?? null
        ];
    }
}

echo json_encode($response);
exit;
?>