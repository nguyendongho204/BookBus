<?php require_once __DIR__ . '/includes/session_bootstrap.php'; ?>

<title>Đặt vé xe BookBus – Các chuyến đi nội tỉnh Cần Thơ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<base href="/src/">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/owl.carousel.css" rel="stylesheet">
<link href="css/awesome.css" rel="stylesheet">
<link rel="icon" type="image/png" href="images/pqe.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<!-- HEADER -->
<div style="background-color:#fff; padding: 5px 20px; display: flex; align-items: center; justify-content: space-between; font-family: Arial, sans-serif;">
  <div style="display: flex; align-items: center;">
    <img src="images/logo-xe.jpg" alt="bookbus Logo" style="height: 50px; margin-right: 10px;">
  </div>
  <ul class="nav navbar-nav navbar-right" style="display: flex; align-items: center; gap: 15px; list-style: none; margin: 0;">
    <div style="display: flex; align-items: center;">
      <span><strong>BookBus</strong><br>App</span>
    </div>
    <img src="images/logo-qr.png" alt="QR Code" style="height: 40px;">

    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: black; text-decoration: none;">
        <i class="fa fa-user"></i>
        <?php
          if (isset($_SESSION['user'])) {
            $u = $_SESSION['user'];
            $displayName = $u['username'] ?? ($u['name'] ?? ($u['email'] ?? 'bạn'));
            echo "Xin chào, <b>" . htmlspecialchars($displayName) . "</b>";
          } else {
            echo '<span id="accountLabel">Tài khoản</span>';
          }
        ?>
        <span class="caret"></span>
      </a>
      <?php /*SITE_BASE*/
$__SITE_BASE = preg_replace('#/(libs|includes)(/.*)?$#', '', APP_BASE);
if ($__SITE_BASE === '') { $__SITE_BASE = '/'; }
?>
<ul class="dropdown-menu">
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="/src/tai-khoan/index.php"><i class="fa fa-user-circle"></i> Tài khoản của tôi</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a href="dangxuat.php"><i class="fa fa-sign-out"></i> Đăng xuất</a></li>
        <?php else: ?>
          <li><a href="#" onclick="showLoginModal()"><i class="fa fa-sign-in"></i> Đăng Nhập</a></li>
          <li><a href="#" onclick="showRegisterModal()"><i class="fa fa-pencil"></i> Đăng Ký</a></li>
        <?php endif; ?>
      </ul>
    </li>

    <a href="#" style="color: black; text-decoration: none;">Tìm kiếm gần đây</a>
    <img src="images/logo-covn.png" alt="VN Flag" style="height: 15px;">
    <span>VND</span>
    <span>Ngôn ngữ</span>
    <a href="lienhe.php" style="color: black; text-decoration: none;">Liên hệ</a>
  </ul>
</div>
        </div>

<!-- Navbar với logo và menu -->
<nav class="navbar navbar-expand-lg navbar-default affix-top" role="navigation" id="BB-nav">
  <div class="container d-flex align-items-center justify-content-center">

    <!-- Gộp logo và menu vào 1 khối flex -->
    <div class="d-flex align-items-center justify-content-center gap-5">

      <!-- Logo -->
      <div class="logo">
        <a href="trangchu.php">
          <img src="images/logo-busbook.jpg" alt="BookBus Logo" style="height: 90px; width: 160px; margin-left: 40px;">
        </a>
      </div>

      <!-- Menu -->
           
      <ul id="menu" class="nav navbar-nav BB-nav menu m-menu d-flex align-items-center gap-4 m-0" style="margin-left: 190px;">
        <li class="active"><a href="trangchu.php"><strong>Trang chủ</strong></a></li>
        <li><a href="datve.php"><strong>Đặt vé Online</strong></a></li>
        <li><a href="huongdan.php"><strong>Hướng dẫn đặt vé</strong></a></li>
        <li><a href="dangky.php"><strong>Đăng ký đại lý</strong></a></li>
        <li><a href="lienhe.php"><strong>Liên hệ</strong></a></li>
      
        <?php if (!empty($_SESSION['user']) && (int)($_SESSION['user']['role'] ?? 1) === 0): ?>
          <li><a href="admin/index.php">Quản Lý</a></li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>


<!-- END HEADER -->

  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>

  <!-- Modal đăng nhập -->

