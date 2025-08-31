<style>
/* Therapist Status Modal Styles */
.therapist-card {
  transition: all 0.3s ease;
  border: 1px solid #dee2e6;
}

.therapist-card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.therapist-card.opacity-50 {
  opacity: 0.5 !important;
  pointer-events: none;
}

.form-switch .form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}

.form-switch .form-check-input:not(:checked) {
  background-color: #dc3545;
  border-color: #dc3545;
}

.form-switch .form-check-input {
  width: 3em;
  height: 1.5em;
}

/* Loading overlay for individual cards */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  z-index: 10;
}

/* Statistics cards */
.stat-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0.5rem;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: scale(1.02);
}
</style>

<div class="modal-header bg-primary text-white">
  <h5 class="modal-title">
    <i class="bi bi-people-fill me-2"></i>
    Therapist Status Management
  </h5>
  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
  <!-- Search and Filter Section -->
  <div class="row mb-3">
    <div class="col-md-8">
      <div class="input-group">
        <span class="input-group-text">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" class="form-control" id="therapistSearch" placeholder="Search therapists by name or service...">
      </div>
    </div>
    <div class="col-md-4">
      <select class="form-select" id="statusFilter">
        <option value="">All Therapists</option>
        <option value="1">Active Only</option>
        <option value="0">Inactive Only</option>
      </select>
    </div>
  </div>

  <!-- Loading State -->
  <div id="therapistLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 text-muted">Loading therapists...</p>
  </div>

  <!-- Therapist List -->
  <div id="therapistList" style="display: none; max-height: 400px; overflow-y: auto;">
    <!-- Dynamic content will be loaded here -->
  </div>

  <!-- Empty State -->
  <div id="therapistEmptyState" style="display: none;" class="text-center py-5">
    <i class="bi bi-people display-1 text-muted"></i>
    <h5 class="text-muted mt-3">No Therapists Found</h5>
    <p class="text-muted">No therapists match your current search criteria.</p>
  </div>

  <!-- Error State -->
  <div id="therapistErrorState" style="display: none;" class="text-center py-5">
    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
    <h5 class="text-warning mt-3">Connection Error</h5>
    <p class="text-muted mb-3" id="errorMessage">Unable to load therapist data. Please try again.</p>
    <button type="button" class="btn btn-primary" onclick="retryLoadTherapists()">
      <i class="bi bi-arrow-clockwise me-2"></i>
      Retry
    </button>
    <button type="button" class="btn btn-outline-secondary ms-2" onclick="testConnection()">
      <i class="bi bi-wifi me-2"></i>
      Test Connection
    </button>
  </div>

  <!-- Statistics Summary -->
  <div id="therapistStats" class="mt-4" style="display: none;">
    <div class="row g-3">
      <div class="col-4">
        <div class="stat-card p-3 text-center">
          <div class="fw-bold text-success fs-4" id="activeCount">0</div>
          <small class="text-muted">Active</small>
        </div>
      </div>
      <div class="col-4">
        <div class="stat-card p-3 text-center">
          <div class="fw-bold text-danger fs-4" id="inactiveCount">0</div>
          <small class="text-muted">Inactive</small>
        </div>
      </div>
      <div class="col-4">
        <div class="stat-card p-3 text-center">
          <div class="fw-bold text-primary fs-4" id="totalCount">0</div>
          <small class="text-muted">Total</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
    <div>
      <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Click the toggle switches to activate/deactivate therapists
      </small>
    </div>
    <div>
      <button type="button" class="btn btn-success btn-sm me-2" onclick="toggleAllTherapists(true)">
        <i class="bi bi-check-circle me-1"></i>
        Activate All
      </button>
      <button type="button" class="btn btn-warning btn-sm" onclick="toggleAllTherapists(false)">
        <i class="bi bi-pause-circle me-1"></i>
        Deactivate All
      </button>
    </div>
  </div>
</div>

<script>
// This script runs when the modal is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Initialize the therapist status management when modal loads
  initializeTherapistStatusModal();
});

function initializeTherapistStatusModal() {
  console.log('🔄 Initializing Therapist Status Modal...');
  
  // Initialize global variables for this modal
  window.allTherapists = [];
  window.filteredTherapists = [];
  
  // Set up search and filter event listeners
  $('#therapistSearch').off('input.therapist').on('input.therapist', function() {
    filterTherapists();
  });

  $('#statusFilter').off('change.therapist').on('change.therapist', function() {
    filterTherapists();
  });

  // Load therapist data
  loadTherapistStatus();
}

