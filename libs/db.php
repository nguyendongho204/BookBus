<?php
try {
    // Kết nối SQLite qua PDO
    $pdo = new PDO("sqlite:" . __DIR__ . "/database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // ánh xạ cho code cũ
    $conn = $pdo;

    // Tạo bảng daily_dangky nếu chưa có
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_dangky (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT,
        password TEXT NOT NULL,
        created_at TEXT DEFAULT (datetime('now', 'localtime')),
        role INTEGER NOT NULL DEFAULT 0
    )");

    // Tạo bảng password_reset nếu chưa có
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT NOT NULL,
        token TEXT NOT NULL,
        created_at TEXT DEFAULT (datetime('now', 'localtime'))
    )");

    // Tạo bảng chuyenxe nếu chưa có
    $pdo->exec("CREATE TABLE IF NOT EXISTS chuyenxe (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ten_nhaxe TEXT NOT NULL,
        diem_di TEXT NOT NULL,
        diem_den TEXT NOT NULL,
        ngay_di TEXT NOT NULL,
        gio_di TEXT NOT NULL,
        gia_ve REAL NOT NULL,
        so_ghe INTEGER DEFAULT 40,
        created_at TEXT DEFAULT (datetime('now', 'localtime'))
    )");

    // Tạo bảng dat_ve nếu chưa có - QUAN TRỌNG!
    $pdo->exec("CREATE TABLE IF NOT EXISTS dat_ve (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        id_chuyen INTEGER,
        ho_ten TEXT,
        email TEXT,
        sdt TEXT,
        so_luong INTEGER NOT NULL DEFAULT 1,
        amount REAL,
        gia_ve REAL,
        payment_status TEXT DEFAULT 'pending',
        order_id TEXT,
        ngay_dat TEXT DEFAULT (datetime('now', 'localtime')),
        created_at TEXT DEFAULT (datetime('now', 'localtime')),
        FOREIGN KEY (user_id) REFERENCES daily_dangky(id),
        FOREIGN KEY (id_chuyen) REFERENCES chuyenxe(id)
    )");

    // Thêm cột user_id vào bảng dat_ve nếu chưa có (cho database cũ)
    try {
        $pdo->exec("ALTER TABLE dat_ve ADD COLUMN user_id INTEGER");
    } catch (PDOException $e) {
        // Cột đã tồn tại, bỏ qua lỗi
    }

} catch (PDOException $e) {
    die("Lỗi kết nối SQLite: " . $e->getMessage());
}
?>