<?php
include "header.php";
?>
<style>
    .tieude {
  text-align: center; /* căn giữa tiêu đề */
  font-size: 32px; /* chữ to */
  font-weight: bold;
  margin: 30px 0 20px;
  color: red;
}

.step-title {
  font-size: 20px; /* to vừa, nhỏ hơn tiêu đề chính */
  font-weight: 600;
  margin: 25px 0 10px;
  line-height: 1.35;
  color: #1d3d71;
}
</style>
<div class="container" style="max-width:900px; margin:40px auto; line-height:1.6;">

    <!-- Tiêu đề chính -->
    <h1 class="mb-4 tieude"><b>Hướng dẫn đặt vé trực tuyến</b></h1>

    <div class="step">
        <h3 class="step-title"><b>Bước 1: Truy cập trang chủ</b></h3>
        <p>Vào website <b>BookBus</b> → chọn mục <i>“Đặt vé Online”</i>.</p>
        <img src="images/huongdan_step1.png" alt="Trang chủ BookBus" style="width:100%;border:1px solid #ccc; margin:10px 0;">
    </div>

    <div class="step">
        <h3 class="step-title"><b>Bước 2: Tìm chuyến xe</b></h3>
        <p>Chọn <b>điểm đi</b>, <b>điểm đến</b>, <b>ngày khởi hành</b> và <b>số vé</b> → bấm <b>Tìm kiếm</b>.</p>
        <img src="images/huongdan_step2.png" alt="Tìm chuyến xe" style="width:100%;border:1px solid #ccc; margin:10px 0;">
    </div>

    <div class="step">
        <h3 class="step-title"><b>Bước 3: Chọn chuyến xe</b></h3>
        <p>Xem danh sách chuyến → nhấn <b>Đặt vé</b> ở chuyến bạn muốn đi.</p>
        <img src="images/huongdan_step3.png" alt="Chọn chuyến xe" style="width:100%;border:1px solid #ccc; margin:10px 0;">
    </div>

    <div class="step">
        <h3 class="step-title"><b>Bước 4: Nhập thông tin hành khách</b></h3>
        <p>Điền đầy đủ họ tên, số điện thoại, email để xác nhận vé.</p>
        <img src="images/huongdan_step4.png" alt="Thông tin hành khách" style="width:100%;border:1px solid #ccc; margin:10px 0;">
    </div>

    <div class="step">
        <h3 class="step-title"><b>Bước 5: Thanh toán</b></h3>
        <p><b>Chọn hình thức thanh toán:</b></p>
        <ul>
            <li>Quét mã QR và thanh toán Ví điện tử (Momo, ZaloPay, Ngân hàng...)</li>
            <li>Thanh toán khi lên xe (nếu có hỗ trợ)</li>
        </ul>
        <img src="images/huongdan_step5.png" alt="Thanh toán" style="width:100%;border:1px solid #ccc; margin:10px 0;">
    </div>

    <hr>
    <h3>Cần hỗ trợ thêm?</h3>
    <p>Liên hệ qua mục <a href="lienhe.php"><b>Liên hệ</b></a> hoặc gọi hotline: <b>1900 1234</b>.</p>
</div>

<?php
include "footer.php";
?>
