<!-- Service Management Modal for Bookings -->
<div class="modal-header bg-primary text-white">
  <h5 class="modal-title">
    <i class="bi bi-list-check me-2"></i>
    Manage Booking Services
  </h5>
  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
  <div id="booking-services-content">
    <div class="text-center p-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading booking services...</p>
    </div>
  </div>
</div>

<div class="modal-footer justify-content-between">
  <div>
    <small class="text-muted">
      <i class="bi bi-info-circle me-1"></i>
      Complete individual services as they are finished
    </small>
  </div>
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

<style>
.service-card {
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.service-card.completed {
  background-color: #f8fff8;
  border-left-color: #198754;
  opacity: 0.8;
}

.service-card.pending {
  background-color: #fffbf0;
  border-left-color: #ffc107;
}

.service-card.stroke-treatment {
  border-left-color: #0d6efd;
}

.progress-input {
  width: 80px;
}

.therapist-notes {
  min-height: 80px;
}

.stroke-progress-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  margin-top: 10px;
}
</style>

<script>
// Global variables for modal
let currentBookingData = null;
let currentServices = null;

// Initialize the modal when it's shown
$(document).ready(function() {
    console.log('🔧 Service modal loaded, checking for booking ID...');
    
    // Listen for modal parameters from showGlobalModal (it uses modalData, not modalParams)
    if (typeof window.modalData !== 'undefined' && window.modalData.bookingid) {
        console.log('🔄 Loading booking services from modalData:', window.modalData.bookingid);
        loadBookingServices(window.modalData.bookingid);
        return;
    }
    
    // Fallback: check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const bookingid = urlParams.get('bookingid');
    if (bookingid) {
        console.log('🔄 Loading booking services from URL param:', bookingid);
        loadBookingServices(bookingid);
        return;
    }
    
    // If no booking ID found, wait a bit and try again (modal might still be loading)
    setTimeout(function() {
        if (typeof window.modalData !== 'undefined' && window.modalData.bookingid) {
            console.log('🔄 Loading booking services from modalData (delayed):', window.modalData.bookingid);
            loadBookingServices(window.modalData.bookingid);
        } else {
            console.error('❌ No booking ID found in modalData or URL params');
            showError('No booking ID provided');
        }
    }, 100);
});