<div id="loginModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:10% auto; padding:20px; border-radius:15px; position:relative;">
    <span onclick="closeLoginModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3 style="text-align:center;">Đăng nhập tài khoản</h3>

    <?php if (isset($_GET['login_err'])): ?>
      <div style="color:#b71c1c; background:#fdecea; border:1px solid #f5c2c7; padding:10px; border-radius:8px; margin:10px 0;">
        <?php
          switch ($_GET['login_err']) {
            case 'empty':      echo "Vui lòng nhập đầy đủ thông tin."; break;
            case 'not_found':  echo "Tài khoản không tồn tại."; break;
            case 'bad_pwd':    echo "Mật khẩu không đúng."; break;
            case 'method':     echo "Cách gửi yêu cầu không hợp lệ."; break;
            case 'need_login': echo "Bạn cần đăng nhập để tiếp tục."; break;
            case 'forbidden':  echo "Bạn không có quyền truy cập trang này."; break;
            default:           echo "Đăng nhập thất bại.";
          }
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['login_ok'])): ?>
      <div style="color:#1b5e20; background:#edf7ed; border:1px solid #a3d3a3; padding:10px; border-radius:8px; margin:10px 0;">
        Đăng nhập thành công.
      </div>
    <?php endif; ?>

    <form id="loginForm" action="libs/xl_dangnhap.php" method="POST">
      <input type="text" name="identity" placeholder="Email hoặc SĐT" style="width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #ccc;" required>
      <input type="password" name="password" placeholder="Mật khẩu" style="width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #ccc;" required>
      <button type="submit" style="width:100%; padding:12px; background:#0d6efd; color:white; border:none; border-radius:25px;">Đăng nhập</button>
    </form>
    <a href="#" id="forgotPasswordBtn" onclick="showPhoneModal()">Quên mật khẩu?</a>

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
    document.getElementById("forgotPasswordBtn").addEventListener("click", function() {
    showPhoneModal();
});

  </script>

  <!-- Modal đăng ký -->
<div id="registerModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:5% auto; padding:20px; border-radius:15px; position:relative;">
    <span onclick="closeRegisterModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3 style="text-align:center;">Đăng ký tài khoản</h3>

    <form action="libs/xl_dangky_daily.php" method="POST">
      <div id="registerError" style="display:none;margin:8px 0;padding:8px 10px;border-radius:6px;background:#fdecea;color:#b71c1c;font-size:14px;"></div>
      <input type="text" name="fullname" placeholder="Họ và tên" required style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      
      <input type="email" name="email" placeholder="Email" required style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      
      <input type="text" name="phone" placeholder="Số điện thoại" required style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      
      <input type="password" name="password" placeholder="Mật khẩu" required style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      
      <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      
      <button type="submit" style="width:100%; padding:12px; background:#28a745; color:white; border:none; border-radius:25px;">Đăng ký</button>
    </form>
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
 <!-- Modal quên mật khẩu (Gửi OTP) -->
<div id="forgotPasswordModal" class="modal">
  <div>
    <span onclick="closeForgotPasswordModal()">&times;</span>
    <h3>Quên mật khẩu</h3>
    <p>Nhập số điện thoại để nhận mã OTP.</p>

    <form action="libs/send_otp.php" method="POST">
      <input type="text" name="phone" placeholder="Nhập số điện thoại" required>
      <button type="submit">Gửi mã OTP</button>
    </form>
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

<!-- Modal gửi OTP -->
<div id="otpRequestModal" class="modal">
  <div>
    <span onclick="closeOtpRequestModal()">&times;</span>
    <h3>Nhập số điện thoại</h3>

    <form action="libs/send_otp.php" method="POST">
      <input type="text" name="phone" placeholder="Nhập số điện thoại" required>
      <button type="submit">Gửi mã OTP</button>
    </form>
  </div>
</div>



<script>
  function showOtpRequestModal() {
    document.getElementById("otpRequestModal").style.display = "block";
  }

  function closeOtpRequestModal() {
    document.getElementById("otpRequestModal").style.display = "none";
  }

  window.onclick = function(event) {
    var modal = document.getElementById("otpRequestModal");
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>


<!-- Modal nhập số điện thoại -->
<div id="phoneModal" class="modal">
  <div class="modal-content">
    <span onclick="closePhoneModal()" class="close">&times;</span>
    <h3 style="text-align:center;">Nhập số điện thoại</h3>

    <form id="otpForm" onsubmit="return false;">
      <input type="text" name="phone" id="phoneInput" placeholder="Nhập số điện thoại để nhận mã OTP" required 
        style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc;">
      <button type="submit" style="width:100%; padding:12px; background:#f4511e; color:white; border:none; border-radius:25px;">
        Gửi mã OTP
      </button>
    </form>
  </div>
</div>


<script>
function showPhoneModal() {
    document.getElementById("phoneModal").style.display = "block";
}

function closePhoneModal() {
    document.getElementById("phoneModal").style.display = "none";
}

// Đóng modal khi click ra ngoài
window.onclick = function(event) {
    var modal = document.getElementById("phoneModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
// Ngăn form reload trang
    showOtpModal(); // Hiển thị modal OTP
});
// Ngăn form reload trang
    showOtpModal(); // Hiển thị modal OTP
});

