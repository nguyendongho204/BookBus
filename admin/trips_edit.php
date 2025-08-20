<?php require_once __DIR__ . '/../libs/admin_guard.php'; ?>

<?php
$pageTitle = "Chuyến xe - Thêm/Sửa";
$active = "trips";
require_once __DIR__ . "/_layout_top.php";
require_once __DIR__ . "/../libs/db_chuyenxe.php"; // $pdo
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Danh sách tuyến (đồng bộ với add_chuyenxe)
$LOCATIONS = [
  "Cần Thơ","Phong Điền","Ô Môn","Vĩnh Long","Kinh Cùng","Cái Tắc",
  "Thốt Nốt","Cờ Đỏ","Vĩnh Thạnh","Bình Tân"
];

$id = (int)($_GET['id'] ?? 0);
$old = $_SESSION['trip_form'] ?? null;
if ($old) { unset($_SESSION['trip_form']); }

// Check for success messages
$success_type = $_GET['success'] ?? '';
$trip_id = $_GET['trip_id'] ?? '';
$route = $_GET['route'] ?? '';

$trip = [
  'ten_nhaxe'=>'','loai_xe'=>'','diem_di'=>'','diem_den'=>'',
  'ngay_di'=>'','gio_di'=>'','gio_den'=>'','gia_ve'=>'','so_ghe'=>'','so_ghe_con'=>''
];

// If success=create, reset form for new entry
if ($success_type === 'create') {
    $trip = [
        'ten_nhaxe'=>'','loai_xe'=>'','diem_di'=>'','diem_den'=>'',
        'ngay_di'=>'','gio_di'=>'','gio_den'=>'','gia_ve'=>'','so_ghe'=>'','so_ghe_con'=>''
    ];
    $id = 0; // Reset to create mode
} else {
    if ($id>0 && !$old) {
        $st = $pdo->prepare("SELECT * FROM chuyenxe WHERE id=:id");
        $st->execute([':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) $trip = $row;
    }
    if ($old) { $trip = array_merge($trip, $old); }
}

$today = date('Y-m-d');
$nowHHmm = date('H:i');
?>
<style>
.input-wrap{ position:relative; }
.input-wrap .invalid-indicator{
  position:absolute; right:12px; top:50%; transform:translateY(-50%);
  color:#ef4444; font-weight:700; display:none; pointer-events:none;
}
.input-wrap .form-control.is-invalid + .invalid-indicator,
.input-wrap .form-select.is-invalid + .invalid-indicator{ display:inline-block; }
.form-control.is-invalid,.form-select.is-invalid{
  border-color:#ef4444 !important;
  box-shadow:0 0 0 4px rgba(239,68,68,.15) !important;
}
.invalid-feedback{ display:none; color:#ef4444; font-weight:500; margin-top:6px; }
.input-wrap .form-control.is-invalid ~ .invalid-feedback,
.input-wrap .form-select.is-invalid ~ .invalid-feedback{ display:block; }

/* Success Modal Styles */
.success-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-out;
}

.success-modal-overlay.show {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translate(-50%, -40%) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-8px);
    }
    60% {
        transform: translateY(-4px);
    }
}

