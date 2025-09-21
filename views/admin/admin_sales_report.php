<?php
// Admin Sales & Commission Report view - Content only for admin panel
?>
<style>
.avatar-sm {
  width: 32px;
  height: 32px;
  font-size: 14px;
  font-weight: bold;
}
</style>
<div class="container-fluid">  
  <!-- Tabs Navigation -->
  <ul class="nav nav-tabs nav-fill mb-3" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-report" type="button" role="tab" aria-controls="sales-report" aria-selected="true">
        <i class="fas fa-chart-line me-2"></i>Sales Report
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="commission-tab" data-bs-toggle="tab" data-bs-target="#commission-report" type="button" role="tab" aria-controls="commission-report" aria-selected="false">
        <i class="fas fa-users me-2"></i>Commission Report
      </button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="reportTabsContent">
    <!-- Sales Report Tab -->
    <div class="tab-pane fade show active" id="sales-report" role="tabpanel" aria-labelledby="sales-tab">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-4">
            <button class="btn btn-outline-primary active" data-period="daily" data-report="sales">Daily</button>
            <button class="btn btn-outline-primary" data-period="weekly" data-report="sales">Weekly</button>
            <button class="btn btn-outline-primary" data-period="monthly" data-report="sales">Monthly</button>
          </div>
      
      <div id="summary" class="row g-3">
        <div class="col-xl-3 col-md-6">
          <div class="card bg-light border-0">
            <div class="card-body text-center">
              <div class="text-muted small">Period Start</div>
              <div id="sum-start" class="h5 mb-0">-</div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card bg-success text-white">
            <div class="card-body text-center">
              <div class="text-white-50 small">Total Sales</div>
              <div id="sum-sales" class="h4 mb-0">₱0.00</div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card bg-warning text-dark">
            <div class="card-body text-center">
              <div class="text-dark-50 small">Commissions</div>
              <div id="sum-commission" class="h4 mb-0">₱0.00</div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <div class="text-white-50 small">Net Revenue</div>
              <div id="sum-net" class="h4 mb-0">₱0.00</div>
            </div>
          </div>
        </div>
      </div>
      
          <div class="mt-4">
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Note:</strong> Sales data includes all confirmed bookings and invoice totals for the selected period.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Report Tab -->
    <div class="tab-pane fade" id="commission-report" role="tabpanel" aria-labelledby="commission-tab">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-users me-2"></i>Therapist Commission Report</h5>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-4">
            <button class="btn btn-outline-primary active" data-period="daily" data-report="commission">Daily</button>
            <button class="btn btn-outline-primary" data-period="weekly" data-report="commission">Weekly</button>
            <button class="btn btn-outline-primary" data-period="monthly" data-report="commission">Monthly</button>
          </div>

          <!-- Commission Summary Cards -->
          <div id="commissionSummary" class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
              <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                  <div class="text-dark-50 small">Total Commissions</div>
                  <div id="sum-total-commission" class="h4 mb-0">₱0.00</div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-info text-white">
                <div class="card-body text-center">
                  <div class="text-white-50 small">Total Hours</div>
                  <div id="sum-total-hours" class="h4 mb-0">0.0h</div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-success text-white">
                <div class="card-body text-center">
                  <div class="text-white-50 small">Active Therapists</div>
                  <div id="sum-active-therapists" class="h4 mb-0">0</div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-primary text-white">
                <div class="card-body text-center">
                  <div class="text-white-50 small">Avg. Commission</div>
                  <div id="sum-avg-commission" class="h4 mb-0">₱0.00</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Therapist Commission Details -->
          <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Individual Therapist Performance
              </h6>
              <div class="d-flex gap-3">
                <small class="text-muted">
                  <i class="fas fa-clock me-1"></i>
                  <span id="totalHours">0</span> total hours
                </small>
                <small class="text-muted">
                  <i class="fas fa-user-check me-1"></i>
                  <span id="activeTherapists">0</span> active therapists
                </small>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="therapistTable">
                <thead class="table-dark">
                  <tr>
                    <th>Therapist Name</th>
                    <th class="text-center">Services Rendered</th>
                    <th class="text-center">Total Hours</th>
                    <th class="text-center">Commission Rate</th>
                    <th class="text-end">Total Commission</th>
                  </tr>
                </thead>
                <tbody id="therapistTableBody">
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="fas fa-spinner fa-spin me-2"></i>
                      Loading therapist data...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-4">
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Note:</strong> Commission is calculated at ₱50/hour based on actual logged hours from therapist time tracking.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  function peso(v) { 
    return '₱' + Number(v).toLocaleString('en-PH', {minimumFractionDigits: 2}); 
  }
  
  function loadSalesData(period) {
    // Update button states for sales tab
    $('[data-report="sales"][data-period]').removeClass('active');
    $('[data-report="sales"][data-period="' + period + '"]').addClass('active');
    
    // Load sales summary
    $.post('../../controller/booking_contr.php', { 
      action: 'get_sales_summary', 
      period: period 
    }, function(res) {
      if (res.status === 'success') {
        $('#sum-start').text(res.start);
        $('#sum-sales').text(peso(res.sales));
        $('#sum-commission').text(peso(res.commission));
        $('#sum-net').text(peso(res.net));
      } else {
        Swal.fire('Error', res.message || 'Failed to load sales data', 'error');
      }
    }, 'json').fail(function() {
      Swal.fire('Error', 'Network error loading sales data', 'error');
    });
  }

  function loadCommissionData(period) {
    // Update button states for commission tab
    $('[data-report="commission"][data-period]').removeClass('active');
    $('[data-report="commission"][data-period="' + period + '"]').addClass('active');
    
    // Load therapist commission details
    loadTherapistCommissions(period);
  }
  
  function loadTherapistCommissions(period) {
    $('#therapistTableBody').html(`
      <tr>
        <td colspan="5" class="text-center text-muted py-4">
          <i class="fas fa-spinner fa-spin me-2"></i>
          Loading therapist data...
        </td>
      </tr>
    `);
    
    $.post('../../controller/booking_contr.php', { 
      action: 'get_therapist_commissions', 
      period: period 
    }, function(res) {
      if (res.status === 'success') {
        let tbody = '';
        let totalHours = 0;
        let activeTherapists = 0;
        
        if (res.therapists && res.therapists.length > 0) {
          res.therapists.forEach(function(therapist) {
            const fullName = therapist.first_name + ' ' + therapist.last_name;
            const hours = parseFloat(therapist.total_hours || 0);
            const commission = parseFloat(therapist.total_commission || 0);
            const services = parseInt(therapist.services_rendered || 0);
            
            // Update totals
            totalHours += hours;
            if (hours > 0) activeTherapists++;
            
            tbody += `
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                      ${fullName.charAt(0).toUpperCase()}
                    </div>
                    <div>
                      <strong>${fullName}</strong>
                      ${hours > 0 ? '<br><small class="text-success"><i class="fas fa-check-circle me-1"></i>Active</small>' : '<br><small class="text-muted"><i class="fas fa-clock me-1"></i>No hours logged</small>'}
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge ${services > 0 ? 'bg-info' : 'bg-secondary'}">${services}</span>
                </td>
                <td class="text-center">
                  <span class="badge ${hours > 0 ? 'bg-success' : 'bg-secondary'}">${hours.toFixed(1)}h</span>
                </td>
                <td class="text-center">
                  <span class="text-muted">₱50/hour</span>
                </td>
                <td class="text-end">
                  <strong class="${commission > 0 ? 'text-success' : 'text-muted'}">${peso(commission)}</strong>
                  ${commission > 0 ? `<br><small class="text-muted">${(commission/50).toFixed(1)} hrs × ₱50</small>` : ''}
                </td>
              </tr>
            `;
          });
        } else {
          tbody = `
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                <i class="fas fa-info-circle me-2"></i>
                No therapist data available for this period
              </td>
            </tr>
          `;
        }
        
        // Update summary statistics
        $('#totalHours').text(totalHours.toFixed(1));
        $('#activeTherapists').text(activeTherapists);
        
        // Update commission summary cards
        const totalCommission = res.therapists.reduce((sum, t) => sum + parseFloat(t.total_commission || 0), 0);
        const avgCommission = activeTherapists > 0 ? totalCommission / activeTherapists : 0;
        
        $('#sum-total-commission').text(peso(totalCommission));
        $('#sum-total-hours').text(totalHours.toFixed(1) + 'h');
        $('#sum-active-therapists').text(activeTherapists);
        $('#sum-avg-commission').text(peso(avgCommission));
        
        $('#therapistTableBody').html(tbody);
      } else {
        $('#therapistTableBody').html(`
          <tr>
            <td colspan="5" class="text-center text-danger py-4">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Error loading therapist data: ${res.message || 'Unknown error'}
            </td>
          </tr>
        `);
      }
    }, 'json').fail(function() {
      $('#therapistTableBody').html(`
        <tr>
          <td colspan="5" class="text-center text-danger py-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Network error loading therapist data
          </td>
        </tr>
      `);
    });
  }
  
  // Handle period button clicks
  $(document).on('click', '[data-period]', function() {
    const period = $(this).data('period');
    const report = $(this).data('report');
    
    if (report === 'sales') {
      loadSalesData(period);
    } else if (report === 'commission') {
      loadCommissionData(period);
    }
  });
  
  // Handle tab switching
  $('#reportTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr('data-bs-target');
    
    if (target === '#sales-report') {
      // Load sales data when switching to sales tab
      const activePeriod = $('[data-report="sales"].active').data('period') || 'daily';
      loadSalesData(activePeriod);
    } else if (target === '#commission-report') {
      // Load commission data when switching to commission tab
      const activePeriod = $('[data-report="commission"].active').data('period') || 'daily';
      loadCommissionData(activePeriod);
    }
  });
  
  // Load initial data for active tab
  loadSalesData('daily');
});
</script>