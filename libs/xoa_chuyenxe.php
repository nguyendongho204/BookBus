<?php
include("db_chuyenxe.php");


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Chuẩn bị câu truy vấn xóa
    $stmt = $pdo->prepare("DELETE FROM chuyenxe WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Sau khi xóa chuyển về trang danh sách
    header("Location: xem_chuyenxe.php");
    exit;
} else {
    echo "❌ Không xác định được ID chuyến xe để xóa.";
}
?>
