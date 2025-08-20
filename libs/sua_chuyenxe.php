<?php
session_start();
include("db_chuyenxe.php");

// Lấy ID
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    die("❌ Thiếu ID chuyến xe.");
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['id'];

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message   = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$error_field     = isset($_SESSION['error_field']) ? $_SESSION['error_field'] : '';
$old_values      = isset($_SESSION['old_values']) ? $_SESSION['old_values'] : [];
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['error_field'], $_SESSION['old_values']);

// POST: cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diem_di    = trim(isset($_POST['diem_di']) ? $_POST['diem_di'] : '');
    $diem_den   = trim(isset($_POST['diem_den']) ? $_POST['diem_den'] : '');
    $ngay_di    = trim(isset($_POST['ngay_di']) ? $_POST['ngay_di'] : '');
    $gio_di     = trim(isset($_POST['gio_di']) ? $_POST['gio_di'] : '');
    $gio_den    = trim(isset($_POST['gio_den']) ? $_POST['gio_den'] : '');
    $gia_ve     = (int)(isset($_POST['gia_ve']) ? $_POST['gia_ve'] : 0);
    $so_ghe     = (int)(isset($_POST['so_ghe']) ? $_POST['so_ghe'] : 0);
    $so_ghe_con = (int)$so_ghe;
// Validate cơ bản như add_chuyenxe
    if ($diem_di === '' || $diem_den === '' || $ngay_di === '' || $gio_di === '' || $gio_den === '' || $gia_ve < 0 || $so_ghe < 0) {
        $_SESSION['error_message'] = "❌ Vui lòng nhập đầy đủ và hợp lệ.";
        $_SESSION['error_field']   = $diem_di === '' ? 'diem_di'
                                : ($diem_den === '' ? 'diem_den'
                                : ($ngay_di === '' ? 'ngay_di'
                                : ($gio_di  === '' ? 'gio_di'
                                : ($gio_den === '' ? 'gio_den'
                                : ($gia_ve <= 0 ? 'gia_ve' : 'so_ghe')))));
        $_SESSION['old_values'] = $_POST;
        header("Location: sua_chuyenxe.php?id=".$id);
        exit;
    }

    // Ngày đi phải từ hôm nay trở đi
    $today = (new DateTime('today'))->format('Y-m-d');
    if ($ngay_di < $today) {
        $_SESSION['error_message'] = "❌ Ngày đi phải từ hôm nay trở đi.";
        $_SESSION['error_field']   = "ngay_di";
        $_SESSION['old_values']    = $_POST;
        header("Location: sua_chuyenxe.php?id=".$id);
        exit;
    }

    // Giờ đến phải sau giờ đi (server-side)
    try {
        $dt_gio_di  = new DateTime($ngay_di . ' ' . $gio_di);
        $dt_gio_den = new DateTime($ngay_di . ' ' . $gio_den);
        if ($dt_gio_den <= $dt_gio_di) {
            $_SESSION['error_message'] = "❌ Giờ đến phải sau giờ đi.";
            $_SESSION['error_field']   = "gio_den";
            $_SESSION['old_values']    = $_POST;
            header("Location: sua_chuyenxe.php?id=".$id);
            exit;
        }
    } catch (Throwable $e) {
        $_SESSION['error_message'] = "❌ Thời gian không hợp lệ.";
        $_SESSION['error_field']   = "gio_di";
        $_SESSION['old_values']    = $_POST;
        header("Location: sua_chuyenxe.php?id=".$id);
        exit;
    }

    // Giờ đến phải sau giờ đi (cùng ngày)
    try {
        $start = new DateTime("$ngay_di $gio_di");
        $end   = new DateTime("$ngay_di $gio_den");
        if ($end <= $start) {
            $_SESSION['error_message'] = "❌ Giờ đến phải sau giờ đi.";
            $_SESSION['error_field']   = "gio_den";
            $_SESSION['old_values']    = $_POST;
            header("Location: sua_chuyenxe.php?id=".$id);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "❌ Ngày/giờ không hợp lệ.";
        $_SESSION['error_field']   = "ngay_di";
        $_SESSION['old_values']    = $_POST;
        header("Location: sua_chuyenxe.php?id=".$id);
        exit;
    }

    // Update dữ liệu
    $stmt = $pdo->prepare("UPDATE chuyenxe SET
        diem_di = :diem_di,
        diem_den = :diem_den,
        ngay_di = :ngay_di,
        gio_di = :gio_di,
        gio_den = :gio_den,
        gia_ve = :gia_ve,
        so_ghe = :so_ghe,
        so_ghe_con = :so_ghe_con
    WHERE id = :id");
    $stmt->execute([
        ':diem_di' => $diem_di,
        ':diem_den' => $diem_den,
        ':ngay_di' => $ngay_di,
        ':gio_di' => $gio_di,
        ':gio_den' => $gio_den,
        ':gia_ve' => $gia_ve,
        ':so_ghe' => $so_ghe,
        ':so_ghe_con' => $so_ghe_con,
        ':id' => $id
    ]);

    // Quay về danh sách kèm toast
    header("Location: xem_chuyenxe.php?toast=updated");
    exit;
}

// GET: load dữ liệu hiện tại
$stmt = $pdo->prepare("SELECT * FROM chuyenxe WHERE id = :id");
$stmt->execute([':id' => $id]);
$cx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cx) {
    die("❌ Không tìm thấy chuyến xe.");
}

