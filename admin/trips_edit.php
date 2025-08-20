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

$trip = [
  'ten_nhaxe'=>'','loai_xe'=>'','diem_di'=>'','diem_den'=>'',
  'ngay_di'=>'','gio_di'=>'','gio_den'=>'','gia_ve'=>'','so_ghe'=>'','so_ghe_con'=>''
];
if ($id>0 && !$old) {
  $st = $pdo->prepare("SELECT * FROM chuyenxe WHERE id=:id");
  $st->execute([':id'=>$id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if ($row) $trip = $row;
}
if ($old) { $trip = array_merge($trip, $old); }

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

<script>
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
