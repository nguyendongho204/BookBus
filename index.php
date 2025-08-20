<?php
//Configuration
	include "libs/remove-unicode.php";
	//include "libs/PHPExcel/IOFactory.php";
//Session

//Site Redirect	
	$ctrl="ctrls/c_index.php";
	$site="";

	if(isset($_GET["ctrl"]))
		$site=$_GET["ctrl"];

//Division Manage:	
	switch ($site)
	{
		//Main
			case "TrangChu":
				$ctrl="homepage.php";
				break;
			case "DatVe":
				$ctrl="booking.php";
				break;
			case "HuongDan":
				$ctrl="huongdan.php";
				break;
			case "DangKy":
				$ctrl="register.php";
				break;
			case "LienHe":
				$ctrl="lienhe.php";
				break;	
		default:
			$ctrl="homepage.php";
			break;
	}
	
//Head		
	include "header.php";
	
//Body	
	include $ctrl; 
?>

<script>
// ===== MODAL BOOKING SUCCESS - SCRIPT DUY NHẤT =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 BookBus Modal System Loaded');
    
    const urlParams = new URLSearchParams(window.location.search);
    const bookingSuccess = urlParams.get('booking_success');
    const bookingFailed = urlParams.get('booking_failed');
    
    if (bookingSuccess === '1') {
        console.log('✅ Showing success modal...');
        handleBookingSuccess();
    } else if (bookingFailed === '1') {
        console.log('❌ Showing failed modal...');
        handleBookingFailed();
    }
});

function handleBookingSuccess() {
    // Lấy thông tin từ localStorage
    let orderData;
    try {
        orderData = JSON.parse(localStorage.getItem('lastBookingOrder') || '{}');
        console.log('📦 Order data from localStorage:', orderData);
    } catch (e) {
        console.warn('⚠️ localStorage parsing error:', e);
        orderData = {};
    }
    
    // Nếu không có dữ liệu thực, tạo thông báo chung
    if (!orderData.order_id && !orderData.id) {
        orderData = {
            order_id: 'Đã tạo thành công',
            so_luong: 'N/A',
            amount: 0,
            message: 'Đặt vé thành công! Vui lòng kiểm tra lịch sử đặt vé để xem chi tiết.',
            isGeneric: true
        };
    }
    
    showBookingSuccessModal(orderData);
    
    // Clean up
    localStorage.removeItem('lastBookingOrder');
    cleanUrlParams(['booking_success']);
}

function handleBookingFailed() {
    showBookingFailedModal();
    cleanUrlParams(['booking_failed']);
}

