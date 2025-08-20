<?php include("header.php"); ?>
<section class="dv" id="dv">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 col-md-12 col-lg-12">
        <h1 class="title text-center">ĐĂNG KÝ MỞ ĐẠI LÝ BÁN VÉ ONLINE</h1>
        <form method="POST" action="dangky.php">
  <div class="form-group">
    <label>Họ tên:</label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Email:</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Mật khẩu:</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button type="submit" name="register" class="btn btn-primary">Đăng ký</button>
</form>

      