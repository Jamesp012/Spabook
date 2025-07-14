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
          <h5 class="mb-0" id="profileNameDisplay">Full Name</h5>
          <p class="text-muted small" id="profileEmailDisplay">email@example.com</p>
        </div>

        <!-- Hidden fields for form submission -->
        <input type="text" class="form-control d-none" id="profileName" readonly>
        <input type="email" class="form-control d-none" id="profileEmail" readonly>

        <!-- Info Section -->
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
  $(document).ready(function() {
    $('#userProfileForm').hide();
    let editing = false;

    $('#editProfileBtn').on('click', function() {
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

    $('#userProfileForm').on('submit', function(e) {
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

  // console.log('User ID:', user_id); // Access the user_id variable
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
        title: 'Logging in...',
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
      Swal.close(); // Close the loading spinner
      $('#userProfileForm').show();
      $('#profile_picture').attr(
        'src',
        response.profile_picture ? `data:image/png;base64,${response.profile_picture}` : '../vendor/images/default_profile.png'
      );
      let contactNumber = response.contact_number || 'N/A';
      let address = response.address || 'N/A';
      $('#profileNameDisplay').text(response.full_name);
      $('#profileEmailDisplay').text(response.email);
      $('#profilePhone').val(contactNumber);
      $('#profileAddress').val(address);
    },
    error: function() {
      alert('Failed to load profile data.');
    }



  });

  function postgresByteaToBase64(bytea) {
    if (!bytea) return '';
    const hex = bytea.startsWith('\\x') ? bytea.slice(2) : bytea; // strip \x
    const binary = hex.match(/.{1,2}/g) // 2 hex chars → byte
      .map(h => String.fromCharCode(parseInt(h, 16)))
      .join('');
    return btoa(binary);
  }
</script>