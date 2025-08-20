<?php
try {
    // Kết nối tới file SQLite trong thư mục libs
    $pdo = new PDO("sqlite:" . __DIR__ . "/libs/database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}
?>
