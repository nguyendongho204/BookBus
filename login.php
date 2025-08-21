<?php
session_start();
$errorMsg = '';
$showLockedModal = false;
$locked_name = $_SESSION['locked_account_info']['name'] ?? 'User';
$locked_email = $_SESSION['locked_account_info']['email'] ?? '';

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: trangchu.php');
    exit;
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'account_locked':
            $errorMsg = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ mở khóa.';
            $showLockedModal = true;
            break;
        case 'wrongpass':
            $errorMsg = 'Email hoặc mật khẩu không đúng.';
            break;
        case 'nouser':
            $errorMsg = 'Không tìm thấy tài khoản. Vui lòng kiểm tra lại.';
            break;
        case 'blank':
            $errorMsg = 'Vui lòng nhập đầy đủ thông tin.';
            break;
        case 'account_deleted':
            $errorMsg = 'Tài khoản đã bị xoá. Vui lòng liên hệ quản trị viên.';
            break;
        default:
            $errorMsg = 'Đăng nhập thất bại. Vui lòng thử lại.';
            break;
    }
}

if ($showLockedModal && isset($_SESSION['locked_account_info'])) {
    unset($_SESSION['locked_account_info']);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập BookBus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            min-height: 100vh;
        }
        .login-center {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .login-box {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,123,94,.15), 0 1.5px 12px rgba(0,0,0,.04);
            padding: 48px 40px 32px 40px;
            max-width: 390px;
            width: 100%;
            position: relative;
            animation: fadein .6s;
        }
        @keyframes fadein {
            from { opacity: 0; transform: scale(.95);}
            to { opacity: 1; transform: scale(1);}
        }
        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }
        .login-logo i {
            font-size: 56px;
            color: #00c6a7;
            background: #e9f7f5;
            border-radius: 50%;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,123,94,.09);
        }
        .login-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.65rem;
            color: #007b5e;
            margin-bottom: 7px;
            letter-spacing: .5px;
        }
        .login-desc {
            text-align: center;
            color: #555;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .alert-custom {
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 17px;
            padding: 9px 14px;
        }
        .form-label {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
            color: #222;
        }
        .form-control {
            border-radius: 8px;
            font-size: 16px;
            background: #f5f7fa;
            border: 1.5px solid #e9ecef;
            transition: border-color .18s;
        }
        .form-control:focus {
            border-color: #00c6a7;
            box-shadow: 0 0 0 2px rgba(0,198,167,.14);
        }
        .btn-bookbus {
            background-image: linear-gradient(90deg,#007bff 40%,#00c6a7 100%);
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            font-size: 17px;
            box-shadow: 0 2px 8px rgba(0,123,94,0.09);
            transition: box-shadow .2s, background-image .2s;
            border: none;
        }
        .btn-bookbus:hover {
            box-shadow: 0 4px 20px rgba(0,123,94,.19);
            background-image: linear-gradient(90deg,#00c6a7 40%,#007bff 100%);
        }
        .btn-home {
            background: #fff;
            border: 1.5px solid #00c6a7;
            color: #00c6a7;
            font-weight: 500;
            border-radius: 8px;
            font-size: 15px;
            margin-right: 8px;
            transition: background .2s, color .2s;
        }
        .btn-home:hover {
            background: #00c6a7;
            color: #fff;
        }
        .text-link {
            color: #00c6a7;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
        }
        .text-link:hover {
            text-decoration: underline;
            color: #007bff;
        }
        .footer-link {
            margin-top: 16px;
            text-align: center;
        }
        .modal-content {
            border-radius: 18px;
        }
        .modal-header.bg-danger {
            background: linear-gradient(90deg,#f44336 60%,#e57373 100%);
        }
        .contact-form label {
            font-weight: 500;
        }
        .contact-form .form-control {
            font-size: 15px;
            border-radius: 7px;
        }
        .contact-form .btn {
            border-radius: 7px;
        }
    </style>
</head>
<body>
<div class="login-center">
    <div class="login-box">
        <div class="login-logo">
            <i class="fa fa-bus"></i>
        </div>
        <div class="login-title">Đăng nhập <span style="color:#00c6a7;">BookBus</span></div>
        <div class="login-desc">Đăng nhập để tiếp tục sử dụng dịch vụ</div>
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="libs/xl_dangnhap.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email hoặc SĐT</label>
                <input type="text" class="form-control" id="email" name="identity" placeholder="Nhập email hoặc số điện thoại" autocomplete="username" required>
            </div>
            <div class="mb-3">
                <label for="matkhau" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="matkhau" name="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
            </div>
            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-bookbus">Đăng nhập</button>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-home" onclick="window.location.href='trangchu.php'">
                    <i class="fa fa-home"></i> Về trang chủ
                </button>
                <span class="text-link" id="showContactBtn"><i class="fa fa-envelope"></i> Liên hệ quản trị viên</span>
            </div>
        </form>
    </div>
</div>

<!-- Modal liên hệ quản trị viên -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content contact-form">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="contactModalLabel"><i class="fa fa-envelope me-2"></i>Liên hệ quản trị viên</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <form id="contactForm">
        <div class="modal-body">
          <label for="contactName">Tên của bạn</label>
          <input type="text" class="form-control mb-2" id="contactName" name="contactName" placeholder="Nhập tên của bạn" required>
          <label for="contactEmail">Email liên hệ</label>
          <input type="email" class="form-control mb-2" id="contactEmail" name="contactEmail" placeholder="Nhập email" required>
          <label for="contactMessage">Nội dung</label>
          <textarea class="form-control mb-2" id="contactMessage" name="contactMessage" rows="3" placeholder="Bạn cần hỗ trợ gì?" required></textarea>
          <div id="contactAlert" class="alert alert-success d-none"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-bookbus"><i class="fa fa-paper-plane"></i> Gửi liên hệ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($showLockedModal): ?>
<!-- Modal cảnh báo tài khoản bị khóa -->
<div class="modal fade" id="lockedAccountModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="lockedAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="lockedAccountModalLabel">
                    <i class="fa fa-lock me-2"></i> Tài khoản bị khóa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fa fa-user-lock fa-4x text-danger mb-2"></i>
                    <h4 class="fw-bold">Tài khoản của bạn đã bị khóa</h4>
                </div>
                <div class="alert alert-warning">
                    <p class="mb-1"><strong>Tên tài khoản:</strong> <?php echo htmlspecialchars($locked_name); ?></p>
                    <?php if (!empty($locked_email)): ?>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($locked_email); ?></p>
                    <?php endif; ?>
                </div>
                <p>Tài khoản của bạn đã bị khóa vì lý do bảo mật hoặc vi phạm điều khoản sử dụng.</p>
                <p>Vui lòng liên hệ với quản trị viên để biết thêm chi tiết và mở khóa tài khoản.</p>
                <button type="button" class="btn btn-bookbus mt-2" id="modalContactBtn">
                    <i class="fa fa-envelope"></i> Liên hệ quản trị viên
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- Bootstrap JS -->
<script src="js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hiện modal tài khoản bị khóa nếu có
    <?php if ($showLockedModal): ?>
    var lockedModal = new bootstrap.Modal(document.getElementById('lockedAccountModal'));
    lockedModal.show();
    document.getElementById('modalContactBtn').onclick = function() {
        var contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
        contactModal.show();
    };
    <?php endif; ?>

    // Hiện modal liên hệ khi bấm nút liên hệ
    document.getElementById('showContactBtn').onclick = function() {
        var contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
        contactModal.show();
    };

    // Gửi form liên hệ (demo, không gửi thực tế)
    document.getElementById('contactForm').onsubmit = function(e) {
        e.preventDefault();
        var alertBox = document.getElementById('contactAlert');
        alertBox.textContent = "Gửi liên hệ thành công! Quản trị viên sẽ phản hồi qua email.";
        alertBox.classList.remove('d-none');
        setTimeout(function() {
            var contactModal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
            contactModal.hide();
            alertBox.classList.add('d-none');
            document.getElementById('contactForm').reset();
        }, 2000);
    };
});
</script>
</body>
</html>