<?php
// libs/xuly_dat_ve.php - stable version (no declare), includes user_id on INSERT AND TRỪ SỐ GHẾ

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_chuyenxe.php'; // $pdo (SQLite PDO)

/*
 Flow MOCK:
 - Nhận form -> tạo đơn 'pending' (payment_provider='mock')
 - Chuyển sang payments/mock_pay.php?order_id=...
 - Người dùng bấm Thành công / Thất bại
 - mock ipn.php cập nhật trạng thái + trừ ghế (nếu thành công)
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../timkiemchuyenxe.php');
    exit;
}

// Lấy dữ liệu từ form
$id_chuyen = (int)($_POST['id_chuyen'] ?? 0);
$ho_ten    = trim($_POST['ho_ten'] ?? '');
$sdt       = trim($_POST['sdt'] ?? '');
$email     = trim($_POST['email'] ?? '');
$so_luong  = max(1, (int)($_POST['so_luong'] ?? 1));

if ($id_chuyen <= 0 || $so_luong <= 0 || $ho_ten === '' || $sdt === '') {
    echo "<script>alert('Dữ liệu không hợp lệ!');history.back();</script>";
    exit;
}

// Lấy chuyến xe để tính giá và kiểm tra số ghế còn
$st = $pdo->prepare("SELECT id, gia_ve, so_ghe_con FROM chuyenxe WHERE id = ?");
$st->execute([$id_chuyen]);
$cx = $st->fetch(PDO::FETCH_ASSOC);
if (!$cx) {
    echo "<script>alert('Chuyến xe không tồn tại!');location.href='../timkiemchuyenxe.php';</script>";
    exit;
}
$so_ghe_con = (int)$cx['so_ghe_con'];
if ($so_luong > $so_ghe_con) {
    echo "<script>alert('Không đủ số ghế!');history.back();</script>";
    exit;
}
$amount = (int)$cx['gia_ve'] * $so_luong;

// Lấy user_id nếu đã đăng nhập
$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

// Tạo order_id duy nhất
$order_id = 'DV' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(3)));

// Đảm bảo cột user_id tồn tại (tương thích DB cũ)
try {
    $cols = $pdo->query("PRAGMA table_info(dat_ve)")->fetchAll(PDO::FETCH_ASSOC);
    $hasUserId = false;
    foreach ($cols as $c) {
        if (strcasecmp($c['name'] ?? '', 'user_id') === 0) { $hasUserId = true; break; }
    }
    if (!$hasUserId) {
        $pdo->exec("ALTER TABLE dat_ve ADD COLUMN user_id INTEGER NULL");
    }
} catch (Throwable $e) {
    // ignore migration error
}

// Tạo bảng nếu chạy mới hoàn toàn
$pdo->exec("CREATE TABLE IF NOT EXISTS dat_ve (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_chuyen INTEGER NOT NULL,
    ho_ten TEXT NOT NULL,
    sdt TEXT NOT NULL,
    email TEXT NOT NULL,
    so_luong INTEGER NOT NULL,
    ngay_dat TEXT NOT NULL,
    payment_provider TEXT,
    payment_status TEXT,
    amount INTEGER,
    order_id TEXT,
    user_id INTEGER
)");

// INSERT đơn có user_id
$sql = "INSERT INTO dat_ve (
            id_chuyen, ho_ten, sdt, email, so_luong, ngay_dat,
            payment_provider, payment_status, amount, order_id, user_id
        ) VALUES (
            :id_chuyen, :ho_ten, :sdt, :email, :so_luong, datetime('now'),
            'mock', 'pending', :amount, :order_id, :user_id
        )";
$stmt = $pdo->prepare($sql);

$ok = $stmt->execute([
    ':id_chuyen' => $id_chuyen,
    ':ho_ten'    => $ho_ten,
    ':sdt'       => $sdt,
    ':email'     => $email,
    ':so_luong'  => $so_luong,
    ':amount'    => $amount,
    ':order_id'  => $order_id,
    ':user_id'   => $user_id
]);

if ($ok) {
    // TRỪ SỐ GHẾ NGAY khi đặt đơn (nếu muốn chờ thanh toán thì chuyển sang mock_pay.php xử lý sau)
    $stmt2 = $pdo->prepare('UPDATE chuyenxe SET so_ghe_con = so_ghe_con - ? WHERE id = ?');
    $stmt2->execute([$so_luong, $id_chuyen]);

    header('Location: ../payments/mock_pay.php?order_id=' . urlencode($order_id));
    exit;
} else {
    echo "<script>alert('Không thể lưu đơn đặt vé!');history.back();</script>";
    exit;
}