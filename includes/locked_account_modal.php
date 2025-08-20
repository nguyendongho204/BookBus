<?php
// Chỉ hiển thị modal nếu có thông báo lỗi về tài khoản bị khóa
// và có thông tin về tài khoản bị khóa
$show_locked_modal = false;

// Kiểm tra từ session
if (isset($_SESSION['login_error']) && 
    strpos($_SESSION['login_error'], 'bị khóa') !== false && 
    isset($_SESSION['locked_account_info'])) {
    $show_locked_modal = true;
}

// Kiểm tra từ URL parameter
if (isset($_GET['login_err']) && $_GET['login_err'] === 'account_locked') {
    $show_locked_modal = true;
}

// Thông tin tài khoản bị khóa
$locked_name = $_SESSION['locked_account_info']['name'] ?? 'User';
$locked_email = $_SESSION['locked_account_info']['email'] ?? '';

// Xóa session để không hiển thị modal nhiều lần
if ($show_locked_modal) {
    // Giữ lại thông tin tài khoản bị khóa chỉ một lần hiển thị
    unset($_SESSION['locked_account_info']);
}
?>

<?php if ($show_locked_modal): ?>
<!-- Modal Tài khoản bị khóa -->
<div class="modal fade" id="lockedAccountModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="lockedAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="lockedAccountModalLabel">
                    <i class="fas fa-lock me-2"></i>Tài khoản bị khóa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-container mb-3">
                        <i class="fas fa-user-lock fa-4x text-danger"></i>
                    </div>
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
            </div>
            <div class="modal-footer">
                <a href="mailto:admin@bookbus.com" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-2"></i>Liên hệ admin
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hiển thị modal khi trang tải xong
    var lockedModal = new bootstrap.Modal(document.getElementById('lockedAccountModal'));
    lockedModal.show();
});
</script>
<?php endif; ?>