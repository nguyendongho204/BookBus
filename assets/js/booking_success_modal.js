// /src/assets/js/booking_success_modal.js
function showBookingSuccessModal(orderData) {
    const modalHTML = `
        <div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-labelledby="bookingSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="bookingSuccessModalLabel">
                            <i class="fa fa-check-circle"></i> Đặt vé thành công!
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="success-icon">
                                <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
                            </div>
                            <h4 class="text-success mt-3">Thanh toán thành công!</h4>
                            <p class="text-muted">Vé của bạn đã được đặt thành công. Vui lòng kiểm tra email để nhận thông tin chi tiết.</p>
                        </div>
                        
                        <div class="booking-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <strong>Mã đơn hàng:</strong>
                                        <span class="badge bg-primary ms-2">${orderData.order_id || 'N/A'}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <strong>Số lượng vé:</strong>
                                        <span class="text-info">${orderData.so_luong || 1} vé</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <strong>Tổng tiền:</strong>
                                        <span class="text-success fw-bold">${formatMoney(orderData.amount || 0)} VNĐ</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <strong>Trạng thái:</strong>
                                        <span class="badge bg-success">Thành công</span>
                                    </div>
                                </div>
                            </div>
                            
                            ${orderData.ten_nhaxe ? `
                                <div class="mt-3 p-3 bg-light rounded">
                                    <h6><i class="fa fa-bus"></i> Thông tin chuyến xe:</h6>
                                    <p class="mb-1"><strong>Nhà xe:</strong> ${orderData.ten_nhaxe}</p>
                                    <p class="mb-1"><strong>Tuyến:</strong> ${orderData.diem_di} → ${orderData.diem_den}</p>
                                    <p class="mb-0"><strong>Ngày đi:</strong> ${orderData.ngay_di} lúc ${orderData.gio_di}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa fa-times"></i> Đóng
                        </button>
                        <a href="/src/tai-khoan/index.php?show=history" class="btn btn-primary">
                            <i class="fa fa-ticket"></i> Xem lịch sử đặt vé
                        </a>
                        <a href="/src/search_routes.php" class="btn btn-success">
                            <i class="fa fa-plus"></i> Đặt vé khác
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Thêm modal vào body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('bookingSuccessModal'));
    modal.show();
    
    // Xóa modal sau khi đóng
    document.getElementById('bookingSuccessModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

// Kiểm tra URL có thông báo thành công không
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingSuccess = urlParams.get('booking_success');
    
    if (bookingSuccess === '1') {
        // Lấy thông tin đơn hàng từ localStorage hoặc API
        const orderData = JSON.parse(localStorage.getItem('lastBookingOrder') || '{}');
        showBookingSuccessModal(orderData);
        
        // Xóa thông tin đơn hàng khỏi localStorage
        localStorage.removeItem('lastBookingOrder');
        
        // Xóa param khỏi URL
        urlParams.delete('booking_success');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        history.replaceState(null, '', newUrl);
    }
});
// Thêm vào cuối file booking_success_modal.js
function showBookingFailedModal() {
    const modalHTML = `
        <div class="modal fade booking-failed-modal" id="bookingFailedModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-times-circle"></i> Thanh toán thất bại
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="failed-icon mb-3">
                            <i class="fa fa-times-circle" style="font-size: 64px;"></i>
                        </div>
                        <h4 class="text-danger">Thanh toán không thành công!</h4>
                        <p class="text-muted">Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <a href="/src/search_routes.php" class="btn btn-primary">Thử lại</a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('bookingFailedModal'));
    modal.show();
    
    document.getElementById('bookingFailedModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Cập nhật DOMContentLoaded listener
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingSuccess = urlParams.get('booking_success');
    const bookingFailed = urlParams.get('booking_failed');
    
    if (bookingSuccess === '1') {
        const orderData = JSON.parse(localStorage.getItem('lastBookingOrder') || '{}');
        showBookingSuccessModal(orderData);
        localStorage.removeItem('lastBookingOrder');
        
        urlParams.delete('booking_success');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        history.replaceState(null, '', newUrl);
    } else if (bookingFailed === '1') {
        showBookingFailedModal();
        
        urlParams.delete('booking_failed');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        history.replaceState(null, '', newUrl);
    }
});