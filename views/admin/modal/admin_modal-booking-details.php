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

// Function to show image debug information
function showImageDebugInfo(encodedData) {
  try {
    const data = JSON.parse(atob(encodedData));
    const debugInfo = `
      <div class="modal fade" id="imageDebugModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Image Debug Information</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <strong>Data Type:</strong> ${data.type}
              </div>
              <div class="mb-3">
                <strong>Data Length:</strong> ${data.length} characters
              </div>
              <div class="mb-3">
                <strong>Data Preview:</strong>
                <pre class="bg-light p-2 mt-1" style="overflow-x: auto;">${data.preview}</pre>
              </div>
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                This information can help diagnose why the image isn't displaying properly.
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Remove any existing debug modal
    $('#imageDebugModal').remove();
    
    // Add the modal to the page
    $('body').append(debugInfo);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('imageDebugModal'));
    modal.show();
  } catch (e) {
    console.error('Error showing debug info:', e);
    alert('Error showing debug information');
  }
}

function loadBookingDetails(bookingid) {
  console.log('Loading booking details for ID:', bookingid);
  
  if (!bookingid) {
    console.error('No booking ID provided');
    $('#booking-details-content').html(`
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No booking ID provided
      </div>
    `);
    return;
  }
  
  // Show loading state
  $('#booking-details-content').html(`
    <div class="text-center p-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading booking details...</p>
    </div>
  `);
  
  // Add a timestamp to prevent caching
  const timestamp = new Date().getTime();
  
  $.ajax({
    url: '../../../controller/booking_contr.php',
    type: 'POST',
    data: { 
      action: 'get_booking_details_admin',
      bookingid: bookingid,
      _: timestamp // Add timestamp to prevent caching
    },
    dataType: 'json',
    cache: false, // Disable caching
    timeout: 30000, // 30 second timeout
    success: function(response) {
      console.log('Booking details response:', response);
      
      if (!response) {
        $('#booking-details-content').html(`
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Empty response received from server
          </div>
        `);
        return;
      }
      
      if (response.status === 'error') {
        $('#booking-details-content').html(`
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${response.message || 'Failed to load booking details'}
          </div>
        `);
        return;
      }
      
      renderBookingDetails(response);
    },
    error: function(xhr, status, error) {
      console.error('Error loading booking details:', error);
      console.error('Status:', status);
      console.error('Response text:', xhr.responseText);
      
      // Try to parse the error response
      let errorMessage = error || 'Unknown error';
      try {
        if (xhr.responseText) {
          const errorResponse = JSON.parse(xhr.responseText);
          console.error('Parsed error response:', errorResponse);
          if (errorResponse.message) {
            errorMessage = errorResponse.message;
          }
        }
      } catch (e) {
        console.error('Could not parse error response as JSON');
      }
      
      $('#booking-details-content').html(`
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>
          Failed to load booking details. Please try again.
          <div class="mt-2 small text-muted">Error: ${errorMessage}</div>
          <div class="mt-2 small text-muted">Status: ${status}</div>
        </div>
      `);
    }
  });
}

// Helper function to display payment image
function displayPaymentImage(imageData) {
  console.log('Payment image data type:', typeof imageData);
  console.log('Payment image data length:', imageData ? imageData.length : 0);
  
  if (!imageData) {
    console.log('No image data provided');
    return `<div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              No payment receipt image available
            </div>`;
  }
  
  // Handle the specific case of double data URL prefix
  if (typeof imageData === 'string' && imageData.startsWith('data:image/png;base64,data:image/')) {
    console.log('Detected double data URL prefix, fixing...');
    // Extract the actual base64 data after the second data: prefix
    const secondDataUrlIndex = imageData.indexOf('data:', 5);
    if (secondDataUrlIndex !== -1) {
      const secondDataUrl = imageData.substring(secondDataUrlIndex);
      return `<img src="${secondDataUrl}" 
                   class="img-fluid rounded border" 
                   style="max-height: 300px; cursor: pointer;"
                   onclick="window.open(this.src, '_blank')"
                   alt="Payment Receipt">`;
    }
  }
  
  // If the image is a file path (starts with uploads/)
  if (typeof imageData === 'string' && imageData.includes('uploads/')) {
    console.log('Image appears to be a file path:', imageData);
    // Ensure the path is properly formatted
    const imagePath = imageData.startsWith('/') ? imageData : '/' + imageData;
    return `<img src="../../../${imagePath}" 
                 class="img-fluid rounded border" 
                 style="max-height: 300px; cursor: pointer;"
                 onclick="window.open('../../../${imagePath}', '_blank')"
                 alt="Payment Receipt">`;
  }
  
  // If the image is already a URL
  if (typeof imageData === 'string' && (imageData.startsWith('http://') || imageData.startsWith('https://'))) {
    console.log('Image appears to be a URL:', imageData);
    return `<img src="${imageData}" 
                 class="img-fluid rounded border" 
                 style="max-height: 300px; cursor: pointer;"
                 onclick="window.open(this.src, '_blank')"
                 alt="Payment Receipt">`;
  }
  
  // If the image is already a data URL
  if (typeof imageData === 'string' && imageData.startsWith('data:')) {
    console.log('Image appears to be a data URL');
    return `<img src="${imageData}" 
                 class="img-fluid rounded border" 
                 style="max-height: 300px; cursor: pointer;"
                 onclick="window.open(this.src, '_blank')"
                 alt="Payment Receipt">`;
  }
  
  // If the image is a base64 string without data URL prefix
  if (typeof imageData === 'string' && imageData.length > 0) {
    console.log('Attempting to process as base64 string');
    // Try to detect if it's a valid base64 string
    try {
      // Remove any whitespace or newlines
      const cleanedData = imageData.replace(/\s/g, '');
      
      // Check if it's a valid base64 string (this is a simple check)
      const isBase64 = /^[A-Za-z0-9+/=]+$/.test(cleanedData);
      
      if (isBase64) {
        console.log('Image appears to be a valid base64 string');
        // Try to determine the image type (default to png)
        return `<img src="data:image/jpeg;base64,${cleanedData}" 
                     class="img-fluid rounded border" 
                     style="max-height: 300px; cursor: pointer;"
                     onerror="this.onerror=null; this.src='data:image/png;base64,${cleanedData}';"
                     onclick="window.open(this.src, '_blank')"
                     alt="Payment Receipt">`;
      } else {
        console.log('Not a valid base64 string');
      }
    } catch (e) {
      console.error('Error processing image data:', e);
    }
  }
  
  // Display a message with debugging info
  console.log('Unable to determine image format');
  return `<div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Payment receipt image cannot be displayed
            <div class="mt-2 small text-muted">Image data may be in an unsupported format</div>
            <button class="btn btn-sm btn-outline-secondary mt-2" onclick="showImageDebugInfo('${btoa(JSON.stringify({type: typeof imageData, length: imageData ? imageData.length : 0, preview: imageData ? imageData.substring(0, 50) + '...' : 'empty'}))}')">
              Show Technical Details
            </button>
          </div>`;
}

function renderBookingDetails(booking) {
  console.log('Rendering booking details:', booking);
  
  // Check if we have a valid booking object
  if (!booking || typeof booking !== 'object') {
    $('#booking-details-content').html(`
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Invalid booking data received
      </div>
    `);
    return;
  }
  
  // Format the booking date and time
  let bookingDate = 'N/A';
  let bookingTime = 'N/A';
  
  try {
    // First, check if we have a dedicated booking_time field
    if (booking.booking_date) {
      const dateObj = new Date(booking.booking_date);
      bookingDate = dateObj.toLocaleDateString();
      
      if (booking.booking_time) {
        // Use the dedicated booking_time field if available
        bookingTime = booking.booking_time;
      } else {
        // Fall back to extracting time from the date
        bookingTime = dateObj.toLocaleTimeString();
      }
    } else if (booking.services && booking.services.length > 0 && booking.services[0].booking_date) {
      // Try to get date from first service if main booking date is missing
      const dateObj = new Date(booking.services[0].booking_date);
      bookingDate = dateObj.toLocaleDateString();
      
      // Use booking_time from service if available
      if (booking.services[0].booking_time) {
        bookingTime = booking.services[0].booking_time;
      } else {
        bookingTime = dateObj.toLocaleTimeString();
      }
    }
    
    console.log('Formatted date:', bookingDate);
    console.log('Formatted time:', bookingTime);
  } catch (e) {
    console.error('Error formatting date:', e);
  }
  
  // Generate services HTML
  let servicesHtml = '';
  if (booking.services && Array.isArray(booking.services) && booking.services.length > 0) {
    booking.services.forEach(service => {
      servicesHtml += `
        <div class="border rounded p-3 mb-2">
          <h6 class="mb-1">${service.name || 'Unknown Service'}</h6>
          <p class="text-muted small mb-1">${service.description || 'No description available'}</p>
          <div class="row">
            <div class="col-sm-4"><small><strong>Duration:</strong> ${service.duration || 0} min</small></div>
            <div class="col-sm-4"><small><strong>Quantity:</strong> ${service.quantity || 1}</small></div>
            <div class="col-sm-4"><small><strong>Price:</strong> ₱${service.price || 0}</small></div>
          </div>
        </div>
      `;
    });
  } else {
    servicesHtml = `
      <div class="alert alert-warning">
        <i class="bi bi-info-circle me-2"></i>
        No services found for this booking
      </div>
    `;
  }
  
  // Generate status badge
  const statusBadge = !booking.booking_status ? 
    '<span class="badge bg-secondary">Unknown</span>' :
    booking.booking_status === 'Pending' ? 
    '<span class="badge bg-warning text-dark">Pending</span>' :
    booking.booking_status === 'Confirmed' ?
    '<span class="badge bg-success">Confirmed</span>' :
    '<span class="badge bg-danger">Rejected</span>';
  
  // Render the booking details
  $('#booking-details-content').html(`
    <div class="row">
      <div class="col-md-6">
        <h6>Customer Information</h6>
        <p><strong>Name:</strong> ${booking.user_name || 'Unknown User'}</p>
        <p><strong>Email:</strong> ${booking.user_email || 'N/A'}</p>
        <p><strong>Phone:</strong> ${booking.user_phone || 'N/A'}</p>
      </div>
      <div class="col-md-6">
        <h6>Booking Information</h6>
        <p><strong>Booking ID:</strong> #${booking.bookingid || 'N/A'}</p>
        <p><strong>Date:</strong> ${bookingDate}</p>
        <p><strong>Time:</strong> ${bookingTime}</p>
        <p><strong>Status:</strong> ${statusBadge}</p>
        <p><strong>Total Amount:</strong> <span class="text-success fw-bold">₱${booking.total_price || 0}</span></p>
      </div>
    </div>
    
    <hr>
    
    <h6>Services Requested</h6>
    ${servicesHtml}
    
    ${booking.payment_img ? `
      <hr>
      <h6>Payment Receipt</h6>
      <div class="text-center">
        ${displayPaymentImage(booking.payment_img)}
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
      url: '../../../controller/booking_contr.php',
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
      url: '../../../controller/booking_contr.php',
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