<?php
// Check if we need to update admin navigation to include this new page
?>
<div class="container py-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1">User Management</h2>
      <p class="text-muted mb-0">Manage all users, admins, and therapists in one place</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" onclick="refreshTable()">
        <i class="fas fa-sync-alt me-1"></i>Refresh
      </button>
      <button class="btn btn-primary" onclick="addNewUser()">
        <i class="fas fa-user-plus me-1"></i>Add User
      </button>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Search Users</label>
          <input type="text" class="form-control" id="searchUsers" placeholder="Search by name or email">
        </div>
        <div class="col-md-2">
          <label class="form-label">Role Filter</label>
          <select class="form-select" id="filterRole">
            <option value="">All Roles</option>
            <option value="User">Users</option>
            <option value="Admin">Admins</option>
            <option value="Therapist">Therapists</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Status Filter</label>
          <select class="form-select" id="filterStatus">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Quick Actions</label>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-info btn-sm" onclick="exportUsers()">
              <i class="fas fa-download me-1"></i>Export
            </button>
            <button class="btn btn-outline-warning btn-sm" onclick="bulkEdit()">
              <i class="fas fa-edit me-1"></i>Bulk Edit
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">All Users</h5>
      <small class="text-muted">Showing <span id="userCount">0</span> users</small>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="unifiedUsersTable">
          <thead class="table-light">
            <tr>
              <th width="50">
                <input type="checkbox" id="selectAll" class="form-check-input">
              </th>
              <th width="60">Avatar</th>
              <th>Name</th>
              <th>Email</th>
              <th width="120">Role</th>
              <th width="100">Status</th>
              <th width="130">Join Date</th>
              <th width="200">Actions</th>
            </tr>
          </thead>
          <tbody id="unifiedUsersTableBody">
            <!-- Populated by JavaScript -->
          </tbody>
        </table>
      </div>
      
      <!-- Loading State -->
      <div id="loadingUsers" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 text-muted">Loading users...</div>
      </div>

      <!-- Empty State -->
      <div id="emptyState" class="text-center py-5 d-none">
        <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-muted">No Users Found</h4>
        <p class="text-muted">No users match your current filters.</p>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <nav class="mt-4" id="paginationContainer">
    <ul class="pagination justify-content-center" id="pagination">
      <!-- Populated by JavaScript -->
    </ul>
  </nav>
</div>

<script>
let currentPage = 1;
let usersPerPage = 10;
let allUsers = [];
let filteredUsers = [];

$(document).ready(function() {
    loadUnifiedUsers();
    
    // Search functionality
    $('#searchUsers').on('keyup', debounce(function() {
        filterAndDisplayUsers();
    }, 300));
    
    // Filter functionality
    $('#filterRole, #filterStatus').on('change', function() {
        filterAndDisplayUsers();
    });
    
    // Select all functionality
    $('#selectAll').on('change', function() {
        $('.user-checkbox').prop('checked', this.checked);
    });
});

function loadUnifiedUsers() {
    showLoading();
    
    // First fetch regular users and admins from users table
    $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'fetch_unified_users'
        },
        success: function(userResult) {
            console.log('Loaded users from users table:', userResult?.length || 0);
            allUsers = userResult || [];
            
            // Now fetch therapists from therapist table
            $.ajax({
                url: '../controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_all_therapists'
                },
                success: function(therapistResult) {
                    console.log('Loaded therapists from therapist table:', therapistResult?.length || 0);
                    
                    // Process and add therapists to allUsers array
                    const processedTherapists = (therapistResult || []).map(therapist => {
                        return {
                            user_id: 'therapist_' + therapist.therapistid,
                            full_name: therapist.therapist_name || 'Unnamed Therapist',
                            email: (therapist.email && therapist.email !== 'N/A') ? therapist.email : 'No email provided',
                            contact_number: therapist.contact_number || therapist.phone_number || 'Not provided',
                            bio: therapist.therapist_desc || 'General Therapy',
                            therapist_services: therapist.services || therapist.service_id || 'Multiple Services',
                            is_active: therapist.is_active === true || therapist.is_active === 1 || therapist.is_active === '1',
                            created_at: therapist.created_at || therapist.date_created || new Date().toISOString(),
                            profile_picture: therapist.profile_picture || null,
                            role: 'Therapist',
                            rate: therapist.rate || 0,
                            therapist_id: therapist.therapistid
                        };
                    });
                    
                    // Combine users and therapists
                    allUsers = allUsers.concat(processedTherapists);
                    console.log('Total unified users loaded:', allUsers.length);
                    
                    hideLoading();
                    filterAndDisplayUsers();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading therapists:', error);
                    // Continue with just regular users if therapists fail to load
                    hideLoading();
                    filterAndDisplayUsers();
                }
            });
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error('Error loading users:', error);
            Swal.fire('Error', 'Failed to load users', 'error');
        }
    });
}

function filterAndDisplayUsers() {
    const searchTerm = $('#searchUsers').val().toLowerCase();
    const roleFilter = $('#filterRole').val();
    const statusFilter = $('#filterStatus').val();
    
    filteredUsers = allUsers.filter(user => {
        const matchesSearch = !searchTerm || 
            user.full_name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm);
        
        const matchesRole = !roleFilter || user.role === roleFilter;
        
        const matchesStatus = !statusFilter || 
            (statusFilter === 'active' && user.is_active !== false) ||
            (statusFilter === 'inactive' && user.is_active === false);
        
        return matchesSearch && matchesRole && matchesStatus;
    });
    
    displayUsers();
    updatePagination();
    $('#userCount').text(filteredUsers.length);
}