// Load therapist status data
function loadTherapistStatus() {
  console.log('🔄 loadTherapistStatus: Starting AJAX call...');
  showTherapistLoading();
  
  $.ajax({
    url: '../controller/admin_dashboard_contr.php',
    type: 'POST',
    dataType: 'json',
    timeout: 30000, // 30 second timeout
    data: { action: 'get_therapist_status' },
    success: function(response) {
      console.log('✅ loadTherapistStatus: AJAX success', response);
      hideTherapistLoading();
      
      if (response && response.status === 'success') {
        window.allTherapists = response.data;
        window.filteredTherapists = response.data;
        displayTherapistList(response.data);
        updateTherapistStats(response.data);
        
        if (response.data.length === 0) {
          showTherapistEmptyState();
        }
        
        console.log(`📊 Loaded ${response.data.length} therapists successfully`);
      } else {
        console.error('❌ Server returned error:', response ? response.message : 'Unknown error');
        showTherapistEmptyState();
        showNotification('Error: ' + (response ? response.message : 'Unknown error'), 'error');
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ AJAX Error loading therapist status:', {
        status: status,
        error: error,
        statusText: xhr.statusText,
        responseText: xhr.responseText
      });
      
      hideTherapistLoading();
      showTherapistErrorState();
      
      let errorMessage = 'Failed to load therapist data. ';
      if (status === 'timeout') {
        errorMessage += 'Request timed out after 30 seconds.';
      } else if (status === 'error') {
        errorMessage += 'Server error occurred.';
      } else if (xhr.status === 0) {
        errorMessage += 'Network connection error.';
      } else {
        errorMessage += `Error: ${error} (HTTP ${xhr.status})`;
      }
      
      $('#errorMessage').text(errorMessage);
      showNotification(errorMessage, 'error');
    },
    beforeSend: function() {
      console.log('🚀 loadTherapistStatus: Sending AJAX request to ../controller/admin_dashboard_contr.php');
    }
  });
}

// Filter therapists based on search and status
function filterTherapists() {
  const searchTerm = $('#therapistSearch').val().toLowerCase();
  const statusFilter = $('#statusFilter').val();
  
  window.filteredTherapists = window.allTherapists.filter(therapist => {
    const matchesSearch = therapist.therapist_name.toLowerCase().includes(searchTerm) ||
                        therapist.services_text.toLowerCase().includes(searchTerm) ||
                        therapist.therapist_desc.toLowerCase().includes(searchTerm);
    
    const matchesStatus = statusFilter === '' || 
                        therapist.is_active.toString() === statusFilter;
    
    return matchesSearch && matchesStatus;
  });
  
  displayTherapistList(window.filteredTherapists);
  
  if (window.filteredTherapists.length === 0) {
    showTherapistEmptyState();
  } else {
    hideTherapistEmptyState();
  }
}

// Display therapist list
function displayTherapistList(therapists) {
  let html = '';
  
  therapists.forEach(therapist => {
    const statusClass = therapist.is_active ? 'text-success' : 'text-danger';
    const statusIcon = therapist.is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    const toggleChecked = therapist.is_active ? 'checked' : '';
    const serviceCount = therapist.service_count;
    
    html += `
      <div class="card mb-3 therapist-card position-relative" data-therapist-id="${therapist.therapistid}">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center mb-2">
                <i class="bi ${statusIcon} ${statusClass} me-2 fs-5"></i>
                <h6 class="mb-0 fw-bold">${therapist.therapist_name}</h6>
              </div>
              <p class="text-muted mb-2 small">${therapist.therapist_desc || 'No description available'}</p>
              <div class="d-flex align-items-center">
                <span class="badge bg-info me-2">${serviceCount} service${serviceCount !== 1 ? 's' : ''}</span>
                <small class="text-muted">${therapist.services_text || 'No services assigned'}</small>
              </div>
            </div>
            <div class="col-md-5">
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-center">
                  <span class="fw-bold ${statusClass} d-block">${therapist.status_text}</span>
                  <small class="text-muted">Current Status</small>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" 
                         id="therapist_${therapist.therapistid}" 
                         ${toggleChecked}
                         onchange="toggleTherapistStatus(${therapist.therapistid}, this.checked)">
                  <label class="form-check-label" for="therapist_${therapist.therapistid}">
                    ${therapist.is_active ? 'Active' : 'Inactive'}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  $('#therapistList').html(html).show();
}

// Toggle individual therapist status
function toggleTherapistStatus(therapistId, isActive) {
  const therapistCard = $(`.therapist-card[data-therapist-id="${therapistId}"]`);
  const originalState = !isActive; // Store original state for rollback
  
  // Show loading overlay on the specific card
  showCardLoading(therapistCard);
  
  $.ajax({
    url: '../controller/admin_dashboard_contr.php',
    type: 'POST',
    dataType: 'json',
    data: { 
      action: 'update_therapist_status',
      therapist_id: therapistId,
      is_active: isActive
    },
    success: function(response) {
      hideCardLoading(therapistCard);
      
      if (response.status === 'success') {
        // Update the local data
        const therapist = window.allTherapists.find(t => t.therapistid == therapistId);
        if (therapist) {
          therapist.is_active = isActive;
          therapist.status_text = isActive ? 'Active' : 'Inactive';
        }
        
        // Refresh the display
        displayTherapistList(window.filteredTherapists);
        updateTherapistStats(window.allTherapists);
        
        // Show success notification
        showNotification(response.message, 'success');
      } else {
        // Rollback the toggle
        $(`#therapist_${therapistId}`).prop('checked', originalState);
        showNotification('Error: ' + response.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      hideCardLoading(therapistCard);
      // Rollback the toggle
      $(`#therapist_${therapistId}`).prop('checked', originalState);
      showNotification('Error updating therapist status: ' + error, 'error');
    }
  });
}

