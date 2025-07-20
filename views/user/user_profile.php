<div class="container py-4">
  <div class="card shadow rounded-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-bold">My Profile</h5>
      <button class="btn btn-outline-primary btn-sm" id="editProfileBtn">Edit Profile</button>
    </div>
    <div class="card-body">
      <form id="userProfileForm">
        <!-- Profile Picture Section -->
        <div class="text-center mb-4">
          <img id="profile_picture" src="#" alt="Profile Picture" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;">
        </div>

        <!-- Editable fields -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="profileName" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="profileEmail" readonly>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="profilePhone" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" id="profileAddress" readonly>
          </div>
        </div>

        <!-- Save Button -->
        <div class="text-end d-none" id="saveBtnWrapper">
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let editing = false;

  $('#editProfileBtn').on('click', function() {
    editing = !editing;
    $('#userProfileForm input').prop('readonly', !editing);
    $('#saveBtnWrapper').toggleClass('d-none', !editing);
    $(this).text(editing ? 'Cancel' : 'Edit Profile');

    if (!editing) {
      // Reload original values if canceled
      loadUserProfile();
    }
  });

  $('#userProfileForm').on('submit', function(e) {
    e.preventDefault();

    const updatedData = {
      action: 'update_user_profile',
      id: user_id,
      name: $('#profileName').val(),
      email: $('#profileEmail').val(),
      contact: $('#profilePhone').val(),
      address: $('#profileAddress').val(),
    };

    $.ajax({
      url: '../controller/user_contr.php',
      type: 'POST',
      data: updatedData,
      success: function(response) {
        if (response === 'success') {
          Swal.fire('Saved!', 'Your profile has been updated.', 'success');
          $('#editProfileBtn').click(); // Switch back to readonly mode
        } else {
          Swal.fire('Error', 'Failed to update profile.', 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Something went wrong!', 'error');
      }
    });
  });

  function loadUserProfile() {
    $.ajax({
      url: '../controller/user_contr.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'get_user_profile',
        id: user_id
      },
      beforeSend: function() {
        Swal.fire({
          title: 'Loading Profile...',
          html: `
              <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
                  <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
              </div>
              <style>
                  .custom-spinner-glow {
                      animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
                      filter: drop-shadow(0 0 16px #a1623f);
                  }
                  @keyframes spin {
                      100% { transform: rotate(360deg); }
                  }
                  @keyframes glow {
                      0% { filter: drop-shadow(0 0 8px #a1623f); }
                      100% { filter: drop-shadow(0 0 32px #a1623f); }
                  }
              </style>
        `,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
          backdrop: true,
        });
      },
      success: function(response) {
        Swal.close();

        $('#userProfileForm').show();
        $('#profile_picture').attr(
          'src',
          response.profile_picture ?
          `data:image/png;base64,${response.profile_picture}` :
          '../vendor/images/default_profile.png'
        );
        $('#profileName').val(response.full_name || 'N/A');
        $('#profileEmail').val(response.email || 'N/A');
        $('#profilePhone').val(response.contact_number || 'N/A');
        $('#profileAddress').val(response.address || 'N/A');
      },
      error: function() {
        alert('Failed to load profile data.');
      }
    });
  }

  // Initialize on load
  $(document).ready(function() {
    loadUserProfile();
  });
</script>