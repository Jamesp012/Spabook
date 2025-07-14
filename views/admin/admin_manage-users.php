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
  let users = [
    {id: 1, name: 'Juan Dela Cruz', email: 'juan@email.com', role: 'user'},
    {id: 2, name: 'Maria Santos', email: 'maria@email.com', role: 'admin'},
    {id: 3, name: 'Pedro Reyes', email: 'pedro@email.com', role: 'user'}
  ];

  function renderUserTable() {
    const tbody = $('#userTableBody');
    tbody.empty();
    users.forEach((user) => {
      tbody.append(`
        <tr class="user-row">
          <td class="text-center"><i class="bi bi-person-circle user-avatar" style="font-size:2.5rem; color:#bdbdbd;"></i></td>
          <td><div class="fw-semibold user-name">${user.name}</div></td>
          <td><span class="badge bg-light text-dark border px-2 py-1">${user.email}</span></td>
          <td><span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'} text-white px-2 py-1">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
          <td>
            <button class='btn btn-sm btn-primary me-1' onclick='openEditUser(${user.id})'>Modify</button>
            <button class='btn btn-sm btn-danger' onclick='deleteUser(${user.id})'>Delete</button>
          </td>
        </tr>
      `);
    });
  }

  window.deleteUser = function(id) {
    if (confirm('Are you sure you want to delete this user?')) {
      users = users.filter(u => u.id !== id);
      renderUserTable();
    }
  };

  renderUserTable();
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