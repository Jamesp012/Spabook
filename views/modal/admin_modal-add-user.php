<div class="modal-header">
  <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Add New Administrator</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <form id="addAdminForm">
    <div class="row g-3">
      <div class="col-12">
        <label for="adminFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="adminFullName" name="full_name" placeholder="e.g., Jane Doe" required>
      </div>
      <div class="col-md-6">
        <label for="adminEmail" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="adminEmail" name="email" placeholder="admin@example.com" required>
      </div>
      <div class="col-md-6">
        <label for="adminContact" class="form-label">Contact Number</label>
        <input type="tel" class="form-control" id="adminContact" name="contact" placeholder="e.g., +63 912 345 6789">
      </div>
      <div class="col-12">
        <label for="adminAddress" class="form-label">Address</label>
        <input type="text" class="form-control" id="adminAddress" name="user_address" placeholder="Street, City, Country">
      </div>
    </div>

    <div class="alert alert-info mt-3 mb-0">
      <small>
        <i class="fas fa-info-circle me-1"></i>
        Admins use the same authentication system as users. This will create a user and set their role to Administrator.
      </small>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="button" id="saveAdminBtn" class="btn btn-danger" onclick="saveAdmin()">
    <i class="fas fa-save me-1"></i>Add Administrator
  </button>
</div>

<script>
function generateUUIDv4() {
  // Simple UUID v4 generator for client-side ID (matches sign_up_user expectations)
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

function saveAdmin() {
  const btn = $('#saveAdminBtn');
  const fullName = $('#adminFullName').val().trim();
  const email = $('#adminEmail').val().trim();
  const contact = $('#adminContact').val().trim();
  const address = $('#adminAddress').val().trim();

  if (!fullName || !email) {
    Swal.fire('Validation', 'Full name and email are required.', 'warning');
    return;
  }

  const supabase_uuid = generateUUIDv4();

  // Step 1: create user via existing endpoint
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
  $.ajax({
    url: '../controller/user_contr.php',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'sign_up_user',
      email: email,
      user_fullname: fullName,
      user_address: address,
      contact: contact,
      supabase_uuid: supabase_uuid
    },
    success: function(resp1) {
      // sign_up_user returns 'success' or 'error' (string). Handle both JSON and string.
      const ok = (resp1 && resp1.status === 'success') || resp1 === 'success';
      if (!ok) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Add Administrator');
        Swal.fire('Error', (resp1 && resp1.message) ? resp1.message : 'Failed to create user record.', 'error');
        return;
      }
      // Step 2: elevate role to Admin using existing update_role endpoint
      $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'update_role', id: supabase_uuid, role: 'Admin' },
        success: function(resp2) {
          const ok2 = resp2 && resp2.status === 'success';
          if (!ok2) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Add Administrator');
            Swal.fire('Warning', (resp2 && resp2.message) ? resp2.message : 'User created, but failed to set role to Admin.', 'warning');
            return;
          }
          Swal.fire({ icon: 'success', title: 'Administrator added!', timer: 1600, showConfirmButton: false })
            .then(() => {
              $('#globalModal').modal('hide');
              // Refresh current tab if function exists, else reload
              if (typeof refreshActiveTab === 'function') { refreshActiveTab(); }
              else { location.reload(); }
            });
        },
        error: function() {
          btn.prop('disabled', false).html('<i class=\"fas fa-save me-1\"></i>Add Administrator');
          Swal.fire('Warning', 'User created, but failed to set role to Admin.', 'warning');
        }
      });
    },
    error: function() {
      btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Add Administrator');
      Swal.fire('Error', 'Failed to create user record.', 'error');
    }
  });
}
</script>

<style>
.form-label { font-weight: 600; }
</style>