function showBookingSuccessModal(orderData) {
    console.log('🎉 Creating success modal with data:', orderData);
    
    const modalHTML = `
        <div class="modal fade" id="bookingSuccessModal" tabindex="-1" style="z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 15px 15px 0 0; border: none;">
                        <h5 class="modal-title">
                            <i class="fa fa-check-circle"></i> Đặt vé thành công!
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeModal('bookingSuccessModal')"></button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <div class="text-center mb-4">
                            <div style="font-size: 80px; animation: bounce 2s infinite;">🎉</div>
                            <h3 class="text-success mt-3">Thanh toán thành công!</h3>
                            <p class="text-muted">
                                ${orderData.message || 'Vé của bạn đã được đặt thành công. Cảm ơn bạn đã sử dụng dịch vụ BookBus!'}
                            </p>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1rem 0;">
                            <div class="row">
                                <div class="col-6"><strong>📋 Mã đơn hàng:</strong></div>
                                <div class="col-6">
                                    <span class="badge bg-primary" style="font-size: 0.9em;">
                                        ${orderData.order_id || orderData.id || 'Đã tạo'}
                                    </span>
                                </div>
                            </div>
                            
                            ${!orderData.isGeneric && orderData.so_luong && orderData.so_luong !== 'N/A' ? `
                                <div class="row mt-2">
                                    <div class="col-6"><strong>🎫 Số lượng vé:</strong></div>
                                    <div class="col-6"><span class="text-info">${orderData.so_luong} vé</span></div>
                                </div>
                            ` : ''}
                            
                            ${!orderData.isGeneric && orderData.amount && orderData.amount > 0 ? `
                                <div class="row mt-2">
                                    <div class="col-6"><strong>💰 Tổng tiền:</strong></div>
                                    <div class="col-6">
                                        <span class="text-success fw-bold" style="font-size: 1.1em;">
                                            ${formatMoney(orderData.amount)} VNĐ
                                        </span>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="row mt-2">
                                <div class="col-6"><strong>✅ Trạng thái:</strong></div>
                                <div class="col-6">
                                    <span class="badge bg-success">Thành công</span>
                                </div>
                            </div>
                        </div>
                        
                        ${!orderData.isGeneric && orderData.ten_nhaxe ? `
                            <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #007bff;">
                                <h6><i class="fa fa-bus text-primary"></i> Thông tin chuyến xe:</h6>
                                <p class="mb-1"><strong>Nhà xe:</strong> ${orderData.ten_nhaxe}</p>
                                <p class="mb-1"><strong>Tuyến đường:</strong> ${orderData.diem_di || ''} → ${orderData.diem_den || ''}</p>
                                <p class="mb-0"><strong>Ngày giờ:</strong> ${orderData.ngay_di || ''} ${orderData.gio_di || ''}</p>
                            </div>
                        ` : ''}
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                Thông tin chi tiết có thể kiểm tra trong lịch sử đặt vé.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding: 1.5rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('bookingSuccessModal')" style="border-radius: 8px;">
                            <i class="fa fa-times"></i> Đóng
                        </button>
                        <a href="/src/tai-khoan/index.php?show=history" class="btn btn-primary" style="border-radius: 8px;">
                            <i class="fa fa-history"></i> Xem lịch sử đặt vé
                        </a>
                        <a href="/src/search_routes.php" class="btn btn-success" style="border-radius: 8px;">
                            <i class="fa fa-plus"></i> Đặt vé khác
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    showModal('bookingSuccessModal');
}

function showBookingFailedModal() {
    const modalHTML = `
        <div class="modal fade" id="bookingFailedModal" tabindex="-1" style="z-index: 9999;">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: #dc3545; color: white; border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title">
                            <i class="fa fa-times-circle"></i> Thanh toán thất bại
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeModal('bookingFailedModal')"></button>
                    </div>
                    <div class="modal-body text-center" style="padding: 2rem;">
                        <div style="font-size: 64px; color: #dc3545; animation: shake 0.5s;">❌</div>
                        <h4 class="text-danger mt-3">Thanh toán không thành công!</h4>
                        <p class="text-muted">Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('bookingFailedModal')">Đóng</button>
                        <a href="/src/search_routes.php" class="btn btn-primary">Thử lại</a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    showModal('bookingFailedModal');
}

// ===== HELPER FUNCTIONS =====
function showModal(modalId) {
    setTimeout(function() {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Sử dụng Bootstrap Modal nếu có
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            modalEl.addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        } else {
            // Fallback manual
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '9998';
            backdrop.id = modalId + '_backdrop';
            document.body.appendChild(backdrop);
            
            backdrop.addEventListener('click', () => closeModal(modalId));
        }
    }, 200);
}

function closeModal(modalId) {
    const modalEl = document.getElementById(modalId);
    const backdropEl = document.getElementById(modalId + '_backdrop');
    
    if (modalEl) {
        modalEl.style.display = 'none';
        modalEl.remove();
    }
    
    if (backdropEl) {
        backdropEl.remove();
    }
    
    document.body.style.overflow = '';
}

function cleanUrlParams(params) {
    const urlParams = new URLSearchParams(window.location.search);
    params.forEach(param => urlParams.delete(param));
    
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
    history.replaceState(null, '', newUrl);
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}
</script>

<style>
/* Modal Animations */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Modal Backdrop for manual mode */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal.show {
    display: block !important;
}
</style>