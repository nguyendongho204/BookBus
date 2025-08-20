<?php
// libs/xuly_dat_ve.php - stable version (no declare), includes user_id on INSERT

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_chuyenxe.php'; // $pdo (SQLite PDO)

/*
 Flow MOCK:
 - Nhan form -> tao don 'pending' (payment_provider='mock')
 - Chuyen sang payments/mock_pay.php?order_id=...
 - Nguoi dung bam Thanh cong / That bai
 - mock ipn.php cap nhat trang thai + tru ghe (neu thanh cong)
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../timkiemchuyenxe.php');
    exit;
}

// Lay du lieu tu form
$id_chuyen = (int)($_POST['id_chuyen'] ?? 0);
$ho_ten    = trim($_POST['ho_ten'] ?? '');
$sdt       = trim($_POST['sdt'] ?? '');
$email     = trim($_POST['email'] ?? '');
$so_luong  = max(1, (int)($_POST['so_luong'] ?? 1));

if ($id_chuyen <= 0 || $so_luong <= 0 || $ho_ten === '' || $sdt === '') {
    echo "<script>alert('Du lieu khong hop le!');history.back();</script>";
    exit;
}

// Lay chuyen xe de tinh gia
$st = $pdo->prepare("SELECT id, gia_ve FROM chuyenxe WHERE id = ?");
$st->execute([$id_chuyen]);
$cx = $st->fetch(PDO::FETCH_ASSOC);
if (!$cx) {
    echo "<script>alert('Chuyen xe khong ton tai!');location.href='../timkiemchuyenxe.php';</script>";
    exit;
}
$amount = (int)$cx['gia_ve'] * $so_luong;

// Lay user_id neu da dang nhap
$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

// Tao order_id duy nhat
$order_id = 'DV' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(3)));

// Dam bao cot user_id ton tai (tuong thich DB cu)
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

// Tao bang neu chay moi hoan toan
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

// INSERT don co user_id
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
    header('Location: ../payments/mock_pay.php?order_id=' . urlencode($order_id));
    exit;
} else {
    echo "<script>alert('Khong the luu don dat ve!');history.back();</script>";
    exit;
}
