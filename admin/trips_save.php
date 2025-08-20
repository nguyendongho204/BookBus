<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
require_once __DIR__.'/../libs/db_chuyenxe.php'; // $pdo
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$ALLOWED_LOCATIONS = [
  "Cần Thơ","Phong Điền","Ô Môn","Vĩnh Long","Kinh Cùng","Cái Tắc",
  "Thốt Nốt","Cờ Đỏ","Vĩnh Thạnh","Bình Tân"
];

$id = (int)($_POST['id'] ?? 0);
$ten_nhaxe = trim($_POST['ten_nhaxe'] ?? '');
$loai_xe   = trim($_POST['loai_xe'] ?? '');
$diem_di   = trim($_POST['diem_di'] ?? '');
$diem_den  = trim($_POST['diem_den'] ?? '');
$ngay_di   = trim($_POST['ngay_di'] ?? '');
$gio_di    = trim($_POST['gio_di'] ?? '');
$gio_den   = trim($_POST['gio_den'] ?? '');
$gia_ve    = (int)($_POST['gia_ve'] ?? 0);
$so_ghe    = (int)($_POST['so_ghe'] ?? 0);
$so_ghe_con= (int)($_POST['so_ghe_con'] ?? 0);

$_SESSION['trip_form'] = [
  'ten_nhaxe'=>$ten_nhaxe,'loai_xe'=>$loai_xe,'diem_di'=>$diem_di,'diem_den'=>$diem_den,
  'ngay_di'=>$ngay_di,'gio_di'=>$gio_di,'gio_den'=>$gio_den,'gia_ve'=>$gia_ve,
  'so_ghe'=>$so_ghe,'so_ghe_con'=>$so_ghe_con,
];

function back_with_error($msg, $id){ 
    header("Location: trips_edit.php?".($id>0?("id=".$id."&"):"")."err=".urlencode($msg)); 
    exit; 
}

// Validation
if ($ten_nhaxe==='' || $loai_xe==='' || $diem_di==='' || $diem_den==='') {
    back_with_error("Vui lòng điền đầy đủ thông tin.", $id);
}

if ($diem_di === $diem_den) {
    back_with_error("Điểm đến phải khác điểm đi.", $id);
}

// Kiểm tra điểm đi/đến có trong danh sách
if (!in_array($diem_di,$ALLOWED_LOCATIONS,true)) {
    back_with_error("Điểm đi không hợp lệ, vui lòng chọn từ danh sách.", $id);
}
if (!in_array($diem_den,$ALLOWED_LOCATIONS,true)) {
    back_with_error("Điểm đến không hợp lệ, vui lòng chọn từ danh sách.", $id);
}

if ($gia_ve < 0) {
    back_with_error("Giá vé không hợp lệ.", $id);
}
if ($so_ghe < 1) {
    back_with_error("Số ghế phải ≥ 1.", $id);
}
if ($so_ghe_con < 0 || $so_ghe_con > $so_ghe) {
    back_with_error("Số ghế còn phải từ 0 đến ".$so_ghe.".", $id);
}

$today = new DateTime('today', new DateTimeZone('Asia/Ho_Chi_Minh'));
$now   = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

if (!$ngay_di || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngay_di)) {
    back_with_error("Ngày đi không hợp lệ.", $id);
}
if (!$gio_di  || !preg_match('/^\d{2}:\d{2}$/', $gio_di)) {
    back_with_error("Giờ đi không hợp lệ.", $id);
}
if (!$gio_den || !preg_match('/^\d{2}:\d{2}$/', $gio_den)) {
    back_with_error("Giờ đến không hợp lệ.", $id);
}

$dep = DateTime::createFromFormat('Y-m-d H:i', $ngay_di.' '.$gio_di, new DateTimeZone('Asia/Ho_Chi_Minh'));
$arr = DateTime::createFromFormat('Y-m-d H:i', $ngay_di.' '.$gio_den, new DateTimeZone('Asia/Ho_Chi_Minh'));

if (!$dep || !$arr) {
    back_with_error("Thời gian không hợp lệ.", $id);
}
if ($dep < $today) {
    back_with_error("Ngày đi không được ở quá khứ.", $id);
}
if ($dep->format('Y-m-d') === $now->format('Y-m-d') && $dep < $now) {
    back_with_error("Giờ đi không được nhỏ hơn hiện tại.", $id);
}
if ($arr <= $dep) {
    back_with_error("Giờ đến phải sau giờ đi.", $id);
}

// Lưu vào database
try {
    if ($id > 0) {
        // Update existing trip
        $sql = "UPDATE chuyenxe SET 
                ten_nhaxe=:ten, loai_xe=:loai, diem_di=:di, diem_den=:den, 
                ngay_di=:ngay, gio_di=:gdi, gio_den=:gden, gia_ve=:gia, 
                so_ghe=:sg, so_ghe_con=:sgc 
                WHERE id=:id";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':ten'=>$ten_nhaxe, ':loai'=>$loai_xe, ':di'=>$diem_di, ':den'=>$diem_den,
            ':ngay'=>$ngay_di, ':gdi'=>$gio_di, ':gden'=>$gio_den, ':gia'=>$gia_ve,
            ':sg'=>$so_ghe, ':sgc'=>$so_ghe_con, ':id'=>$id
        ]);
        
        // Xóa form data và redirect với success message
        unset($_SESSION['trip_form']);
        header("Location: trips_edit.php?id={$id}&success=update");
        exit;
        
    } else {
        // Insert new trip
        $sql = "INSERT INTO chuyenxe(ten_nhaxe,loai_xe,diem_di,diem_den,ngay_di,gio_di,gio_den,gia_ve,so_ghe,so_ghe_con) 
                VALUES(:ten,:loai,:di,:den,:ngay,:gdi,:gden,:gia,:sg,:sgc)";
        $st = $pdo->prepare($sql);
        $st->execute([
            ':ten'=>$ten_nhaxe, ':loai'=>$loai_xe, ':di'=>$diem_di, ':den'=>$diem_den,
            ':ngay'=>$ngay_di, ':gdi'=>$gio_di, ':gden'=>$gio_den, ':gia'=>$gia_ve,
            ':sg'=>$so_ghe, ':sgc'=>$so_ghe_con
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // Xóa form data và redirect với success message
        unset($_SESSION['trip_form']);
        header("Location: trips_edit.php?success=create&trip_id={$newId}&route=" . urlencode($diem_di . ' → ' . $diem_den));
        exit;
    }
    
} catch (Exception $e) {
    back_with_error("Lỗi lưu dữ liệu: " . $e->getMessage(), $id);
}
?>