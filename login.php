<?php
session_start();

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['user'])) {
    // Kiểm tra trạng thái tài khoản trước khi redirect
    require_once "libs/db.php";
    require_once "libs/check_account_status.php";
    $account_status = checkAccountStatus();
    
    // Nếu tài khoản hoạt động bình thường thì mới redirect
    if ($account_status === 'active') {
        if ((int)$_SESSION['user']['role'] === 1) {
            header('Location: src/admin/index.php');
        } else {
            header('Location: src/index.php');
        }
        exit;
    }
    // Nếu tài khoản bị khóa hoặc xóa, không redirect mà tiếp tục hiển thị trang login
}

// Lấy thông báo lỗi
$login_error = $_SESSION['login_error'] ?? '';
$account_locked = $_SESSION['account_locked'] ?? null;
$show_locked_modal = isset($_GET['locked']) && $_GET['locked'] == 1 && $account_locked;

// Xử lý các tham số URL
$error_param = $_GET['error'] ?? '';
if ($error_param === 'account_locked') {
    $login_error = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
    $show_locked_modal = true;
} elseif ($error_param === 'account_deleted') {
    $login_error = 'Tài khoản không tồn tại hoặc đã bị xóa.';
}

// Xóa session errors sau khi đã lấy
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - BookBus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo h1 {
            color: #ff5722;
            font-weight: 800;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .brand-logo p {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }

        .btn-login {
            background: #ff5722;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #e64a19;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
        }

        /* Modal tài khoản bị khóa */
        .locked-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        }

        .locked-modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .locked-modal {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideInUp 0.4s ease-out;
            overflow: hidden;
            position: relative;
        }

        .modal-header-locked {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .modal-header-locked::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(30px);
        }

        .locked-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
            position: relative;
            z-index: 2;
        }

        .modal-title-locked {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            position: relative;
            z-index: 2;
        }

        .modal-subtitle-locked {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .modal-body-locked {
            padding: 24px;
        }

        .user-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #6c757d;
            font-weight: 500;
        }

        .info-value {
            color: #212529;
            font-weight: 600;
        }

        .contact-info {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: center;
        }

        .contact-info .text-primary {
            color: #1976d2 !important;
        }

        .modal-btn-locked {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
        }

        .modal-btn-locked:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        /* Mobile responsive */
        @media (max-width: 480px) {
            .locked-modal {
                margin: 20px;
                width: calc(100% - 40px);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand-logo">
                <h1><i class="fas fa-bus"></i> BookBus</h1>
                <p>Đăng nhập để tiếp tục</p>
            </div>

            <?php if ($login_error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($login_error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login_process.php">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Đăng nhập
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="src/tai-khoan/index.php" class="text-decoration-none">
                    Chưa có tài khoản? Đăng ký ngay
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Tài khoản bị khóa -->
    <?php 
    // Hiển thị modal nếu tài khoản bị khóa hoặc tham số URL yêu cầu
    $show_modal = $show_locked_modal || 
                 (isset($_GET['locked']) && $_GET['locked'] == 1) || 
                 (isset($_GET['error']) && $_GET['error'] == 'account_locked');
                 
    // Lấy thông tin tài khoản bị khóa từ session
    $account_info = $_SESSION['account_locked'] ?? $_SESSION['locked_account_info'] ?? null;
    
    if ($show_modal && $account_info): 
    ?>
    <div class="locked-modal-overlay" id="lockedModal">
        <div class="locked-modal">
            <div class="modal-header-locked">
                <div class="locked-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="modal-title-locked">Tài khoản bị khóa</div>
                <div class="modal-subtitle-locked">Tài khoản của bạn đang bị tạm khóa</div>
            </div>
            
            <div class="modal-body-locked">
                <div class="user-info">
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-user"></i> Tên tài khoản
                        </span>
                        <span class="info-value"><?= htmlspecialchars($account_info['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-envelope"></i> Email
                        </span>
                        <span class="info-value"><?= htmlspecialchars($account_info['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-clock"></i> Thời gian
                        </span>
                        <span class="info-value"><?= date('H:i - d/m/Y') ?></span>
                    </div>
                </div>

                <div class="contact-info">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Cần hỗ trợ?
                    </h6>
                    <p class="mb-2 small">
                        Tài khoản của bạn đã bị khóa bởi quản trị viên. 
                        Vui lòng liên hệ để được hỗ trợ mở khóa.
                    </p>
                    <div class="small">
                        <strong>Email:</strong> support@bookbus.com<br>
                        <strong>Hotline:</strong> 1900-1234
                    </div>
                </div>

                <button class="modal-btn-locked" onclick="closeLockedModal()">
                    <i class="fas fa-times me-2"></i>
                    Đóng
                </button>
            </div>
        </div>
    </div>
    <?php 
    // Xóa session sau khi hiển thị
    unset($_SESSION['show_locked_modal'], $_SESSION['account_locked'], $_SESSION['locked_account_info']);
    endif; 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Show locked modal if needed
    <?php if ($show_modal): ?>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const modal = document.getElementById('lockedModal');
            if (modal) {
                modal.classList.add('show');
                
                // Log để debug
                console.log('🔒 Showing locked account modal');
            }
        }, 100);
    });
    <?php endif; ?>

    function closeLockedModal() {
        const modal = document.getElementById('lockedModal');
        if (modal) {
            modal.classList.remove('show');
            
            // Clean URL
            if (window.history.replaceState) {
                window.history.replaceState(null, null, 'login.php');
            }
        }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('locked-modal-overlay')) {
            closeLockedModal();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('lockedModal');
        if (modal && modal.classList.contains('show')) {
            if (e.key === 'Escape') {
                closeLockedModal();
            }
        }
    });
    </script>
</body>
</html>