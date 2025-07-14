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
  $(document).ready(function() {

    function renderUserTable() {
      const tbody = $('#userTableBody');
      tbody.empty();

      $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'fetch_user_data'
        },
        beforeSend: function() {
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
          });
        }
      })

    }

    window.deleteUser = function(id) {
      if (confirm('Are you sure you want to delete this user?')) {
        users = users.filter(u => u.id !== id);
        renderUserTable();
      }
    };

    renderUserTable();

    $(document).on('click', '.btn-view', function(e) {
      e.preventDefault();
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