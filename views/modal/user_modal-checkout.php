<div class="modal-header">
  <h5 class="modal-title">
    <i class="bi bi-calendar-check me-2"></i>Complete Your Booking
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <!-- Services and Scheduling -->
  <div id="servicesSchedulingContainer">
    <!-- Services with time/therapist selection will be loaded here -->
  </div>
  
  <!-- Booking Summary -->
  <div class="card mt-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">
        <i class="bi bi-receipt me-1"></i>Booking Summary
      </h6>
    </div>
    <div class="card-body">
      <div id="bookingSummary">
        <!-- Summary will be populated here -->
      </div>
      <hr>
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Total Amount:</h5>
        <h5 class="mb-0 text-success" id="checkoutTotal">₱0</h5>
      </div>
    </div>
  </div>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="button" class="btn btn-success" id="proceedToPaymentBtn" disabled>
    <i class="bi bi-credit-card me-1"></i>Proceed to Payment
  </button>
</div>

<script>
$(document).ready(function() {
    // Initialize checkout when modal opens
    setTimeout(() => {
        initializeCheckout();
    }, 100);
});

var checkoutData = {
    services: [],
    totalAmount: 0
};

function initializeCheckout() {
    const cart = window.serviceCart || [];
    
    if (cart.length === 0) {
        $('#servicesSchedulingContainer').html(`
            <div class="text-center py-5">
                <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No services in cart</h5>
                <p class="text-muted">Please add services to your cart before checkout.</p>
            </div>
        `);
        return;
    }
    
    // Initialize checkout data
    checkoutData.services = cart.map((service, index) => ({
        ...service,
        index: index,
        selectedDate: '',
        selectedTime: '',
        availableTherapists: [],
        selectedTherapists: service.therapists || []
    }));
    
    renderServicesScheduling();
    updateBookingSummary();
}

