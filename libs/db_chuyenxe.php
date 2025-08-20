<?php
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Chỉ tạo bảng chuyenxe nếu chưa có
    $pdo->exec("CREATE TABLE IF NOT EXISTS chuyenxe (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ten_nhaxe TEXT NOT NULL,
        loai_xe TEXT NOT NULL,
        diem_di TEXT NOT NULL,
        diem_den TEXT NOT NULL,
        ngay_di DATE NOT NULL,
        gio_di TIME NOT NULL,
        gio_den TIME NOT NULL,
        gia_ve INTEGER NOT NULL,
        so_ghe INTEGER NOT NULL,
        so_ghe_con INTEGER NOT NULL
    )");

} catch (PDOException $e) {
    die("❌ Lỗi kết nối CSDL (chuyenxe): " . $e->getMessage());
}
?>
