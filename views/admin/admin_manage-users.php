<div class="container py-4">
  <div class="table-responsive d-none d-lg-block">
    <table class="table table-bordered table-hover align-middle text-center" id="userTable">
      <thead class="table-light">
        <tr class="bg-white rounded-top">
          <th style="width:60px;"></th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th style="width:160px;">Actions</th>
        </tr>
      </thead>
    </table>
    <div class="table-body-scroll">
      <table class="table table-bordered table-hover align-middle text-center">
        <tbody id="userTableBody">
          <!-- Users rendered by JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Mobile Version -->
  <div class="d-lg-none" id="mobileUserCards">
    <!-- Cards rendered dynamically -->
  </div>
</div>

<script>
  $(document).ready(function () {
    function renderUserTable() {
      const tbody = $('#userTableBody');
      const mobileCards = $('#mobileUserCards');
      tbody.empty();
      mobileCards.empty();

      $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'fetch_user_data'
        },
        beforeSend: function () {
          Swal.fire({
            title: 'Loading Users...',
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
        success: result => {
          Swal.close();
          result.forEach((user) => {
            // Desktop table
            tbody.append(`
              <tr class="user-row">
                   <td class="text-center">
                   ${
              user.profile_picture
                ? `<img src="data:image/jpeg;base64,${user.profile_picture}" style="width:30px; height:30px; border-radius:50%;">`
                : `<i class="bi bi-person-circle user-avatar" style="font-size:2.5rem; color:#bdbdbd;"></i>`
            }
                 </td>
                <td><div class="fw-semibold user-name">${user.full_name}</div></td>
                <td><span class="badge bg-light text-dark border px-2 py-1">${user.email}</span></td>
                <td><span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'} text-white px-2 py-1">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                <td>
                  <button class='btn btn-sm btn-view btn-primary me-1' data-id='${user.user_id}' data-name='${user.full_name}' data-email='${user.email}' data-role='${user.role}'>Modify</button>
                  <button class='btn btn-sm btn-danger' onclick='deleteUser(${user.user_id})'>Delete</button>
                </td>
              </tr>
            `);

            // Mobile cards
            mobileCards.append(`
              <div class="card shadow-sm mb-3">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    ${
              user.profile_picture
                ? `<img src="data:image/jpeg;base64,${user.profile_picture}" style="width:40px; height:40px; border-radius:50%; margin-right:10px;">`
                : `<i class="bi bi-person-circle" style="font-size:2rem; color:#bdbdbd; margin-right:10px;"></i>`
            }
                    <div>
                      <div class="fw-semibold">${user.full_name}</div>
                      <div class="text-muted small">${user.email}</div>
                    </div>
                  </div>
                  <div class="mb-2">
                    <span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'} text-white px-2 py-1">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
                  </div>
                  <div>
                    <button class='btn btn-sm btn-view btn-primary me-2' data-id='${user.user_id}' data-name='${user.full_name}' data-email='${user.email}' data-role='${user.role}'>Modify</button>
                    <button class='btn btn-sm btn-danger' onclick='deleteUser(${user.user_id})'>Delete</button>
                  </div>
                </div>
              </div>
            `);
          });
        }
      });
    }

    window.deleteUser = function (id) {
      if (confirm('Are you sure you want to delete this user?')) {
        users = users.filter(u => u.id !== id);
        renderUserTable();
      }
    };

    renderUserTable();

    $(document).on('click', '.btn-view', function () {
      const id = $(this).data('id');
      const name = $(this).data('name');
      const email = $(this).data('email');
      const role = $(this).data('role');
      showGlobalModal('modal/admin_modal-edit-user.php', {
        id: id,
        name: name,
        email: email,
        role: role
      });
    });
  });
</script>

<style>
  .user-avatar {
    border-radius: 50%;
    padding: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }

  .user-row td {
    vertical-align: middle !important;
    word-break: break-word;
    min-width: 100px;
  }

  .user-name {
    letter-spacing: 0.5px;
  }
  
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