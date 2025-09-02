<!-- Accepted bookings will be loaded here dynamically -->
<div id="booking-accepted-container" style="max-height: 80vh; overflow-y: auto;">
  <div class="text-center p-4" >
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">Loading accepted bookings...</p>
  </div>
</div>

<script>
  $(document).ready(function () {
    loadAcceptedBookings();
    
    // Handle Manage Services button click
    $(document).on('click', '.btn-manage-services', function (e) {
      e.preventDefault();
      const bookingid = $(this).data('bookingid');
      console.log('🔧 Opening service management for booking:', bookingid);
      showGlobalModal('../modal/admin_modal-booking-services.php', { bookingid: bookingid });
    });

    // Handle Complete Booking button click
    $(document).on('click', '.btn-complete-booking', function (e) {
      e.preventDefault();
      const bookingid = $(this).data('bookingid');
      const userName = $(this).data('username');
      
      if (confirm(`Are you sure you want to mark ${userName}'s booking as completed? This will move it to appointment history.`)) {
        completeBooking(bookingid, $(this));
      }
    });
  });
  
  function loadAcceptedBookings() {
    console.log('Loading accepted bookings...');
    $.ajax({
      url: '../controller/booking_contr.php',
      type: 'POST',
      data: { action: 'get_admin_booking_accepted' },
      dataType: 'json',
      success: function(response) {
        console.log('Accepted bookings response:', response);
        
        // Check if we have bookings in the response
        if (response && response.bookings && Array.isArray(response.bookings)) {
          renderAcceptedBookings(response.bookings);
        } else if (response && response.status === 'nodata') {
          // Handle no data case
          $('#booking-accepted-container').html(`
            <div class="text-center p-4">
              <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
              <h5 class="mt-3 text-muted">No Accepted Bookings</h5>
              <p class="text-muted">There are no accepted bookings at the moment.</p>
            </div>
          `);
        } else {
          // Handle other response formats
          renderAcceptedBookings(response);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error loading accepted bookings:', error);
        console.error('Response text:', xhr.responseText);
        $('#booking-accepted-container').html(`
          <div class="alert alert-danger text-center">
            <i class="bi bi-exclamation-triangle"></i>
            Failed to load accepted bookings. Please try again.
          </div>
        `);
      }
    });
  }
  
  function renderAcceptedBookings(bookings) {
    const container = $('#booking-accepted-container');
    
    console.log('Accepted bookings data:', bookings);
    
    // Check if bookings is an object with status property
    if (bookings && bookings.status === 'nodata') {
      container.html(`
        <div class="text-center p-4">
          <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
          <h5 class="mt-3 text-muted">No Accepted Bookings</h5>
          <p class="text-muted">There are no accepted bookings at the moment.</p>
        </div>
      `);
      return;
    }
    
    // Check if bookings is an array with length 0
    if (!bookings || !Array.isArray(bookings) || bookings.length === 0) {
      container.html(`
        <div class="text-center p-4">
          <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
          <h5 class="mt-3 text-muted">No Accepted Bookings</h5>
          <p class="text-muted">There are no accepted bookings at the moment.</p>
        </div>
      `);
      return;
    }
    
    let html = '';
    bookings.forEach(booking => {
      const servicesText = booking.services.map(s => s.name).join(', ');
      const bookingDate = new Date(booking.booking_date).toLocaleDateString();
      
      html += `
        <div class="container-sm bg-white rounded-3 p-3 shadow-sm mb-3">
          <!-- Desktop Version -->
          <div class="row g-3 align-items-center d-none d-md-flex">
            <!-- Image -->
            <div class="col-auto d-flex align-items-center justify-content-center" style="height:100%; min-height:80px;">
              <i class="bi bi-person-circle user-avatar" style="font-size:4.5rem; min-height:72px; min-width:72px; display:flex; align-items:center; justify-content:center;"></i>
            </div>
            <!-- Info Text -->
            <div class="col">
              <div class="fw-semibold user-name">${booking.user_name}</div>
              <div class="small text-muted mb-1">Booking ID: #${booking.bookingid}</div>
              <div class="d-flex flex-wrap gap-2 small mt-1">
                <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-briefcase me-1"></i>${servicesText}</span>
                <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-calendar-event me-1"></i>${bookingDate}</span>
                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-currency-dollar me-1"></i>₱${booking.total_price}</span>
                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
              </div>
            </div>
            <!-- Action Buttons -->
            <div class="col-auto d-flex flex-row gap-2">
              <button class="btn btn-primary btn-manage-services px-4" data-bookingid="${booking.bookingid}">
                <i class="bi bi-list-check me-1"></i>
                Manage Services
              </button>
              <button class="btn btn-success btn-complete-booking px-4" data-bookingid="${booking.bookingid}" data-username="${booking.user_name}">
                <i class="bi bi-check-circle me-1"></i>
                Complete
              </button>
            </div>
          </div>
          <!-- Mobile/Tablet Compact Version -->
          <div class="d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 d-flex d-md-none">
            <!-- Image -->
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center" style="min-width:56px;">
              <i class="bi bi-person-circle user-avatar-compact"></i>
            </div>
            <!-- Info Text -->
            <div class="flex-grow-1 text-center text-sm-start">
              <div class="fw-semibold user-name-compact">${booking.user_name}</div>
              <div class="small text-muted">Booking ID: #${booking.bookingid}</div>
              <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-2 small mt-1">
                <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-briefcase me-1"></i>${servicesText}</span>
                <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-calendar-event me-1"></i>${bookingDate}</span>
                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-currency-dollar me-1"></i>₱${booking.total_price}</span>
                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
              </div>
            </div>
            <!-- Action Buttons -->
            <div class="d-flex flex-row flex-sm-column gap-1 ms-sm-2 mt-2 mt-sm-0">
              <button class="btn btn-primary btn-manage-services btn-sm px-3" data-bookingid="${booking.bookingid}">
                <i class="bi bi-list-check me-1"></i>
                Services
              </button>
              <button class="btn btn-success btn-complete-booking btn-sm px-3" data-bookingid="${booking.bookingid}" data-username="${booking.user_name}">
                <i class="bi bi-check-circle me-1"></i>
                Complete
              </button>
            </div>
          </div>
        </div>
      `;
    });
    
    container.html(html);
  }

  function completeBooking(bookingid, button) {
    const originalText = button.text();
    const bookingCard = $(`#booking-${bookingid}`);
    
    // Disable all buttons in this booking card
    bookingCard.find('button').prop('disabled', true);
    button.text('Completing...').addClass('btn-warning').removeClass('btn-success');
    
    $.ajax({
      url: '../controller/booking_contr.php',
      type: 'POST',
      data: { 
        action: 'complete_booking',
        bookingid: bookingid
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          // Show success animation
          bookingCard.addClass('border-success').css('background-color', '#d4edda');
          
          // Show success message
          alert('✅ Booking completed successfully! It has been moved to appointment history.');
          
          // Remove the booking card with animation after 1 second
          setTimeout(() => {
            bookingCard.fadeOut(500, function() {
              $(this).remove();
              // Check if no more bookings exist
              if ($('#booking-accepted-container .container-sm').length === 0) {
                $('#booking-accepted-container').html(`
                  <div class="text-center p-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="mt-3 text-muted">No Accepted Bookings</h5>
                    <p class="text-muted">There are no accepted bookings at the moment.</p>
                  </div>
                `);
              }
            });
          }, 1000);
        } else {
          alert('❌ Failed to complete booking. Please try again.');
          // Restore button state
          bookingCard.find('button').prop('disabled', false);
          button.text(originalText).removeClass('btn-warning').addClass('btn-success');
        }
      },
      error: function() {
        alert('❌ Network error. Please try again.');
        // Restore button state
        bookingCard.find('button').prop('disabled', false);
        button.text(originalText).removeClass('btn-warning').addClass('btn-success');
      }
    });
  }
