<style>
  @media (max-width: 768px) {
    #total_booking_table thead {
      display: none;
    }

    #total_booking_table tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 0.75rem;
      background-color: #fff;
    }

    #total_booking_table td {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      border: none !important;
      font-size: 0.95rem;
    }

    #total_booking_table td::before {
      content: attr(data-label);
      font-weight: 600;
      flex: 1;
      color: #555;
    }

    .dashboard-view .card-body .fs-6 {
      font-size: 0.9rem;
    }

    .dashboard-view .card-body .fs-2 {
      font-size: 1.5rem;
    }

    .dashboard-view .card-body .text-truncate {
      white-space: normal;
    }
  }

  /* Therapist Modal Styles */
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

  /* Notification styles */
  .alert {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  }

  /* Custom card hover effect for dashboard stats */
  .custom_card_hover {
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .custom_card_hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
  }

  .custom_card_hover.active {
    border-left: 4px solid #0d6efd;
    box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
  }

  /* Loading spinner customization */
  .spinner-border-sm {
    width: 1rem;
    height: 1rem;
  }
  
  /* Pagination customization */
  .pagination {
    margin-top: 1rem;
    margin-bottom: 0;
  }
  
  .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }
  
  .page-link {
    color: #0d6efd;
  }
  
  .page-link:hover {
    color: #0a58ca;
  }
  
  @media (max-width: 768px) {
    .pagination {
      justify-content: center;
    }
    
    .page-link {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
    }
  }
  
  /* Responsive Table Heights */
  .table-body-scroll {
    border-top: 1px solid #dee2e6;
    overflow-y: auto;
    /* Dynamic height: Full viewport minus header, nav, footer, and padding */
    max-height: calc(100vh - 200px);
    /* Minimum height to prevent too small tables */
    min-height: 300px;
  }
  
  /* Responsive adjustments */
  @media (max-width: 767px) {
    .table-body-scroll {
      /* Smaller height on mobile to save space */
      max-height: calc(100vh - 250px);
      min-height: 250px;
    }
  }
  
  @media (min-width: 1400px) {
    .table-body-scroll {
      /* More space on large screens */
      max-height: calc(100vh - 180px);
      min-height: 400px;
    }
  }
  
  .table-body-scroll .table {
    margin-bottom: 0;
  }
  
  .table-body-scroll::-webkit-scrollbar {
    width: 8px;
  }
  
  .table-body-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }
  
  .table-body-scroll::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
  }
  
  .table-body-scroll::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }
</style>

<div class="dashboard-view">
  <!-- Dashboard Stats Cards -->
  <div class="row row-cols-1 row-cols-sm-3">
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow active" onclick="loadDashboardData('Bookings')" id="bookings-card">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Total Bookings</span>
            <div class="fs-2 fw-bold mb-1" id="total_bookings_count">
              <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <span class="fs-6" id="bookings_subtitle">
              <i class="pe-2 bi bi-hourglass-split"></i>Loading...
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('History')" id="history-card">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Appointment History</span>
            <div class="fs-2 fw-bold mb-1" id="history_count">
              <div class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <span class="fs-6" id="history_subtitle">
              <i class="pe-2 bi bi-clock-history"></i>Loading...
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('Recovery')" id="recovery-card">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Recovery Management</span>
            <div class="fs-2 fw-bold mb-1" id="recovery_count">
              <div class="spinner-border spinner-border-sm text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <span class="fs-6" id="recovery_subtitle">
              <i class="pe-2 bi bi-arrow-repeat"></i>Loading...
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Data Display Card -->
  <div class="card rounded-1 shadow mb-4">
    <div class="card-header rounded-top-1 d-flex justify-content-between align-items-center">
      <div class="col-auto">
        <h5 class="mb-0" id="active_dashboard">Dashboard Overview</h5>
      </div>
    </div>
    <div class="card-body">
      <!-- Loading State -->
      <div id="dashboard_loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading dashboard data...</p>
      </div>
      
      <!-- Data Table Container -->
      <div id="dashboard_table_container" style="display: none;">
        <table id="dashboard_data_table" class="table table-striped w-100">
          <thead class="table-secondary" id="table_header">
            <!-- Dynamic headers will be inserted here -->
          </thead>
        </table>
        <div class="table-body-scroll">
          <table class="table table-striped w-100">
            <tbody id="table_body">
              <!-- Dynamic data will be inserted here -->
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Recovery Actions Container -->
      <div id="recovery_actions_container" style="display: none;">
        <div class="row">
          <div class="col-md-6">
            <div class="card border-warning">
              <div class="card-header bg-warning text-white">
                <h6 class="mb-0">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  Recoverable Bookings
                </h6>
              </div>
              <div class="card-body">
                <div id="recoverable_bookings">
                  <!-- Recoverable bookings will be loaded here -->
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-success">
              <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                  <i class="bi bi-check-circle me-2"></i>
                  Recently Recovered
                </h6>
              </div>
              <div class="card-body">
                <div id="recovered_bookings">
                  <!-- Recently recovered bookings will be loaded here -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div id="dashboard_empty_state" style="display: none;" class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
        <h5 class="text-muted">No Data Available</h5>
        <p class="text-muted">There's no data to display for this section yet.</p>
      </div>
    </div>
  </div>
</div>