function displayUsers() {
    const tbody = $('#unifiedUsersTableBody');
    const startIndex = (currentPage - 1) * usersPerPage;
    const endIndex = startIndex + usersPerPage;
    const usersToShow = filteredUsers.slice(startIndex, endIndex);
    
    if (usersToShow.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>No users found matching your criteria</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    usersToShow.forEach(user => {
        const avatar = user.profile_picture 
            ? `<img src="data:image/jpeg;base64,${user.profile_picture}" class="rounded-circle" width="35" height="35">`
            : `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><i class="fas fa-user text-white"></i></div>`;
        
        const roleColor = {
            'Admin': 'danger',
            'Therapist': 'success', 
            'User': 'primary'
        }[user.role] || 'secondary';
        
        const statusBadge = user.is_active !== false 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
            
        const joinDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';
        
        html += `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input user-checkbox" value="${user.user_id}">
                </td>
                <td>${avatar}</td>
                <td>
                    <div class="fw-semibold">${user.full_name}</div>
                    ${user.role === 'Therapist' && user.therapist_services ? 
                        `<small class="text-muted">Services: ${user.therapist_services}</small>` : ''}
                </td>
                <td>
                    <span class="text-muted">${user.email}</span>
                </td>
                <td>
                    <span class="badge bg-${roleColor}">${user.role}</span>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <small class="text-muted">${joinDate}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="editUser('${user.user_id}', '${user.role}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="viewUser('${user.user_id}', '${user.role}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${user.role === 'Therapist' ? `
                        <button class="btn btn-outline-success" onclick="manageTherapistServices('${user.user_id}')" title="Manage Services">
                            <i class="fas fa-spa"></i>
                        </button>` : ''}
                        <button class="btn btn-outline-danger" onclick="deleteUser('${user.user_id}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.html(html);
}

function showLoading() {
    $('#loadingUsers').show();
    $('#unifiedUsersTable, #emptyState').hide();
}

function hideLoading() {
    $('#loadingUsers').hide();
    $('#unifiedUsersTable').show();
}

function editUser(userId, role) {
    if (role === 'Therapist') {
        const actualUserId = userId.toString().startsWith('therapist_') ? userId.replace('therapist_', '') : userId;
        showGlobalModal('../modal/admin_modal-edit-therapist.php', {
            user_id: actualUserId,
            action: 'edit'
        });
    } else {
        showGlobalModal('../modal/admin_modal-edit-user.php', {
            id: userId,
            action: 'edit'
        });
    }
}

function viewUser(userId, role) {
    const actualUserId = userId.toString().startsWith('therapist_') ? userId.replace('therapist_', '') : userId;
    showGlobalModal('../modal/admin_modal-view-user.php', {
        user_id: actualUserId,
        role: role
    });
}

function manageTherapistServices(userId) {
    const actualUserId = userId.toString().startsWith('therapist_') ? userId.replace('therapist_', '') : userId;
    showGlobalModal('../modal/admin_modal-therapist-services.php', {
        user_id: actualUserId
    });
}

function addNewUser() {
    Swal.fire({
        title: 'Add New User',
        text: 'What type of user would you like to add?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Regular User',
        denyButtonText: 'Therapist',
        cancelButtonText: 'Admin'
    }).then((result) => {
        if (result.isConfirmed) {
            showGlobalModal('../modal/admin_modal-add-user.php', { role: 'User' });
        } else if (result.isDenied) {
            showGlobalModal('../modal/admin_modal-add-therapist.php', { role: 'Therapist' });
        } else if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
            showGlobalModal('../modal/admin_modal-add-user.php', { role: 'Admin' });
        }
    });
}

function deleteUser(userId) {
    Swal.fire({
        title: 'Delete User?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            // Determine if this is a therapist or regular user
            const isTherapist = userId.toString().startsWith('therapist_');
            const controllerUrl = isTherapist ? '../controller/therapist_contr.php' : '../controller/user_contr.php';
            const action = isTherapist ? 'delete_therapist' : 'delete_user';
            const userIdField = isTherapist ? 'therapist_id' : 'user_id';
            const actualUserId = isTherapist ? userId.replace('therapist_', '') : userId;
            
            $.ajax({
                url: controllerUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: action,
                    [userIdField]: actualUserId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', 'User has been deleted.', 'success');
                        loadUnifiedUsers();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to delete user', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to delete user', 'error');
                }
            });
        }
    });
}

function refreshTable() {
    loadUnifiedUsers();
    $('#searchUsers').val('');
    $('#filterRole, #filterStatus').val('');
}

function updatePagination() {
    const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
    const pagination = $('#pagination');
    
    if (totalPages <= 1) {
        $('#paginationContainer').hide();
        return;
    }
    
    $('#paginationContainer').show();
    let html = '';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
             </li>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                     </li>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
             </li>`;
    
    pagination.html(html);
}

function changePage(page) {
    const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayUsers();
        updatePagination();
    }
}

function exportUsers() {
    // Implementation for export functionality
    Swal.fire('Info', 'Export functionality coming soon!', 'info');
}

function bulkEdit() {
    const selected = $('.user-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        Swal.fire('Info', 'Please select users to edit', 'info');
        return;
    }
    
    // Implementation for bulk edit
    Swal.fire('Info', 'Bulk edit functionality coming soon!', 'info');
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<style>
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.btn-group-sm .btn {
    padding: 0.375rem 0.5rem;
}

.badge {
    font-size: 0.75rem;
}

.user-checkbox {
    cursor: pointer;
}

#unifiedUsersTable tbody tr:hover {
    background-color: #f8f9fa;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>