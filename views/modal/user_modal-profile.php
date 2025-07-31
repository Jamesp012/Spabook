<!-- views/modal/user_modal-profile.php -->
<div class="modal-header">
  <h5 class="modal-title">Edit Profile</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
  <form id="userProfileForm">
    <!-- Profile Picture -->
    <div class="text-center mb-4">
      <img id="profile_picture" src="../vendor/images/default_profile.png" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" id="profileName" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" id="profileEmail" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" id="profilePhone">
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <input type="text" class="form-control" id="profileAddress">
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
  <button type="submit" class="btn btn-primary" form="userProfileForm">Save Changes</button>
</div>

<script>
  $(document).ready(function () {
    const user_id = sessionStorage.getItem('user_id');

    // Load user data
    $.post('../controller/user_contr.php', { action: 'get_user_profile', id: user_id }, function (response) {
      $('#profile_picture').attr('src', response.profile_picture ? 'data:image/png;base64,' + response.profile_picture : '../vendor/images/default_profile.png');
      $('#profileName').val(response.full_name || '');
      $('#profileEmail').val(response.email || '');
      $('#profilePhone').val(response.contact_number || '');
      $('#profileAddress').val(response.address || '');
    }, 'json');

    // Submit profile update
    $('#userProfileForm').on('submit', function (e) {
      e.preventDefault();

      const updatedData = {
        action: 'update_user_profile',
        id: user_id,
        name: $('#profileName').val(),
        email: $('#profileEmail').val(),
        contact: $('#profilePhone').val(),
        address: $('#profileAddress').val()
      };

      $.post('../controller/user_contr.php', updatedData, function (response) {
        if (response === 'success') {
          Swal.fire('Success', 'Profile updated successfully!', 'success');
          $('#globalModal').modal('hide');
        } else {
          Swal.fire('Error', 'Failed to update profile.', 'error');
        }
      });
    });
  });
</script>
