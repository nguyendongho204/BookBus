<?php
session_start();
include("libs/db_chuyenxe.php");


// Lấy danh sách tuyến (điểm đi - điểm đến) có sẵn để đổ vào dropdown
$tuyens_stmt = $pdo->query("SELECT DISTINCT diem_di, diem_den FROM chuyenxe ORDER BY diem_di, diem_den");
$tuyens = $tuyens_stmt ? $tuyens_stmt->fetchAll(PDO::FETCH_ASSOC) : [];


$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
$error_field = $_SESSION['error_field'] ?? '';
$old_values = $_SESSION['old_values'] ?? [];

unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['error_field'], $_SESSION['old_values']);

function redirect_back() {
    header("Location: add_chuyenxe.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lưu tạm dữ liệu để đổ lại form khi có lỗi
    $_SESSION['old_values'] = $_POST;

    // ===== Server-side validation =====
    $diem_di  = trim($_POST['diem_di'] ?? '');
    $diem_den = trim($_POST['diem_den'] ?? '');
    // Gán mặc định vì bỏ trường nhập Nhà xe/Loại xe trên form
    $ten_nhaxe = 'BookBus';
    $loai_xe = 'Xe ghế ngồi';
    $ngay_di  = $_POST['ngay_di'] ?? '';
    $gio_di   = $_POST['gio_di'] ?? '';
    $gio_den  = $_POST['gio_den'] ?? '';
    $gia_ve   = $_POST['gia_ve'] ?? null;
    $so_ghe   = $_POST['so_ghe'] ?? null;
    $so_ghe_con = $_POST['so_ghe_con'] ?? null;

    if ($diem_di === '') {
        $_SESSION['error_message'] = 'Vui lòng nhập điểm đi';
        $_SESSION['error_field'] = 'diem_di';
        redirect_back();
    }
    if ($diem_den === '') {
        $_SESSION['error_message'] = 'Vui lòng nhập điểm đến';
        $_SESSION['error_field'] = 'diem_den';
        redirect_back();
    }

    foreach (['gia_ve' => $gia_ve, 'so_ghe' => $so_ghe] as $field => $val) {
        if ($val === '' || $val === null || !is_numeric($val) || floatval($val) < 0) {
            $_SESSION['error_message'] = 'Trường "'.str_replace('_',' ', $field).'" phải là số và ≥ 0';
            $_SESSION['error_field'] = $field;
            redirect_back();
        }
    }

    // Bạn có thể thêm các kiểm tra khác tại đây (ví dụ: ngày/giờ, logic kinh doanh...)

    try {
        // Kiểm tra trùng lịch đã chèn sẵn ở trên

        // Kiểm tra trùng lịch: cùng tuyến + cùng ngày + cùng giờ đi
        $chk = $pdo->prepare("SELECT COUNT(*) FROM chuyenxe WHERE diem_di=? AND diem_den=? AND ngay_di=? AND gio_di=?");
        $chk->execute([$diem_di, $diem_den, $ngay_di, $gio_di]);
        if ($chk->fetchColumn() > 0) {
            $_SESSION['error_message'] = 'Chuyến này đã tồn tại (trùng tuyến, ngày và giờ đi).';
            $_SESSION['error_field'] = 'gio_di';
            $_SESSION['old_values'] = $_POST;
            redirect_back();
        }

        // Nếu là chuyến mới và không nhập số ghế còn, tự đặt bằng số ghế
        if ($so_ghe_con === null || $so_ghe_con === '' ) {
            $so_ghe_con = $so_ghe;
        }
        $stmt = $pdo->prepare("INSERT INTO chuyenxe (ten_nhaxe, loai_xe, diem_di, diem_den, ngay_di, gio_di, gio_den, gia_ve, so_ghe, so_ghe_con) VALUES (?,?,?,?,?,?,?,?,?,?)");
$stmt->execute([$ten_nhaxe, $loai_xe, $diem_di, $diem_den, $ngay_di, $gio_di, $gio_den, $gia_ve, $so_ghe, $so_ghe_con]);
$_SESSION['success_message'] = 'Thêm chuyến xe thành công!';
unset($_SESSION['old_values']);
redirect_back();
    } catch (Throwable $e) {
        $_SESSION['error_message'] = 'Có lỗi khi lưu dữ liệu: '.$e->getMessage();
        redirect_back();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thêm chuyến xe mới</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      background: url('images/bg-bus.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    }
    .form-container {
      max-width: 760px;
      margin: 40px auto;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 28px 36px;
      width: 620px;
      box-shadow: 0 8px 20px rgba(0,0,0,.2);
    }
    h2 { color: #ff3c3c; text-align:center; margin-bottom: 20px; }
    label { font-weight: 600; margin: 8px 0 6px; color:#0a0534; }
    .form-control, .form-select { border-radius: 10px; height: 46px; }
    .is-invalid + .invalid-feedback { display:block; }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>THÊM CHUYẾN XE MỚI</h2>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="post" novalidate onsubmit="return validateForm(this)">
  <div class="row g-3">
    <div class="col-12">
<label for="diem_di" class="form-label">Điểm đi:</label>
        <select id="diem_di" name="diem_di" class="form-select" data-old="<?= htmlspecialchars($old_values['diem_di'] ?? '') ?>" required>
          <!-- options sẽ render bằng JS -->
        </select>
          <div class="invalid-feedback">Vui lòng chọn điểm đi.</div>
    </div>
    <div class="col-12">
<label for="diem_den" class="form-label">Điểm đến:</label>
        <select id="diem_den" name="diem_den" class="form-select" data-old="<?= htmlspecialchars($old_values['diem_den'] ?? '') ?>" required>
          <!-- options sẽ render bằng JS -->
        </select>
          <div class="invalid-feedback">Vui lòng chọn điểm đến.</div>
    </div>
    <div class="col-12">
<label for="ngay_di" class="form-label">Ngày đi:</label>
        <input type="text" id="ngay_di" name="ngay_di" class="form-control" lang="vi"
               value="<?= htmlspecialchars($old_values['ngay_di'] ?? '') ?>" required />
          <div class="invalid-feedback">Ngày đi không được ở quá khứ.</div>
    </div>
    <div class="col-12 col-md-6">
<label for="gio_di" class="form-label">Giờ đi:</label>
          <input type="time" id="gio_di" name="gio_di" class="form-control"
                 value="<?= htmlspecialchars($old_values['gio_di'] ?? '') ?>" required />
          <div class="invalid-feedback">Giờ đi không hợp lệ.</div>
    </div>
    <div class="col-12 col-md-6">
<label for="gio_den" class="form-label">Giờ đến:</label>
          <input type="time" id="gio_den" name="gio_den" class="form-control"
                 value="<?= htmlspecialchars($old_values['gio_den'] ?? '') ?>" required />
          <div class="invalid-feedback">Giờ đến phải sau giờ đi.</div>
    </div>
    <div class="col-12">
<label for="gia_ve" class="form-label">Giá vé:</label>
        <input type="number" id="gia_ve" name="gia_ve"
               class="form-control"
               min="0" step="1"
               value="<?= htmlspecialchars($old_values['gia_ve'] ?? '0') ?>"
               required
               oninvalid="this.setCustomValidity('Giá vé phải từ 0 trở lên')"
               oninput="this.setCustomValidity(''); if (this.value !== '' && Number(this.value) < 0) this.value = 0;"/>
          <div class="invalid-feedback">Vui lòng nhập giá vé hợp lệ (≥ 0).</div>
    </div>
    <div class="col-12">
<label for="so_ghe" class="form-label">Số ghế:</label>
          <input type="number" id="so_ghe" name="so_ghe"
                 class="form-control"
                 min="0" step="1"
                 value="<?= htmlspecialchars($old_values['so_ghe'] ?? '0') ?>"
                 required
                 oninvalid="this.setCustomValidity('Số ghế phải từ 0 trở lên')"
                 oninput="this.setCustomValidity(''); if (this.value !== '' && Number(this.value) < 0) this.value = 0;"/>
          <div class="invalid-feedback">Số ghế phải từ 0 trở lên.</div>
    </div>
    </div>
    <div class="col-12 d-grid">
      <button type="submit" class="btn btn-danger btn-lg w-100">Thêm</button>
    </div>
  </div>
</form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
  
  <script>
    (function() {
      const form = document.querySelector('form');
      if (!form) return;

      const $ = (id) => document.getElementById(id);
      const fields = {
        diem_di: $('diem_di'),
        diem_den: $('diem_den'),
        ngay_di: $('ngay_di'),
        gio_di: $('gio_di'),
        gio_den: $('gio_den'),
        gia_ve: $('gia_ve'),
        so_ghe: $('so_ghe')
      };

      // Khởi tạo flatpickr cho ô ngày đi: hiển thị dd/MM/yyyy nhưng submit Y-m-d
      if (window.flatpickr) {
        flatpickr("#ngay_di", {
          dateFormat: "Y-m-d",   // giá trị submit
          altInput: true,
          altFormat: "d/m/Y",    // hiển thị cho người dùng
          locale: flatpickr.l10ns.vn,
          minDate: "today",
          maxDate: new Date().fp_incr(2) // +2 ngày
        });
      }


      (function setDateLimits(){
        const el = document.getElementById('ngay_di');
        if (!el) return;
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth()+1).padStart(2,'0');
        const dd = String(now.getDate()).padStart(2,'0');
        el.setAttribute('min', `${yyyy}-${mm}-${dd}`);

        const maxDate = new Date(now);
        maxDate.setDate(maxDate.getDate() + 2);
        const maxY = maxDate.getFullYear();
        const maxM = String(maxDate.getMonth()+1).padStart(2,'0');
        const maxD = String(maxDate.getDate()).padStart(2,'0');
        el.setAttribute('max', `${maxY}-${maxM}-${maxD}`);
      })();


      // Set min date = hôm nay (định dạng YYYY-MM-DD cho thuộc tính min)
      // Set min date = hôm nay, max date = hôm nay + 2 ngày
      (function setDateLimits(){
        const el = document.getElementById('ngay_di');
        if (!el) return;
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth()+1).padStart(2,'0');
        const dd = String(now.getDate()).padStart(2,'0');
        el.setAttribute('min', `${yyyy}-${mm}-${dd}`);

        const maxDate = new Date(now);
        maxDate.setDate(maxDate.getDate() + 2);
        const maxY = maxDate.getFullYear();
        const maxM = String(maxDate.getMonth()+1).padStart(2,'0');
        const maxD = String(maxDate.getDate()).padStart(2,'0');
        el.setAttribute('max', `${maxY}-${maxM}-${maxD}`);
      })();


      function feedbackEl(input) {
        let el = input.nextElementSibling;
        // skip across whitespace text nodes
        while (el && el.nodeType === 3) el = el.nextSibling;
        return (el && el.classList && el.classList.contains('invalid-feedback')) ? el : null;
      }

      function showError(input, message) {
        if (!input) return;
        input.classList.add('is-invalid');
        input.setCustomValidity(message || '');
        const fb = feedbackEl(input);
        if (fb) fb.textContent = message;
      }

      function clearError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.setCustomValidity('');
      }

      function todayYMD() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth()+1).padStart(2,'0');
        const d = String(now.getDate()).padStart(2,'0');
        return `${y}-${m}-${d}`;
      }

      function parseTimeToMinutesHHMM(str) {
        if (!str || !/^\d{2}:\d{2}$/.test(str)) return null;
        const [h, m] = str.split(':').map(Number);
        return h*60 + m;
      }

      function minutesNow() {
        const n = new Date();
        return n.getHours()*60 + n.getMinutes();
      }

      function validateRequired(input, msg) {
        if (!input) return true;
        if (String(input.value || '').trim() === '') {
          showError(input, msg);
          return false;
        }
        clearError(input);
        return true;
      }

      function validateNonNegativeNumber(input, msg) {
        if (!input) return true;
        const val = input.value;
        if (val === '' || isNaN(val) || Number(val) < 0) {
          showError(input, msg);
          return false;
        }
        clearError(input);
        return true;
      }

      function validateDateNotPast(input) {
        if (!input) return true;
        const v = input.value;
        if (!v) { showError(input, 'Vui lòng chọn ngày đi.'); return false; }
        const today = todayYMD();
        if (v < today) {
          showError(input, 'Ngày đi không được ở quá khứ.');
          return false;
        }
        clearError(input);
        return true;
      }

      function validateTimes() {
        const dOK = validateDateNotPast(fields.ngay_di);
        if (!dOK) return false;

        const gDi = parseTimeToMinutesHHMM(fields.gio_di.value);
        const gDen = parseTimeToMinutesHHMM(fields.gio_den.value);

        let ok = true;

        // Required checks for time fields
        if (fields.gio_di.value === '') {
          showError(fields.gio_di, 'Vui lòng chọn giờ đi.');
          ok = false;
        } else {
          clearError(fields.gio_di);
        }
        if (fields.gio_den.value === '') {
          showError(fields.gio_den, 'Vui lòng chọn giờ đến.');
          ok = false;
        } else {
          clearError(fields.gio_den);
        }
        if (!ok) return false;

        // If ngay_di is today, gio_di must be >= now
        const today = todayYMD();
        if (fields.ngay_di.value === today && gDi !== null) {
          if (gDi < minutesNow()) {
            showError(fields.gio_di, 'Giờ đi không được nhỏ hơn giờ hiện tại.');
            ok = false;
          } else {
            clearError(fields.gio_di);
          }
        }

        // Giờ đến phải sau giờ đi (giả định cùng ngày)
        if (gDi !== null && gDen !== null && gDen <= gDi) {
          showError(fields.gio_den, 'Giờ đến phải sau giờ đi.');
          ok = false;
        } else if (gDen !== null) {
          // only clear if we set it
          clearError(fields.gio_den);
        }

        return ok;
      }

      // Attach live validation
      if (fields.diem_di) fields.diem_di.addEventListener('change', () => validateRequired(fields.diem_di, 'Vui lòng chọn điểm đi.'));
      if (fields.diem_den) fields.diem_den.addEventListener('input', () => validateRequired(fields.diem_den, 'Vui lòng nhập điểm đến.'));
      if (fields.gia_ve) fields.gia_ve.addEventListener('input', () => validateNonNegativeNumber(fields.gia_ve, 'Vui lòng nhập giá vé hợp lệ (≥ 0).'));
      if (fields.so_ghe) fields.so_ghe.addEventListener('input', () => validateNonNegativeNumber(fields.so_ghe, 'Số ghế phải từ 0 trở lên.'));
      if (fields.ngay_di) fields.ngay_di.addEventListener('change', () => { validateDateNotPast(fields.ngay_di); validateTimes(); });
      if (fields.gio_di) fields.gio_di.addEventListener('change', validateTimes);
      if (fields.gio_den) fields.gio_den.addEventListener('change', validateTimes);

      
      // Dropdown tuyến -> auto fill điểm đi/đến
      const selTuyen = document.getElementById('tuyen');
      if (selTuyen) {
        selTuyen.addEventListener('change', () => {
          const v = selTuyen.value;
          if (!v) return;
          const parts = v.split('|');
          if (parts.length === 2) {
            if (fields.diem_di) fields.diem_di.value = parts[0];
            if (fields.diem_den) fields.diem_den.value = parts[1];
            clearError(fields.diem_di);
            clearError(fields.diem_den);
          }
        });
      }


      
      // ===== Danh sách tuyến từ Cần Thơ và ngược lại =====
      const ORIGIN = "Cần Thơ";
      const DESTS_FROM_CT = ["Phong Điền","Ô Môn","Vĩnh Long","Kinh Cùng","Cái Tắc","Thốt Nốt","Cờ Đỏ","Vĩnh Thạnh","Bình Tân"];
      const ALL_POINTS = [ORIGIN, ...DESTS_FROM_CT];

      function fillSelectOptions(select, items, selected) {
        select.innerHTML = '<option value="">-- Chọn --</option>' + items.map(x => {
          const sel = (selected && selected === x) ? ' selected' : '';
          return `<option value="${x}"${sel}>${x}</option>`;
        }).join('');
      }

      // Khởi tạo dropdown
      (function initRouteDropdowns(){
        const oldDi = fields.diem_di ? fields.diem_di.getAttribute('data-old') : '';
        const oldDen = fields.diem_den ? fields.diem_den.getAttribute('data-old') : '';

        fillSelectOptions(fields.diem_di, ALL_POINTS, oldDi || "");
        // Tùy vào điểm đi, render điểm đến hợp lệ
        function updateDestinations(){
          const origin = fields.diem_di.value;
          let dests = [];
          if (origin === ORIGIN) {
            dests = DESTS_FROM_CT.slice();
          } else if (DESTS_FROM_CT.includes(origin)) {
            dests = [ORIGIN];
          } else {
            dests = ALL_POINTS.filter(x => x !== origin);
          }
          fillSelectOptions(fields.diem_den, dests, oldDen);
          if (fields.diem_den.value === '') clearError(fields.diem_den);
        }

        updateDestinations();
        fields.diem_di.addEventListener('change', () => {
          clearError(fields.diem_di);
          updateDestinations();
        });
        fields.diem_den.addEventListener('change', () => clearError(fields.diem_den));
      })();


      form.addEventListener('submit', function(e) {
        let ok = true;
        ok &= validateRequired(fields.diem_di, 'Vui lòng chọn điểm đi.');
        ok &= validateRequired(fields.diem_den, 'Vui lòng nhập điểm đến.');
        ok &= validateDateNotPast(fields.ngay_di);
        ok &= validateNonNegativeNumber(fields.gia_ve, 'Vui lòng nhập giá vé hợp lệ (≥ 0).');
        ok &= validateNonNegativeNumber(fields.so_ghe, 'Số ghế phải từ 0 trở lên.');
        ok &= validateTimes();

        if (!ok) {
          e.preventDefault();
          const invalid = form.querySelector('.is-invalid');
          if (invalid) {
            invalid.focus();
            invalid.scrollIntoView({behavior:'smooth', block:'center'});
          }
        }
      });
    })();
  </script>

</body>
</html>