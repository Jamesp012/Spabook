<div class="modal-header">
  <h5 class="modal-title">Book Service</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <!-- Service Information -->
  <div class="mb-3">
    <p class="mb-1">Selected Service:</p>
    <h5 id="selected-service-name" class="fw-bold mb-1"></h5>
    <p id="selected-service-price" class="text-muted mb-0" data-price=""></p>
    <input type="hidden" id="selected-service-id" value="">
  </div>

  <!-- Number of People -->
  <div class="mb-3">
    <label for="numPeople" class="form-label fw-semibold">Number of People:</label>
    <input type="number" class="form-control" id="numPeople" min="1" max="10" value="1">
    <div class="form-text">Maximum 10 people per booking</div>
  </div>

  <!-- Therapist Selection -->
  <div class="mb-3">
    <label class="form-label fw-semibold">
      <i class="bi bi-person-fill me-1"></i>Select Therapist(s):
    </label>
    <div id="therapist-loading" class="text-center py-3 d-none">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <div class="mt-2 text-muted">Loading therapists...</div>
    </div>
    <div id="therapist-selection-container">
      <!-- Therapist options will be populated here -->
    </div>
    <div id="no-therapists" class="alert alert-info d-none">
      <i class="bi bi-info-circle me-1"></i>
      No therapists available for this service.
    </div>
  </div>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="button" class="btn btn-primary" id="confirmServiceBtn" disabled>Add to Cart</button>
</div>

<script>
// Global variables for modal
let selectedServiceData = {};
let availableTherapists = [];
let selectedTherapists = [];

// Function to load therapists for the selected service
function loadTherapists(serviceId) {
    $('#therapist-loading').removeClass('d-none');
    $('#therapist-selection-container').html('');
    $('#no-therapists').addClass('d-none');
    $('#confirmServiceBtn').prop('disabled', true);

    $.ajax({
        url: '../controller/therapist_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_therapists_by_service',
            service_id: serviceId
        },
        success: function(result) {
            $('#therapist-loading').addClass('d-none');
            
            if (result === 'nodata' || !result || result.length === 0) {
                $('#no-therapists').removeClass('d-none');
                // Allow booking without therapist selection
                $('#confirmServiceBtn').prop('disabled', false);
                availableTherapists = [];
            } else {
                availableTherapists = result;
                generateTherapistSelection();
                $('#confirmServiceBtn').prop('disabled', false);
            }
        },
        error: function() {
            $('#therapist-loading').addClass('d-none');
            $('#therapist-selection-container').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Failed to load therapists. You can still proceed without therapist selection.
                </div>
            `);
            $('#confirmServiceBtn').prop('disabled', false);
            availableTherapists = [];
        }
    });
}

// Function to generate therapist selection UI based on number of people
function generateTherapistSelection() {
    const numPeople = parseInt($('#numPeople').val());
    let html = '';

    if (availableTherapists.length === 0) {
        return;
    }

    for (let i = 1; i <= numPeople; i++) {
        html += `
            <div class="therapist-person-selection mb-3 border rounded p-3" data-person="${i}">
                <h6 class="mb-2">
                    <i class="bi bi-person me-1"></i>Person ${i} - Therapist Selection
                </h6>
                <div class="row">
                    ${availableTherapists.map(therapist => `
                        <div class="col-md-6 mb-2">
                            <div class="form-check therapist-option">
                                <input class="form-check-input therapist-radio" 
                                       type="radio" 
                                       name="therapist-person-${i}" 
                                       value="${therapist.therapistid}" 
                                       id="therapist-${therapist.therapistid}-person-${i}">
                                <label class="form-check-label w-100" for="therapist-${therapist.therapistid}-person-${i}">
                                    <div class="therapist-card p-2 border rounded">
                                        <div class="fw-semibold">${therapist.therapist_name}</div>
                                        <div class="text-muted small">${therapist.therapist_desc || 'Professional therapist'}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Select a therapist for person ${i} (optional)
                    </small>
                </div>
            </div>
        `;
    }

    $('#therapist-selection-container').html(html);

    // Add click handler for therapist cards
    $('.therapist-card').on('click', function() {
        $(this).closest('.therapist-option').find('.therapist-radio').prop('checked', true);
        updateSelectedTherapists();
    });

    // Add change handler for radio buttons
    $('.therapist-radio').on('change', function() {
        updateSelectedTherapists();
    });
}

// Function to update selected therapists array
function updateSelectedTherapists() {
    selectedTherapists = [];
    $('.therapist-radio:checked').each(function() {
        const therapistId = $(this).val();
        const personIndex = $(this).attr('name').split('-')[2];
        const therapist = availableTherapists.find(t => t.therapistid == therapistId);
        
        if (therapist) {
            selectedTherapists.push({
                person: parseInt(personIndex),
                therapistId: therapistId,
                therapistName: therapist.therapist_name,
                therapistDesc: therapist.therapist_desc
            });
        }
    });

    console.log('Selected therapists:', selectedTherapists);
}

// Event handler for number of people change
$('#numPeople').on('input change', function() {
    const numPeople = parseInt($(this).val());
    if (numPeople > 0 && availableTherapists.length > 0) {
        generateTherapistSelection();
    }
    
    // Update price display
    const basePrice = parseFloat($('#selected-service-price').data('price'));
    const totalPrice = basePrice * numPeople;
    $('#selected-service-price').text(`₱${basePrice} × ${numPeople} = ₱${totalPrice}`);
});
</script>

<style>
.therapist-option {
    transition: all 0.2s ease-in-out;
}

.therapist-card {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.therapist-card:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.therapist-radio:checked + .form-check-label .therapist-card {
    background-color: #e3f2fd;
    border-color: #007bff !important;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.therapist-person-selection {
    background-color: #fafafa;
    border: 1px solid #e9ecef !important;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
</style>