</script>


<!-- Modal nhập OTP -->
<div id="otpModal" class="modal">
  <div class="modal-content">
    <span onclick="closeOtpModal()" class="close">&times;</span>
    <h3>Xác thực OTP</h3>

    <form action="libs/verify_otp.php" method="POST" onsubmit="showOtpModal(); return false;">
      <input type="hidden" name="phone" id="otpPhone">
      <input type="text" name="otp" placeholder="Nhập mã OTP" required>
      <button type="submit">Xác nhận</button>
    </form>
  </div>
</div>
<script>
  function showPhoneModal() {
    document.getElementById("phoneModal").style.display = "block";
  }

  function closePhoneModal() {
    document.getElementById("phoneModal").style.display = "none";
  }

  function showOtpModal() {
    closePhoneModal();
    document.getElementById("otpModal").style.display = "block";
    document.getElementById("otpPhone").value = document.getElementById("phoneInput").value;
  }

  function closeOtpModal() {
    document.getElementById("otpModal").style.display = "none";
  }
  function showOtpModal() {
    closePhoneModal();
    var otpModal = document.getElementById("otpModal");
    var phoneInput = document.getElementById("phoneInput").value;

    if (otpModal && phoneInput) {
        document.getElementById("otpPhone").value = phoneInput; // Gán số điện thoại vào modal OTP
        otpModal.style.display = "block";
        console.log("Modal OTP mở thành công với số: " + phoneInput);
    } else {
        console.error("Không tìm thấy modal OTP hoặc số điện thoại.");
    }
}


  window.onclick = function(event) {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = "none";
    }
  }
</script>



<script src="js/script.js"></script>


  

<!-- === Đồng bộ tiêu đề "Tài khoản" sau khi đăng nhập (whoami) === -->
<script>
(function(){
  function refreshAccountLabel() {
    var el = document.getElementById('accountLabel');
    if (!el) return;
    fetch('/src/includes/whoami.php', {credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){ if (d && d.ok && d.name) el.textContent = d.name; })
      .catch(function(){});
  }

  document.addEventListener('DOMContentLoaded', function(){
    refreshAccountLabel();
    try {
      var hash = window.location.hash || "";
      if (hash.indexOf('?') >= 0) {
        var qs = new URLSearchParams(hash.split('?')[1] || '');
        if (qs.get('login') === '1') {
          refreshAccountLabel();
          var base = hash.split('?')[0];
          history.replaceState(null, '', location.pathname + base);
        }
      }
    } catch(e){}
  });
})();
</script>


<!-- === Đồng bộ "Tài khoản" sau đăng nhập + xử lý lỗi đăng nhập === -->
<script>
(function(){
  function refreshAccountLabel(){
    var el = document.getElementById('accountLabel'); if (!el) return;
    fetch('/src/includes/whoami.php', {credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){ if (d && d.ok && d.name) el.textContent = d.name; })
      .catch(function(){});
  }
  function parseHashQS(){
    var h = location.hash || ""; if (h.indexOf("?")<0) return new URLSearchParams();
    try { return new URLSearchParams(h.split("?")[1]||""); } catch(e){ return new URLSearchParams(); }
  }
  function clearHashQS(){ var base = location.hash.split("?")[0]; history.replaceState(null,"", location.pathname + base); }
  function setInlineError(input, id, text){
    if (!input || !text) return;
    var s = document.getElementById(id);
    if (!s){ s = document.createElement('span'); s.id=id; s.style.display='block'; s.style.color='#d9534f'; s.style.fontSize='12px'; s.style.marginTop='6px'; input.insertAdjacentElement('afterend', s); }
    s.textContent = text;
  }

  document.addEventListener('DOMContentLoaded', function(){
    refreshAccountLabel();
    var qs = parseHashQS();

    if (qs.get('login') === '1'){
      if (window.closeLoginModal) closeLoginModal();
      refreshAccountLabel();
      clearHashQS();
    }

    if (qs.get('show')==='login' && qs.get('login_err')){
      if (window.showLoginModal) showLoginModal();
      var pass = document.querySelector('#login_password, input[name="password"]');
      setInlineError(pass || document.querySelector('#login_id, input[name="identity"]'), 'loginError', 'Sai thông tin đăng nhập.');
      clearHashQS();
    }
  });
})();
</script>

