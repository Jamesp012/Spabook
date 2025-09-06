<div class="container">


  <!-- Tabs Navigation -->
  <ul class="nav nav-tabs nav-fill mb-2" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#regular-users" type="button" role="tab" aria-controls="regular-users" aria-selected="true">
        <i class="fas fa-users me-2"></i>Customers <span class="badge bg-primary ms-1" id="userCount">0</span>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-users" type="button" role="tab" aria-controls="admin-users" aria-selected="false">
        <i class="fas fa-user-shield me-2"></i>Administrators <span class="badge bg-danger ms-1" id="adminCount">0</span>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="therapist-tab" data-bs-toggle="tab" data-bs-target="#therapist-users" type="button" role="tab" aria-controls="therapist-users" aria-selected="false">
        <i class="fas fa-spa me-2"></i>Therapists <span class="badge bg-success ms-1" id="therapistCount">0</span>
      </button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="userTabsContent">
    <!-- Regular Users Tab -->
    <div class="tab-pane fade show active" id="regular-users" role="tabpanel" aria-labelledby="users-tab">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customer Accounts</h5>
        </div>
        <div class="card-body p-0">
          <!-- Desktop Table -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0" id="userTable">
              <thead class="table-light">
                <tr>
                  <th width="60">Avatar</th>
                  <th width="300">Name</th>
                  <th class="text-truncate">Email</th>
                  <th width="100">Contact</th>
                  <th width="80">Status</th>
                  <th width="100">Join Date</th>
                  <th width="150">Actions</th>
                </tr>
              </thead>
            </table>
            <div class="table-body-scroll">
              <table class="table table-hover mb-0">
                <tbody id="userTableBody">
                  <!-- Populated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-end p-3">
            <nav aria-label="Customers pagination">
              <ul class="pagination pagination-sm mb-0" id="userPagination"></ul>
            </nav>
          </div>
          <!-- Mobile Cards -->
          <div class="d-lg-none" id="userMobileCards">
            <!-- Cards rendered dynamically -->
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Users Tab -->
    <div class="tab-pane fade" id="admin-users" role="tabpanel" aria-labelledby="admin-tab">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Administrator Accounts</h5>
              <small class="text-muted">Manage system administrators with full access</small>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" onclick="refreshActiveTab()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
              </button>
              <button class="btn btn-danger btn-sm" onclick="addNewAdmin()">
                <i class="fas fa-user-plus me-1"></i>Add Administrator
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <!-- Desktop Table -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0" id="adminTable">
              <thead class="table-light">
                <tr>
                  <th width="60">Avatar</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th width="120">Status</th>
                  <th width="130">Join Date</th>
                  <th width="180">Actions</th>
                </tr>
              </thead>
            </table>
            <div class="table-body-scroll">
              <table class="table table-hover mb-0">
                <tbody id="adminTableBody">
                  <!-- Populated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-end p-3">
            <nav aria-label="Administrators pagination">
              <ul class="pagination pagination-sm mb-0" id="adminPagination"></ul>
            </nav>
          </div>
          <!-- Mobile Cards -->
          <div class="d-lg-none" id="adminMobileCards">
            <!-- Cards rendered dynamically -->
          </div>
        </div>
      </div>
    </div>

    <!-- Therapist Users Tab -->
    <div class="tab-pane fade" id="therapist-users" role="tabpanel" aria-labelledby="therapist-tab">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0"><i class="fas fa-spa me-2"></i>Therapist Accounts</h5>
              <small class="text-muted">Manage therapists and their specializations</small>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" onclick="refreshActiveTab()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
              </button>
              <button class="btn btn-success btn-sm" onclick="addNewTherapist()">
                <i class="fas fa-user-plus me-1"></i>Add Therapist
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <!-- Desktop Table -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0" id="therapistTable">
              <thead class="table-light">
                <tr>
                  <th width="60">Avatar</th>
                  <th>Name & Contact</th>
                  <th>Email</th>
                  <th>Specialization</th>
                  <th width="100">Services</th>
                  <th width="120">Schedule Status</th>
                  <th width="130">Join Date</th>
                  <th width="240">Actions</th>
                </tr>
              </thead>
            </table>
            <div class="table-body-scroll">
              <table class="table table-hover mb-0">
                <tbody id="therapistTableBody">
                  <!-- Populated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-end p-3">
            <nav aria-label="Therapists pagination">
              <ul class="pagination pagination-sm mb-0" id="therapistPagination"></ul>
            </nav>
          </div>
          <!-- Mobile Cards -->
          <div class="d-lg-none" id="therapistMobileCards">
            <!-- Cards rendered dynamically -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // let allUsers = [];
  // let regularUsers = [];
  // let adminUsers = [];
  // let therapistUsers = [];

  // Pagination state
  const pageSize = 10;
  let userPage = 1;
  let adminPage = 1;
  let therapistPage = 1;

  $(document).ready(function() {
    // Load data when page loads
    loadAllUsers();

    // Tab change events
    $('#users-tab').on('click', function() {
      if (regularUsers.length === 0) loadRegularUsers();
    });

    $('#admin-tab').on('click', function() {
      if (adminUsers.length === 0) loadAdminUsers();
    });

    $('#therapist-tab').on('click', function() {
      if (therapistUsers.length === 0) loadTherapistUsers();
    });
  });

  // Load all users and therapists from their respective sources
  function loadAllUsers() {
    showLoading('users');

    // Fetch users from users table
    $.ajax({
      url: '../controller/user_contr.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'fetch_unified_users'
      },
      success: function(userResult) {
        allUsers = userResult.all_users || [];
        regularUsers = userResult.regular_user || [];
        adminUsers = userResult.admin || [];
        therapistUsers = userResult.therapist || [];
        // console.log('Loaded users from users table:', allUsers.length);
        // console.log('User data sample:', userResult);
        // // Separate regular users and admins from users table
        // regularUsers = allUsers.filter(user => {
        //     const role = (user.role || '').toLowerCase();
        //     return role === 'user' || role === '' || role === null || !user.role;
        // });
        // adminUsers = allUsers.filter(user => {
        //     const role = (user.role || '').toLowerCase();
        //     return role === 'admin' || role === 'administrator';
        // });

        // console.log('Regular users found:', regularUsers.length);
        // console.log('Admin users found:', adminUsers.length);
        // console.log('Admin users:', adminUsers.map(u => ({name: u.full_name, role: u.role})));
        // console.log('All user roles:', allUsers.map(u => ({name: u.full_name, role: u.role})));

        // Now fetch therapists from therapist table
        // $.ajax({
        //     url: '../controller/therapist_contr.php',
        //     type: 'POST',
        //     dataType: 'json',
        //     data: {
        //         action: 'get_all_therapists'
        //     },
        //     success: function(therapistResult) {
        //         hideLoading('users');
        //         console.log('Loaded therapists from therapist table:', therapistResult?.length || 0);
        //         console.log('Therapist data sample:', therapistResult?.[0]);

        //         // Process therapist data - convert to match user structure
        //         therapistUsers = (therapistResult || []).map(therapist => {
        //             return {
        //                 user_id: therapist.therapistid,
        //                 full_name: therapist.therapist_name || 'Unnamed Therapist',
        //                 email: (therapist.email && therapist.email !== 'N/A') ? therapist.email : 'No email provided',
        //                 contact_number: therapist.contact_number || therapist.phone_number || 'Not provided',
        //                 phone_number: therapist.phone_number || therapist.contact_number || 'Not provided',
        //                 bio: therapist.therapist_desc || therapist.specialization || 'General Therapy',
        //                 specialization: therapist.specialization || therapist.therapist_desc || 'General Therapy',
        //                 therapist_services: therapist.services || therapist.service_id || 'Multiple Services',
        //                 is_active: therapist.is_active === true || therapist.is_active === 1 || therapist.is_active === '1',
        //                 created_at: therapist.created_at || therapist.date_created || new Date().toISOString(),
        //                 profile_picture: therapist.profile_picture || null,
        //                 role: 'Therapist',
        //                 rate: therapist.rate || 0
        //             };
        //         });

        //         console.log('Processed therapist users:', therapistUsers.length);

        //         // Update counts and render initial tab
        updateCounts();
        //         renderRegularUsers();
        //     },
        //     error: function(xhr, status, error) {
        //         hideLoading('users');
        //         console.error('Error loading therapists:', error);
        //         // Continue without therapists if error
        //         therapistUsers = [];
        //         updateCounts();
        renderRegularUsers();
        renderAdminUsers();
        renderTherapistUsers();


        //     }
        // });
      },
      error: function(xhr, status, error) {
        hideLoading('users');
        console.error('Error loading users:', error);
        Swal.fire('Error', 'Failed to load users', 'error');
      }
    });
  }

  function loadRegularUsers() {
    renderRegularUsers();
  }

  function loadAdminUsers() {
    renderAdminUsers();
  }

  function loadTherapistUsers() {
    renderTherapistUsers();
  }

  function renderRegularUsers() {
    const tbody = $('#userTableBody');
    const mobileCards = $('#userMobileCards');

    tbody.empty();
    mobileCards.empty();

    if (regularUsers.length === 0) {
      tbody.append(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No Customers Found</h5>
                        <p>Customers sign up through the registration page.</p>
                    </div>
                </td>
            </tr>
        `);
      $('#userPagination').empty();
      return;
    }

    // Pagination slice
    const start = (userPage - 1) * pageSize;
    const paged = regularUsers.slice(start, start + pageSize);

    paged.forEach(user => {
      const avatar = user.profile_picture ?
        `<img src="${user.profile_picture}" class="rounded-circle" width="35" height="35">` :
        `<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><i class="fas fa-user text-white"></i></div>`;

      const statusBadge = user.is_active !== false ?
        '<span class="badge bg-success">Active</span>' :
        '<span class="badge bg-danger">Inactive</span>';

      const joinDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';
      const contact = user.contact_number || user.phone_number || 'Not provided';

      // Desktop table row
      tbody.append(`
            <tr>
                <td>${avatar}</td>
                <td>
                    <div class="fw-semibold">${user.full_name}</div>
                    <small class="text-muted">Customer</small>
                </td>
                <td><span class="text-muted">${user.email}</span></td>
                <td><small class="text-muted">${contact}</small></td>
                <td>${statusBadge}</td>
                <td><small class="text-muted">${joinDate}</small></td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="editUser('${user.user_id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="viewUser('${user.user_id}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteUser('${user.user_id}', 'User')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);

      // Mobile card
      mobileCards.append(`
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        ${avatar}
                        <div class="ms-3">
                            <div class="fw-semibold">${user.full_name}</div>
                            <div class="text-muted small">${user.email}</div>
                            <div class="text-muted small">${contact}</div>
                            ${statusBadge}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="editUser('${user.user_id}')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="viewUser('${user.user_id}')">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.user_id}', 'User')">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        `);
    });
    // Build pagination
    // buildPagination('#userPagination', userPage, Math.ceil(regularUsers.length / pageSize), (p) => {
    //   userPage = p;
    //   renderRegularUsers();
    // });
  }

  function renderAdminUsers() {
    console.log('🛡️ Rendering admin users. Count:', adminUsers.length);
    const tbody = $('#adminTableBody');
    const mobileCards = $('#adminMobileCards');

    tbody.empty();
    mobileCards.empty();

    if (adminUsers.length === 0) {
      tbody.append(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <h5>No Administrators Found</h5>
                        <p>Click "Add Admin" to create administrator accounts.</p>
                        <small class="text-warning">Debug: Check browser console for data loading info</small>
                    </div>
                </td>
            </tr>
        `);
      $('#adminPagination').empty();
      return;
    }

    const start = (adminPage - 1) * pageSize;
    const paged = adminUsers.slice(start, start + pageSize);

    paged.forEach(user => {
      const avatar = user.profile_picture ?
        `<img src="${user.profile_picture}" class="rounded-circle" width="35" height="35">` :
        `<div class="rounded-circle bg-danger d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><i class="fas fa-user-shield text-white"></i></div>`;

      const statusBadge = user.is_active !== false ?
        '<span class="badge bg-success">Active</span>' :
        '<span class="badge bg-danger">Inactive</span>';

      const joinDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';

      // Desktop table row
      tbody.append(`
            <tr>
                <td>${avatar}</td>
                <td>
                    <div class="fw-semibold">${user.full_name}</div>
                    <small class="text-muted">${user.email}</small>
                </td>
                <td><span class="text-muted">${user.email}</span></td>
                <td>${statusBadge}</td>
                <td><small class="text-muted">${joinDate}</small></td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="editAdmin('${user.user_id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="viewAdmin('${user.user_id}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteUser('${user.user_id}', 'Admin')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);

      // Mobile card
      mobileCards.append(`
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        ${avatar}
                        <div class="ms-3">
                            <div class="fw-semibold">${user.full_name}</div>
                            <div class="text-muted small">${user.email}</div>
                            ${statusBadge}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="editAdmin('${user.user_id}')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="viewAdmin('${user.user_id}')">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.user_id}', 'Admin')">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        `);
    });
    // Build pagination
    // buildPagination('#adminPagination', adminPage, Math.ceil(adminUsers.length / pageSize), (p) => {
    //   adminPage = p;
    //   renderAdminUsers();
    // });
  }

  function renderTherapistUsers() {
    console.log('🌿 Rendering therapist users. Count:', therapistUsers.length);
    const tbody = $('#therapistTableBody');
    const mobileCards = $('#therapistMobileCards');

    tbody.empty();
    mobileCards.empty();

    if (therapistUsers.length === 0) {
      tbody.append(`
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-spa fa-3x mb-3"></i>
                        <h5>No Therapists Found</h5>
                        <p>Click "Add Therapist" to create therapist accounts.</p>
                        <small class="text-warning">Debug: Check browser console for data loading info</small>
                    </div>
                </td>
            </tr>
        `);
      return;
    }

    therapistUsers.forEach(user => {
      const avatar = user.profile_picture ?
        `<img src="${user.profile_picture}" class="rounded-circle" width="35" height="35">` :
        `<div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><i class="fas fa-spa text-white"></i></div>`;

      const scheduleStatus = user.is_active !== false ?
        '<span class="badge bg-success">Available</span>' :
        '<span class="badge bg-warning">Unavailable</span>';

      const joinDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A';
      const specialization = user.bio || 'General Therapy';
      const contact = user.contact_number || user.phone_number || 'Not provided';
      const servicesOffered = user.therapist_services || 'Multiple Services';

      // Desktop table row
      tbody.append(`
            <tr>
                <td>${avatar}</td>
                <td>
                    <div class="fw-semibold">${user.full_name}</div>
                    <small class="text-muted">${contact}</small>
                </td>
                <td><span class="text-muted">${user.email}</span></td>
                <td><span class="badge bg-info text-white">${specialization}</span></td>
                <td><small class="text-primary fw-semibold">${servicesOffered}</small></td>
                <td>${scheduleStatus}</td>
                <td><small class="text-muted">${joinDate}</small></td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="editTherapist('${user.user_id}')" title="Edit Profile">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="viewTherapist('${user.user_id}')" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="manageTherapistSchedule('${user.user_id}')" title="Manage Schedule">
                            <i class="fas fa-calendar-alt"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="manageTherapistServices('${user.user_id}')" title="Manage Services">
                            <i class="fas fa-spa"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteUser('${user.user_id}', 'Therapist')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);

      // Mobile card
      mobileCards.append(`
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        ${avatar}
                        <div class="ms-3">
                            <div class="fw-semibold">${user.full_name}</div>
                            <div class="text-muted small">${user.email}</div>
                            <div class="text-muted small">${contact}</div>
                            <div class="mb-1">
                                <span class="badge bg-info text-white">${specialization}</span>
                            </div>
                            <div class="mb-1">
                                <small class="text-primary fw-semibold">${servicesOffered}</small>
                            </div>
                            ${scheduleStatus}
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <button class="btn btn-sm btn-outline-primary" onclick="editTherapist('${user.user_id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="viewTherapist('${user.user_id}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="manageTherapistSchedule('${user.user_id}')" title="Schedule">
                            <i class="fas fa-calendar-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="manageTherapistServices('${user.user_id}')" title="Services">
                            <i class="fas fa-spa"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.user_id}', 'Therapist')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
    });
  }

  function updateCounts() {
    $('#userCount').text(regularUsers.length);
    $('#adminCount').text(adminUsers.length);
    $('#therapistCount').text(therapistUsers.length);
  }

  function showLoading(tab) {
    let target, colspan;

    switch (tab) {
      case 'users':
        target = '#userTableBody';
        colspan = '7';
        break;
      case 'admin':
        target = '#adminTableBody';
        colspan = '6';
        break;
      case 'therapist':
        target = '#therapistTableBody';
        colspan = '8';
        break;
      default:
        target = '#userTableBody';
        colspan = '7';
    }

    $(target).html(`
        <tr>
            <td colspan="${colspan}" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Loading ${tab} users...</div>
            </td>
        </tr>
    `);
  }

  function hideLoading(tab) {
    // Loading will be cleared by render functions
  }

  // Action Functions
  function getModalUrl(filename) {
    const path = window.location.pathname;
    if (path.includes('/views/admin/')) return '../modal/' + filename; // when loaded directly under /views/admin/
    if (path.includes('/views/')) return './modal/' + filename; // when embedded under /views/
    return 'views/modal/' + filename; // fallback
  }

  function addNewAdmin() {
    showGlobalModal(getModalUrl('admin_modal-add-user.php'), {
      role: 'Admin',
      title: 'Add New Administrator'
    });
  }

  function addNewTherapist() {
    showGlobalModal(getModalUrl('admin_modal-add-therapist.php'), {
      role: 'Therapist',
      title: 'Add New Therapist'
    });
  }

  function editUser(userId) {
    const user = regularUsers.find(u => u.user_id === userId);
    showGlobalModal('../views/modal/admin_modal-edit-user.php', {
      id: userId,
      name: user?.full_name,
      email: user?.email,
      role: 'User'
    });
  }

  function editAdmin(userId) {
    const user = adminUsers.find(u => u.user_id === userId);
    showGlobalModal('../modal/admin_modal-edit-user.php', {
      id: userId,
      name: user?.full_name,
      email: user?.email,
      role: 'Admin'
    });
  }

  function editTherapist(userId) {
    const user = therapistUsers.find(u => u.user_id === userId);
    showGlobalModal('../modal/admin_modal-edit-user.php', {
      id: userId,
      name: user?.full_name,
      email: user?.email,
      role: 'Therapist'
    });
  }

  function viewUser(userId) {
    showGlobalModal('../modal/admin_modal-view-user.php', {
      user_id: userId,
      role: 'User'
    });
  }

  function viewAdmin(userId) {
    showGlobalModal('../modal/admin_modal-view-user.php', {
      user_id: userId,
      role: 'Admin'
    });
  }

  function viewTherapist(userId) {
    showGlobalModal('../modal/admin_modal-view-user.php', {
      user_id: userId,
      role: 'Therapist'
    });
  }

  function manageTherapistSchedule(userId) {
    showGlobalModal('../modal/admin_modal-therapist-schedule.php', {
      user_id: userId
    });
  }

  function manageTherapistServices(userId) {
    showGlobalModal('../modal/admin_modal-therapist-services.php', {
      user_id: userId
    });
  }

  function deleteUser(userId, role) {
    Swal.fire({
      title: `Delete ${role}?`,
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      confirmButtonText: 'Yes, Delete'
    }).then((result) => {
      if (result.isConfirmed) {
        // Use appropriate controller based on role
        const isTherapist = role.toLowerCase() === 'therapist';
        const controllerUrl = isTherapist ? '../controller/therapist_contr.php' : '../controller/user_contr.php';
        const action = isTherapist ? 'delete_therapist' : 'delete_user';
        const userIdField = isTherapist ? 'therapist_id' : 'user_id';

        $.ajax({
          url: controllerUrl,
          type: 'POST',
          dataType: 'json',
          data: {
            action: action,
            [userIdField]: userId
          },
          success: function(response) {
            if (response.status === 'success') {
              Swal.fire('Deleted!', `${role} has been deleted.`, 'success');
              refreshActiveTab();
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

  function refreshActiveTab() {
    const activeTab = $('#userTabs .nav-link.active').attr('id');

    // Clear the appropriate user array and reload all data
    switch (activeTab) {
      case 'users-tab':
        regularUsers = [];
        break;
      case 'admin-tab':
        adminUsers = [];
        break;
      case 'therapist-tab':
        therapistUsers = [];
        break;
    }

    loadAllUsers();
  }

  // Global callback for modal success
  window.userManagementSuccess = function() {
    refreshActiveTab();
  };
</script>

<style>
  /* Tab Styling */
  .nav-tabs {
    border-bottom: 2px solid #dee2e6;
  }

  .nav-tabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 20px;
    margin-right: 4px;
    transition: all 0.3s ease;
  }

  .nav-tabs .nav-link:hover {
    border: none;
    background-color: #f8f9fa;
    color: #495057;
  }

  .nav-tabs .nav-link.active {
    background-color: #fff;
    color: #495057;
    border: 2px solid #dee2e6;
    border-bottom: 2px solid #fff;
    margin-bottom: -2px;
    position: relative;
    z-index: 1;
  }

  /* Card Styling */
  .card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 12px;
  }

  .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 12px 12px 0 0 !important;
    padding: 1rem 1.25rem;
  }

  .card-header .d-flex {
    align-items: start;
  }

  .card-header .d-flex .btn {
    white-space: nowrap;
    margin-left: 0.25rem;
  }

  /* Table Styling */
  .table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    padding: 12px;
  }

  .table td {
    vertical-align: middle;
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
  }

  /* Button Groups */
  .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }

  /* Badge Styling */
  .badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.35rem 0.65rem;
  }

  /* Dropdown Styling */
  .dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    padding: 0.5rem;
  }

  .dropdown-item {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .dropdown-item:hover:not(.disabled) {
    background-color: #f8f9fa;
  }

  /* Avatar Styling */
  .rounded-circle {
    object-fit: cover;
  }

  /* Mobile Cards */
  .card-body {
    padding: 1.25rem;
  }

  /* Loading States */
  .spinner-border {
    width: 2rem;
    height: 2rem;
  }

  /* Empty State */
  .text-muted i.fa-3x {
    opacity: 0.5;
  }

  /* Page Header */
  .container h2 {
    color: #2c3e50;
    font-weight: 700;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .nav-tabs .nav-link {
      padding: 8px 12px;
      font-size: 0.9rem;
    }

    .card-header h5 {
      font-size: 1.1rem;
    }

    .card-header .d-flex {
      flex-direction: column;
      align-items: stretch !important;
      gap: 1rem;
    }

    .card-header .d-flex .btn {
      font-size: 0.875rem;
    }

    .btn-group .btn {
      padding: 0.2rem 0.4rem;
    }

    .dropdown-toggle {
      padding: 0.5rem 0.75rem;
    }
  }

  /* Animation for tab content */
  .tab-pane {
    animation: fadeIn 0.3s ease-in;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Custom scrollbar for tables on mobile */
  .table-responsive::-webkit-scrollbar {
    height: 6px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Fixed table header with scrollable body - Dynamic Height */
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

  /* Ensure table columns align properly */
  .table-responsive .table thead th:first-child,
  .table-body-scroll .table tbody td:first-child {
    width: 60px;
  }

  .table-responsive .table thead th:nth-child(2),
  .table-body-scroll .table tbody td:nth-child(2) {
    width: 300px;
  }

  .table-responsive .table thead th:nth-child(4),
  .table-body-scroll .table tbody td:nth-child(4) {
    width: 100px;
  }

  .table-responsive .table thead th:nth-child(5),
  .table-body-scroll .table tbody td:nth-child(5) {
    width: 80px;
  }

  .table-responsive .table thead th:nth-child(6),
  .table-body-scroll .table tbody td:nth-child(6) {
    width: 100px;
  }

  .table-responsive .table thead th:nth-child(7),
  .table-body-scroll .table tbody td:nth-child(7) {
    width: 150px;
  }
</style>