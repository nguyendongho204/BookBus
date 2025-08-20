<?php
// timvexenhanh.php — widget tìm vé nhanh (đã sửa)
include __DIR__ . '/libs/db_chuyenxe.php';

// Chấp nhận cả 2 bộ tên tham số: (diem_di, diem_den, ngay_khoi_hanh, so_khach) và (from, to, date, seats)
$diem_di         = $_GET['diem_di']         ?? ($_GET['from']  ?? '');
$diem_den        = $_GET['diem_den']        ?? ($_GET['to']    ?? '');
$ngay_khoi_hanh  = $_GET['ngay_khoi_hanh']  ?? ($_GET['date']  ?? '');
$so_khach        = (int)($_GET['so_khach']  ?? ($_GET['seats'] ?? 1));

// Chuẩn hoá ngày về Y-m-d (phòng trường hợp browser gửi định dạng khác)
if ($ngay_khoi_hanh) {
    $d = date_create($ngay_khoi_hanh);
    if ($d) $ngay_khoi_hanh = $d->format('Y-m-d');
}

// Tìm kiếm
$query = "SELECT * FROM chuyenxe 
          WHERE diem_di = :diem_di 
            AND diem_den = :diem_den 
            AND date(ngay_di) = :ngay_di";
$stmt = $pdo->prepare($query);
$stmt->execute([
    ':diem_di' => $diem_di,
    ':diem_den' => $diem_den,
    ':ngay_di' => $ngay_khoi_hanh
]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Danh sách điểm (có thể lấy từ DB, tạm cứng để demo)
$LOCATIONS = [
  "Cần Thơ","Phong Điền","Ô Môn","Vĩnh Long","Kinh Cùng",
  "Cái Tắc","Thốt Nốt","Cờ Đỏ","Vĩnh Thạnh","Bình Tân"
];
?>
<style>
/* =========== Layout card tìm vé =========== */
.qs3-card{
  max-width: 980px;
  margin: 0 auto;
  background: rgba(255,255,255,.9);
  box-shadow: 0 10px 30px rgba(0,0,0,.15);
  border-radius: 14px;
  padding: 18px 18px 20px;
  position: relative;    /* cần để áp z-index */
  z-index: 50;           /* NỔI LÊN trên banner */
}
.qs3-title{
  margin: 2px 0 14px;
  text-align: center;
  font-weight: 700;
  font-size: 20px;
}
.qs3-grid{
  display: grid;
  grid-template-columns: minmax(160px,1fr) minmax(160px,1fr) minmax(140px,.8fr) minmax(110px,.6fr) auto;
  gap: 10px;
  align-items: start;
}
.qs3-field label{
  display: block;
  font-size: 13px;
  color: #333;
  margin-bottom: 6px;
  font-weight: 600;
}
.qs3-field select,
.qs3-field input{
  width: 100%;
  height: 44px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0 12px;
  font-size: 15px;
  outline: none;
  background: #fff;
  position: relative; /* để áp z-index */
  z-index: 60;        /* dropdown không bị che */
}
.qs3-field input[type="date"]{
  padding-right: 8px;
}
.qs3-submit{
  height: 44px;
  border: 0;
  border-radius: 10px;
  padding: 0 18px;
  font-weight: 700;
  cursor: pointer;
  background: #ff5722;
  color: #fff;
  white-space: nowrap;
}
.qs3-submit:hover{ filter: brightness(.96); }

.error-msg{
  margin-top: 6px;
  font-size: 12px;
  color: #d93025;
}

/* ====== RẤT QUAN TRỌNG: tránh banner cắt dropdown ======
   Nếu form nằm trong khối banner, đảm bảo khối banner KHÔNG che dropdown.
   Đổi .banner-box bên dưới thành class banner của bạn nếu khác. */
.banner-box{
  position: relative;
  z-index: 10;       /* thấp hơn .qs3-card */
  overflow: visible; /* KHÔNG cắt menu của <select> */
}

/* mobile */
@media (max-width: 768px){
  .qs3-grid{
    grid-template-columns: 1fr;
  }
  .qs3-submit{ width: 100%; }
}
</style>

<div class="qs3-card">
  <div class="qs3-title">Tìm vé xe nhanh</div>
  <!--
    action: thay bằng trang kết quả của bạn (ví dụ xemchuyenxe.php)
    method: GET để share link
  -->
  <form id="quickSearch" action="timkiemchuyenxe.php" method="get" novalidate>
    <div class="qs3-grid">
      <!-- Điểm đi -->
      <div class="qs3-field">
        <label for="qs3_from">Điểm đi</label>
        <select id="qs3_from" name="from" required>
          <option value="">Chọn điểm đi</option>
          <?php foreach ($LOCATIONS as $l): ?>
            <option value="<?= htmlspecialchars($l) ?>"><?= htmlspecialchars($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Điểm đến -->
      <div class="qs3-field">
        <label for="qs3_to">Điểm đến</label>
        <select id="qs3_to" name="to" required>
          <option value="">Chọn điểm đến</option>
          <?php foreach ($LOCATIONS as $l): ?>
            <option value="<?= htmlspecialchars($l) ?>"><?= htmlspecialchars($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Ngày khởi hành -->
      <div class="qs3-field">
        <label for="qs3_date">Ngày khởi hành</label>
        <input type="date" id="qs3_date" name="date" required />
      </div>

      <!-- Số vé -->
      <div class="qs3-field">
        <label for="qs3_seat">Số vé</label>
        <input type="number" id="qs3_seat" name="seats" min="1" max="10" value="1" required />
      </div>

      <div class="qs3-field" style="align-self:end">
        <button class="qs3-submit" type="submit">Tìm Kiếm</button>
      </div>
    </div>
  </form>
</div>

<script>
(function(){
  const fromSel = document.getElementById('qs3_from');
  const toSel   = document.getElementById('qs3_to');
  const form    = document.getElementById('quickSearch');

  // 1) Không cho chọn điểm đến trùng điểm đi
  function filterToOptions() {
    const fromVal = fromSel.value;
    [...toSel.options].forEach(opt => {
      if (!opt.value) return;                 // bỏ placeholder
      opt.disabled = (opt.value === fromVal);
      if (opt.disabled && toSel.value === opt.value) {
        toSel.value = '';                     // reset nếu đang trùng
      }
    });
  }
  fromSel.addEventListener('change', filterToOptions);
  document.addEventListener('DOMContentLoaded', filterToOptions);

  // 2) Validate nhẹ khi submit
  form.addEventListener('submit', function(e){
    // xoá lỗi cũ
    form.querySelectorAll('.error-msg').forEach(x => x.remove());

    let ok = true;
    const addErr = (el, msg) => {
      const div = document.createElement('div');
      div.className = 'error-msg';
      div.textContent = msg;
      el.closest('.qs3-field').appendChild(div);
      ok = false;
    };

    if (!fromSel.value) addErr(fromSel, 'Vui lòng chọn điểm đi');
    if (!toSel.value)   addErr(toSel,   'Vui lòng chọn điểm đến');
    if (fromSel.value && toSel.value && fromSel.value === toSel.value) {
      addErr(toSel, 'Điểm đến không được trùng điểm đi');
    }

    const d = document.getElementById('qs3_date');
    if (!d.value) addErr(d, 'Vui lòng chọn ngày khởi hành');

    const seats = document.getElementById('qs3_seat');
    if (!seats.value || +seats.value < 1) addErr(seats, 'Số vé tối thiểu là 1');

    if (!ok) e.preventDefault();
  });
})();
</script>