<script>
// --- SAFE submit handlers ---
// Chỉ áp dụng cho form OTP, không đụng tới form đăng nhập
(function(){
  function ready(fn){ if (document.readyState!=='loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var otp = document.getElementById('otpForm');
    if (otp) {
      otp.addEventListener('submit', function(e){
        e.preventDefault();
        if (typeof showOtpModal==='function') showOtpModal();
      });
    }
  });
})();
</script>


<?php if (isset($_GET['open'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  try {
    var openWhich = "<?php echo $_GET['open']; ?>";
    if (openWhich === 'login' && typeof showLoginModal === 'function') { showLoginModal(); }
    if (openWhich === 'register' && typeof showRegisterModal === 'function') { showRegisterModal(); }
  } catch(e){ console && console.warn && console.warn(e); }
});
</script>
<?php endif; ?>


<!-- Modal đăng ký thành công -->
<div id="registerSuccessModal" class="modal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4);">
  <div style="background:#fff; width:350px; margin:10% auto; padding:20px; border-radius:15px; position:relative; text-align:center;">
    <span onclick="closeRegisterSuccessModal()" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3>Đăng ký thành công</h3>
    <p>Tài khoản của bạn đã được tạo. Vui lòng đăng nhập để tiếp tục.</p>
    <button id="gotoLoginFromSuccess" type="button" style="width:100%; padding:12px; background:#f4511e; color:white; border:none; border-radius:25px; cursor:pointer;">Đăng nhập</button>
  </div>
</div>


<script>
// === Chuỗi modal sau đăng ký ===
function onRegisterSuccess() {
  try { closeRegisterModal(); } catch(e) {}
  setTimeout(showRegisterSuccessModal, 150);
}
function showRegisterSuccessModal() {
  var el = document.getElementById('registerSuccessModal');
  if (el) el.style.display = 'block';
}
function closeRegisterSuccessModal() {
  var el = document.getElementById('registerSuccessModal');
  if (el) el.style.display = 'none';
  if (typeof showLoginModal === 'function') showLoginModal();
}
document.addEventListener('DOMContentLoaded', function(){
  // Nút "Đăng nhập" trong modal thành công
  var btn = document.getElementById('gotoLoginFromSuccess');
  if (btn) btn.addEventListener('click', function(){ closeRegisterSuccessModal(); });

  // Tự động chạy theo query ?regsucc=1
  try {
    var params = new URLSearchParams(location.search);
    if (params.get('regsucc') === '1') {
      onRegisterSuccess();
      history.replaceState(null, '', location.pathname + location.hash);
    }
  } catch(e) {}
});
</script>


<script>
(function(){
  function parseHash(){
    var h = (window.location.hash || '').replace(/^#\?/, '');
    var out = {}; if(!h) return out;
    h.split('&').forEach(function(p){
      var kv = p.split('=');
      if(kv[0]) out[decodeURIComponent(kv[0])] = decodeURIComponent(kv[1]||'');
    });
    return out;
  }
  function showRegisterError(msg){
    var box = document.getElementById('registerError');
    if (!box) return;
    box.textContent = msg;
    box.style.display = 'block';
  }
  var hp = parseHash();
  if (hp.show === 'register' && typeof showRegisterModal === 'function') { showRegisterModal(); }
  if (hp.err) {
    var msg = 'Đăng ký không thành công.';
    if (hp.err === 'phone_exists') msg = 'Số điện thoại đã được sử dụng.';
    else if (hp.err === 'email_exists') msg = 'Email đã được sử dụng.';
    else if (hp.err === 'invalid_phone') msg = 'Số điện thoại không hợp lệ.';
    else if (hp.err === 'invalid_email') msg = 'Email không hợp lệ.';
    else if (hp.err === 'missing') msg = 'Vui lòng nhập đầy đủ thông tin.';
    showRegisterError(msg);
  }
  if (hp.regsucc === '1' && typeof onRegisterSuccess === 'function') { onRegisterSuccess(); }
  // Clean the hash to avoid repeating
  if (hp.err || hp.regsucc || hp.show) {
    try { history.replaceState(null,'', location.pathname + '#'); } catch(e) {}
  }
})();
</script>
<!-- Thêm vào cuối file header.php, trước </head> -->

<script src="/src/assets/js/booking_success_modal.js"></script>