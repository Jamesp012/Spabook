<style>
/* Checkbox Service Selection Styles */
#therapistServicesContainer {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#therapistServicesContainer.is-invalid {
    border-color: #dc3545;
}

/* Service selection controls styling */
.service-selection-controls {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.service-selection-controls .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

/* Service list container */
#therapistServicesList .form-check {
    padding: 12px 16px;
    border-radius: 6px;
    transition: background-color 0.15s ease-in-out;
    border: 1px solid transparent;
    margin-bottom: 8px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    position: relative;
}

#therapistServicesList .form-check:hover {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

#therapistServicesList .form-check-input {
    margin-top: 4px;
    margin-left: 0;
    margin-right: 0;
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    position: static;
}

#therapistServicesList .form-check-label {
    margin-left: 0;
    width: 100%;
    cursor: pointer;
    line-height: 1.4;
}

#therapistServicesList .form-check-input:checked + .form-check-label {
    color: #0d6efd;
    font-weight: 500;
}

#therapistServicesList .form-check:has(.form-check-input:checked) {
    background-color: #e7f3ff;
    border-color: #0d6efd;
}

#selectedServicesDisplay {
    max-height: 100px;
    overflow-y: auto;
}

.badge {
    font-size: 12px;
}



/* Validation feedback for checkboxes */
#servicesValidation {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

/* Service count display */
#serviceCount {
    font-weight: 500;
}
</style>

<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-semibold" id="therapistModalLabel">Add New Therapist</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body pt-0">
    <!-- Therapist Photo Preview + Upload -->
    <div class="mb-4">
        <label class="form-label fw-semibold">Therapist Photo (Optional)</label>
        <div class="position-relative rounded-3 overflow-hidden border border-2 shadow-sm" style="height: 200px; cursor: pointer;" onclick="document.getElementById('therapistPhoto').click();">
            <div class="therapist_photo_container">
                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                    <div class="text-center">
                        <i class="bi bi-person-plus fs-1 text-muted"></i>
                        <div class="text-muted mt-2">Click to add photo</div>
                    </div>
                </div>
            </div>
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex justify-content-center align-items-center text-white fw-semibold" style="opacity: 0; transition: opacity 0.3s;" id="uploadOverlay">
                Click to select image
            </div>
        </div>
        <input type="file" class="form-control mt-2 d-none" id="therapistPhoto" name="photo" accept="image/*">
    </div>

    <!-- Therapist Name -->
    <div class="mb-3">
        <label for="therapistName" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="therapistName" name="name" placeholder="e.g., Maria Santos" required>
        <div class="invalid-feedback"></div>
    </div>

    <!-- Service Assignment (Multiple Checkbox Selection) -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Assigned Services <span class="text-danger">*</span></label>
        <div class="d-flex align-items-center gap-2 mb-2">
            <small class="text-muted">Select multiple services this therapist can perform:</small>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshServices()" title="Refresh Services">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        
        <!-- Schema Info Alert -->
        <div class="alert alert-success alert-sm py-2 mb-2" style="font-size: 0.875rem;">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Schema:</strong> <code>therapistid, therapist_name, service_id, therapist_desc, rate</code><br>
            <strong>Multiple Services:</strong> Stored as comma-separated values in <code>service_id</code> field.
        </div>
        
        <!-- Service Checkbox Group with Controls -->
        <div id="therapistServicesContainer" class="border rounded" style="max-height: 300px;">
            <!-- Service Selection Controls (inside container) -->
            <div class="service-selection-controls d-flex justify-content-between align-items-center p-2 border-bottom bg-light d-none" id="serviceSelectionControls">
                <small class="text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="serviceCount">0 services available</span>
                </small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllServices()" title="Select All Services">
                        <i class="bi bi-check-all me-1"></i>Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllServices()" title="Clear All Services">
                        <i class="bi bi-x-circle me-1"></i>Clear All
                    </button>
                </div>
            </div>
            
            <!-- Services List -->
            <div id="therapistServicesList" class="p-3" style="max-height: 220px; overflow-y: auto;">
                <div class="text-center text-muted py-3">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading services...
                </div>
            </div>
        </div>
        
        <div class="form-text mt-2">
            <i class="bi bi-info-circle me-1"></i>
            Select multiple services that this therapist can perform. 
            <span class="text-muted">Need to add a new service? Go to <strong>Manage Services</strong> first.</span>
        </div>
        
        <!-- Selected Services Display -->
        <div id="selectedServicesDisplay" class="mt-2 p-2 bg-light rounded d-none">
            <small class="text-muted d-block mb-1">Selected Services:</small>
            <div id="selectedServicesList"></div>
        </div>
        
        <div class="invalid-feedback" id="servicesValidation"></div>
        <div id="serviceLoadError" class="text-danger small mt-1" style="display: none;">
            ⚠️ Failed to load services. <a href="#" onclick="refreshServices()" class="text-primary">Try again</a>
        </div>
    </div>



    <!-- Description -->
    <div class="mb-3">
        <label for="therapistDescription" class="form-label fw-semibold">Professional Description</label>
        <textarea class="form-control" id="therapistDescription" name="description" rows="4" 
                  placeholder="e.g., Certified massage therapist with 5+ years experience in Swedish and deep tissue massage..."></textarea>
        <div class="form-text">Brief description of therapist's expertise and experience</div>
        <div class="invalid-feedback"></div>
    </div>

    <!-- REMOVED FIELDS: Not in database schema -->
    <!-- 
    Schema: therapistid, therapist_name, service_id, therapist_desc
    Removed fields: specialties, experience_years, certification, phone
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="specialties" class="form-label fw-semibold">Specialties (Optional)</label>
            <input type="text" class="form-control" id="specialties" name="specialties" placeholder="e.g., Deep tissue, Swedish">
            <div class="form-text">Comma-separated specialties</div>
        </div>

        <div class="col-md-6">
            <label for="experienceYears" class="form-label fw-semibold">Years of Experience</label>
            <input type="number" class="form-control" id="experienceYears" name="experience" min="0" max="50" placeholder="e.g., 5">
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <label for="certification" class="form-label fw-semibold">Certification</label>
            <input type="text" class="form-control" id="certification" name="certification" placeholder="e.g., Licensed Massage Therapist">
        </div>

        <div class="col-md-6">
            <label for="therapistPhone" class="form-label fw-semibold">Contact Number</label>
            <input type="tel" class="form-control" id="therapistPhone" name="phone" placeholder="e.g., +63 912 345 6789">
        </div>
    </div>
    -->

    <!-- Status Toggle - REMOVED: 'active' column doesn't exist in therapist table -->
    <!-- 
    <div class="mb-3 mt-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="therapistActive" name="active" checked>
            <label class="form-check-label fw-semibold" for="therapistActive">
                Active Status
            </label>
            <div class="form-text">Inactive therapists won't appear in booking selections</div>
        </div>
    </div>
    -->