.success-modal {
    background: white;
    border-radius: 16px;
    padding: 0;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: slideInUp 0.4s ease-out;
    overflow: hidden;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.modal-header {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    padding: 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.modal-header::before {
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

.success-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
    animation: bounce 1s ease-in-out;
    position: relative;
    z-index: 2;
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 6px;
    position: relative;
    z-index: 2;
}

.modal-subtitle {
    font-size: 14px;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.modal-body {
    padding: 24px;
}

.trip-info {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
    border-left: 4px solid #22c55e;
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
    color: #64748b;
    font-weight: 500;
}

.info-value {
    color: #1e293b;
    font-weight: 600;
}

.modal-actions {
    display: flex;
    gap: 12px;
}

.modal-btn {
    flex: 1;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 14px;
}

.btn-primary-custom {
    background: #ff5722;
    color: white;
}

.btn-primary-custom:hover {
    background: #e64a19;
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.btn-secondary-custom {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.btn-secondary-custom:hover {
    background: #e2e8f0;
    color: #475569;
    text-decoration: none;
}

/* Auto-close countdown */
.countdown-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    width: 100%;
    animation: countdown 10s linear forwards;
}

@keyframes countdown {
    from { width: 100%; }
    to { width: 0%; }
}

.countdown-text {
    position: absolute;
    bottom: 8px;
    right: 12px;
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

/* Mobile responsive */
@media (max-width: 480px) {
    .success-modal {
        margin: 20px;
        width: calc(100% - 40px);
        max-width: calc(100% - 40px);
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .modal-actions {
        flex-direction: column;
    }
}

/* Ensure modal stays centered even on scroll */
body.modal-open {
    overflow: hidden;
}
</style>

<div class="form-card">
  <h5><?= $id>0 ? 'Cập nhật chuyến xe' : 'Thêm chuyến xe' ?></h5>
  <form id="tripForm" method="post" action="trips_save.php" novalidate>
    <input type="hidden" name="id" value="<?=$id?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nhà xe</label>
        <div class="input-wrap">
          <input class="form-control" name="ten_nhaxe" required value="<?=htmlspecialchars($trip['ten_nhaxe'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Loại xe</label>
        <div class="input-wrap">
          <input class="form-control" name="loai_xe" required value="<?=htmlspecialchars($trip['loai_xe'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Điểm đi</label>
        <div class="input-wrap">
          <select class="form-select" name="diem_di" id="diem_di" required>
            <option value="">-- Chọn --</option>
            <?php foreach($LOCATIONS as $l): ?>
              <option value="<?=htmlspecialchars($l)?>" <?=$trip['diem_di']===$l?'selected':''?>><?=htmlspecialchars($l)?></option>
            <?php endforeach; ?>
            <?php if ($trip['diem_di'] && !in_array($trip['diem_di'],$LOCATIONS,true)): ?>
              <option value="<?=htmlspecialchars($trip['diem_di'])?>" selected><?=htmlspecialchars($trip['diem_di'])?> (khác)</option>
            <?php endif; ?>
          </select>
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Điểm đến</label>
        <div class="input-wrap">
          <select class="form-select" name="diem_den" id="diem_den" required>
            <option value="">-- Chọn --</option>
            <?php foreach($LOCATIONS as $l): ?>
              <option value="<?=htmlspecialchars($l)?>" <?=$trip['diem_den']===$l?'selected':''?>><?=htmlspecialchars($l)?></option>
            <?php endforeach; ?>
            <?php if ($trip['diem_den'] && !in_array($trip['diem_den'],$LOCATIONS,true)): ?>
              <option value="<?=htmlspecialchars($trip['diem_den'])?>" selected><?=htmlspecialchars($trip['diem_den'])?> (khác)</option>
            <?php endif; ?>
          </select>
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Ngày đi</label>
        <div class="input-wrap">
          <input type="date" class="form-control" id="ngay_di" name="ngay_di" min="<?=$today?>" required value="<?=htmlspecialchars($trip['ngay_di'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Giờ đi</label>
        <div class="input-wrap">
          <input type="time" class="form-control" id="gio_di" name="gio_di" required value="<?=htmlspecialchars($trip['gio_di'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Giờ đến</label>
        <div class="input-wrap">
          <input type="time" class="form-control" id="gio_den" name="gio_den" required value="<?=htmlspecialchars($trip['gio_den'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Giá vé (đ)</label>
        <div class="input-wrap">
          <input type="number" min="0" step="1000" class="form-control" id="gia_ve" name="gia_ve" required value="<?=htmlspecialchars($trip['gia_ve'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Số ghế</label>
        <div class="input-wrap">
          <input type="number" min="1" step="1" class="form-control" id="so_ghe" name="so_ghe" required value="<?=htmlspecialchars($trip['so_ghe'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Số ghế còn</label>
        <div class="input-wrap">
          <input type="number" min="0" step="1" class="form-control" id="so_ghe_con" name="so_ghe_con" required value="<?=htmlspecialchars($trip['so_ghe_con'])?>">
          <span class="invalid-indicator">!</span>
          <div class="invalid-feedback"></div>
        </div>
      </div>
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-brand"><?= $id>0?'Lưu thay đổi':'Thêm chuyến' ?></button>
      <a class="btn btn-ghost" href="trips.php">Hủy</a>
    </div>
  </form>
</div>

<!-- Success Modal -->
<?php if ($success_type): ?>
<div class="success-modal-overlay" id="successModal">
    <div class="success-modal">
        <div class="modal-header">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="modal-title">
                <?= $success_type === 'create' ? 'Thêm chuyến xe thành công!' : 'Cập nhật thành công!' ?>
            </div>
            <div class="modal-subtitle">
                <?= $success_type === 'create' ? 'Chuyến xe mới đã được tạo' : 'Thông tin chuyến xe đã được cập nhật' ?>
            </div>
        </div>
        
        <div class="modal-body">
            <?php if ($success_type === 'create' && $route): ?>
            <div class="trip-info">
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-hashtag"></i> Mã chuyến
                    </span>
                    <span class="info-value">#<?= htmlspecialchars($trip_id) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-route"></i> Tuyến đường
                    </span>
                    <span class="info-value"><?= htmlspecialchars($route) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        <i class="fas fa-clock"></i> Thời gian tạo
                    </span>
                    <span class="info-value"><?= date('H:i - d/m/Y') ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="modal-actions">
                <a href="trips.php" class="modal-btn btn-primary-custom">
                    <i class="fas fa-list"></i>
                    Xem danh sách
                </a>
                <button class="modal-btn btn-secondary-custom" onclick="closeSuccessModal()">
                    <i class="fas fa-plus"></i>
                    Thêm chuyến khác
                </button>
            </div>
        </div>
        
        <!-- Auto-close countdown bar -->
        <div class="countdown-bar"></div>
        <div class="countdown-text" id="countdownText">Tự động đóng sau <span id="countdownNumber">10</span>s</div>
    </div>
</div>
<?php endif; ?>

<script>
// Success Modal Functions
let countdownTimer;
let currentCountdown = 10;

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        startCountdown();
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }
        // Clean URL
        if (window.history.replaceState) {
            window.history.replaceState(null, null, 'trips_edit.php');
        }
    }
}

function startCountdown() {
    const countdownElement = document.getElementById('countdownNumber');
    
    countdownTimer = setInterval(function() {
        currentCountdown--;
        if (countdownElement) {
            countdownElement.textContent = currentCountdown;
        }
        
        if (currentCountdown <= 0) {
            clearInterval(countdownTimer);
            closeSuccessModal();
        }
    }, 1000);
}

// Show modal if success parameter exists
<?php if ($success_type): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(showSuccessModal, 100);
});
<?php endif; ?>

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('success-modal-overlay')) {
        closeSuccessModal();
    }
});

