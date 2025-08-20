<?php
// includes/locked_account_modal.php - Modal thông báo tài khoản bị khóa
// File này sẽ được include vào index.php

// Kiểm tra có hiển thị modal không
$show_locked_modal = isset($_SESSION['show_locked_modal']) && $_SESSION['show_locked_modal'] === true;
$account_info = $_SESSION['locked_account_info'] ?? [];

// Nếu cần hiển thị, tạo modal và hiển thị
if ($show_locked_modal):
?>
<!-- Modal Tài khoản bị khóa -->
<div class="modal fade" id="lockedAccountModal" tabindex="-1" aria-labelledby="lockedAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <div class="modal-header bg-danger text-white" style="border-radius: 10px 10px 0 0;">
        <h5 class="modal-title" id="lockedAccountModalLabel">
          <i class="fa fa-lock me-2"></i> Tài khoản bị khóa
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeLockedModal()"></button>
      </div>
      <div class="modal-body text-center p-4">
        <div class="mb-4" style="font-size: 64px;">🔒</div>
        
        <h4 class="text-danger mb-3">Tài khoản của bạn đã bị khóa</h4>
        
        <?php if (!empty($account_info['name'])): ?>
        <p class="mb-1">Xin chào <strong><?php echo htmlspecialchars($account_info['name']); ?></strong>,</p>
        <?php endif; ?>
        
        <p class="text-muted mb-4">
          Tài khoản của bạn đã bị tạm khóa bởi quản trị viên.
          <br>Vui lòng liên hệ quản trị viên để biết thêm chi tiết.
        </p>
        
        <div class="alert alert-warning">
          <i class="fa fa-info-circle me-2"></i>
          Nếu bạn cho rằng đây là nhầm lẫn, vui lòng liên hệ với chúng tôi qua:
          <br>
          <strong>Email:</strong> support@bookbus.com
          <br>
          <strong>Hotline:</strong> 1900 xxxx
        </div>
      </div>
      <div class="modal-footer justify-content-center" style="border-top: 1px solid #eee;">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="closeLockedModal()">
          <i class="fa fa-times me-1"></i> Đóng
        </button>
        <a href="/login.php" class="btn btn-primary">
          <i class="fa fa-home me-1"></i> Về trang đăng nhập
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Hiển thị modal khi trang load xong
document.addEventListener('DOMContentLoaded', function() {
  console.log('🔒 Checking locked account modal...');
  
  <?php if($show_locked_modal): ?>
  console.log('🔒 Showing locked account modal');
  showLockedAccountModal();
  <?php endif; ?>
});

function showLockedAccountModal() {
  if (typeof bootstrap !== 'undefined') {
    var myModal = new bootstrap.Modal(document.getElementById('lockedAccountModal'));
    myModal.show();
  } else {
    // Fallback nếu không có Bootstrap
    var modal = document.getElementById('lockedAccountModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    
    // Tạo backdrop
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    document.body.appendChild(backdrop);
  }
}

function closeLockedModal() {
  // Xóa session sau khi hiển thị
  fetch('/src/ajax/clear_locked_session.php')
    .then(response => response.json())
    .then(data => {
      console.log('🔒 Cleared locked session');
    })
    .catch(error => {
      console.error('Error clearing locked session:', error);
    });
    
  if (typeof bootstrap !== 'undefined') {
    var myModal = bootstrap.Modal.getInstance(document.getElementById('lockedAccountModal'));
    if (myModal) myModal.hide();
  } else {
    // Fallback
    var modal = document.getElementById('lockedAccountModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    
    // Xóa backdrop
    var backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
  }
}
</script>
<?php
// Xóa session sau khi hiển thị để không hiển thị lại khi refresh
$_SESSION['show_locked_modal'] = false;
endif;
?>