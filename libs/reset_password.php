<form action="libs/update_password.php" method="POST">
    <input type="hidden" name="phone" value="<?php echo $_GET['phone']; ?>">
    <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" required>
    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
    <button type="submit">Cập nhật mật khẩu</button>
</form>
