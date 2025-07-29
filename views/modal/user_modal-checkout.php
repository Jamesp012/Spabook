<div class="modal-header">
  <h5 class="modal-title">Check-out Summary</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <ul id="checkoutServiceList" class="list-group mb-3"></ul>
  <h5 class="text-end">Total: <span id="checkoutTotal" class="text-success">₱0</span></h5>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
  <button type="button" class="btn btn-success" id="proceedToPaymentBtn">Proceed to Payment</button>
</div>

<style>
  .remove-service-btn {
    z-index: 10;
  }

  @media (max-width: 576px) {
    .remove-service-btn {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }
  }
</style>
