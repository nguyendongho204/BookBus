document.getElementById("otpForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Ngăn form tải lại trang

    var phone = document.getElementById("phoneInput").value;

    fetch("libs/send_otp.php", {
        method: "POST",
        body: new URLSearchParams({ phone: phone }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(response => response.json()).then(data => {
        if (data.success) {
            document.getElementById("otpPhone").value = phone;
            document.getElementById("otpModal").style.display = "block"; // Hiển thị modal nhập OTP
        } else {
            alert(data.message); // Hiển thị thông báo nếu số điện thoại sai
        }
    });
});
