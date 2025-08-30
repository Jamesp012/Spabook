<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bell-fill me-2"></i>Notification Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="dropdown me-3">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="notificationFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="notificationFilterDropdown">
                                    <li><a class="dropdown-item notification-filter active" data-filter="all" href="#">All Notifications</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item notification-filter" data-filter="info" href="#">Information</a></li>
                                    <li><a class="dropdown-item notification-filter" data-filter="success" href="#">Success</a></li>
                                    <li><a class="dropdown-item notification-filter" data-filter="warning" href="#">Warnings</a></li>
                                    <li><a class="dropdown-item notification-filter" data-filter="error" href="#">Errors</a></li>
                                </ul>
                            </div>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchNotifications" placeholder="Search notifications...">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-primary" id="createNotificationBtn">
                            <i class="bi bi-plus-circle me-1"></i>Create Notification
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="notificationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                        <div class="table-body-scroll">
                            <table class="table table-hover">
                                <tbody id="notificationsTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading notifications...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> notifications
                        </div>
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination pagination-sm mb-0" id="paginationLinks">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="createNotificationModal" tabindex="-1" aria-labelledby="createNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createNotificationModalLabel">
                    <i class="bi bi-bell-fill me-2"></i>Create Notification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createNotificationForm">
                    <div class="mb-3">
                        <label for="notificationRecipient" class="form-label">Recipient</label>
                        <select class="form-select" id="notificationRecipient" required>
                            <option value="" selected disabled>Select recipient type</option>
                            <option value="specific">Specific User</option>
                            <option value="all">All Users</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="specificUserContainer" style="display: none;">
                        <label for="specificUser" class="form-label">Select User</label>
                        <select class="form-select" id="specificUser">
                            <option value="" selected disabled>Loading users...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notificationType" class="form-label">Notification Type</label>
                        <select class="form-select" id="notificationType" required>
                            <option value="info" selected>Information</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notificationTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="notificationTitle" placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notificationMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="notificationMessage" rows="4" placeholder="Enter notification message" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notificationMetadata" class="form-label">Additional Data (Optional)</label>
                        <textarea class="form-control" id="notificationMetadata" rows="3" placeholder='{"key": "value"}'></textarea>
                        <div class="form-text">Enter valid JSON data if needed (e.g., booking IDs, links, etc.)</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendNotificationBtn">
                    <i class="bi bi-send me-1"></i>Send Notification
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNotificationModalLabel">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="notification-view-container">
                    <h5 id="viewNotificationTitle" class="mb-2"></h5>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge" id="viewNotificationType"></span>
                        <small class="text-muted" id="viewNotificationTime"></small>
                    </div>
                    <hr>
                    <p id="viewNotificationMessage"></p>
                    
                    <div id="viewNotificationMetadataContainer" class="mt-3 p-3 bg-light rounded" style="display: none;">
                        <h6 class="mb-2">Additional Data:</h6>
                        <pre id="viewNotificationMetadata" class="mb-0" style="white-space: pre-wrap;"></pre>
                    </div>
                    
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>Recipient:</strong></p>
                                <p id="viewNotificationUser"></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Status:</strong></p>
                                <p id="viewNotificationStatus"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Variables
        let currentPage = 1;
        const pageSize = 15;
        let totalNotifications = 0;
        let currentFilter = 'all';
        let searchQuery = '';
        
        // Initialize
        loadNotifications();
        loadUsers();
        
        // Event handlers
        $('#createNotificationBtn').on('click', function() {
            $('#createNotificationForm')[0].reset();
            $('#specificUserContainer').hide();
            $('#createNotificationModal').modal('show');
        });
        
        $('#notificationRecipient').on('change', function() {
            if ($(this).val() === 'specific') {
                $('#specificUserContainer').show();
            } else {
                $('#specificUserContainer').hide();
            }
        });
        
        $('#sendNotificationBtn').on('click', sendNotification);
        
        $('.notification-filter').on('click', function(e) {
            e.preventDefault();
            $('.notification-filter').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            currentPage = 1;
            loadNotifications();
        });
        
        $('#searchBtn').on('click', function() {
            searchQuery = $('#searchNotifications').val().trim();
            currentPage = 1;
            loadNotifications();
        });
        
        $('#searchNotifications').on('keypress', function(e) {
            if (e.which === 13) {
                searchQuery = $(this).val().trim();
                currentPage = 1;
                loadNotifications();
            }
        });
        
        // Functions
        function loadNotifications() {
            $('#notificationsTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading notifications...</p>
                    </td>
                </tr>
            `);
            
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'get_admin_notifications',
                    limit: 0 // Get all for admin view
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let notifications = response.notifications;
                        totalNotifications = notifications.length;
                        
                        // Filter notifications if needed
                        if (['info', 'success', 'warning', 'error'].includes(currentFilter)) {
                            notifications = notifications.filter(n => n.type === currentFilter);
                        }
                        
                        // Search if query exists
                        if (searchQuery) {
                            const query = searchQuery.toLowerCase();
                            notifications = notifications.filter(n => 
                                (n.title && n.title.toLowerCase().includes(query)) || 
                                (n.message && n.message.toLowerCase().includes(query)) ||
                                (n.first_name && n.first_name.toLowerCase().includes(query)) ||
                                (n.last_name && n.last_name.toLowerCase().includes(query)) ||
                                (n.email && n.email.toLowerCase().includes(query))
                            );
                        }
                        
                        // Update counts
                        $('#totalCount').text(totalNotifications);
                        $('#showingCount').text(notifications.length);
                        
                        // Paginate
                        const totalPages = Math.ceil(notifications.length / pageSize);
                        const start = (currentPage - 1) * pageSize;
                        const end = start + pageSize;
                        const paginatedNotifications = notifications.slice(start, end);
                        
                        renderNotifications(paginatedNotifications);
                        renderPagination(totalPages);
                    } else {
                        console.error('Error loading notifications:', response.message);
                        $('#notificationsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-4 text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Error loading notifications. Please try again later.
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $('#notificationsTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Error connecting to the server. Please check your connection and try again.
                            </td>
                        </tr>
                    `);
                }
            });
        }
        
        function renderNotifications(notifications) {
            if (notifications.length === 0) {
                $('#notificationsTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2">No notifications found</p>
                        </td>
                    </tr>
                `);
                return;
            }
            
            let html = '';
            
            notifications.forEach(notification => {
                const typeClass = getTypeClass(notification.type);
                const statusBadge = notification.is_read 
                    ? '<span class="badge bg-secondary">Read</span>' 
                    : '<span class="badge bg-success">Unread</span>';
                
                const userName = notification.first_name && notification.last_name 
                    ? `${notification.first_name} ${notification.last_name}` 
                    : (notification.email || 'Unknown User');
                
                html += `
                    <tr>
                        <td>${notification.notificationid}</td>
                        <td>${userName}</td>
                        <td>${notification.title}</td>
                        <td><span class="badge ${typeClass}">${notification.type || 'info'}</span></td>
                        <td>${statusBadge}</td>
                        <td>${notification.relative_time}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary view-notification-btn" data-id="${notification.notificationid}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-notification-btn" data-id="${notification.notificationid}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            $('#notificationsTableBody').html(html);
            
            // Attach event handlers to buttons
            $('.view-notification-btn').on('click', function() {
                const notificationId = $(this).data('id');
                viewNotification(notificationId, notifications);
            });
            
            $('.delete-notification-btn').on('click', function() {
                const notificationId = $(this).data('id');
                deleteNotification(notificationId);
            });
        }
        
        function renderPagination(totalPages) {
            if (totalPages <= 1) {
                $('#paginationLinks').html('');
                return;
            }
            
            let html = `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
            
            for (let i = 1; i <= totalPages; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            
            html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
            
            $('#paginationLinks').html(html);
            
            // Attach event handlers
            $('.page-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage && page > 0) {
                    currentPage = page;
                    loadNotifications();
                }
            });
        }
        
        function getTypeClass(type) {
            switch (type) {
                case 'info': return 'bg-primary';
                case 'success': return 'bg-success';
                case 'warning': return 'bg-warning text-dark';
                case 'error': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }
        
        function loadUsers() {
            $.ajax({
                url: '../controller/user_contr.php',
                type: 'POST',
                data: {
                    action: 'get_all_users'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const users = response.users;
                        let html = '<option value="" selected disabled>Select a user</option>';
                        
                        users.forEach(user => {
                            const userName = `${user.first_name} ${user.last_name} (${user.email})`;
                            html += `<option value="${user.user_id}">${userName}</option>`;
                        });
                        
                        $('#specificUser').html(html);
                    } else {
                        $('#specificUser').html('<option value="" selected disabled>Error loading users</option>');
                    }
                },
                error: function() {
                    $('#specificUser').html('<option value="" selected disabled>Error loading users</option>');
                }
            });
        }
        
        function sendNotification() {
            const recipientType = $('#notificationRecipient').val();
            const specificUserId = $('#specificUser').val();
            const type = $('#notificationType').val();
            const title = $('#notificationTitle').val();
            const message = $('#notificationMessage').val();
            let metadata = $('#notificationMetadata').val();
            
            // Validate form
            if (!recipientType || !type || !title || !message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields'
                });
                return;
            }
            
            if (recipientType === 'specific' && !specificUserId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a user'
                });
                return;
            }
            
            // Validate metadata JSON if provided
            if (metadata) {
                try {
                    JSON.parse(metadata);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid JSON',
                        text: 'Please enter valid JSON for additional data or leave it empty'
                    });
                    return;
                }
            } else {
                metadata = '{}';
            }
            
            // Show loading
            Swal.fire({
                title: 'Sending Notification...',
                html: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            if (recipientType === 'all') {
                // Send to all users
                $.ajax({
                    url: '../controller/user_contr.php',
                    type: 'POST',
                    data: {
                        action: 'get_all_users'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const users = response.users;
                            let successCount = 0;
                            let failCount = 0;
                            let totalUsers = users.length;
                            let processedCount = 0;
                            
                            users.forEach(user => {
                                sendToUser(user.user_id, type, title, message, metadata, function(success) {
                                    processedCount++;
                                    if (success) {
                                        successCount++;
                                    } else {
                                        failCount++;
                                    }
                                    
                                    if (processedCount === totalUsers) {
                                        // All notifications sent
                                        Swal.fire({
                                            icon: successCount > 0 ? 'success' : 'error',
                                            title: 'Notification Sent',
                                            html: `Successfully sent to ${successCount} users.<br>${failCount > 0 ? `Failed to send to ${failCount} users.` : ''}`
                                        });
                                        
                                        $('#createNotificationModal').modal('hide');
                                        loadNotifications();
                                    }
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to get users'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to get users'
                        });
                    }
                });
            } else {
                // Send to specific user
                sendToUser(specificUserId, type, title, message, metadata, function(success) {
                    if (success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Notification sent successfully'
                        });
                        
                        $('#createNotificationModal').modal('hide');
                        loadNotifications();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to send notification'
                        });
                    }
                });
            }
        }
        
        function sendToUser(userId, type, title, message, metadata, callback) {
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'create_notification',
                    user_id: userId,
                    title: title,
                    message: message,
                    type: type,
                    metadata: metadata
                },
                dataType: 'json',
                success: function(response) {
                    callback(response.status === 'success');
                },
                error: function() {
                    callback(false);
                }
            });
        }
        
        function viewNotification(notificationId, notifications) {
            const notification = notifications.find(n => n.notificationid == notificationId);
            
            if (!notification) {
                console.error('Notification not found:', notificationId);
                return;
            }
            
            // Set notification details in modal
            $('#viewNotificationTitle').text(notification.title);
            $('#viewNotificationMessage').text(notification.message);
            $('#viewNotificationTime').text(notification.formatted_time);
            
            const typeClass = getTypeClass(notification.type);
            $('#viewNotificationType').attr('class', `badge ${typeClass}`).text(notification.type || 'info');
            
            const userName = notification.first_name && notification.last_name 
                ? `${notification.first_name} ${notification.last_name}` 
                : (notification.email || 'Unknown User');
            $('#viewNotificationUser').text(userName);
            
            $('#viewNotificationStatus').html(
                notification.is_read 
                    ? '<span class="badge bg-secondary">Read</span>' 
                    : '<span class="badge bg-success">Unread</span>'
            );
            
            // Handle metadata
            if (notification.metadata && Object.keys(notification.metadata).length > 0) {
                $('#viewNotificationMetadata').text(JSON.stringify(notification.metadata, null, 2));
                $('#viewNotificationMetadataContainer').show();
            } else {
                $('#viewNotificationMetadataContainer').hide();
            }
            
            // Show modal
            $('#viewNotificationModal').modal('show');
        }
        
        function deleteNotification(notificationId) {
            Swal.fire({
                title: 'Delete Notification',
                text: 'Are you sure you want to delete this notification?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../controller/notification_contr.php',
                        type: 'POST',
                        data: {
                            action: 'delete_notification',
                            notification_id: notificationId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The notification has been deleted.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                loadNotifications();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete notification'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to connect to the server'
                            });
                        }
                    });
                }
            });
        }
    });
</script>

<style>
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