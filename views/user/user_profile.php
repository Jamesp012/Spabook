<div class="container py-4">
  <div class="card shadow rounded-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">My Profile</h5>
      <button class="btn btn-outline-primary btn-sm" id="editProfileBtn">Edit Profile</button>
    </div>
    <div class="card-body">
      <form id="userProfileForm">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="profileName" value="Juan Dela Cruz" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" id="profileEmail" value="juan@example.com" readonly>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="profilePhone" value="09171234567" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" id="profileAddress" value="123 Main St, Manila" readonly>
          </div>
        </div>

        <div class="text-end d-none" id="saveBtnWrapper">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  let editing = false;

  $('#editProfileBtn').on('click', function () {
    editing = !editing;

    $('#userProfileForm input').prop('readonly', !editing);

    if (editing) {
      $('#saveBtnWrapper').removeClass('d-none');
      $(this).text('Cancel Edit').removeClass('btn-outline-primary').addClass('btn-outline-secondary');
    } else {
      $('#saveBtnWrapper').addClass('d-none');
      $(this).text('Edit Profile').removeClass('btn-outline-secondary').addClass('btn-outline-primary');

      // Optionally reset form fields here
    }
  });

  $('#userProfileForm').on('submit', function (e) {
    e.preventDefault();

    const updatedData = {
      name: $('#profileName').val(),
      email: $('#profileEmail').val(),
      phone: $('#profilePhone').val(),
      address: $('#profileAddress').val()
    };

    // 🔄 Simulate save to server
    console.log('Saving profile...', updatedData);

    // After save
    $('#editProfileBtn').click(); // Simulate clicking cancel to exit edit mode
    alert('Profile updated successfully!');
  });
});
</script>
