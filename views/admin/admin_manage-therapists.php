    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-12 px-md-4">

                <!-- Filters and Search -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchTherapist" placeholder="Search therapists...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterByService">
                            <option value="">All Services</option>
                            <!-- Services will be loaded here -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterByStatus">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" onclick="applyFilters()">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Therapists Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-table me-1"></i>Therapists List
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="addTherapist()">
                                <i class="bi bi-person-plus me-1"></i>Add Therapist
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadTherapists()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="therapistsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Service</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Added Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="table-body-scroll">
                                <table class="table table-bordered table-hover">
                                    <tbody id="therapistsTableBody">
                                        <!-- Therapists will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Loading State -->
                        <div id="loadingTherapists" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted">Loading therapists...</div>
                        </div>
                        
                        <!-- No Data State -->
                        <div id="noTherapists" class="text-center py-5 d-none">
                            <i class="bi bi-people fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Therapists Found</h5>
                            <p class="text-muted">No therapists match your search criteria.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal container is available from parent page (admin_home_page.php) -->
    
    <script>
        $(document).ready(function() {
            loadTherapists();
            loadServices(); // For filter dropdown
            
            // Search functionality
            $('#searchTherapist').on('keyup', function() {
                applyFilters();
            });
            
            // Filter change handlers
            $('#filterByService, #filterByStatus').on('change', function() {
                applyFilters();
            });
        });

        // Load all therapists
        function loadTherapists() {
            $('#loadingTherapists').removeClass('d-none');
            $('#noTherapists').addClass('d-none');
            $('#therapistsTableBody').html('');

            $.ajax({
                url: '../controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_all_therapists_admin'
                },
                success: function(result) {
                    $('#loadingTherapists').addClass('d-none');
                    
                    if (result === 'nodata' || !result || result.length === 0) {
                        $('#noTherapists').removeClass('d-none');
                    } else {
                        displayTherapists(result);
                    }
                },
                error: function() {
                    $('#loadingTherapists').addClass('d-none');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load therapists. Please try again.'
                    });
                }
            });
        }

        // Display therapists in table
        function displayTherapists(therapists) {
            let html = '';
            
            therapists.forEach(function(therapist) {
                // Check for active status - try is_active field first, then fallback
                let isActive = true; // Default to active
                if (therapist.hasOwnProperty('is_active')) {
                    isActive = therapist.is_active == 1 || therapist.is_active === true;
                } else if (therapist.hasOwnProperty('active')) {
                    isActive = therapist.active == 1 || therapist.active === true;
                } else if (therapist.therapist_desc && therapist.therapist_desc.includes('[INACTIVE]')) {
                    isActive = false;
                }
                
                const statusBadge = isActive ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-secondary">Inactive</span>';
                
                const photoHtml = therapist.photo ? 
                    `<img src="${therapist.photo}" alt="${therapist.therapist_name}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">` :
                    '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">'+therapist.therapist_name.charAt(0)+'</div>';
                
                const addedDate = therapist.date_added ? new Date(therapist.date_added).toLocaleDateString() : 'N/A';
                
                html += `
                    <tr>
                        <td>${therapist.therapistid}</td>
                        <td>${photoHtml}</td>
                        <td class="fw-semibold">${therapist.therapist_name}</td>
                        <td>
                            ${therapist.service_count > 0 ? 
                                therapist.service_names.slice(0, 3).map(service => 
                                    `<span class="badge bg-info me-1 mb-1">${service}</span>`
                                ).join('') + 
                                (therapist.service_count > 3 ? `<br><small class="text-muted">+${therapist.service_count - 3} more service(s)</small>` : '')
                                : '<span class="badge bg-secondary">No services</span>'
                            }
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="${therapist.therapist_desc || ''}">
                                ${therapist.therapist_desc || 'No description'}
                            </div>
                        </td>
                        <td>${statusBadge}</td>
                        <td>${addedDate}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm ${isActive ? 'btn-success' : 'btn-outline-secondary'}" 
                                        onclick="toggleTherapistStatus(${therapist.therapistid}, ${!isActive})" 
                                        title="${isActive ? 'Deactivate' : 'Activate'}">
                                    <i class="bi ${isActive ? 'bi-pause-circle' : 'bi-play-circle'}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTherapist(${therapist.therapistid})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTherapist(${therapist.therapistid}, '${therapist.therapist_name}')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewTherapist(${therapist.therapistid})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            $('#therapistsTableBody').html(html);
        }

        // Load services for filter dropdown
        function loadServices() {
            $.ajax({
                url: '../controller/booking_services_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_services'
                },
                success: function(result) {
                    if (result && result.length > 0) {
                        let options = '<option value="">All Services</option>';
                        result.forEach(function(service) {
                            options += `<option value="${service.id}">${service.service_name}</option>`;
                        });
                        $('#filterByService').html(options);
                    }
                }
            });
        }

        // Load statistics
        function loadStats() {
            $.ajax({
                url: '../controller/therapist_contr.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_therapist_stats'
                },
                success: function(result) {
                    if (result && !result.error) {
                        $('#totalTherapists').text(result.total || 0);
                        $('#activeTherapists').text(result.active || 0);
                        $('#servicesCovered').text(result.services || 0);
                    }
                },
                error: function() {
                    $('#totalTherapists, #activeTherapists, #servicesCovered').text('--');
                }
            });
        }

        // Apply filters
        function applyFilters() {
            const search = $('#searchTherapist').val().toLowerCase();
            const serviceFilter = $('#filterByService').val();
            const statusFilter = $('#filterByStatus').val();
            
            $('#therapistsTableBody tr').each(function() {
                const row = $(this);
                const name = row.find('td:nth-child(3)').text().toLowerCase();
                const description = row.find('td:nth-child(5)').text().toLowerCase();
                const serviceId = row.find('td:nth-child(4)').data('service-id') || '';
                const isActive = row.find('.badge.bg-success').length > 0;
                
                let showRow = true;
                
                // Search filter
                if (search && !name.includes(search) && !description.includes(search)) {
                    showRow = false;
                }
                
                // Service filter
                if (serviceFilter && serviceId != serviceFilter) {
                    showRow = false;
                }
                
                // Status filter
                if (statusFilter) {
                    if (statusFilter === 'active' && !isActive) {
                        showRow = false;
                    } else if (statusFilter === 'inactive' && isActive) {
                        showRow = false;
                    }
                }
                
                row.toggle(showRow);
            });
        }

        // Add new therapist
        function addTherapist() {
            showGlobalModal('modal/admin_modal-manage-therapists.php', {
                action: 'add'
            });
        }

        // Edit therapist
        function editTherapist(therapistId) {
            showGlobalModal('modal/admin_modal-manage-therapists.php', {
                action: 'edit',
                therapistid: therapistId
            });
        }

        // Delete therapist
        function deleteTherapist(therapistId, therapistName) {
            Swal.fire({
                title: 'Delete Therapist?',
                text: `Are you sure you want to delete "${therapistName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../controller/therapist_contr.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'delete_therapist',
                            therapist_id: therapistId
                        },
                        success: function(response) {
                            if (response && response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Therapist has been deleted successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadTherapists();
                                loadStats();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete therapist. Please try again.'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete therapist. Please try again.'
                            });
                        }
                    });
                }
            });
        }

        // View therapist details
        function viewTherapist(therapistId) {
            showGlobalModal('modal/admin_modal-manage-therapists.php', {
                action: 'view',
                therapistid: therapistId
            });
        }

        // Toggle therapist active/inactive status
        function toggleTherapistStatus(therapistId, newStatus) {
            const actionText = newStatus ? 'activate' : 'deactivate';
            
            Swal.fire({
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Therapist?`,
                text: `Are you sure you want to ${actionText} this therapist?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newStatus ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText}!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        text: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing therapist...`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '../controller/therapist_contr.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'update_therapist_status',
                            therapist_id: therapistId,
                            active: newStatus ? 1 : 0
                        },
                        success: function(response) {
                            if (response.status === 'success' || response === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: `Therapist ${newStatus ? 'activated' : 'deactivated'} successfully!`,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                // Reload therapists data
                                loadTherapists();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || `Failed to ${actionText} therapist. Please try again.`
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Toggle therapist status error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: `Failed to ${actionText} therapist. Please try again.`
                            });
                        }
                    });
                }
            });
        }
    </script>

    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .table th {
            border-top: none;
        }
        .btn-group .btn {
            border-radius: 0.25rem !important;
            margin-right: 2px;
        }
        .table td {
            vertical-align: middle;
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