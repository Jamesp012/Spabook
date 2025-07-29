<div class="modal-header">
  <h5 class="modal-title">Upload Payment Receipt</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
  <p>Please upload a photo or screenshot of your payment receipt.</p>

  <form id="paymentForm" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="receiptUpload" class="form-label">Receipt Image</label>
      <input class="form-control" type="file" id="receiptUpload" name="receipt" accept="image/*" required>
    </div>
    <div id="previewContainer" class="mb-3 d-none">
      <label class="form-label">Preview:</label><br>
      <img id="receiptPreview" src="#" alt="Preview" class="img-fluid rounded border" style="max-height: 250px;">
    </div>
  </form>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="submit" form="paymentForm" class="btn btn-success">Submit Payment</button>
</div>