// Prefill nếu chưa có old_values
if (empty($old_values)) {
    $old_values = [
        'diem_di'    => isset($cx['diem_di']) ? $cx['diem_di'] : '',
        'diem_den'   => isset($cx['diem_den']) ? $cx['diem_den'] : '',
        'ngay_di'    => isset($cx['ngay_di']) ? $cx['ngay_di'] : '',
        'gio_di'     => isset($cx['gio_di']) ? $cx['gio_di'] : '',
        'gio_den'    => isset($cx['gio_den']) ? $cx['gio_den'] : '',
        'gia_ve'     => isset($cx['gia_ve']) ? $cx['gia_ve'] : '',
        'so_ghe'     => isset($cx['so_ghe']) ? $cx['so_ghe'] : '',
        'so_ghe_con' => isset($cx['so_ghe_con']) ? $cx['so_ghe_con'] : '',
    ];
}

// ---- Biến hiển thị (value + class) để tránh inline dài và không gạch đỏ
$val_diem_di    = htmlspecialchars($old_values['diem_di'],    ENT_QUOTES, 'UTF-8');
$val_diem_den   = htmlspecialchars($old_values['diem_den'],   ENT_QUOTES, 'UTF-8');
$val_ngay_di    = htmlspecialchars($old_values['ngay_di'],    ENT_QUOTES, 'UTF-8');
$val_gio_di     = htmlspecialchars($old_values['gio_di'],     ENT_QUOTES, 'UTF-8');
$val_gio_den    = htmlspecialchars($old_values['gio_den'],    ENT_QUOTES, 'UTF-8');
$val_gia_ve     = htmlspecialchars($old_values['gia_ve'],     ENT_QUOTES, 'UTF-8');
$val_so_ghe     = htmlspecialchars($old_values['so_ghe'],     ENT_QUOTES, 'UTF-8');
$val_so_ghe_con = htmlspecialchars($old_values['so_ghe_con'], ENT_QUOTES, 'UTF-8');

$class_diem_di    = ($error_field === 'diem_di')    ? 'error-field' : '';
$class_diem_den   = ($error_field === 'diem_den')   ? 'error-field' : '';
$class_ngay_di    = ($error_field === 'ngay_di')    ? 'error-field' : '';
$class_gio_di     = ($error_field === 'gio_di')     ? 'error-field' : '';
$class_gio_den    = ($error_field === 'gio_den')    ? 'error-field' : '';
$class_gia_ve     = ($error_field === 'gia_ve')     ? 'error-field' : '';
$class_so_ghe     = ($error_field === 'so_ghe')     ? 'error-field' : '';
$class_so_ghe_con = ($error_field === 'so_ghe_con') ? 'error-field' : '';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SỬA CHUYẾN XE #<?php echo htmlspecialchars($id); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      background: url('../images/bg-bus.jpg') no-repeat center center fixed;
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
    <h2> SỬA CHUYẾN XE #<?php echo htmlspecialchars($id); ?> </h2>

    <form method="post" id="chuyenxe-form" novalidate novalidate onsubmit="return validateForm(this)">
  <div class="row g-3">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <div class="col-12">
