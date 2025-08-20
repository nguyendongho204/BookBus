<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang Chủ</title>
  <link rel="stylesheet" href="/src/css/bootstrap.min.css">
  <link rel="stylesheet" href="/src/css/font-awesome.min.css">
  <link rel="stylesheet" href="/src/css/owl.carousel.min.css">
  <link rel="stylesheet" href="/src/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

  <!-- chèn header vào trang chủ -->
<?php include_once('header.php'); ?>

<section class="banner">
  <div id="banner-new" class="owl-carousel">
    <div class="item" style="background: url(images/poster.png) no-repeat center center;">
      <div class="container">
		<div class="row">
		  <div class="col-sm-12 col-md-12 col-lg-12">
			<div class="banner-box">
<!-- chèn form tìm vé xe vào ảnh -->
<?php include 'timvexenhanh.php'; ?>    
			</div>
		  </div>
		</div>
	    </div>
    </div>
  </div>  
</section>

<section class="dv" id="dv">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 col-md-12 col-lg-12">
      
      </div>
    </div>
  </div>
  
  <!-- chèn khuyến mãi -->
  <div class="promo-section text-center" style="padding: 20px 0;">
    <h2 style="color: #007b5e; font-weight: bold;">KHUYẾN MÃI NỔI BẬT</h2>
    <div class="promo-cards d-flex justify-content-center flex-wrap gap-3" style="margin-top: 20px;">
        <img src="images/banner-khuyenmai1.png" class="promo-img" alt="Khuyến mãi 1">
        <img src="images/banner-khuyenmai2.png" class="promo-img" alt="Khuyến mãi 2">
        <img src="images/banner-khuyenmai3.png" class="promo-img" alt="Khuyến mãi 3">
    </div>
</div>
 <!-- hết -->

<!--noi dung đánh giá -->
 <section class="futa-quality">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h2 class="quality-title">#BOOKBUS – AN TOÀN LÀ DANH DỰ</h2>
        <p class="quality-subtitle">Tại sao lại được khách hàng tin tưởng và lựa chọn?   </p>

        <div class="quality-item">
          <img src="images/icon-khach.png" class="quality-icon" alt="Khách">
          <div class="quality-text">
            <strong>Chất Lượng</strong> <span>Lượt khách</span>
            <p>Uy tín, an toàn đặt lên hàng đầu, được nhiều người lựa chọn </p>
          </div>
        </div>

        <div class="quality-item">
          <img src="images/icon-buu.png" class="quality-icon" alt="Bưu cục">
          <div class="quality-text">
            <strong>Vị Trí</strong> <span>Điểm đi - Điểm đến</span>
            <p>Kết nối các quận, huyện nhanh chóng và an toàn.</p>
          </div>
        </div>

        <div class="quality-item">
          <img src="images/icon-xe.png" class="quality-icon" alt="Chuyến xe">
          <div class="quality-text">
            <strong>Chuyến xe</strong>
            <p>BookBus vận hành hàng trăm chuyến xe buýt tuyến cố định, phục vụ người dân di chuyển nội thành thuận tiện, đúng giờ.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 text-center">
        <img src="images/illustration.png" class="img-fluid" alt="Minh họa">
      </div>
    </div>
  </div>
</section>
<!--hết noi dung đánh giá -->


  <div class="container" style="padding-top:50px;">
	
	<div class="row">
          <div class="col-md-12 border-tlr-radius price">
          
          <div class="panel info-card radius blue-fill shadowDepth1">
               <h3 style="text-align: center;">BẢNG GIÁ VÉ XE THEO TUYẾN </h3>
          </div>
          <div class="panel info-card radius shadowDepth1 msg small-class">
            <div style="max-height: 300px; overflow-y: auto;"> 
              <table class="table table-responsive table-bordered price-table">
                  <thead>
                      <tr>
                          <th>Tuyến</th>
                          <th>Khoảng cách</th>
                          <th>Giá Vé </th>
                          <th>Thời Gian</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <td>Cần Thơ ⇔ Ô Môn</td>
                          <td>23 km</td>
                          <td>5.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>


                      <tr>
                          <td>Cần Thơ ⇔ Phong Điền</td>
                          <td>34 km</td>
                          <td>7.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
					  
					   <tr>
                          <td>Cần Thơ ⇔ Vĩnh Long</td>
                          <td>50 km</td>
                          <td>15.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
					   <tr>
                          <td>Cần Thơ ⇔ Kinh Cùng</td>
                          <td>35 km</td>
                          <td>7.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
					   <tr>
                          <td>Cần Thơ ⇔ Cái Tắc</td>
                          <td>40 km</td>
                          <td>10.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
                       <tr>
                          <td>Cần Thơ ⇔ Thốt Nốt</td>
                          <td>45 km</td>
                          <td>12.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
              <tr>
                          <td>Cần Thơ ⇔ Cờ Đỏ</td>
                          <td>38 km</td>
                          <td>10.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
              <tr>
                          <td>Cần Thơ ⇔ Vĩnh Thạnh</td>
                          <td>62 km</td>
                          <td>15.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
              <tr>
                          <td>Cần Thơ ⇔ Bình Tân</td>
                          <td>48 km</td>
                          <td>12.000(VND/lượt)</td>  
                          <td>.</td>
                      </tr>
                  </tbody>
              </table>
              </div>
              <br>
              <ul class="ul-circle"></ul>
          </div>
        </div>
      </div>
  </div>
               <h3 style="text-align: center;"> (Trẻ em dưới 6 tuổi hoặc cao dưới 1m2 được miễn phí vé khi ngồi cùng người lớn).</h3>

</section>

<?php include_once('footer.php'); ?>
