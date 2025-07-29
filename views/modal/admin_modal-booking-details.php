<!-- Only the inner modal-content part -->
<div class="modal-header">
  <h5 class="modal-title">Booking Details</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <div id="booking-details-content">
    <div class="text-center p-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading booking details...</p>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
  <div id="booking-actions">
    <!-- Action buttons will be loaded here -->
  </div>
</div>

<script>
$(document).ready(function() {
  // Load booking details when modal data is available
  if (window.modalData && window.modalData.bookingid) {
    loadBookingDetails(window.modalData.bookingid);
  }
});

function loadBookingDetails(bookingid) {
  $.ajax({
    url: '../controller/booking_contr.php',
    type: 'POST',
    data: { 
      action: 'get_booking_details_admin',
      bookingid: bookingid
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'error') {
        $('#booking-details-content').html(`
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            ${response.message || 'Failed to load booking details'}
          </div>
        `);
        return;
      }
      
      renderBookingDetails(response);
    },
    error: function(xhr, status, error) {
      console.error('Error loading booking details:', error);
      $('#booking-details-content').html(`
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i>
          Failed to load booking details. Please try again.
        </div>
      `);
    }
  });
}

function renderBookingDetails(booking) {
  const bookingDate = new Date(booking.booking_date).toLocaleDateString();
  const bookingTime = new Date(booking.booking_date).toLocaleTimeString();
  
  let servicesHtml = '';
  booking.services.forEach(service => {
    servicesHtml += `
      <div class="border rounded p-3 mb-2">
        <h6 class="mb-1">${service.name}</h6>
        <p class="text-muted small mb-1">${service.description}</p>
        <div class="row">
          <div class="col-sm-4"><small><strong>Duration:</strong> ${service.duration} min</small></div>
          <div class="col-sm-4"><small><strong>Quantity:</strong> ${service.quantity}</small></div>
          <div class="col-sm-4"><small><strong>Price:</strong> ₱${service.price}</small></div>
        </div>
      </div>
    `;
  });
  
  const statusBadge = booking.booking_status === 'Pending' ? 
    '<span class="badge bg-warning text-dark">Pending</span>' :
    booking.booking_status === 'Confirmed' ?
    '<span class="badge bg-success">Confirmed</span>' :
    '<span class="badge bg-danger">Rejected</span>';
  
  $('#booking-details-content').html(`
    <div class="row">
      <div class="col-md-6">
        <h6>Customer Information</h6>
        <p><strong>Name:</strong> ${booking.user_name}</p>
        <p><strong>Email:</strong> ${booking.user_email}</p>
        <p><strong>Phone:</strong> ${booking.user_phone}</p>
      </div>
      <div class="col-md-6">
        <h6>Booking Information</h6>
        <p><strong>Booking ID:</strong> #${booking.bookingid}</p>
        <p><strong>Date:</strong> ${bookingDate}</p>
        <p><strong>Time:</strong> ${bookingTime}</p>
        <p><strong>Status:</strong> ${statusBadge}</p>
        <p><strong>Total Amount:</strong> <span class="text-success fw-bold">₱${booking.total_price}</span></p>
      </div>
    </div>
    
    <hr>
    
    <h6>Services Requested</h6>
    ${servicesHtml}
    
    ${booking.payment_img ? `
      <hr>
      <h6>Payment Receipt</h6>
      <div class="text-center">
        <img src="data:image/png;base64,${booking.payment_img}" 
             class="img-fluid rounded border" 
             style="max-height: 300px; cursor: pointer;"
             onclick="window.open(this.src, '_blank')"
             alt="Payment Receipt">
        <p class="small text-muted mt-2">Click image to view full size</p>
      </div>
    ` : ''}
  `);
  
  // Show action buttons only for pending bookings
  if (booking.booking_status === 'Pending') {
    $('#booking-actions').html(`
      <button type="button" class="btn btn-danger me-2" onclick="declineBookingFromModal(${booking.bookingid})">
        <i class="bi bi-x-circle"></i> Decline
      </button>
      <button type="button" class="btn btn-success" onclick="acceptBookingFromModal(${booking.bookingid})">
        <i class="bi bi-check-circle"></i> Accept
      </button>
    `);
  } else {
    $('#booking-actions').html('');
  }
}

function acceptBookingFromModal(bookingid) {
  if (confirm('Are you sure you want to accept this booking?')) {
    $.ajax({
      url: '../controller/booking_contr.php',
      type: 'POST',
      data: { 
        action: 'accept_booking',
        bookingid: bookingid
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          alert('✅ Booking accepted successfully!');
          $('#globalModal').modal('hide');
          // Reload the booking requests page if it exists
          if (typeof loadBookingRequests === 'function') {
            loadBookingRequests();
          }
        } else {
          alert('❌ Failed to accept booking. Please try again.');
        }
      },
      error: function() {
        alert('❌ Network error. Please try again.');
      }
    });
  }
}

function declineBookingFromModal(bookingid) {
  if (confirm('Are you sure you want to decline this booking?')) {
    $.ajax({
      url: '../controller/booking_contr.php',
      type: 'POST',
      data: { 
        action: 'decline_booking',
        bookingid: bookingid
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          alert('✅ Booking declined successfully!');
          $('#globalModal').modal('hide');
          // Reload the booking requests page if it exists
          if (typeof loadBookingRequests === 'function') {
            loadBookingRequests();
          }
        } else {
          alert('❌ Failed to decline booking. Please try again.');
        }
      },
      error: function() {
        alert('❌ Network error. Please try again.');
      }
    });
  }
}
</script>
