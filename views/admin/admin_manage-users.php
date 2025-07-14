<div class="container py-4">
  <div class="table-responsive">
    <table class="table align-middle" id="userTable">
      <thead>
        <tr class="bg-white rounded-top">
          <th style="width:60px;"></th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th style="width:160px;">Actions</th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        <!-- Users rendered by JS -->
      </tbody>
    </table>
  </div>
</div>

<script>
  renderUserTable();

  function renderUserTable() {
    $.ajax({
      url: '../controller/user_contr.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'fetch_user_data',
      },
      beforeSend: function() {
        Swal.fire({
          title: 'Loading Users...',
          html: `
            <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
          `,
          showConfirmButton: false,
          allowOutsideClick: false
        });
      },
      success: result => {
        Swal.close(); // Close the loading spinner
        console.log('Users loaded:', result);

        const tbody = $('#userTableBody');
        tbody.empty();
        result.forEach((user) => {
          let action
          if (user.role === 'Admin') {
            action = `<span class="badge bg-primary text-white px-2 py-1">${user.role}</span>`;
          } else {
            action = `<button class='btn btn-sm btn-primary me-1' onclick="openEditUser('${user.user_id}','${user.full_name}','${user.email}','${user.role}');">Modify</button>
            <button class='btn btn-sm btn-danger' onclick='deleteUser(${user.user_id})'>Delete</button>`;
          }
          tbody.append(`
        <tr class="user-row">
          <td class="text-center">
            <img src="data:image/png;base64,${user.profile_picture}" alt="Profile Picture" class="rounded-circle mb-2" style="width: 40px; height: 40px; object-fit: cover;">
          </td>
          <td><div class="fw-semibold user-name">${user.full_name}</div></td>
          <td><span class="badge bg-light text-dark border px-2 py-1">${user.email}</span></td>
          <td><span class="badge ${user.role === 'Admin' ? 'bg-primary' : 'bg-secondary'} text-white px-2 py-1">${user.role}</span></td>
          <td>
          ${action}
          </td>
        </tr>
      `);
        });

      },
    });
  }

  function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
      users = users.filter(u => u.id !== id);
      renderUserTable();
    }
  };

  function openEditUser(id, name, email, role) {
    // 🧹 Clear previous modal
    $('#modalContainer').empty();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');

    // 🧱 Modal HTML
    const modalHTML = `
    <div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editAdminModalLabel">Edit User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editUserForm">
            <div class="modal-body">
              <input type="hidden" id="editUserId" value="${id}">
              <div class="mb-3">
                <label for="editUserName" class="form-label">Name</label>
                <input type="text" class="form-control" id="editUserName" value="${name}" readonly>
              </div>
              <div class="mb-3">
                <label for="editUserEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editUserEmail" value="${email}" readonly>
              </div>
              <div class="mb-3">
                <label for="editUserRole" class="form-label">Role</label>
                <select class="form-select" id="editUserRole">
                  <option value="user" ${role === 'user' ? 'selected' : ''}>User</option>
                  <option value="admin" ${role === 'admin' ? 'selected' : ''}>Admin</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>`;

    $('#modalContainer').html(modalHTML);

    const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
    modal.show();

    $('#editUserForm').on('submit', function(e) {
      console.log(id + ' | ' + $('#editUserRole').val());
      // e.preventDefault();
      // $.ajax({
      //   url: '../controller/user_contr.php',
      //   type: 'POST',
      //   dataType: 'json',
      //   data: {
      //     action: 'update_role',
      //     id: id,
      //     role: $('#editUserRole').val()
      //   },
      //   beforeSend: function() {
      //     Swal.fire({
      //       title: 'Saving Data...',
      //       html: `
      //         <div class="d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
      //             <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
      //         </div>
      //         <style>
      //             .custom-spinner-glow {
      //                 animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
      //                 filter: drop-shadow(0 0 16px #a1623f);
      //             }
      //             @keyframes spin {
      //                 100% { transform: rotate(360deg); }
      //             }
      //             @keyframes glow {
      //                 0% { filter: drop-shadow(0 0 8px #a1623f); }
      //                 100% { filter: drop-shadow(0 0 32px #a1623f); }
      //             }
      //         </style>
      //       `,
      //       showConfirmButton: false,
      //       allowOutsideClick: false,
      //       allowEscapeKey: false,
      //       backdrop: true,
      //     });
      //   },
      //   success: function(response) {
      //     if (response.status === 'success') {
      //       alert('User updated successfully!');
      //       renderUserTable();
      //       modal.hide();
      //       bootstrap.Modal.getInstance(document.getElementById('editAdminModal')).hide();
      //     } else {
      //       alert('Failed to update user.');
      //     }
      //   },
      //   error: function() {
      //     alert('Error updating user.');
      //   }
      // });

    });

    $('#editAdminModal').on('hidden.bs.modal', function() {
      $('#editAdminModal').remove();
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open').css('padding-right', '');
    });
  }
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

  @media (max-width: 992px) {
    #userTable thead {
      display: none;
    }

    #userTable tr {
      display: block;
      margin-bottom: 1.2rem;
      border-radius: 1rem;
      box-shadow: 0 1px 6px rgba(0, 0, 0, 0.03);
      background: #fff;
    }

    #userTable td {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 0.75rem 1rem;
      border: none !important;
      background: none !important;
      position: relative;
    }

    #userTable td:before {
      content: attr(data-label);
      flex: 0 0 110px;
      font-weight: 600;
      color: #888;
      margin-right: 1rem;
      font-size: 0.95em;
      text-align: left;
      min-width: 90px;
      display: block;
    }

    #userTable .user-avatar {
      margin-right: 1rem;
    }
  }

  .table,
  .table thead,
  .table tr,
  .table td,
  .table th {
    background: none !important;
  }
</style>