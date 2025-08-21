<?php 
require_once __DIR__ . '/header.php'; // nạp header + session + css/js

// === Tạo CSRF token ===
if (empty($_SESSION['csrf_contact'])) {
    $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_contact'];

// Biến xử lý form
$errors = [];
$success = false;
$posted = ['fullname'=>'','email'=>'','phone'=>'','message'=>'','csrf'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted['fullname'] = trim($_POST['fullname'] ?? '');
    $posted['email']    = trim($_POST['email'] ?? '');
    $posted['phone']    = trim($_POST['phone'] ?? '');
    $posted['message']  = trim($_POST['message'] ?? '');
    $posted['csrf']     = $_POST['csrf'] ?? '';

    // Kiểm tra CSRF
    if (!hash_equals($_SESSION['csrf_contact'] ?? '', $posted['csrf'])) {
        $errors[] = 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.';
    }

    // Kiểm tra dữ liệu
    if ($posted['fullname'] === '') $errors[] = 'Vui lòng nhập Họ và tên.';
    if ($posted['phone'] === '') {
        $errors[] = 'Vui lòng nhập Số điện thoại.';
    } elseif (!preg_match('/^(0|\+?84)([0-9]{8,11})$/', preg_replace('/\s+/', '', $posted['phone']))) {
        $errors[] = 'Số điện thoại không đúng định dạng.';
    }
    if ($posted['email'] !== '' && !filter_var($posted['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if ($posted['message'] === '') $errors[] = 'Vui lòng nhập Nội dung liên hệ.';

    // Nếu không có lỗi → lưu
    if (!$errors) {
        $storageDir = __DIR__ . '/storage';
        if (!is_dir($storageDir)) { @mkdir($storageDir, 0775, true); }
        $file = $storageDir . '/lien_he.csv';

        $row = [
            date('Y-m-d H:i:s'),
            $posted['fullname'],
            $posted['phone'],
            $posted['email'],
            str_replace(["\r","\n"], [' ',' '], $posted['message']),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        $fp = fopen($file, file_exists($file) ? 'a' : 'w');
        if (filesize($file) === 0) {
            fputcsv($fp, ['created_at','fullname','phone','email','message','ip','user_agent']);
        }
        fputcsv($fp, $row);
        fclose($fp);

        $success = true;
        // reset form
        $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
        $csrfToken = $_SESSION['csrf_contact'];
        $posted = ['fullname'=>'','email'=>'','phone'=>'','message'=>'','csrf'=>''];
    }
}
?>

<style>
.contact-form-wrapper {max-width:900px; margin:30px auto;}
.contact-form-card {background:#fff; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,.08); padding:24px;}
.contact-form-card h2 {font-size:22px; margin-bottom:16px}
.contact-form-card .form-label {font-weight:600}
.contact-form-card .btn-primary {background:#e91e63; border:none; border-radius:999px; padding:.6rem 1.4rem}
.contact-form-card .required {color:#e91e63}
.alert-success, .alert-danger {border-radius:12px}
</style>

<section class="dv" id="dv">
  <div class="container contact-form-wrapper">
    <div class="contact-form-card">
      <h2 class="text-center">Liên hệ BookBus</h2>
      <p class="text-muted text-center">Hãy để lại thông tin, chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>

      <?php if ($success): ?>
        <div class="alert alert-success">
          ✅ Gửi liên hệ thành công! Chúng tôi đã ghi nhận yêu cầu của bạn.
        </div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <b>Vui lòng kiểm tra lại:</b>
          <ul style="margin:8px 0 0 18px">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="lienhe.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Họ và tên <span class="required">*</span></label>
            <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" 
                   value="<?= htmlspecialchars($posted['fullname']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Số điện thoại <span class="required">*</span></label>
            <input type="text" name="phone" class="form-control" placeholder="0xxxxxxxxx" 
                   value="<?= htmlspecialchars($posted['phone']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email (không bắt buộc)</label>
            <input type="email" name="email" class="form-control" placeholder="email@domain.com" 
                   value="<?= htmlspecialchars($posted['email']) ?>">
          </div>
          <div class="col-md-12">
            <label class="form-label">Nội dung <span class="required">*</span></label>
            <textarea name="message" class="form-control" rows="5" 
                      placeholder="Bạn cần hỗ trợ điều gì?"><?= htmlspecialchars($posted['message']) ?></textarea>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="trangchu.php" class="btn btn-outline-secondary">← Về trang chủ</a>
          <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/footer.php'; ?>