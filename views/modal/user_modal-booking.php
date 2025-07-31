<div class="modal-header">
  <h5 class="modal-title">Book Service</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <p>Selected Service:</p>
  <h5 id="selected-service-name" class="fw-bold mb-1"></h5>
  <p id="selected-service-price" class="text-muted mb-3" data-price=""></p>
  <input type="hidden" id="selected-service-id" value="">

  <label for="numPeople">Number of People:</label>
  <input type="number" class="form-control" id="numPeople" min="1" value="1">
</div>


<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="button" class="btn btn-primary" id="confirmServiceBtn">Add</button>
</div>