<label for="diem_di" class="form-label">Điểm đi:</label>
        <select id="diem_di" name="diem_di" class="form-select" data-old="<?php echo htmlspecialchars($val_diem_di); ?>" data-old="<?= htmlspecialchars($old_values['diem_di'] ?? '') ?>" required>
          <!-- options sẽ render bằng JS -->
        </select>
          <div class="invalid-feedback">Vui lòng chọn điểm đi.</div>
    </div>
    <div class="col-12">
<label for="diem_den" class="form-label">Điểm đến:</label>
        <select id="diem_den" name="diem_den" class="form-select" data-old="<?php echo htmlspecialchars($val_diem_den); ?>" data-old="<?= htmlspecialchars($old_values['diem_den'] ?? '') ?>" required>
          <!-- options sẽ render bằng JS -->
        </select>
          <div class="invalid-feedback">Vui lòng chọn điểm đến.</div>
    </div>
    <div class="col-12">
<label for="ngay_di" class="form-label">Ngày đi:</label>
        <input type="text" id="ngay_di" name="ngay_di" class="form-control" lang="vi"
               value="<?php echo htmlspecialchars($val_ngay_di); ?>" required />
          <div class="invalid-feedback">Ngày đi không được ở quá khứ.</div>
    </div>
    <div class="col-12 col-md-6">
<label for="gio_di" class="form-label">Giờ đi:</label>
          <input type="time" id="gio_di" name="gio_di" class="form-control"
                 value="<?php echo htmlspecialchars($val_gio_di); ?>" required />
          <div class="invalid-feedback">Giờ đi không hợp lệ.</div>
    </div>
    <div class="col-12 col-md-6">
<label for="gio_den" class="form-label">Giờ đến:</label>
          <input type="time" id="gio_den" name="gio_den" class="form-control"
                 value="<?php echo htmlspecialchars($val_gio_den); ?>" required />
          <div class="invalid-feedback">Giờ đến phải sau giờ đi.</div>
    </div>
    <div class="col-12">
<label for="gia_ve" class="form-label">Giá vé:</label>
        <input type="number" id="gia_ve" name="gia_ve"
               class="form-control"
               min="0" step="1"
               value="<?php echo htmlspecialchars($val_gia_ve); ?>"
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
                 value="<?php echo htmlspecialchars($val_so_ghe); ?>"
                 required
                 oninvalid="this.setCustomValidity('Số ghế phải từ 0 trở lên')"
                 oninput="this.setCustomValidity(''); if (this.value !== '' && Number(this.value) < 0) this.value = 0;"/>
          <div class="invalid-feedback">Số ghế phải từ 0 trở lên.</div>
    </div>
    </div>
    <div class="col-12 d-grid">
      <button type="submit" class="btn btn-danger btn-lg w-100">Lưu thay đổi</button>
    </div>
  </div>
</form>
  </div>

  <!-- Modal Thông báo -->
  <div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="notifyModalLabel">Thông báo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <?php echo htmlspecialchars($success_message ?: $error_message); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-info text-white" data-bs-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var message = <?php echo json_encode($success_message ?: $error_message); ?>;
      var errorField = <?php echo json_encode($error_field); ?>;

      if (message) {
        var el = document.getElementById('notifyModal');
        var modal = new bootstrap.Modal(el);
        modal.show();

        // Lắng nghe trực tiếp trên element (chuẩn Bootstrap 5)
        el.addEventListener('hidden.bs.modal', function () {
          if (errorField) {
            var field = document.getElementById(errorField);
            if (field) {
              field.scrollIntoView({ behavior: 'smooth', block: 'center' });
              field.classList.add('error-field');
              field.focus();
            }
          }
        });
      }
    });
  </script>

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