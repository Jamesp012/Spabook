<?php
// Admin Billing per booking - Content only for admin panel
?>
<div class="container-fluid py-3">
  <h4 class="mb-3">Billing Management</h4>
  
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0"><i class="fas fa-search me-2"></i>Find Invoice</h5>
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Booking ID</label>
          <input type="number" id="booking-id" class="form-control" placeholder="Enter booking ID" />
        </div>
        <div class="col-md-3">
          <button id="btn-load" class="btn btn-primary">
            <i class="fas fa-file-invoice me-2"></i>Load Invoice
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <div id="invoice-container" class="d-none">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Invoice Details</h5>
        <div class="d-flex align-items-center gap-3">
          <label class="form-label mb-0 me-2">Payment Status:</label>
          <select id="payment-status" class="form-select form-select-sm" style="width: auto;">
            <option value="Unpaid">Unpaid</option>
            <option value="Down Payment">Down Payment</option>
            <option value="Paid">Paid</option>
            <option value="Refunded">Refunded</option>
          </select>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="text-muted small" id="inv-meta">Invoice information will appear here</div>
        </div>
        
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Service Description</th>
                <th class="text-center" width="80">Qty</th>
                <th class="text-end" width="120">Unit Price</th>
                <th class="text-end" width="120">Total</th>
              </tr>
            </thead>
          </table>
          <div class="table-body-scroll">
            <table class="table table-hover">
              <tbody id="inv-items">
                <!-- Invoice items will be loaded here -->
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="row justify-content-end">
          <div class="col-md-4">
            <table class="table table-sm">
              <tr>
                <td class="text-end"><strong>Subtotal:</strong></td>
                <td class="text-end" id="inv-subtotal">₱0.00</td>
              </tr>
              <tr>
                <td class="text-end"><strong>Discount:</strong></td>
                <td class="text-end" id="inv-discount">₱0.00</td>
              </tr>
              <tr class="table-primary">
                <td class="text-end"><strong>Total:</strong></td>
                <td class="text-end h5" id="inv-total">₱0.00</td>
              </tr>
            </table>
          </div>
        </div>
        
        <div class="mt-3 d-flex gap-2">
          <button id="btn-save-status" class="btn btn-success">
            <i class="fas fa-save me-2"></i>Save Payment Status
          </button>
          <button id="btn-refresh" class="btn btn-outline-secondary">
            <i class="fas fa-sync me-2"></i>Refresh
          </button>
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
  
  function getStatusBadgeClass(status) {
    switch(status) {
      case 'Paid': return 'bg-success';
      case 'Down Payment': return 'bg-warning text-dark';
      case 'Refunded': return 'bg-info';
      default: return 'bg-danger';
    }
  }
  
  function renderInvoice(data) {
    const inv = data.invoice;
    const items = data.items || [];
    
    $('#invoice-container').removeClass('d-none');
    
    // Update meta information
    const statusBadge = `<span class="badge ${getStatusBadgeClass(inv.payment_status)} ms-2">${inv.payment_status}</span>`;
    $('#inv-meta').html(`
      <div class="row">
        <div class="col-md-6">
          <strong>Invoice #:</strong> ${inv.invoice_id}<br>
          <strong>Booking #:</strong> ${inv.booking_id}<br>
          <strong>User ID:</strong> ${inv.user_id}
        </div>
        <div class="col-md-6">
          <strong>Issued:</strong> ${new Date(inv.issued_at).toLocaleString()}<br>
          <strong>Updated:</strong> ${new Date(inv.updated_at).toLocaleString()}<br>
          <strong>Status:</strong> ${statusBadge}
        </div>
      </div>
    `);
    
    // Update payment status selector
    $('#payment-status').val(inv.payment_status);
    
    // Render invoice items
    if (items.length > 0) {
      const rows = items.map(item => `
        <tr>
          <td>
            <strong>${item.description || ('Service #' + item.service_id)}</strong>
            <div class="text-muted small">Service ID: ${item.service_id}</div>
          </td>
          <td class="text-center">${item.quantity}</td>
          <td class="text-end">${peso(item.unit_price)}</td>
          <td class="text-end">${peso(item.line_total)}</td>
        </tr>
      `).join('');
      $('#inv-items').html(rows);
    } else {
      $('#inv-items').html('<tr><td colspan="4" class="text-center text-muted"><em>No items found</em></td></tr>');
    }
    
    // Update totals
    $('#inv-subtotal').text(peso(inv.subtotal));
    $('#inv-discount').text(peso(inv.discount));
    $('#inv-total').text(peso(inv.total));
  }
  
  function loadInvoice() {
    const bookingId = $('#booking-id').val();
    if (!bookingId) {
      Swal.fire('Input Required', 'Please enter a booking ID', 'warning');
      return;
    }
    
    $.post('../../controller/booking_contr.php', { 
      action: 'get_invoice_by_booking', 
      booking_id: bookingId 
    }, function(res) {
      if (res.status === 'success') {
        renderInvoice(res);
      } else {
        $('#invoice-container').addClass('d-none');
        Swal.fire('Notice', res.message || 'No invoice found for this booking', 'info');
      }
    }, 'json').fail(function() {
      $('#invoice-container').addClass('d-none');
      Swal.fire('Error', 'Network error loading invoice', 'error');
    });
  }
  
  // Event handlers
  $('#btn-load').on('click', loadInvoice);
  
  $('#btn-refresh').on('click', loadInvoice);
  
  $('#booking-id').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
      loadInvoice();
    }
  });
  
  $('#btn-save-status').on('click', function() {
    const bookingId = $('#booking-id').val();
    const status = $('#payment-status').val();
    
    if (!bookingId) {
      Swal.fire('Error', 'No booking selected', 'warning');
      return;
    }
    
    $.post('../../controller/booking_contr.php', { 
      action: 'update_invoice_payment_status', 
      booking_id: bookingId, 
      payment_status: status 
    }, function(res) {
      if (res.status === 'success') {
        Swal.fire('Saved', 'Payment status updated successfully', 'success');
        loadInvoice(); // Refresh to show updated status
      } else {
        Swal.fire('Error', res.message || 'Failed to update status', 'error');
      }
    }, 'json').fail(function() {
      Swal.fire('Error', 'Network error updating status', 'error');
    });
  });
});
</script>

<style>
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