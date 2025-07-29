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
</style>

<div class="dashboard-view">
  <div class="row row-cols-1 row-cols-sm-3">
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow active" onclick="loadDashboardData('Bookings')">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Total Bookings</span>
            <div class="fs-2 fw-bold mb-1" id="filebackup_pending_count">1</div>
            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Accepted Booking: 100</span>
          </div>
        </div>
      </div>
    </div>
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('History')">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Appointment History</span>
            <div class="fs-2 fw-bold mb-1" id="filebackup_ongoing_count">1</div>
            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Appointment History</span>
          </div>
        </div>
      </div>
    </div>
    <div class="col mb-4">
      <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('Recovery')">
        <div class="card-body">
          <div class="d-flex flex-column">
            <span class="fs-6 mb-1">Update Recovery</span>
            <div class="fs-2 fw-bold mb-1" id="filebackup_received_count">1</div>
            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Total Updated Recovery: 10</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card rounded-1 shadow mb-4">
    <div class="card-header rounded-top-1">
      <div class="row">
        <div class="col-12 mt-2">
          <span class="fs-25 fw-bold text-dark text-center d-block" id="active_dashboard"></span>
        </div>
      </div>
    </div>
    <div class="card-body">
      <!-- ==================== Cancelled Request Table ==================== -->
      <div id="booking_table_container">
        <table id="total_booking_table" class="table table-striped w-100">
          <thead class="table-secondary">
            <tr>
              <th>CLIENT</th>
              <th>SERVICES</th>
              <th>DATE & TIME</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            <!-- Example row: -->
            <tr>
              <td data-label="CLIENT">Juan Dela Cruz</td>
              <td data-label="SERVICES">Massage Therapy</td>
              <td data-label="DATE & TIME">July 24, 3PM</td>
              <td data-label="STATUS">Confirmed</td>
            </tr>
            <tr>
              <td data-label="CLIENT">Sample Client 2</td>
              <td data-label="SERVICES">Facial</td>
              <td data-label="DATE & TIME">July 25, 2PM</td>
              <td data-label="STATUS">Pending</td>
            </tr>
            <!-- Add server-side pagination or JS dynamic rendering for performance -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