function loadBookingServices(bookingId) {
    if (!bookingId) {
        showError('Invalid booking ID provided');
        return;
    }
    
    console.log('📞 Making AJAX call to load services for booking:', bookingId);
    
    $.ajax({
        url: '../controller/booking_contr.php',
        type: 'POST',
        data: { 
            action: 'get_booking_services',
            bookingid: bookingId
        },
        dataType: 'json',
        success: function(response) {
            console.log('✅ Booking services response:', response);
            
            if (response.status === 'success' && response.data) {
                currentBookingData = response.data;
                currentServices = response.data.services;
                displayBookingServices(response.data);
            } else {
                console.error('❌ Server error:', response.message);
                showError(response.message || 'Failed to load booking services');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            
            let errorMessage = 'Failed to load booking services. ';
            if (xhr.status === 404) {
                errorMessage += 'Service not found (404).';
            } else if (xhr.status === 500) {
                errorMessage += 'Server error (500).';
            } else if (status === 'timeout') {
                errorMessage += 'Request timeout.';
            } else {
                errorMessage += `Error: ${error}`;
            }
            
            showError(errorMessage);
        },
        timeout: 30000
    });
}

function displayBookingServices(data) {
    const { booking, services } = data;
    let html = '';
    
    // Booking header
    html += `
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <div>
                <strong>Booking #${booking.bookingid}</strong> - ${booking.user_name}
                <br><small class="text-muted">${services.length} service${services.length !== 1 ? 's' : ''} in this booking</small>
            </div>
        </div>
    `;
    
    // Services list
    services.forEach(service => {
        const isCompleted = service.status === 'completed';
        const isStrokeTreatment = service.service_name && 
            (service.service_name.toLowerCase().includes('stroke') || 
             service.service_name.toLowerCase().includes('special treatment for stroke'));
        
        const cardClass = isCompleted ? 'completed' : 'pending';
        const statusIcon = isCompleted ? 'bi-check-circle-fill text-success' : 'bi-clock text-warning';
        const statusText = isCompleted ? 'Completed' : 'Pending';
        
        html += `
            <div class="card service-card ${cardClass} ${isStrokeTreatment ? 'stroke-treatment' : ''} mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="card-title mb-1">
                                ${service.service_name}
                                ${isStrokeTreatment ? '<span class="badge bg-info ms-2">Stroke Treatment</span>' : ''}
                            </h6>
                            <p class="text-muted small mb-2">Quantity: ${service.quantity} | Price: ₱${service.price}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge ${isCompleted ? 'bg-success' : 'bg-warning'}">
                                <i class="bi ${statusIcon.split(' ')[0]} me-1"></i>
                                ${statusText}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Progress display for completed stroke treatments -->
                    ${isCompleted && isStrokeTreatment && (service.pain_level || service.mobility_level || service.overall_progress) ? `
                        <div class="progress-display mb-3 p-2 bg-light rounded">
                            <small class="text-muted fw-bold">Progress Data:</small>
                            <div class="d-flex gap-3 mt-1">
                                ${service.pain_level ? `<span class="badge bg-warning">Pain: ${service.pain_level}/10</span>` : ''}
                                ${service.mobility_level ? `<span class="badge bg-info">Mobility: ${service.mobility_level}%</span>` : ''}
                                ${service.overall_progress ? `<span class="badge bg-success">Progress: ${service.overall_progress}%</span>` : ''}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Therapist notes display -->
                    ${service.therapist_notes ? `
                        <div class="therapist-notes-display mb-3">
                            <small class="text-muted fw-bold">Therapist Notes:</small>
                            <div class="bg-light p-2 rounded small mt-1">${service.therapist_notes}</div>
                        </div>
                    ` : ''}
                    
                    <!-- Action buttons for pending services -->
                    ${!isCompleted ? `
                        <div class="service-actions">
                            <div class="mb-3">
                                <label class="form-label small">Therapist Notes (Required)</label>
                                <textarea class="form-control" id="notes_${service.bookingdetailsid}" 
                                         placeholder="Add notes about this service session..." rows="3"></textarea>
                            </div>
                            
                            ${isStrokeTreatment ? `
                                <!-- Stroke treatment progress inputs -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small">Pain Level (1-10)</label>
                                        <input type="number" class="form-control" id="pain_${service.bookingdetailsid}" 
                                               min="1" max="10" placeholder="1-10">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Mobility (%)</label>
                                        <input type="number" class="form-control" id="mobility_${service.bookingdetailsid}" 
                                               min="0" max="100" placeholder="0-100">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Overall Progress (%)</label>
                                        <input type="number" class="form-control" id="progress_${service.bookingdetailsid}" 
                                               min="0" max="100" placeholder="0-100">
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-complete" data-service-id="${service.bookingdetailsid}">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Complete Service
                                </button>
                                ${isStrokeTreatment ? `
                                    <button class="btn btn-info btn-update-progress" data-service-id="${service.bookingdetailsid}">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Update Progress
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    ` : `
                        <!-- Completion info for completed services -->
                        <div class="completion-info">
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Completed on ${service.completed_at ? new Date(service.completed_at).toLocaleDateString() : 'N/A'}
                            </small>
                        </div>
                    `}
                </div>
            </div>
        `;
    });
    
    $('#booking-services-content').html(html);
    
    // Attach event handlers
    attachServiceActionHandlers();
}

function attachServiceActionHandlers() {
    // Complete service button
    $('.btn-complete').off('click').on('click', function() {
        const serviceId = $(this).data('service-id');
        completeService(serviceId, 'complete');
    });
    
    // Update progress button  
    $('.btn-update-progress').off('click').on('click', function() {
        const serviceId = $(this).data('service-id');
        completeService(serviceId, 'update');
    });
}

function completeService(serviceId, action) {
    const notes = $(`#notes_${serviceId}`).val().trim();
    
    if (!notes) {
        showNotification('Therapist notes are required', 'error');
        return;
    }
    
    // Collect progress data for stroke treatments
    let progressData = null;
    if ($(`#pain_${serviceId}`).length) {
        progressData = {
            pain_level: $(`#pain_${serviceId}`).val() || null,
            mobility_level: $(`#mobility_${serviceId}`).val() || null,
            overall_progress: $(`#progress_${serviceId}`).val() || null
        };
    }
    
    // Disable button and show loading
    const button = $(`.btn-${action === 'complete' ? 'complete' : 'update-progress'}[data-service-id="${serviceId}"]`);
    const originalText = button.html();
    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
    
    $.ajax({
        url: '../controller/booking_contr.php',
        type: 'POST',
        data: {
            action: 'update_service_completion',
            booking_detail_id: serviceId,
            therapist_notes: notes,
            progress_data: progressData ? JSON.stringify(progressData) : null,
            completion_action: action
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                
                // Reload the services to show updated status
                if (currentBookingData && currentBookingData.booking) {
                    loadBookingServices(currentBookingData.booking.bookingid);
                }
                
                // If all services completed, show special message
                if (response.all_completed) {
                    setTimeout(() => {
                        showNotification('All services completed! Booking moved to history.', 'success');
                    }, 1000);
                }
            } else {
                showNotification(response.message || 'Failed to update service', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating service:', error);
            showNotification('Error updating service: ' + error, 'error');
        },
        complete: function() {
            // Re-enable button
            button.prop('disabled', false).html(originalText);
        }
    });
}

function showError(message) {
    $('#booking-services-content').html(`
        <div class="text-center p-4">
            <i class="bi bi-exclamation-triangle text-warning display-1"></i>
            <h5 class="text-warning mt-3">Error Loading Services</h5>
            <p class="text-muted">${message}</p>
            <button type="button" class="btn btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Retry
            </button>
        </div>
    `);
}

// Utility function for notifications (if not available globally)
function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
            text: message,
            icon: type,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

console.log('📋 Service Management Modal initialized');
</script>

<script>
$(document).ready(function() {
  // Load booking services when modal data is available
  if (window.modalData && window.modalData.bookingid) {
    loadBookingServices(window.modalData.bookingid);
  }
});

function loadBookingServices(bookingid) {
  console.log('Loading booking services for booking:', bookingid);
  
  $.ajax({
    url: '../controller/booking_contr.php',
    type: 'POST',
    data: { 
      action: 'get_booking_services',
      bookingid: bookingid
    },
    dataType: 'json',
    success: function(response) {
      console.log('Booking services response:', response);
      
      if (response.status === 'error') {
        $('#booking-services-content').html(`
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${response.message || 'Failed to load booking services'}
          </div>
        `);
        return;
      }
      
      if (response.status === 'success' && response.data) {
        renderBookingServices(response.data);
      } else {
        $('#booking-services-content').html(`
          <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            No services found for this booking.
          </div>
        `);
      }
    },
    error: function(xhr, status, error) {
      console.error('Error loading booking services:', error);
      $('#booking-services-content').html(`
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>
          Error loading services: ${error}
        </div>
      `);
    }
  });
}

function renderBookingServices(bookingData) {
  console.log('Rendering booking services:', bookingData);
  
  const booking = bookingData.booking;
  const services = bookingData.services;
  
  let html = `
    <!-- Booking Info Header -->
    <div class="card border-0 bg-light mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h6 class="mb-1">
              <i class="bi bi-person-circle me-2"></i>
              ${booking.user_name}
            </h6>
            <div class="d-flex flex-wrap gap-2 small">
              <span class="badge bg-primary">ID: #${booking.bookingid}</span>
              <span class="badge bg-info">
                <i class="bi bi-calendar me-1"></i>
                ${new Date(booking.date_created).toLocaleDateString()}
              </span>
              <span class="badge bg-success">
                <i class="bi bi-currency-dollar me-1"></i>
                ₱${booking.total_price}
              </span>
            </div>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="mt-2">
              <span class="text-muted small">Services Progress</span>
              <div class="progress mt-1" style="height: 6px;">
                <div class="progress-bar bg-success" id="overall-progress" role="progressbar" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Services List -->
    <div class="services-list">
  `;
  
  services.forEach((service, index) => {
    const isStrokeTreatment = service.service_name.toLowerCase().includes('stroke') || 
                              service.service_name.toLowerCase().includes('special treatment for stroke');
    const isCompleted = service.status === 'completed';
    const cardClass = isCompleted ? 'completed' : 'pending';
    const statusIcon = isCompleted ? 'bi-check-circle-fill text-success' : 'bi-clock text-warning';
    
    html += `
      <div class="card service-card ${cardClass} ${isStrokeTreatment ? 'stroke-treatment' : ''} mb-3" 
           data-service-id="${service.bookingdetailsid}">
        <div class="card-body">
          <!-- Service Header -->
          <div class="row align-items-start">
            <div class="col-md-8">
              <div class="d-flex align-items-center mb-2">
                <i class="${statusIcon} me-2 fs-5"></i>
                <h6 class="mb-0 fw-bold">${service.service_name}</h6>
                ${isStrokeTreatment ? '<span class="badge bg-info ms-2">Stroke Treatment</span>' : ''}
              </div>
              <p class="text-muted mb-2 small">${service.service_description || 'No description available'}</p>
              <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark me-2">
                  <i class="bi bi-clock me-1"></i>
                  ${service.per_minute} min
                </span>
                <span class="badge bg-light text-dark me-2">
                  <i class="bi bi-currency-dollar me-1"></i>
                  ₱${service.price}
                </span>
                <span class="badge bg-light text-dark">
                  <i class="bi bi-calendar me-1"></i>
                  ${new Date(service.booking_date).toLocaleDateString()}
                </span>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="col-md-4 text-md-end">
              <div class="btn-group-vertical gap-2" role="group">
                ${renderServiceButtons(service, isStrokeTreatment, isCompleted)}
              </div>
            </div>
          </div>
          
          <!-- Therapist Notes Section -->
          <div class="therapist-notes-section mt-3" id="notes-section-${service.bookingdetailsid}">
            <label class="form-label small fw-bold">
              <i class="bi bi-journal-text me-1"></i>
              Therapist Notes
            </label>
            <textarea class="form-control therapist-notes" 
                      id="therapist-notes-${service.bookingdetailsid}"
                      placeholder="Enter notes about this service session..."
                      ${isCompleted && !isStrokeTreatment ? 'readonly' : ''}>${service.therapist_notes || ''}</textarea>
          </div>
          
          <!-- Stroke Treatment Progress (only for stroke services) -->
          ${isStrokeTreatment ? renderStrokeProgressSection(service) : ''}
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  
  $('#booking-services-content').html(html);
  
  // Update overall progress
  updateOverallProgress(services);
}

function renderServiceButtons(service, isStrokeTreatment, isCompleted) {
  if (isCompleted) {
    // Service is completed - show status
    return `
      <span class="badge bg-success fs-6 py-2 px-3">
        <i class="bi bi-check-circle me-1"></i>
        Completed
      </span>
    `;
  } else if (isStrokeTreatment) {
    // Stroke treatment - show both buttons
    return `
      <button type="button" class="btn btn-warning btn-sm" 
              onclick="updateStrokeProgress(${service.bookingdetailsid})"
              title="Update patient progress">
        <i class="bi bi-arrow-up-circle me-1"></i>
        Update Progress
      </button>
      <button type="button" class="btn btn-success btn-sm" 
              onclick="completeService(${service.bookingdetailsid})"
              title="Mark service as completed">
        <i class="bi bi-check-circle me-1"></i>
        Complete
      </button>
    `;
  } else {
    // Regular service - only complete button
    return `
      <button type="button" class="btn btn-success btn-sm" 
              onclick="completeService(${service.bookingdetailsid})"
              title="Mark service as completed">
        <i class="bi bi-check-circle me-1"></i>
        Complete
      </button>
    `;
  }
}

function renderStrokeProgressSection(service) {
  return `
    <div class="stroke-progress-container mt-3" id="stroke-progress-${service.bookingdetailsid}">
      <h6 class="fw-bold text-primary mb-3">
        <i class="bi bi-activity me-1"></i>
        Patient Recovery Progress
      </h6>
      
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small fw-bold">Pain Level (1-10)</label>
          <div class="input-group">
            <input type="number" class="form-control progress-input" 
                   id="pain-level-${service.bookingdetailsid}"
                   min="1" max="10" value="${service.pain_level || ''}"
                   placeholder="1-10">
            <span class="input-group-text">/10</span>
          </div>
        </div>
        
        <div class="col-md-4">
          <label class="form-label small fw-bold">Mobility (%)</label>
          <div class="input-group">
            <input type="number" class="form-control progress-input" 
                   id="mobility-level-${service.bookingdetailsid}"
                   min="0" max="100" value="${service.mobility_level || ''}"
                   placeholder="0-100">
            <span class="input-group-text">%</span>
          </div>
        </div>
        
        <div class="col-md-4">
          <label class="form-label small fw-bold">Overall Progress (%)</label>
          <div class="input-group">
            <input type="number" class="form-control progress-input" 
                   id="overall-recovery-${service.bookingdetailsid}"
                   min="0" max="100" value="${service.overall_progress || ''}"
                   placeholder="0-100">
            <span class="input-group-text">%</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

function updateOverallProgress(services) {
  const totalServices = services.length;
  const completedServices = services.filter(s => s.status === 'completed').length;
  const progressPercent = totalServices > 0 ? (completedServices / totalServices) * 100 : 0;
  
  $('#overall-progress').css('width', progressPercent + '%');
  $('.modal-title').html(`
    <i class="bi bi-list-check me-2"></i>
    Manage Services (${completedServices}/${totalServices} completed)
  `);
}

// Service completion functions
function completeService(bookingDetailId) {
  const therapistNotes = $(`#therapist-notes-${bookingDetailId}`).val().trim();
  
  if (!therapistNotes) {
    Swal.fire({
      title: 'Therapist Notes Required',
      text: 'Please add therapist notes before completing this service.',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }
  
  // Check if this is a stroke treatment (has progress inputs)
  const isStrokeTreatment = $(`#stroke-progress-${bookingDetailId}`).length > 0;
  let progressData = null;
  
  if (isStrokeTreatment) {
    const painLevel = $(`#pain-level-${bookingDetailId}`).val();
    const mobilityLevel = $(`#mobility-level-${bookingDetailId}`).val();
    const overallProgress = $(`#overall-recovery-${bookingDetailId}`).val();
    
    if (!painLevel || !mobilityLevel || !overallProgress) {
      Swal.fire({
        title: 'Progress Data Required',
        text: 'Please fill in all progress fields for stroke treatment services.',
        icon: 'warning',
        confirmButtonText: 'OK'
      });
      return;
    }
    
    progressData = {
      pain_level: painLevel,
      mobility_level: mobilityLevel,
      overall_progress: overallProgress
    };
  }
  
  Swal.fire({
    title: 'Complete Service?',
    text: 'This service will be marked as completed. This action cannot be undone.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Complete Service'
  }).then((result) => {
    if (result.isConfirmed) {
      submitServiceCompletion(bookingDetailId, therapistNotes, progressData, 'complete');
    }
  });
}

function updateStrokeProgress(bookingDetailId) {
  const therapistNotes = $(`#therapist-notes-${bookingDetailId}`).val().trim();
  const painLevel = $(`#pain-level-${bookingDetailId}`).val();
  const mobilityLevel = $(`#mobility-level-${bookingDetailId}`).val();
  const overallProgress = $(`#overall-recovery-${bookingDetailId}`).val();
  
  if (!therapistNotes) {
    Swal.fire({
      title: 'Therapist Notes Required',
      text: 'Please add therapist notes before updating progress.',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }
  
  if (!painLevel || !mobilityLevel || !overallProgress) {
    Swal.fire({
      title: 'Progress Data Required',
      text: 'Please fill in all progress fields.',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }
  
  const progressData = {
    pain_level: painLevel,
    mobility_level: mobilityLevel,
    overall_progress: overallProgress
  };
  
  submitServiceCompletion(bookingDetailId, therapistNotes, progressData, 'update');
}

function submitServiceCompletion(bookingDetailId, therapistNotes, progressData, action) {
  // Show loading
  const serviceCard = $(`.service-card[data-service-id="${bookingDetailId}"]`);
  const originalContent = serviceCard.find('.btn-group-vertical').html();
  serviceCard.find('.btn-group-vertical').html(`
    <div class="spinner-border spinner-border-sm text-primary" role="status">
      <span class="visually-hidden">Processing...</span>
    </div>
  `);
  
  $.ajax({
    url: '../controller/booking_contr.php',
    type: 'POST',
    data: {
      action: 'update_service_completion',
      booking_detail_id: bookingDetailId,
      therapist_notes: therapistNotes,
      progress_data: JSON.stringify(progressData),
      completion_action: action
    },
    dataType: 'json',
    success: function(response) {
      if (response.status === 'success') {
        Swal.fire({
          title: 'Success!',
          text: response.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
        
        // Reload the booking services to refresh the view
        if (window.modalData && window.modalData.bookingid) {
          loadBookingServices(window.modalData.bookingid);
          
          // If all services are completed, refresh the parent page
          if (response.all_completed) {
            setTimeout(() => {
              // Close modal and refresh accepted bookings
              $('#globalModal').modal('hide');
              if (typeof loadAcceptedBookings === 'function') {
                loadAcceptedBookings();
              }
            }, 2000);
          }
        }
      } else {
        serviceCard.find('.btn-group-vertical').html(originalContent);
        Swal.fire({
          title: 'Error',
          text: response.message || 'Failed to update service',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    },
    error: function(xhr, status, error) {
      serviceCard.find('.btn-group-vertical').html(originalContent);
      Swal.fire({
        title: 'Error',
        text: 'Network error: ' + error,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
  });
}
</script>