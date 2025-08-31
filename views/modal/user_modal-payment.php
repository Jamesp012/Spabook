<div class="modal-header">
  <h5 class="modal-title">
    <i class="bi bi-credit-card me-2"></i>Payment & Booking Confirmation
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <!-- Booking Summary -->
  <div class="card mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">
        <i class="bi bi-receipt me-1"></i>Your Booking Summary
      </h6>
    </div>
    <div class="card-body" id="finalBookingSummary">
      <!-- Booking summary will be populated here -->
    </div>
  </div>

  <!-- Payment Instructions -->
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Payment Instructions:</strong><br>
    Please complete your payment and upload the receipt below to confirm your booking.
  </div>

  <form id="paymentForm" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="receiptUpload" class="form-label fw-semibold">
        <i class="bi bi-cloud-upload me-1"></i>Payment Receipt
      </label>
      <input class="form-control" type="file" id="receiptUpload" name="receipt" accept="image/*" required>
      <div class="form-text">Upload a clear photo or screenshot of your payment receipt.</div>
    </div>
    
    <div id="previewContainer" class="mb-3 d-none">
      <label class="form-label">Preview:</label><br>
      <img id="receiptPreview" src="#" alt="Preview" class="img-fluid rounded border" style="max-height: 200px;">
    </div>
  </form>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="button" class="btn btn-success" id="submitBookingBtn" disabled>
    <i class="bi bi-check-circle me-1"></i>Confirm Booking & Payment
  </button>
</div>

<script>
$(document).ready(function() {
    // Initialize payment modal
    setTimeout(() => {
        initializePaymentModal();
    }, 100);
});

var bookingData = null;

function initializePaymentModal() {
    // Get booking data from window
    bookingData = window.pendingBookingData;
    
    if (!bookingData || !bookingData.services || bookingData.services.length === 0) {
        $('#finalBookingSummary').html(`
            <div class="text-center py-3 text-danger">
                <i class="bi bi-exclamation-circle fs-1 mb-3"></i>
                <h5>No Booking Data Found</h5>
                <p>Please go back and complete your booking selection.</p>
            </div>
        `);
        $('#submitBookingBtn').prop('disabled', true);
        return;
    }
    
    // Display booking summary
    displayFinalBookingSummary();
    
    // Handle file upload preview
    $('#receiptUpload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#receiptPreview').attr('src', e.target.result);
                $('#previewContainer').removeClass('d-none');
                $('#submitBookingBtn').prop('disabled', false);
            };
            reader.readAsDataURL(file);
        } else {
            $('#previewContainer').addClass('d-none');
            $('#submitBookingBtn').prop('disabled', true);
        }
    });
    
    // Handle booking submission
    $('#submitBookingBtn').on('click', function() {
        submitBookingWithPayment();
    });
}

function displayFinalBookingSummary() {
    let summaryHtml = '';
    let totalAmount = 0;
    
    bookingData.services.forEach((service) => {
        const serviceTotal = service.price * service.people;
        totalAmount += serviceTotal;
        
        // Format date and time
        const bookingDate = formatDate(service.selectedDate);
        const bookingTime = formatTime(service.selectedTime);
        
        // Format therapist assignments
        let therapistAssignments = '';
        if (service.selectedTherapists && service.selectedTherapists.length > 0) {
            service.selectedTherapists.forEach(assignment => {
                therapistAssignments += `
                    <div class="text-success small">
                        <i class="bi bi-person-check me-1"></i>Person ${assignment.person}: ${assignment.therapistName}
                    </div>
                `;
            });
        } else {
            therapistAssignments = `
                <div class="text-muted small">
                    <i class="bi bi-person me-1"></i>Therapist will be assigned automatically
                </div>
            `;
        }
        
        summaryHtml += `
            <div class="booking-service-item border-start border-3 border-primary ps-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${service.serviceName}</h6>
                        <div class="text-muted small mb-2">
                            <div><i class="bi bi-people me-1"></i>${service.people} person(s) × ₱${service.price}</div>
                            <div><i class="bi bi-calendar me-1"></i>${bookingDate}</div>
                            <div><i class="bi bi-clock me-1"></i>${bookingTime}</div>
                        </div>
                        ${therapistAssignments}
                    </div>
                    <div class="text-end">
                        <span class="h6 text-success">₱${serviceTotal}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    summaryHtml += `
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Total Amount:</h5>
            <h5 class="mb-0 text-success">₱${totalAmount}</h5>
        </div>
    `;
    
    $('#finalBookingSummary').html(summaryHtml);
}

function submitBookingWithPayment() {
    const receiptFile = $('#receiptUpload')[0].files[0];
    
    if (!receiptFile) {
        Swal.fire({
            icon: 'warning',
            title: 'Receipt Required',
            text: 'Please upload your payment receipt before submitting.',
        });
        return;
    }
    
    // Convert receipt to base64
    const reader = new FileReader();
    reader.onload = function(e) {
        const receiptBase64 = e.target.result;
        
        // Prepare booking data for submission with correct field names
        const servicesForSubmission = bookingData.services.map(service => ({
            id: service.serviceId,
            name: service.serviceName,
            people: service.people,
            price: service.price,
            selectedDate: service.selectedDate,
            selectedTime: service.selectedTime,
            therapists: service.selectedTherapists || []
        }));
        
        const submissionData = {
            action: 'create_booking',
            user_id: sessionStorage.getItem('user_id'),
            total_price: bookingData.totalAmount,
            payment_img: receiptBase64,
            services: JSON.stringify(servicesForSubmission)
        };
        
        // Show loading
        Swal.fire({
            title: 'Processing Your Booking...',
            html: `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 100px;">
                    <div class="spinner-border text-primary me-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div>
                        <div class="fw-semibold">Creating your booking</div>
                        <div class="text-muted small">Please wait while we process your request...</div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
        
        // Submit booking
        $.ajax({
            url: '../controller/booking_contr.php',
            type: 'POST',
            data: submissionData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Clear the cart
                    window.serviceCart = [];
                    if (typeof window.updateCheckoutBadge === 'function') {
                        window.updateCheckoutBadge();
                    }
                    
                    // Clear pending booking data
                    window.pendingBookingData = null;
                    
                    // Close modal
                    $('#globalModal').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Submitted Successfully!',
                        html: `
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                </div>
                                <p>Your booking has been submitted and is now pending approval.</p>
                                <p class="small text-muted">
                                    Booking ID: <strong>#${response.bookingid}</strong><br>
                                    You will receive a confirmation once your booking is approved.
                                </p>
                            </div>
                        `,
                        confirmButtonText: 'View My Bookings',
                        showCancelButton: true,
                        cancelButtonText: 'Continue Browsing'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to bookings page or refresh current data
                            if (typeof window.refreshBookingData === 'function') {
                                window.refreshBookingData();
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Failed',
                        text: response.message || 'Something went wrong. Please try again.',
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Unable to submit booking. Please check your connection and try again.',
                });
            }
        });
    };
    
    reader.readAsDataURL(receiptFile);
}

function formatDate(dateString) {
    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    return new Date(`2000-01-01 ${timeString}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}
</script>

<style>
.booking-service-item {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}

#receiptPreview {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

@media (max-width: 576px) {
    .booking-service-item {
        padding: 0.75rem;
    }
    
    .modal-body {
        padding: 1rem 0.75rem;
    }
}
</style>
