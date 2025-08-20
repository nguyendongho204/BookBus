<?php
// libs/xl_dangnhap.php (FINAL FIX)
declare(strict_types=1);

// Define APP_BASE if not exists - QUAN TRỌNG!
if (!defined('APP_BASE')) {
    define('APP_BASE', '/src');
}

// Start session first
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Debug log
error_log("LOGIN DEBUG - Start processing");
error_log("LOGIN DEBUG - Session ID: " . session_id());
error_log("LOGIN DEBUG - Session before: " . print_r($_SESSION, true));

// Include session bootstrap AFTER defining APP_BASE
require_once __DIR__ . '/../includes/session_bootstrap.php';

// Nạp file kết nối DB gốc
if (is_file(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

$mode = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $mode = 'pdo';
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} elseif (isset($conn) && $conn instanceof mysqli) {
    $mode = 'mysqli';
    if (method_exists($conn, 'set_charset')) $conn->set_charset('utf8mb4');
} elseif (isset($db) && $db instanceof SQLite3) {
    $mode = 'sqlite3';
} else {
    http_response_code(500);
    die('Không tìm thấy kết nối CSDL. Hãy đảm bảo libs/db.php khởi tạo $pdo/$conn/$db.');
}

// Trang chủ - SIMPLE PATH
$home = '/src/trangchu.php';

// Chỉ chấp nhận POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $home . '?show=login');
    exit;
}

// Input
$identity = trim((string)($_POST['identity'] ?? $_POST['username'] ?? $_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

error_log("LOGIN DEBUG - Identity: " . $identity);

if ($identity === '' || $password === '') {
    header('Location: ' . $home . '?show=login&login_err=blank');
    exit;
}

// ---- Helpers kiểm tra bảng ----
function table_exists_pdo(PDO $pdo, string $name): bool {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
        $stmt->execute([$name]);
        return (bool)$stmt->fetchColumn();
    } else {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_name = ? LIMIT 1");
        try { $stmt->execute([$name]); } catch (Throwable $e) { return false; }
        return (bool)$stmt->fetchColumn();
    }
}
function table_exists_mysqli(mysqli $conn, string $name): bool {
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_name = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $name);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $ok = $res && $res->fetch_row();
            $stmt->close();
            return (bool)$ok;
        }
        $stmt->close();
    }
    return false;
}
function table_exists_sqlite3(SQLite3 $db, string $name): bool {
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :name");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res ? $res->fetchArray(SQLITE3_NUM) : false;
    return (bool)$row;
}

// Xác định bảng
$useTable = null;
if ($mode === 'pdo') {
    if (table_exists_pdo($pdo, 'quan_tri_vien'))      $useTable = 'quan_tri_vien';
    elseif (table_exists_pdo($pdo, 'daily_dangky'))   $useTable = 'daily_dangky';
} elseif ($mode === 'mysqli') {
    if (table_exists_mysqli($conn, 'quan_tri_vien'))    $useTable = 'quan_tri_vien';
    elseif (table_exists_mysqli($conn, 'daily_dangky')) $useTable = 'daily_dangky';
} else {
    if (table_exists_sqlite3($db, 'quan_tri_vien'))     $useTable = 'quan_tri_vien';
    elseif (table_exists_sqlite3($db, 'daily_dangky'))  $useTable = 'daily_dangky';
}

if (!$useTable) {
    http_response_code(500);
    die('Không tìm thấy bảng người dùng (quan_tri_vien hoặc daily_dangky).');
}

error_log("LOGIN DEBUG - Using table: " . $useTable);

// ---- Lấy user theo bảng ----
$user = null;

