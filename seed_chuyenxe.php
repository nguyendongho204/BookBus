<?php
include("libs/db_chuyenxe.php");

// Xóa dữ liệu cũ nếu có (nếu muốn giữ dữ liệu thì bỏ dòng này)
$pdo->exec("DELETE FROM chuyenxe");

// Dữ liệu mẫu
$data = [
    ['Futa Bus', 'Xe ghế ngồi', 'Cần Thơ', 'Vĩnh Long', '2025-08-05', '07:00', '08:30', 15000, 40, 30],
    ['Phương Trang', 'Xe giường nằm', 'Cần Thơ', 'Ô Môn', '2025-08-05', '09:00', '10:00', 5000, 30, 25],
    ['Mai Linh', 'Xe ghế ngồi', 'Phong Điền', 'Cần Thơ', '2025-08-05', '13:00', '14:00', 7000, 35, 20],
    ['Thanh Buoi', 'Xe giường nằm', 'Cần Thơ', 'Thốt Nốt', '2025-08-05', '15:00', '17:00', 12000, 40, 35],
    ['Cao Đạt', 'Xe ghế ngồi', 'Cần Thơ', 'Bình Tân', '2025-08-06', '08:00', '09:30', 12000, 32, 28],
    ['Anh Quốc', 'Limousine 11 chỗ', 'Cần Thơ', 'Sân Bay Cần Thơ', '2025-08-06', '18:30', '20:45', 195500, 11, 8],
];

$stmt = $pdo->prepare("INSERT INTO chuyenxe 
    (ten_nhaxe, loai_xe, diem_di, diem_den, ngay_di, gio_di, gio_den, gia_ve, so_ghe, so_ghe_con) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($data as $row) {
    $stmt->execute($row);
}

echo "<h3>✅ Đã thêm dữ liệu mẫu cho bảng chuyenxe thành công!</h3>";
?>
    