function renderServicesScheduling() {
    let html = '';
    
    checkoutData.services.forEach((service, index) => {
        const serviceTotal = service.price * service.people;
        
        html += `
            <div class="card mb-3 service-scheduling-card" data-service-index="${index}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="bi bi-spa me-1"></i>${service.name}
                        </h6>
                        <small class="text-muted">${service.people} person(s) × ₱${service.price} = ₱${serviceTotal}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeService(${index})" title="Remove service">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <!-- Date Selection -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1"></i>Select Date:
                            </label>
                            <input type="date" class="form-control date-selector" 
                                   data-service-index="${index}" 
                                   min="${new Date().toISOString().split('T')[0]}"
                                   value="${service.selectedDate || ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-clock me-1"></i>Select Time:
                            </label>
                            <select class="form-select time-selector" data-service-index="${index}" disabled>
                                <option value="">Select a date first</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Therapist Selection Container -->
                    <div class="therapist-selection-container" data-service-index="${index}">
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-calendar-week me-1"></i>
                            Please select date and time to view available therapists
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#servicesSchedulingContainer').html(html);
    
    // Attach event handlers
    attachSchedulingEventHandlers();
}

function attachSchedulingEventHandlers() {
    // Date selection handler
    $('.date-selector').on('change', function() {
        const serviceIndex = $(this).data('service-index');
        const selectedDate = $(this).val();
        
        checkoutData.services[serviceIndex].selectedDate = selectedDate;
        checkoutData.services[serviceIndex].selectedTime = '';
        checkoutData.services[serviceIndex].availableTherapists = [];
        checkoutData.services[serviceIndex].selectedTherapists = [];
        
        if (selectedDate) {
            loadTimeSlots(serviceIndex, selectedDate);
        } else {
            resetTimeAndTherapists(serviceIndex);
        }
        
        updateBookingSummary();
        validateCheckout();
    });
    
    // Time selection handler
    $('.time-selector').on('change', function() {
        const serviceIndex = $(this).data('service-index');
        const selectedTime = $(this).val();
        
        checkoutData.services[serviceIndex].selectedTime = selectedTime;
        checkoutData.services[serviceIndex].availableTherapists = [];
        checkoutData.services[serviceIndex].selectedTherapists = [];
        
        if (selectedTime) {
            loadAvailableTherapists(serviceIndex);
        } else {
            resetTherapists(serviceIndex);
        }
        
        updateBookingSummary();
        validateCheckout();
    });
}

function loadTimeSlots(serviceIndex, selectedDate) {
    const timeSelector = $(`.time-selector[data-service-index="${serviceIndex}"]`);
    
    // Show loading state
    timeSelector.prop('disabled', false).html('<option value="">Loading times...</option>');
    
    // Generate time slots (9 AM to 8 PM, every hour)
    const timeSlots = [];
    for (let hour = 9; hour <= 20; hour++) {
        const time24 = `${hour.toString().padStart(2, '0')}:00`;
        const time12 = new Date(`2000-01-01 ${time24}`).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        timeSlots.push({ value: time24, label: time12 });
    }
    
    // Check for existing bookings on this date
    $.ajax({
        url: '../controller/booking_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_booked_times',
            date: selectedDate
        },
        success: function(bookedTimes) {
            let html = '<option value="">Select time</option>';
            
            timeSlots.forEach(slot => {
                const isBooked = bookedTimes && bookedTimes.includes(slot.value);
                const disabled = isBooked ? 'disabled' : '';
                const label = isBooked ? `${slot.label} (Fully Booked)` : slot.label;
                
                html += `<option value="${slot.value}" ${disabled}>${label}</option>`;
            });
            
            timeSelector.html(html);
        },
        error: function() {
            // If error, still show time slots
            let html = '<option value="">Select time</option>';
            timeSlots.forEach(slot => {
                html += `<option value="${slot.value}">${slot.label}</option>`;
            });
            timeSelector.html(html);
        }
    });
}

function loadAvailableTherapists(serviceIndex) {
    const service = checkoutData.services[serviceIndex];
    const container = $(`.therapist-selection-container[data-service-index="${serviceIndex}"]`);
    
    // Show loading state
    container.html(`
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">Loading available therapists...</div>
        </div>
    `);
    
    $.ajax({
        url: '../controller/therapist_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_available_therapists',
            service_id: service.id,
            date: service.selectedDate,
            time: service.selectedTime
        },
        success: function(result) {
            console.log('Therapist API Response:', result); // Debug log
            
            if (result === 'nodata' || !result || result.length === 0) {
                container.html(`
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No therapists available for ${service.selectedDate} at ${formatTime(service.selectedTime)}
                        <div class="mt-2 small">
                            Please try selecting a different time slot or proceed without specific therapist selection.
                            <br><button class="btn btn-sm btn-outline-primary mt-2" onclick="proceedWithoutTherapist(${serviceIndex})">
                                Proceed Without Therapist Selection
                            </button>
                        </div>
                    </div>
                `);
                return;
            }
            
            checkoutData.services[serviceIndex].availableTherapists = result;
            renderTherapistSelection(serviceIndex);
        },
        error: function(xhr, status, error) {
            console.error('Therapist loading error:', xhr.responseText, status, error); // Debug log
            container.html(`
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Failed to load therapists.</strong> You can still proceed without therapist selection.
                    <div class="mt-2">
                        <button class="btn btn-sm btn-primary" onclick="proceedWithoutTherapist(${serviceIndex})">
                            Continue Without Therapist Selection
                        </button>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="loadAvailableTherapists(${serviceIndex})">
                            Try Again
                        </button>
                    </div>
                </div>
            `);
        }
    });
}

function renderTherapistSelection(serviceIndex) {
    const service = checkoutData.services[serviceIndex];
    const container = $(`.therapist-selection-container[data-service-index="${serviceIndex}"]`);
    
    if (service.availableTherapists.length === 0) {
        return;
    }
    
    let html = `
        <div class="therapist-selection-section">
            <label class="form-label fw-semibold">
                <i class="bi bi-people me-1"></i>Select Therapist(s) for ${formatTime(service.selectedTime)} on ${formatDate(service.selectedDate)}:
            </label>
            <div class="row">
    `;
    
    // Generate therapist selection for each person
    for (let person = 1; person <= service.people; person++) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="person-therapist-selection border rounded p-3">
                    <h6 class="mb-2">
                        <i class="bi bi-person me-1"></i>Person ${person}
                    </h6>
                    <div class="therapist-options">
                        ${service.availableTherapists.map(therapist => `
                            <div class="form-check mb-2">
                                <input class="form-check-input therapist-radio" 
                                       type="radio" 
                                       name="therapist-person-${serviceIndex}-${person}" 
                                       value="${therapist.therapistid}"
                                       id="therapist-${serviceIndex}-${person}-${therapist.therapistid}">
                                <label class="form-check-label w-100" for="therapist-${serviceIndex}-${person}-${therapist.therapistid}">
                                    <div class="therapist-info p-2 border rounded">
                                        <div class="fw-semibold">${therapist.therapist_name}</div>
                                        <div class="text-muted small">${therapist.therapist_desc || 'Professional therapist'}</div>
                                        <div class="text-success small mt-1">
                                            <i class="bi bi-check-circle me-1"></i>Available
                                        </div>
                                    </div>
                                </label>
                            </div>
                        `).join('')}
                        <div class="form-check">
                            <input class="form-check-input therapist-radio" 
                                   type="radio" 
                                   name="therapist-person-${serviceIndex}-${person}" 
                                   value="any"
                                   id="therapist-any-${serviceIndex}-${person}">
                            <label class="form-check-label" for="therapist-any-${serviceIndex}-${person}">
                                <div class="therapist-info p-2 border rounded bg-light">
                                    <div class="fw-semibold text-muted">Any Available Therapist</div>
                                    <div class="text-muted small">We'll assign the best available therapist</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += `
            </div>
        </div>
    `;
    
    container.html(html);
    
    // Attach therapist selection handlers
    $(`.therapist-radio[name^="therapist-person-${serviceIndex}"]`).on('change', function() {
        updateSelectedTherapists(serviceIndex);
    });
}

function updateSelectedTherapists(serviceIndex) {
    const selectedTherapists = [];
    
    for (let person = 1; person <= checkoutData.services[serviceIndex].people; person++) {
        const selectedRadio = $(`input[name="therapist-person-${serviceIndex}-${person}"]:checked`);
        
        if (selectedRadio.length > 0) {
            const therapistId = selectedRadio.val();
            
            if (therapistId === 'any') {
                selectedTherapists.push({
                    person: person,
                    therapistId: 'any',
                    therapistName: 'Any Available Therapist'
                });
            } else {
                const therapist = checkoutData.services[serviceIndex].availableTherapists.find(t => t.therapistid == therapistId);
                if (therapist) {
                    selectedTherapists.push({
                        person: person,
                        therapistId: therapistId,
                        therapistName: therapist.therapist_name
                    });
                }
            }
        }
    }
    
    checkoutData.services[serviceIndex].selectedTherapists = selectedTherapists;
    updateBookingSummary();
    validateCheckout();
}

function resetTimeAndTherapists(serviceIndex) {
    $(`.time-selector[data-service-index="${serviceIndex}"]`).prop('disabled', true).html('<option value="">Select a date first</option>');
    resetTherapists(serviceIndex);
}

function resetTherapists(serviceIndex) {
    $(`.therapist-selection-container[data-service-index="${serviceIndex}"]`).html(`
        <div class="text-center py-3 text-muted">
            <i class="bi bi-calendar-week me-1"></i>
            Please select date and time to view available therapists
        </div>
    `);
}

function updateBookingSummary() {
    let summaryHtml = '';
    let totalAmount = 0;
    
    checkoutData.services.forEach((service, index) => {
        const serviceTotal = service.price * service.people;
        totalAmount += serviceTotal;
        
        const dateTimeInfo = service.selectedDate && service.selectedTime 
            ? `${formatDate(service.selectedDate)} at ${formatTime(service.selectedTime)}`
            : 'Date & time not selected';
        
        const therapistInfo = service.selectedTherapists.length > 0
            ? service.selectedTherapists.map(t => `Person ${t.person}: ${t.therapistName}`).join(', ')
            : 'No therapists selected';
        
        summaryHtml += `
            <div class="summary-item mb-3 p-2 border-start border-3 border-primary">
                <div class="d-flex justify-content-between">
                    <strong>${service.name}</strong>
                    <span class="text-success fw-bold">₱${serviceTotal}</span>
                </div>
                <div class="text-muted small mt-1">
                    <div><i class="bi bi-people me-1"></i>${service.people} person(s)</div>
                    <div><i class="bi bi-calendar me-1"></i>${dateTimeInfo}</div>
                    <div><i class="bi bi-person-check me-1"></i>${therapistInfo}</div>
                </div>
            </div>
        `;
    });
    
    $('#bookingSummary').html(summaryHtml);
    $('#checkoutTotal').text(`₱${totalAmount}`);
    checkoutData.totalAmount = totalAmount;
}

function validateCheckout() {
    let isValid = true;
    
    // Check if all services have date and time selected
    checkoutData.services.forEach(service => {
        if (!service.selectedDate || !service.selectedTime) {
            isValid = false;
        }
    });
    
    $('#proceedToPaymentBtn').prop('disabled', !isValid);
}

function proceedWithoutTherapist(serviceIndex) {
    // Mark service as ready to proceed without therapist selection
    checkoutData.services[serviceIndex].selectedTherapists = [];
    checkoutData.services[serviceIndex].proceedWithoutTherapist = true;
    
    const container = $(`.therapist-selection-container[data-service-index="${serviceIndex}"]`);
    container.html(`
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Ready to proceed!</strong> A therapist will be assigned automatically when your booking is confirmed.
        </div>
    `);
    
    updateBookingSummary();
    validateCheckout();
}

function removeService(index) {
    // Remove from checkout data
    checkoutData.services.splice(index, 1);
    
    // Remove from global cart
    window.serviceCart.splice(index, 1);
    
    // Update checkout button
    if (typeof window.updateCheckoutBadge === 'function') {
        window.updateCheckoutBadge();
    }
    
    if (checkoutData.services.length === 0) {
        $('#globalModal').modal('hide');
        Swal.fire({
            icon: 'info',
            title: 'Cart Empty',
            text: 'All services have been removed from your cart.',
        });
    } else {
        // Re-render with updated indices
        initializeCheckout();
    }
}

function formatDate(dateString) {
    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
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

// Handle proceed to payment
$('#proceedToPaymentBtn').on('click', function() {
    // Prepare booking data for payment
    const bookingData = {
        services: checkoutData.services.map(service => ({
            serviceId: service.id,
            serviceName: service.name,
            people: service.people,
            price: service.price,
            selectedDate: service.selectedDate,
            selectedTime: service.selectedTime,
            selectedTherapists: service.selectedTherapists
        })),
        totalAmount: checkoutData.totalAmount
    };
    
    // Store booking data for payment modal
    window.pendingBookingData = bookingData;
    
    // Close checkout modal and open payment modal
    $('#globalModal').modal('hide');
    
    setTimeout(() => {
        showGlobalModal('../views/modal/user_modal-payment.php', bookingData);
    }, 300);
});
</script>

<style>
  .service-scheduling-card {
    border-left: 4px solid #007bff;
  }
  
  .person-therapist-selection {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6 !important;
  }
  
  .therapist-info {
    transition: all 0.2s ease;
    cursor: pointer;
  }
  
  .therapist-info:hover {
    background-color: #e9ecef !important;
  }
  
  .therapist-radio:checked + .form-check-label .therapist-info {
    background-color: #e3f2fd !important;
    border-color: #007bff !important;
  }
  
  .summary-item {
    background-color: #f8f9fa;
  }
  
  .modal-body {
    max-height: 80vh;
    overflow-y: auto;
  }
  
  .date-selector, .time-selector {
    border-radius: 0.375rem;
  }
  
  .date-selector:focus, .time-selector:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }

  @media (max-width: 576px) {
    .person-therapist-selection {
      margin-bottom: 1rem;
    }
    
    .modal-body {
      padding: 1rem 0.5rem;
    }
  }
</style>