// Toggle all therapists
function toggleAllTherapists(isActive) {
  const action = isActive ? 'activate' : 'deactivate';
  const count = window.allTherapists.length;
  
  if (!confirm(`Are you sure you want to ${action} all ${count} therapist(s)?`)) {
    return;
  }
  
  // Show loading state
  $('#therapistList').addClass('opacity-50');
  
  $.ajax({
    url: '../controller/admin_dashboard_contr.php',
    type: 'POST',
    dataType: 'json',
    data: { 
      action: 'toggle_all_therapists',
      is_active: isActive
    },
    success: function(response) {
      $('#therapistList').removeClass('opacity-50');
      
      if (response.status === 'success') {
        // Reload therapist data
        loadTherapistStatus();
        showNotification(response.message, 'success');
      } else {
        showNotification('Error: ' + response.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      $('#therapistList').removeClass('opacity-50');
      showNotification('Error toggling all therapists: ' + error, 'error');
    }
  });
}

// Update therapist statistics
function updateTherapistStats(therapists) {
  const activeCount = therapists.filter(t => t.is_active).length;
  const inactiveCount = therapists.filter(t => !t.is_active).length;
  const totalCount = therapists.length;
  
  $('#activeCount').text(activeCount);
  $('#inactiveCount').text(inactiveCount);
  $('#totalCount').text(totalCount);
  $('#therapistStats').show();
}

// Helper functions for therapist modal
function showTherapistLoading() {
  $('#therapistLoading').show();
  $('#therapistList').hide();
  $('#therapistEmptyState').hide();
  $('#therapistErrorState').hide();
  $('#therapistStats').hide();
}

function hideTherapistLoading() {
  $('#therapistLoading').hide();
}

function showTherapistEmptyState() {
  $('#therapistEmptyState').show();
  $('#therapistList').hide();
  $('#therapistErrorState').hide();
}

function hideTherapistEmptyState() {
  $('#therapistEmptyState').hide();
}

function showTherapistErrorState() {
  $('#therapistErrorState').show();
  $('#therapistEmptyState').hide();
  $('#therapistList').hide();
}

function hideTherapistErrorState() {
  $('#therapistErrorState').hide();
}

// Card loading helpers
function showCardLoading(card) {
  if (card.find('.loading-overlay').length === 0) {
    card.addClass('position-relative').append(`
      <div class="loading-overlay">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    `);
  }
}

function hideCardLoading(card) {
  card.find('.loading-overlay').remove();
}

// Show notification function
function showNotification(message, type) {
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
  
  const notification = $(`
    <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
         style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
      <i class="bi ${iconClass} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `);
  
  $('body').append(notification);
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    notification.alert('close');
  }, 5000);
}

// Retry loading therapists
function retryLoadTherapists() {
  console.log('🔄 Retrying therapist load...');
  hideTherapistErrorState();
  loadTherapistStatus();
}

// Test connection to controller
function testConnection() {
  console.log('🧪 Testing controller connection...');
  
  // Show loading on test button
  const testBtn = $('button[onclick="testConnection()"]');
  const originalHtml = testBtn.html();
  testBtn.html('<i class="bi bi-hourglass-split me-2"></i>Testing...').prop('disabled', true);
  
  $.ajax({
    url: '../controller/admin_dashboard_contr.php',
    type: 'POST',
    dataType: 'json',
    timeout: 10000, // 10 second timeout for test
    data: { action: 'test_connection' },
    success: function(response) {
      console.log('✅ Connection test successful:', response);
      testBtn.html(originalHtml).prop('disabled', false);
      
      if (response && response.status === 'success') {
        showNotification('Connection test successful! Controller is accessible.', 'success');
        // Automatically retry loading therapists after successful connection test
        setTimeout(() => {
          retryLoadTherapists();
        }, 1000);
      } else {
        showNotification('Connection test failed: ' + (response ? response.message : 'Unknown error'), 'error');
      }
    },
    error: function(xhr, status, error) {
      console.error('❌ Connection test failed:', {
        status: status,
        error: error,
        statusText: xhr.statusText,
        responseText: xhr.responseText
      });
      
      testBtn.html(originalHtml).prop('disabled', false);
      
      let errorMessage = 'Connection test failed: ';
      if (status === 'timeout') {
        errorMessage += 'Request timed out.';
      } else if (xhr.status === 0) {
        errorMessage += 'Network connection error.';
      } else {
        errorMessage += `${error} (HTTP ${xhr.status})`;
      }
      
      showNotification(errorMessage, 'error');
    }
  });
}
</script>