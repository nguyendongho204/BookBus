<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
require_once __DIR__.'/../libs/db_chuyenxe.php'; // $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id > 0) {
        try {
            // Kiểm tra xem có đơn đặt vé nào đang sử dụng chuyến xe này không
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM dat_ve WHERE id_chuyen = :id");
            $checkStmt->execute([':id' => $id]);
            $hasBookings = (int)$checkStmt->fetchColumn();
            
            if ($hasBookings > 0) {
                // Có đơn đặt vé, không cho phép xóa
                header("Location: trips.php?err=" . urlencode("Không thể xóa: chuyến xe này đã có {$hasBookings} đơn đặt vé."));
                exit;
            }
            
            // Không có đơn đặt vé, có thể xóa
            $deleteStmt = $pdo->prepare("DELETE FROM chuyenxe WHERE id = :id");
            $deleteStmt->execute([':id' => $id]);
            
            if ($deleteStmt->rowCount() > 0) {
                header("Location: trips.php?success=" . urlencode("Đã xóa chuyến xe thành công."));
            } else {
                header("Location: trips.php?err=" . urlencode("Không tìm thấy chuyến xe để xóa."));
            }
            
        } catch (Exception $e) {
            header("Location: trips.php?err=" . urlencode("Lỗi khi xóa: " . $e->getMessage()));
        }
    } else {
        header("Location: trips.php?err=" . urlencode("ID chuyến xe không hợp lệ."));
    }
} else {
    // Không phải POST request
    header("Location: trips.php?err=" . urlencode("Phương thức không được phép."));
}

exit;
?>