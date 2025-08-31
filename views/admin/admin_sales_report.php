<?php
// Admin Sales Report view (daily/weekly/monthly) - Content only for admin panel
?>
<div class="container-fluid py-3">
  <h4 class="mb-3">Sales & Commission Report</h4>
  
  <div class="card">
    <div class="card-body">
      <div class="d-flex gap-2 mb-4">
        <button class="btn btn-outline-primary active" data-period="daily">Daily</button>
        <button class="btn btn-outline-primary" data-period="weekly">Weekly</button>
        <button class="btn btn-outline-primary" data-period="monthly">Monthly</button>
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
          <strong>Note:</strong> Commission is calculated at ₱50/hour based on actual logged hours from therapist time tracking.
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
  
  function loadSummary(period) {
    // Update button states
    $('[data-period]').removeClass('active');
    $('[data-period="' + period + '"]').addClass('active');
    
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
  
  $(document).on('click', '[data-period]', function() {
    loadSummary($(this).data('period'));
  });
  
  // Load daily data by default
  loadSummary('daily');
});
</script>