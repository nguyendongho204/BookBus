<?php
include 'header.php'; // hoặc require 'header.php';
?>

<!-- Modal đăng nhập -->
<div id="loginModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:10% auto; padding:20px; border-radius:15px; position:relative;">
    <span onclick="closeLoginModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3 style="text-align:center;">Đăng nhập tài khoản</h3>
    <input type="text" placeholder="Nhập số điện thoại" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <input type="password" placeholder="Nhập mật khẩu" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <button style="width:100%; padding:12px; background:#f4511e; color:white; border:none; border-radius:25px;">Đăng nhập</button>
    <!-- hiện modal khi bấm quên mật khẩu -->
    <a href="#" onclick="showForgotPasswordModal()" style="display:block; text-align:right; color:red; margin-top:10px;">a?</a>
  </div>
</div>

<script>
  function showLoginModal() {
    document.getElementById("loginModal").style.display = "block";
  }

  function closeLoginModal() {
    document.getElementById("loginModal").style.display = "none";
  }

  window.onclick = function(event) {
    var modal = document.getElementById("loginModal");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>

<!-- Modal đăng ký -->
<div id="registerModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:5% auto; padding:20px; border-radius:15px; position:relative;">
    <span onclick="closeRegisterModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3 style="text-align:center;">Đăng ký tài khoản</h3>
    <input type="text" placeholder="Họ và tên" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <input type="email" placeholder="Email" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <input type="text" placeholder="Số điện thoại" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <input type="password" placeholder="Mật khẩu" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <input type="password" placeholder="Xác nhận mật khẩu" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <button style="width:100%; padding:12px; background:#28a745; color:white; border:none; border-radius:25px;">Đăng ký</button>
  </div>
</div>

<script>
  function showRegisterModal() {
    document.getElementById("registerModal").style.display = "block";
  }

  function closeRegisterModal() {
    document.getElementById("registerModal").style.display = "none";
  }

  window.onclick = function(event) {
    var registerModal = document.getElementById("registerModal");
    if (event.target == registerModal) {
      registerModal.style.display = "none";
    }
  }
</script>

<!-- Modal đăng ký thành công -->
<div id="registerSuccessModal" class="modal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index: 1100;">
  <div style="background:#fff; width:350px; margin:10% auto; padding:20px; border-radius:15px; position:relative; text-align:center;">
    <span onclick="closeRegisterSuccessModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3>Đăng ký thành công</h3>
    <p> Vui lòng đăng nhập để tiếp tục.</p>
    <button id="gotoLoginFromSuccess" style="width:100%; padding:12px; background:#f4511e; color:white; border:none; border-radius:25px; cursor:pointer;">
      Đăng nhập
    </button>
  </div>
</div>

<!-- Modal quên mật khẩu -->
<div id="forgotPasswordModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:10% auto; padding:20px; border-radius:15px; position:relative;">
    <span onclick="closeForgotPasswordModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <p style="text-align:center;">Nhập email hoặc số điện thoại để nhận hướng dẫn đặt lại mật khẩu.</p>
    <input type="text" placeholder="Nhập email hoặc số điện thoại" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
    <button style="width:100%; padding:12px; background:#f4511e; color:white; border:none; border-radius:25px;">Gửi yêu cầu</button>
  </div>
</div>

<script>
  function showForgotPasswordModal() {
    document.getElementById("forgotPasswordModal").style.display = "block";
  }

  function closeForgotPasswordModal() {
    document.getElementById("forgotPasswordModal").style.display = "none";
  }

  window.onclick = function(event) {
    var modal = document.getElementById("forgotPasswordModal");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>

<script>
// === Chuỗi sau khi đăng ký thành công (thuần JS) ===
function onRegisterSuccess() {
  try { closeRegisterModal(); } catch(e) {}
  setTimeout(function(){ showRegisterSuccessModal(); }, 150);
}
function showRegisterSuccessModal() {
  var sm = document.getElementById("registerSuccessModal");
  if (sm) sm.style.display = "block";
}
function closeRegisterSuccessModal() {
  var sm = document.getElementById("registerSuccessModal");
  if (sm) sm.style.display = "none";
  try { showLoginModal(); } catch(e) {}
}
document.addEventListener("DOMContentLoaded", function () {
  var btn = document.getElementById("gotoLoginFromSuccess");
  if (btn) btn.addEventListener("click", function () { closeRegisterSuccessModal(); });

  // Click ngoài khung để đóng modal thành công
  window.addEventListener("click", function (e) {
    var sm = document.getElementById("registerSuccessModal");
    if (e.target === sm) { closeRegisterSuccessModal(); }
  });

  // Tự kích hoạt nếu URL có ?regsucc=1 (tiện cho redirect từ server)
  try {
    var usp = new URLSearchParams(window.location.search);
    if (usp.get('regsucc') === '1') { onRegisterSuccess(); }
  } catch(e) {}
});
</script>
