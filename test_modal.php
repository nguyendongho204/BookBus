<!DOCTYPE html>
<html>
<head>
    <title>Test Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Modal</h2>
        <button class="btn btn-success" onclick="testModal()">Test Success Modal</button>
        
        <script>
        function testModal() {
            const orderData = {
                order_id: 'TEST_ORDER_123',
                so_luong: 2,
                amount: 300000,
                ten_nhaxe: 'Nhà xe Test',
                diem_di: 'Hồ Chí Minh',
                diem_den: 'Đà Lạt',
                ngay_di: '2025-08-25',
                gio_di: '08:00'
            };
            
            showBookingSuccessModal(orderData);
        }
        
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
        </script>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>