if ($useTable === 'quan_tri_vien') {
    if ($mode === 'pdo') {
        $st = $pdo->prepare('SELECT id, ten_dang_nhap, email, mat_khau, vai_tro FROM quan_tri_vien WHERE ten_dang_nhap = ? LIMIT 1');
        $st->execute([$identity]);
        if ($row = $st->fetch()) {
            $user = [
                'id'       => $row['id'],
                'username' => $row['ten_dang_nhap'],
                'email'    => $row['email'] ?? null,
                'password' => $row['mat_khau'],
                'role'     => $row['vai_tro'] ?? null,
            ];
        }
    } elseif ($mode === 'mysqli') {
        $sql = 'SELECT id, ten_dang_nhap, email, mat_khau, vai_tro FROM quan_tri_vien WHERE ten_dang_nhap = ? LIMIT 1';
        $st = $conn->prepare($sql);
        if (!$st) { http_response_code(500); die('SQL error: '.$conn->error); }
        $st->bind_param('s', $identity);
        $st->execute();
        $res = $st->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $user = [
                'id'       => $row['id'],
                'username' => $row['ten_dang_nhap'],
                'email'    => $row['email'] ?? null,
                'password' => $row['mat_khau'],
                'role'     => $row['vai_tro'] ?? null,
            ];
        }
        $st->close();
    } else {
        $st = $db->prepare('SELECT id, ten_dang_nhap, email, mat_khau, vai_tro FROM quan_tri_vien WHERE ten_dang_nhap = :u LIMIT 1');
        $st->bindValue(':u', $identity, SQLITE3_TEXT);
        $res = $st->execute();
        if ($res && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $user = [
                'id'       => $row['id'],
                'username' => $row['ten_dang_nhap'],
                'email'    => $row['email'] ?? null,
                'password' => $row['mat_khau'],
                'role'     => $row['vai_tro'] ?? null,
            ];
        }
    }
} else {
    if ($mode === 'pdo') {
        $st = $pdo->prepare('SELECT id, name, email, phone, password, role, status, deleted_at FROM daily_dangky WHERE email = ? OR phone = ? OR name = ? LIMIT 1');
        $st->execute([$identity, $identity, $identity]);
        if ($row = $st->fetch()) {
            $user = [
                'id'        => $row['id'],
                'username'  => $row['name'],
                'email'     => $row['email'] ?? null,
                'password'  => $row['password'],
                'role'      => isset($row['role']) ? (int)$row['role'] : 0,
                'status'    => isset($row['status']) ? (int)$row['status'] : 1,
                'deleted_at' => $row['deleted_at'] ?? null,
            ];
        }
    } elseif ($mode === 'mysqli') {
        $sql = 'SELECT id, name, email, phone, password, role, status, deleted_at FROM daily_dangky WHERE email = ? OR phone = ? OR name = ? LIMIT 1';
        $st = $conn->prepare($sql);
        if (!$st) { http_response_code(500); die('SQL error: '.$conn->error); }
        $st->bind_param('sss', $identity, $identity, $identity);
        $st->execute();
        $res = $st->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $user = [
                'id'        => $row['id'],
                'username'  => $row['name'],
                'email'     => $row['email'] ?? null,
                'password'  => $row['password'],
                'role'      => isset($row['role']) ? (int)$row['role'] : 0,
                'status'    => isset($row['status']) ? (int)$row['status'] : 1,
                'deleted_at' => $row['deleted_at'] ?? null,
            ];
        }
        $st->close();
    } else {
        $st = $db->prepare('SELECT id, name, email, phone, password, role, status, deleted_at FROM daily_dangky WHERE email = :i OR phone = :i OR name = :i LIMIT 1');
        $st->bindValue(':i', $identity, SQLITE3_TEXT);
        $res = $st->execute();
        if ($res && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $user = [
                'id'        => $row['id'],
                'username'  => $row['name'],
                'email'     => $row['email'] ?? null,
                'password'  => $row['password'],
                'role'      => isset($row['role']) ? (int)$row['role'] : 0,
                'status'    => isset($row['status']) ? (int)$row['status'] : 1,
                'deleted_at' => $row['deleted_at'] ?? null,
            ];
        }
    }
}

error_log("LOGIN DEBUG - User found: " . ($user ? 'YES' : 'NO'));
if ($user) {
    error_log("LOGIN DEBUG - User data: " . print_r($user, true));
}

if (!$user) {
    header('Location: ' . $home . '?show=login&login_err=nouser');
    exit;
}

// Kiểm tra mật khẩu
$hash = (string)($user['password'] ?? '');
$ok = false;
if ($hash !== '') {
    $info = password_get_info($hash);
    if (!empty($info['algo'])) {
        $ok = password_verify($password, $hash);
    } else {
        $ok = hash_equals($hash, $password);
    }
}

error_log("LOGIN DEBUG - Password check: " . ($ok ? 'PASS' : 'FAIL'));

if (!$ok) {
    header('Location: ' . $home . '?show=login&login_err=wrongpass');
    exit;
}

// ===== THÊM PHẦN KIỂM TRA TRẠNG THÁI TÀI KHOẢN =====
if ($useTable === 'daily_dangky') {
    // Kiểm tra tài khoản đã bị xóa chưa
    if (isset($user['deleted_at']) && $user['deleted_at'] !== null) {
        error_log("LOGIN DEBUG - Account is deleted. User ID: " . $user['id']);
        header('Location: ' . $home . '?show=login&login_err=account_deleted');
        exit;
    }
    
    // Kiểm tra trạng thái tài khoản (status = 0 là đã bị khóa)
    if (isset($user['status']) && (int)$user['status'] === 0) {
        error_log("LOGIN DEBUG - Account is locked. User ID: " . $user['id']);
        
        // Lưu thông tin tài khoản bị khóa vào session để hiển thị chi tiết trong modal
        if (!empty($user['username']) || !empty($user['email'])) {
            $_SESSION['locked_account_info'] = [
                'name' => $user['username'],
                'email' => $user['email']
            ];
        }
        
        // Set session cho login_error
        $_SESSION['login_error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
        
        // Chuyển hướng về trang đăng nhập với thông báo lỗi
        header('Location: ' . $home . '?show=login&login_err=account_locked');
        exit;
    }
}
// ===== KẾT THÚC PHẦN KIỂM TRA =====

// Chuẩn hoá role
$sessionRole = null;
if ($useTable === 'quan_tri_vien') {
    $sessionRole = isset($user['role']) ? (int)$user['role'] : 1; // 0=Admin,1=Nhân sự
} else {
    $sessionRole = ((int)($user['role'] ?? 0) === 1) ? 0 : 1; // daily_dangky: 1=admin,0=user
}

// Tạo session - QUAN TRỌNG!
session_regenerate_id(true);
$_SESSION['user'] = [
    'id'       => (int)$user['id'],
    'username' => (string)$user['username'],
    'email'    => $user['email'] ?? null,
    'role'     => $sessionRole,
    'status'   => isset($user['status']) ? (int)$user['status'] : 1, // Thêm trạng thái vào session
];

// Debug session sau khi set
error_log("LOGIN DEBUG - Session after set: " . print_r($_SESSION, true));

// Set cookie để backup (optional)
if (!empty($user['email'])) {
    setcookie('user_email', $user['email'], time() + (86400 * 30), '/'); // 30 days
}

// ---- Điều hướng sau đăng nhập ----
// Redirect về trang chủ với thông báo thành công
header('Location: /src/trangchu.php?login_ok=1');
exit();
?>