</script>


<style>
  .user-avatar-compact {
    font-size: 2.2rem;
    border-radius: 50%;
    background: #f3f3f3;
    padding: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    color: #888;
  }
  .user-name-compact {
    font-size: 1.08rem;
    letter-spacing: 0.2px;
    margin-bottom: 2px;
  }
  .user-avatar {
    font-size: 4.5rem;
    min-height: 72px;
    min-width: 72px;
    border-radius: 50%;
    background: #f3f3f3;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    color: #888;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .user-name {
    font-size: 1.18rem;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
  }
  @media (max-width: 767.98px) {
    .user-avatar {
      font-size: 48px !important;
      margin-bottom: 8px;
    }
    .user-name {
      font-size: 1.1rem !important;
    }
    .container-sm {
      padding: 1rem 0.5rem !important;
    }
    .row.g-3 {
      gap: 0.5rem !important;
    }
    .btn {
      font-size: 0.95rem;
      padding-left: 1.5rem !important;
      padding-right: 1.5rem !important;
    }
  }
  @media (min-width: 768px) {
    .col-auto.d-flex.flex-row.gap-2 {
      flex-direction: row !important;
      align-items: center !important;
      justify-content: flex-end !important;
    }
  }
  @media (max-width: 575.98px) {
    .user-avatar-compact {
      font-size: 1.6rem;
      padding: 4px;
    }
    .user-name-compact {
      font-size: 1rem;
    }
    .user-avatar {
      font-size: 1.6rem !important;
      padding: 4px;
    }
    .user-name {
      font-size: 1rem !important;
    }
    .container-sm {
      padding: 0.5rem 0.2rem !important;
    }
    .btn {
      font-size: 0.9rem;
      padding-left: 1rem !important;
      padding-right: 1rem !important;
    }
    .badge {
      font-size: 0.93rem;
      padding: 0.35em 0.7em;
      display: block;
      margin: 0.1rem auto;
      width: fit-content;
    }
    .row.g-3 {
      gap: 0.3rem !important;
    }
    .col-auto.d-flex.flex-column.gap-2 {
      flex-direction: row !important;
      gap: 0.5rem !important;
      margin-top: 0.5rem !important;
      justify-content: center !important;
    }
    .col {
      text-align: center !important;
    }
  }
</style>