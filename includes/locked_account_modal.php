<?php
// Component modal thông báo tài khoản bị khóa
$show_modal = isset($_SESSION['show_locked_modal']) && $_SESSION['show_locked_modal'];
$account_info = $_SESSION['locked_account_info'] ?? null;

if ($show_modal && $account_info) {
    // Xóa session sau khi hiển thị
    unset($_SESSION['show_locked_modal']);
    unset($_SESSION['locked_account_info']);
?>
<!-- Modal Tài khoản bị khóa -->
<div class="locked-modal-overlay show" id="lockedModal">
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
                        <i class="fas fa-calendar"></i> Thời gian
                    </span>
                    <span class="info-value"><?= date('H:i:s d/m/Y') ?></span>
                </div>
            </div>
            
            <div class="locked-message">
                <p><i class="fas fa-info-circle"></i> Tài khoản của bạn đã bị khóa bởi quản trị viên.</p>
                <p>Vui lòng liên hệ với bộ phận hỗ trợ để được hỗ trợ mở khóa tài khoản.</p>
                <p><strong>Hotline:</strong> 1900-xxxx</p>
                <p><strong>Email:</strong> support@bookbus.com</p>
            </div>
        </div>
        
        <div class="modal-footer-locked">
            <button class="btn-close-locked" onclick="closeLockedModal()">
                <i class="fas fa-times"></i> Đóng
            </button>
            <button class="btn-logout-locked" onclick="logoutUser()">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </button>
        </div>
    </div>
</div>

<style>
.locked-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.locked-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.locked-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.7) translateY(-50px);
    transition: transform 0.3s ease;
}

.locked-modal-overlay.show .locked-modal {
    transform: scale(1) translateY(0);
}

.modal-header-locked {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 30px;
    text-align: center;
    border-radius: 12px 12px 0 0;
}

.locked-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.9;
}

.modal-title-locked {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 8px;
}

.modal-subtitle-locked {
    font-size: 16px;
    opacity: 0.9;
}

.modal-body-locked {
    padding: 30px;
}

.user-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #495057;
}

.info-value {
    color: #212529;
    font-weight: 500;
}

.locked-message {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    color: #856404;
}

.locked-message p {
    margin-bottom: 10px;
}

.locked-message p:last-child {
    margin-bottom: 0;
}

.modal-footer-locked {
    padding: 20px 30px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn-close-locked, .btn-logout-locked {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}

.btn-close-locked {
    background: #6c757d;
    color: white;
}

.btn-close-locked:hover {
    background: #5a6268;
}

.btn-logout-locked {
    background: #dc3545;
    color: white;
}

.btn-logout-locked:hover {
    background: #c82333;
}
</style>

<script>
function closeLockedModal() {
    const modal = document.getElementById('lockedModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function logoutUser() {
    if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        window.location.href = '/src/tai-khoan/logout.php';
    }
}

// Hiển thị modal khi trang load
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('lockedModal');
    if (modal) {
        setTimeout(() => {
            modal.classList.add('show');
        }, 500);
    }
});

// Đóng modal khi nhấn ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLockedModal();
    }
});
</script>

<?php } ?>