// Prevent modal from closing when clicking inside
document.addEventListener('click', function(e) {
    if (e.target.closest('.success-modal') && !e.target.classList.contains('success-modal-overlay')) {
        e.stopPropagation();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('successModal');
    if (modal && modal.classList.contains('show')) {
        if (e.key === 'Escape') {
            closeSuccessModal();
        } else if (e.key === 'Enter') {
            // Go to trips list
            window.location.href = 'trips.php';
        }
    }
});

// Original form validation code
(function(){
  function wrap(el){ return el.closest('.input-wrap') || el.parentElement; }
  function setInvalid(el, msg){
    el.classList.add('is-invalid');
    var fb = wrap(el).querySelector('.invalid-feedback');
    if (fb) fb.textContent = msg || 'Giá trị không hợp lệ.';
  }
  function clearInvalid(el){
    el.classList.remove('is-invalid');
    var fb = wrap(el).querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
  }
  function minutes(s){
    if (!s || s.indexOf(':')===-1) return NaN;
    var p = s.split(':');
    return parseInt(p[0],10)*60 + parseInt(p[1],10);
  }

  var form  = document.getElementById('tripForm');
  var diemDi  = document.getElementById('diem_di');
  var diemDen = document.getElementById('diem_den');
  var ngay  = document.getElementById('ngay_di');
  var gioDi = document.getElementById('gio_di');
  var gioDen= document.getElementById('gio_den');
  var giaVe = document.getElementById('gia_ve');
  var soGhe = document.getElementById('so_ghe');
  var soGheCon = document.getElementById('so_ghe_con');
  var today = "<?=$today?>";
  var nowHHmm = "<?=$nowHHmm?>";

  function updateMinTime(){
    if (!ngay.value) return;
    if (ngay.value === today) { gioDi.min = nowHHmm; }
    else { gioDi.removeAttribute('min'); }
  }

  function validate(){
    var ok = true;
    [diemDi, diemDen, ngay, gioDi, gioDen, giaVe, soGhe, soGheCon].forEach(clearInvalid);
    form.querySelectorAll('[name=ten_nhaxe],[name=loai_xe]').forEach(function(el){
      clearInvalid(el);
      if (!el.value.trim()) { setInvalid(el, 'Trường này là bắt buộc.'); ok=false; }
    });

    if (!diemDi.value) { setInvalid(diemDi, 'Vui lòng chọn điểm đi.'); ok=false; }
    if (!diemDen.value) { setInvalid(diemDen, 'Vui lòng chọn điểm đến.'); ok=false; }
    if (diemDi.value && diemDen.value && diemDi.value === diemDen.value){
      setInvalid(diemDen, 'Điểm đến phải khác điểm đi.');
      ok=false;
    }

    if (!ngay.value) { setInvalid(ngay, 'Vui lòng chọn ngày đi.'); ok=false; }
    else if (ngay.value < today) { setInvalid(ngay, 'Ngày đi không được ở quá khứ.'); ok=false; }

    if (!gioDi.value) { setInvalid(gioDi, 'Vui lòng chọn giờ đi.'); ok=false; }
    else if (ngay.value === today && minutes(gioDi.value) < minutes(nowHHmm)) {
      setInvalid(gioDi, 'Giờ đi không được nhỏ hơn giờ hiện tại.'); ok=false;
    }

    if (!gioDen.value) { setInvalid(gioDen, 'Vui lòng chọn giờ đến.'); ok=false; }
    else if (!isNaN(minutes(gioDi.value)) && !isNaN(minutes(gioDen.value)) && minutes(gioDen.value) <= minutes(gioDi.value)) {
      setInvalid(gioDen, 'Giờ đến phải sau giờ đi.'); ok=false;
    }

    var gv = parseInt(giaVe.value || '0', 10);
    if (isNaN(gv) || gv < 0) { setInvalid(giaVe, 'Giá vé phải ≥ 0.'); ok=false; }

    var sg  = parseInt(soGhe.value || '0', 10);
    var sgc = parseInt(soGheCon.value || '0', 10);
    if (isNaN(sg) || sg < 1) { setInvalid(soGhe, 'Số ghế phải ≥ 1.'); ok=false; }
    if (isNaN(sgc) || sgc < 0 || sgc > sg) { setInvalid(soGheCon, 'Số ghế còn phải từ 0 đến ' + (isNaN(sg)?'số ghế':sg) + '.'); ok=false; }

    if (!ok) {
      var first = form.querySelector('.is-invalid');
      if (first) first.focus({preventScroll:true});
      first && first.scrollIntoView({behavior:'smooth', block:'center'});
    }
    return ok;
  }

  form.addEventListener('input', function(e){
    if (e.target.classList.contains('is-invalid')) clearInvalid(e.target);
  });
  [diemDi,diemDen].forEach(function(el){
    el.addEventListener('change', function(){ if (el.classList.contains('is-invalid')) clearInvalid(el); });
  });
  ngay.addEventListener('change', function(){ updateMinTime(); clearInvalid(gioDi); });

  form.addEventListener('submit', function(e){
    updateMinTime();
    if (!validate()) e.preventDefault();
  });

  updateMinTime();
})();
</script>

<?php require_once __DIR__ . "/_layout_bottom.php"; ?>