</div>

<div class="modal-footer border-0 pt-3">
    <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-outline-info btn-sm" onclick="testConnection();">Test Connection</button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="testServices();">Test Services</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="checkTherapistSchema();">Check Schema</button>
        <button type="button" class="btn btn-outline-warning btn-sm" onclick="testSimpleAdd();">Test Simple Add</button>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="testManualAdd();">Manual Test</button>
    </div>
    <div class="ms-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary addTherapistBtn" onclick="addNewTherapist();">Add Therapist</button>
        <button type="button" class="btn btn-success updateTherapistBtn" onclick="updateTherapist()" style="display: none;">Update Therapist</button>
    </div>
</div>

<?php include_once '../../helper/input_validation.php'; ?>
<script>
    var therapistId = '';
    var modalAction = '';

    // Initialize modal based on action
    $(document).ready(function() {
        loadServicesForModal();
        
        const data = window.modalData;
        if (data) {
            modalAction = data.action;
            
            if (data.action === 'edit' && data.therapistid) {
                therapistId = data.therapistid;
                $('#therapistModalLabel').text('Edit Therapist');
                $('.addTherapistBtn').hide();
                $('.updateTherapistBtn').show();
                loadTherapistData(therapistId);
            } else if (data.action === 'view' && data.therapistid) {
                therapistId = data.therapistid;
                $('#therapistModalLabel').text('View Therapist Details');
                $('.addTherapistBtn, .updateTherapistBtn').hide();
                loadTherapistData(therapistId, true); // Read-only mode
            } else {
                // Add mode
                $('#therapistModalLabel').text('Add New Therapist');
                $('.addTherapistBtn').show();
                $('.updateTherapistBtn').hide();
                setDefaultPhoto();
            }
        }
    });

    // Load services for checkbox selection
    function loadServicesForModal() {
        console.log('🔧 Loading services for checkbox selection...');
        
        // Reset containers
        $('#serviceSelectionControls').addClass('d-none');
        $('#therapistServicesList').html(`
            <div class="text-center text-muted py-3">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Loading services...
            </div>
        `);
        
        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_services'
            },
            success: function(result) {
                console.log('✅ Services loaded:', result);
                
                if (result && Array.isArray(result) && result.length > 0) {
                    let checkboxesHtml = '';
                    
                    result.forEach(function(service, index) {
                        const servicePrice = service.price ? ` (₱${service.price})` : '';
                        checkboxesHtml += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="therapistServices" 
                                       id="service_${service.id}" value="${service.id}" 
                                       data-service-name="${service.service_name}"
                                       data-service-price="${service.price || 0}">
                                <label class="form-check-label w-100" for="service_${service.id}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">${service.service_name}</span>
                                        ${service.price ? `<span class="text-muted small">₱${service.price}</span>` : ''}
                                    </div>
                                    ${service.description ? `<small class="text-muted d-block">${service.description}</small>` : ''}
                                </label>
                            </div>
                        `;
                    });
                    
                    $('#therapistServicesList').html(checkboxesHtml);
                    console.log('✅ Service checkboxes populated with', result.length, 'services');
                    
                    // Store services for later use
                    window.availableServices = result;
                    
                    // Update service count and show selection controls
                    $('#serviceCount').text(`${result.length} services available`);
                    $('#serviceSelectionControls').removeClass('d-none');
                    
                    // Add change event listener to update selected services display
                    $('input[name="therapistServices"]').off('change').on('change', function() {
                        updateSelectedServicesDisplay();
                        updateServiceCount();
                    });
                    
                } else {
                    $('#therapistServicesList').html(`
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-exclamation-circle fs-2 text-warning mb-2"></i>
                            <div>No services available</div>
                            <small>Add services in <strong>Manage Services</strong> first</small>
                        </div>
                    `);
                    $('#serviceCount').text('No services available');
                    console.log('⚠️ No services found');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to load services:', error);
                $('#therapistServicesList').html(`
                    <div class="text-center text-danger py-3">
                        <i class="bi bi-exclamation-triangle fs-2 mb-2"></i>
                        <div>Error loading services</div>
                        <small>${error}</small>
                    </div>
                `);
                $('#serviceCount').text('Error loading services');
                $('#serviceLoadError').show();
            }
        });
    }

    // Update selected services display
    function updateSelectedServicesDisplay() {
        const selectedCheckboxes = $('input[name="therapistServices"]:checked');
        const selectedCount = selectedCheckboxes.length;
        
        if (selectedCount > 0) {
            let displayHtml = '';
            selectedCheckboxes.each(function() {
                const serviceName = $(this).data('service-name');
                const servicePrice = $(this).data('service-price');
                displayHtml += `<span class="badge bg-primary me-1 mb-1">${serviceName}</span>`;
            });
            
            $('#selectedServicesList').html(displayHtml);
            $('#selectedServicesDisplay').removeClass('d-none');
            
            // Remove validation error if present
            $('#servicesValidation').text('');
            $('#therapistServicesContainer').removeClass('is-invalid');
        } else {
            $('#selectedServicesDisplay').addClass('d-none');
        }
    }

    // Update service count display
    function updateServiceCount() {
        const totalServices = window.availableServices ? window.availableServices.length : 0;
        const selectedCount = $('input[name="therapistServices"]:checked').length;
        
        if (selectedCount > 0) {
            $('#serviceCount').html(`
                <i class="bi bi-check-circle text-success me-1"></i>
                ${selectedCount} of ${totalServices} services selected
            `);
        } else {
            $('#serviceCount').html(`
                <i class="bi bi-info-circle me-1"></i>
                ${totalServices} services available
            `);
        }
    }

    // Refresh services function
    function refreshServices() {
        console.log('🔄 Refreshing services...');
        loadServicesForModal();
    }

    // Select all services
    function selectAllServices() {
        $('input[name="therapistServices"]').prop('checked', true);
        updateSelectedServicesDisplay();
        updateServiceCount();
        console.log('✅ All services selected');
    }

    // Clear all services
    function clearAllServices() {
        $('input[name="therapistServices"]').prop('checked', false);
        updateSelectedServicesDisplay();
        updateServiceCount();
        console.log('🧹 All services cleared');
    }

    // Get selected service IDs (multiple selection)
    function getSelectedServiceIds() {
        const selectedCheckboxes = $('input[name="therapistServices"]:checked');
        const serviceIds = [];
        
        selectedCheckboxes.each(function() {
            serviceIds.push($(this).val());
        });
        
        return serviceIds;
    }

    // Set selected service IDs (for edit mode)
    function setSelectedServiceIds(serviceIds) {
        // Clear all checkboxes first
        $('input[name="therapistServices"]').prop('checked', false);
        
        if (serviceIds && serviceIds.length > 0) {
            // Convert to array if it's a comma-separated string
            if (typeof serviceIds === 'string') {
                serviceIds = serviceIds.split(',').map(id => id.trim()).filter(id => id);
            }
            
            console.log('🎯 Setting selected services:', serviceIds);
            
            // Select all matching service IDs
            serviceIds.forEach(function(serviceId) {
                $(`input[name="therapistServices"][value="${serviceId}"]`).prop('checked', true);
            });
            
            updateSelectedServicesDisplay();
            updateServiceCount();
        } else {
            updateSelectedServicesDisplay();
            updateServiceCount();
        }
    }

    // Validate at least one service is selected
    function validateServiceSelection() {
        const selectedIds = getSelectedServiceIds();
        
        if (!selectedIds || selectedIds.length === 0) {
            $('#servicesValidation').text('Please select at least one service for this therapist.');
            $('#therapistServicesContainer').addClass('is-invalid');
            return false;
        } else {
            $('#servicesValidation').text('');
            $('#therapistServicesContainer').removeClass('is-invalid');
            return true;
        }
    }

    // Load therapist data for edit/view
    function loadTherapistData(id, readOnly = false) {
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_therapist_by_id',
                therapist_id: id
            },
            success: function(result) {
                if (result && result.therapistid) {
                    populateTherapistForm(result, readOnly);
                }
            }
        });
    }

    // Populate form with therapist data (schema-compatible fields only)
    function populateTherapistForm(therapist, readOnly = false) {
        $('#therapistName').val(therapist.therapist_name || '');
        $('#therapistDescription').val(therapist.therapist_desc || '');

        // Set assigned services (multiple selection)
        if (therapist.service_id) {
            console.log('🎯 Setting selected services from service_id:', therapist.service_id);
            // Handle comma-separated service IDs
            setSelectedServiceIds(therapist.service_id);
        } else if (therapist.service_ids && therapist.service_ids.length > 0) {
            // Handle array format
            console.log('🔄 Setting services from service_ids array:', therapist.service_ids);
            setSelectedServiceIds(therapist.service_ids);
        } else if (therapist.assigned_services && therapist.assigned_services.length > 0) {
            // Handle service objects
            const serviceIds = therapist.assigned_services.map(service => service.id.toString());
            console.log('📋 Setting services from assigned_services:', serviceIds);
            setSelectedServiceIds(serviceIds);
        }

        // If read-only mode, disable all inputs
        if (readOnly) {
            $('#therapistName, #therapistDescription')
                .prop('disabled', true);
            $('input[name="therapistServices"]').prop('disabled', true);
            $('.modal-footer .btn-primary, .modal-footer .btn-success').hide();
        }
    }

    // Set default photo for add mode
    function setDefaultPhoto() {
        $('.therapist_photo_container').html(`
            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                <div class="text-center">
                    <i class="bi bi-person-plus fs-1 text-muted"></i>
                    <div class="text-muted mt-2">Click to add photo</div>
                </div>
            </div>
        `);
    }

    // Add new therapist
    function addNewTherapist() {
        console.log('🚀 Starting addNewTherapist function...');
        
        // Simple validation first
        const therapistName = $('#therapistName').val().trim();
        if (!therapistName) {
            Swal.fire({
                icon: 'warning',
                title: 'Name Required',
                text: 'Please enter the therapist name.'
            });
            return;
        }
        
        const selectedServiceIds = getSelectedServiceIds();
        if (selectedServiceIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Services Required',
                html: `
                    <p>Please select at least one service for this therapist.</p>
                    <hr>
                    <small class="text-muted">
                        <strong>Troubleshooting:</strong><br>
                        • Click "Test Services" to see available services<br>
                        • If no services appear, create services first in Manage Services<br>
                        • Use "Manual Test" to test with a specific service ID
                    </small>
                `,
                footer: 'Run diagnostic tests below if issues persist'
            });
            return;
        }
        
        console.log('✅ Validation passed. Name:', therapistName, 'Services:', selectedServiceIds);
        
        // Show loading
        Swal.fire({
            title: 'Adding Therapist...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Schema-compatible object format with multiple services
        const requestData = {
            action: 'add_therapist',
            therapist_name: therapistName,
            service_ids: selectedServiceIds, // Multiple services array
            therapist_desc: $('#therapistDescription').val().trim() || ''
        };
        
        console.log('📤 Sending request data:', requestData);
        
        // Try with regular data first
        console.log('🔄 Attempting with regular POST data...');
        
        // Send request
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: requestData,
            timeout: 15000, // 15 second timeout
            success: function(response) {
                console.log('📥 Response received:', response);
                
                if (response && response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: `Therapist "${therapistName}" has been added successfully.`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Close modal and refresh
                    $('#globalModal').modal('hide');
                    if (typeof loadTherapists === 'function') {
                        loadTherapists();
                    }
                } else {
                    console.error('❌ Error in response:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Add Therapist',
                        text: response?.message || 'An unknown error occurred.',
                        footer: 'Check the console for more details.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('💥 Regular AJAX Failed, trying FormData...', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                // Try with FormData as fallback
                console.log('🔄 Attempting with FormData...');
                
                $.ajax({
                    url: '../controller/therapist_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 15000,
                    success: function(response) {
                        console.log('📥 FormData Response received:', response);
                        
                        if (response && response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: `Therapist "${therapistName}" has been added successfully.`,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            
                            $('#globalModal').modal('hide');
                            if (typeof loadTherapists === 'function') {
                                loadTherapists();
                            }
                        } else {
                            console.error('❌ FormData Error in response:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'FormData Request Failed',
                                text: response?.message || 'Unknown error occurred.',
                                footer: 'Check console for details.'
                            });
                        }
                    },
                    error: function(xhr2, status2, error2) {
                        console.error('💥 Both requests failed!', {
                            regular: { status, error, responseText: xhr.responseText, statusCode: xhr.status },
                            formData: { status: status2, error: error2, responseText: xhr2.responseText, statusCode: xhr2.status }
                        });
                        
                        let errorMessage = 'Both request methods failed.';
                        if (xhr.status === 400 || xhr2.status === 400) {
                            errorMessage = 'Bad Request (400): Server rejected the data format.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Controller not found (404). Check file path.';
                        } else if (xhr.status === 500 || xhr2.status === 500) {
                            errorMessage = 'Server error (500). Check PHP logs.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            html: `
                                <div class="text-start">
                                    <p>${errorMessage}</p>
                                    <hr>
                                    <small><strong>Regular Request:</strong> ${status} (${xhr.status})</small><br>
                                    <small><strong>FormData Request:</strong> ${status2} (${xhr2.status})</small><br>
                                    <small><strong>Response:</strong> ${xhr.responseText || xhr2.responseText || 'No response'}</small>
                                </div>
                            `
                        });
                    }
                });
            }
        });
    }

    // Test connection function
    function testConnection() {
        console.log('🧪 Testing connection...');
        
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'test_connection' },
            success: function(response) {
                console.log('✅ Test successful:', response);
                Swal.fire({
                    icon: 'success',
                    title: 'Connection Test Successful',
                    text: response.message,
                    footer: `Timestamp: ${response.timestamp}`
                });
            },
            error: function(xhr, status, error) {
                console.error('❌ Test failed:', { status, error, responseText: xhr.responseText });
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Test Failed',
                    text: `Status: ${status}, Error: ${error}`,
                    footer: `Response: ${xhr.responseText || 'No response'}`
                });
            }
        });
    }

    // Test services function
    function testServices() {
        console.log('🧪 Testing services fetch...');
        
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'test_services' },
            success: function(response) {
                console.log('✅ Services test result:', response);
                Swal.fire({
                    icon: response.status === 'success' ? 'success' : 'error',
                    title: 'Services Test Result',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${response.status}</p>
                            <p><strong>Message:</strong> ${response.message}</p>
                            <p><strong>Services Found:</strong> ${response.services ? response.services.length : 0}</p>
                            <hr>
                            <pre style="text-align: left; font-size: 11px; max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
                        </div>
                    `,
                    width: '600px'
                });
            },
            error: function(xhr, status, error) {
                console.error('❌ Services test failed:', { status, error, responseText: xhr.responseText });
                Swal.fire({
                    icon: 'error',
                    title: 'Services Test Failed',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${status}</p>
                            <p><strong>Error:</strong> ${error}</p>
                            <p><strong>Response:</strong></p>
                            <pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">${xhr.responseText || 'No response'}</pre>
                        </div>
                    `
                });
            }
        });
    }

    // Test simple add function
    function testSimpleAdd() {
        console.log('🧪 Testing simple add...');
        
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'test_add_simple' },
            success: function(response) {
                console.log('✅ Simple add test result:', response);
                Swal.fire({
                    icon: response.status === 'success' ? 'success' : 'error',
                    title: 'Simple Add Test Result',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${response.status}</p>
                            <p><strong>Message:</strong> ${response.message}</p>
                            <hr>
                            <pre style="text-align: left; font-size: 12px; max-height: 200px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
                        </div>
                    `
                });
            },
            error: function(xhr, status, error) {
                console.error('❌ Simple add test failed:', { status, error, responseText: xhr.responseText });
                Swal.fire({
                    icon: 'error',
                    title: 'Simple Add Test Failed',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${status}</p>
                            <p><strong>Error:</strong> ${error}</p>
                            <p><strong>Response:</strong></p>
                            <pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">${xhr.responseText || 'No response'}</pre>
                        </div>
                    `
                });
            }
        });
    }

    // Check therapist schema function
    function checkTherapistSchema() {
        console.log('🔍 Checking therapist table schema...');
        
        $.ajax({
            url: '../controller/therapist_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'check_therapist_schema' },
            success: function(response) {
                console.log('✅ Schema check result:', response);
                Swal.fire({
                    icon: response.status === 'success' ? 'success' : 'error',
                    title: 'Therapist Schema Check',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${response.status}</p>
                            <p><strong>Message:</strong> ${response.message}</p>
                            <hr>
                            <p><strong>Available Columns:</strong></p>
                            <pre style="text-align: left; font-size: 11px; max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
                        </div>
                    `,
                    width: '700px'
                });
            },
            error: function(xhr, status, error) {
                console.error('❌ Schema check failed:', { status, error, responseText: xhr.responseText });
                Swal.fire({
                    icon: 'error',
                    title: 'Schema Check Failed',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${status}</p>
                            <p><strong>Error:</strong> ${error}</p>
                            <p><strong>Response:</strong></p>
                            <pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">${xhr.responseText || 'No response'}</pre>
                        </div>
                    `
                });
            }
        });
    }

    // Test manual add function
    function testManualAdd() {
        console.log('🧪 Testing manual add...');
        
        Swal.fire({
            title: 'Manual Test Configuration',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Service ID to use:</label>
                        <input type="number" id="testServiceId" class="form-control" value="1" min="1">
                        <small class="text-muted">Enter a service ID that exists in your database</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Test Therapist Name:</label>
                        <input type="text" id="testTherapistName" class="form-control" value="Manual Test Therapist">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Run Test',
            preConfirm: () => {
                const serviceId = document.getElementById('testServiceId').value;
                const therapistName = document.getElementById('testTherapistName').value;
                
                if (!serviceId || !therapistName) {
                    Swal.showValidationMessage('Please fill in both fields');
                    return false;
                }
                
                return { serviceId, therapistName };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { serviceId, therapistName } = result.value;
                
                console.log('Running manual test with Service ID:', serviceId, 'Name:', therapistName);
                
                $.ajax({
                    url: '../controller/therapist_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { 
                        action: 'test_add_manual',
                        service_id: serviceId,
                        therapist_name: therapistName
                    },
                    success: function(response) {
                        console.log('✅ Manual test result:', response);
                        Swal.fire({
                            icon: response.status === 'success' ? 'success' : 'error',
                            title: 'Manual Test Result',
                            html: `
                                <div class="text-start">
                                    <p><strong>Status:</strong> ${response.status}</p>
                                    <p><strong>Message:</strong> ${response.message}</p>
                                    <p><strong>Used Service ID:</strong> ${response.used_service_id || 'N/A'}</p>
                                    <p><strong>Used Therapist Name:</strong> ${response.used_therapist_name || 'N/A'}</p>
                                    <hr>
                                    <pre style="text-align: left; font-size: 11px; max-height: 300px; overflow-y: auto;">${JSON.stringify(response, null, 2)}</pre>
                                </div>
                            `,
                            width: '700px'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Manual test failed:', { status, error, responseText: xhr.responseText });
                        Swal.fire({
                            icon: 'error',
                            title: 'Manual Test Failed',
                            html: `
                                <div class="text-start">
                                    <p><strong>Status:</strong> ${status}</p>
                                    <p><strong>Error:</strong> ${error}</p>
                                    <p><strong>Response:</strong></p>
                                    <pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">${xhr.responseText || 'No response'}</pre>
                                </div>
                            `
                        });
                    }
                });
            }
        });
    }

    // Update therapist
    function updateTherapist() {
        // Validate required fields and service selection
        const isNameValid = inputValidation('therapistName');
        const isServiceValid = validateServiceSelection();
        
        if (isNameValid && isServiceValid) {
            const photoValue = $('#therapist_photo').attr('value') || null;
            const selectedServiceIds = getSelectedServiceIds();
            
            console.log('🔄 Updating therapist with services:', selectedServiceIds);
            
            $.ajax({
                url: '../controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update_therapist',
                    therapist_id: therapistId,
                    therapist_name: $('#therapistName').val(),
                    service_ids: selectedServiceIds, // Controller will convert to service_id string
                    therapist_desc: $('#therapistDescription').val()
                },
                beforeSend: function() {
                    showLoadingSpinner('Updating Therapist...');
                },
                success: function(response) {
                    if (response && response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Therapist Updated Successfully',
                            text: selectedServiceIds.length > 0 ? `Now assigned to ${selectedServiceIds.length} service(s)` : 'Therapist updated',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $('#globalModal').modal('hide');
                        if (typeof loadTherapists === 'function') {
                            loadTherapists();
                        }
                        if (typeof loadStats === 'function') {
                            loadStats();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Updating Therapist',
                            text: response?.message || 'Unknown error occurred'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update therapist. Please try again.'
                    });
                }
            });
        }
    }

    // Photo upload handler
    $('#therapistPhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.therapist_photo_container').html(
                    `<img src="${e.target.result}" value="${e.target.result}" class="img-fluid rounded-3" id="therapist_photo" style="height: 200px; width: 100%; object-fit: cover;" alt="Preview">`
                );
                
                // Upload to server (if you have image upload functionality)
                // For now, we'll store the base64 data
                $('#therapist_photo').attr('value', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Show loading spinner
    function showLoadingSpinner(message) {
        Swal.fire({
            title: message,
            html: `
                <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
                    <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
                </div>
                <style>
                    .custom-spinner-glow {
                        animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
                        filter: drop-shadow(0 0 16px #a1623f);
                    }
                    @keyframes spin {
                        100% { transform: rotate(360deg); }
                    }
                    @keyframes glow {
                        0% { filter: drop-shadow(0 0 8px #a1623f); }
                        100% { filter: drop-shadow(0 0 32px #a1623f); }
                    }
                </style>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true,
        });
    }

    // Upload overlay hover effect
    $('.therapist_photo_container').parent().hover(
        function() {
            $('#uploadOverlay').css('opacity', '1');
        },
        function() {
            $('#uploadOverlay').css('opacity', '0');
        }
    );
</script>

<style>
.form-label {
    color: #374151;
}

.form-control:focus,
.form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.therapist_photo_container {
    height: 200px;
    width: 100%;
    border-radius: 0.375rem;
    overflow: hidden;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.text-danger {
    color: #dc2626 !important;
}

/* Service selection styles */
.service-checkbox:checked + .form-check-label {
    color: #0d6efd;
    font-weight: 600;
}

.service-checkbox {
    margin-top: 0.2rem;
}

.form-check-label {
    cursor: pointer;
    padding-left: 0.5rem;
}

.form-check {
    padding: 0.375rem;
    border-radius: 0.375rem;
    transition: background-color 0.15s ease-in-out;
}

.form-check:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.service-checkbox:checked + .form-check-label {
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    margin-left: -0.5rem;
}

#servicesCheckboxContainer.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>