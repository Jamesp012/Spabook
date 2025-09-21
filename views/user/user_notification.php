<div class="notification-container">
    <div class="notification-header d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications</h4>
        <div>
            <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-check-all me-1"></i>Mark All as Read
            </button>
            <div class="dropdown d-inline-block">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="notificationFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <ul class="dropdown-menu" aria-labelledby="notificationFilterDropdown">
                    <li><a class="dropdown-item notification-filter active" data-filter="all" href="#">All Notifications</a></li>
                    <li><a class="dropdown-item notification-filter" data-filter="unread" href="#">Unread Only</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item notification-filter" data-filter="info" href="#">Information</a></li>
                    <li><a class="dropdown-item notification-filter" data-filter="success" href="#">Success</a></li>
                    <li><a class="dropdown-item notification-filter" data-filter="warning" href="#">Warnings</a></li>
                    <li><a class="dropdown-item notification-filter" data-filter="error" href="#">Errors</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div id="notificationList" class="notification-list">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading notifications...</p>
        </div>
    </div>
    
    <div id="loadMoreContainer" class="text-center mt-3 d-none">
        <button id="loadMoreBtn" class="btn btn-outline-primary">
            <i class="bi bi-arrow-down-circle me-1"></i>Load More
        </button>
    </div>
    
    <div id="emptyNotifications" class="text-center py-5 d-none">
        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Notifications</h5>
        <p class="text-muted">You don't have any notifications at the moment.</p>
    </div>
</div>

<style>
    .notification-container {
        border-radius: 18px;
        padding: 0 0 30px 0;
        margin: 0;
        max-height: calc(100vh - 130px);
        overflow-y: auto;
    }

    .notification-list {
        padding: 0 18px;
    }

    .notification-item {
        background: #fff;
        border-radius: 10px;
        margin-bottom: 18px;
        padding: 18px 24px;
        font-size: 1.1rem;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        position: relative;
        cursor: pointer;
        transition: box-shadow 0.2s;
        overflow: hidden;
        border-left: 4px solid #6c757d; /* Default color */
    }
    
    .notification-item.unread {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .notification-item.info {
        border-left-color: #0d6efd; /* Bootstrap primary/info */
    }
    
    .notification-item.success {
        border-left-color: #198754; /* Bootstrap success */
    }
    
    .notification-item.warning {
        border-left-color: #ffc107; /* Bootstrap warning */
    }
    
    .notification-item.error {
        border-left-color: #dc3545; /* Bootstrap danger */
    }
    
    /* Special styling for booking-related notifications */
    .notification-item[data-booking-id] {
        border-left-width: 6px;
    }
    
    .notification-item .booking-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .notification-item .reminder-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .notification-item:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
    }

    .notification-close {
        position: absolute;
        top: 10px;
        right: 14px;
        background: #eee;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 1.3rem;
        color: #b48a6a;
        cursor: pointer;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .notification-close:hover {
        background: #b48a6a;
        color: #fff;
    }
    
    .notification-time {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 5px;
        font-weight: normal;
    }

    .notification-details {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(.4, 0, .2, 1), opacity 0.3s;
        font-size: 1rem;
        margin-top: 0;
        font-weight: normal;
    }

    .notification-item.expanded .notification-details {
        max-height: 300px;
        opacity: 1;
        margin-top: 10px;
    }
    
    .notification-metadata {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-top: 10px;
        font-size: 0.9rem;
    }

    @media (max-width: 767.98px) {
        .notification-list {
            padding: 0 5px;
        }

        .notification-item {
            font-size: 1rem;
            padding: 14px 38px 14px 10px;
            /* Add right padding for close button space */
            position: relative;
        }

        .notification-close {
            right: 8px;
            /* Move close button a bit more to the left */
            left: auto;
            top: 10px;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // Variables
        const userId = sessionStorage.getItem('user_id');
        let currentPage = 1;
        const pageSize = 10;
        let currentFilter = 'all';
        let hasMoreNotifications = true;
        
        // Initial load
        loadNotifications();
        
        // Event handlers
        $('#markAllReadBtn').on('click', markAllAsRead);
        $('#loadMoreBtn').on('click', loadMoreNotifications);
        $('.notification-filter').on('click', function(e) {
            e.preventDefault();
            $('.notification-filter').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            currentPage = 1;
            hasMoreNotifications = true;
            loadNotifications(true);
        });
        
        // Functions
        function loadNotifications(reset = false) {
            if (reset) {
                $('#notificationList').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading notifications...</p>
                    </div>
                `);
                $('#loadMoreContainer').addClass('d-none');
                $('#emptyNotifications').addClass('d-none');
            }
            
            const unreadOnly = currentFilter === 'unread';
            
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'get_user_notifications',
                    user_id: userId,
                    limit: pageSize * currentPage,
                    unread_only: unreadOnly
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const notifications = response.notifications;
                        
                        // Filter notifications if needed
                        let filteredNotifications = notifications;
                        if (['info', 'success', 'warning', 'error'].includes(currentFilter)) {
                            filteredNotifications = notifications.filter(n => n.type === currentFilter);
                        }
                        
                        if (filteredNotifications.length === 0) {
                            $('#notificationList').empty();
                            $('#emptyNotifications').removeClass('d-none');
                            $('#loadMoreContainer').addClass('d-none');
                            return;
                        }
                        
                        $('#emptyNotifications').addClass('d-none');
                        
                        // Check if we have more notifications to load
                        hasMoreNotifications = filteredNotifications.length >= pageSize * currentPage;
                        
                        if (hasMoreNotifications) {
                            $('#loadMoreContainer').removeClass('d-none');
                        } else {
                            $('#loadMoreContainer').addClass('d-none');
                        }
                        
                        // Render notifications
                        renderNotifications(filteredNotifications);
                    } else {
                        console.error('Error loading notifications:', response.message);
                        
                        // Check if it's a table missing error
                        if (response.message && response.message.includes('table does not exist')) {
                            $('#notificationList').html(`
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <h5>Notification System Setup Required</h5>
                                    <p>The notification table needs to be created in your database.</p>
                                    <p><strong>Quick Fix:</strong></p>
                                    <ol>
                                        <li>Open your Supabase Dashboard</li>
                                        <li>Go to SQL Editor</li>
                                        <li>Run the notification table creation script</li>
                                    </ol>
                                    <a href="test_table_creation.php" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="bi bi-tools me-1"></i>Test & Setup Guide
                                    </a>
                                </div>
                            `);
                        } else {
                            $('#notificationList').html(`
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Error loading notifications: ${response.message}
                                </div>
                            `);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Error connecting to the server.';
                    let helpLink = '';
                    
                    if (xhr.status === 404) {
                        errorMessage = 'Notification controller not found. Please check file paths.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. This is likely due to the notification table not existing.';
                        helpLink = '<a href="test_table_creation.php" class="btn btn-warning btn-sm mt-2" target="_blank"><i class="bi bi-tools me-1"></i>Setup Guide</a>';
                    }
                    
                    $('#notificationList').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <h6>${errorMessage}</h6>
                            <small>Status: ${status} | Error: ${error}</small>
                            ${helpLink}
                        </div>
                    `);
                }
            });
        }
        
        function loadMoreNotifications() {
            if (!hasMoreNotifications) return;
            
            currentPage++;
            loadNotifications();
        }
        
        function renderNotifications(notifications) {
            let html = '';
            
            notifications.forEach(notification => {
                const unreadClass = notification.is_read ? '' : 'unread';
                const typeClass = notification.type || 'info';
                
                let metadataHtml = '';
                if (notification.metadata && Object.keys(notification.metadata).length > 0) {
                    metadataHtml = `
                        <div class="notification-metadata">
                            ${renderMetadata(notification.metadata)}
                        </div>
                    `;
                }
                
                const bookingIdAttr = notification.metadata && notification.metadata.booking_id ? 
                    `data-booking-id="${notification.metadata.booking_id}"` : '';
                
                html += `
                    <div class="notification-item ${unreadClass} ${typeClass}" data-id="${notification.notificationid}" ${bookingIdAttr} onclick="toggleNotification(this)">
                        <button class="notification-close" onclick="deleteNotification(event, ${notification.notificationid})">&times;</button>
                        <div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>${notification.title}</div>
                                ${!notification.is_read ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                            </div>
                            <div class="notification-time">${notification.relative_time}</div>
                        </div>
                        <div class="notification-details">
                            <hr>
                            <div>${notification.message}</div>
                            ${metadataHtml}
                            <div class="text-end mt-3">
                                <button class="btn btn-sm btn-outline-secondary mark-read-btn" onclick="markAsRead(event, ${notification.notificationid})">
                                    <i class="bi bi-check-circle me-1"></i>Mark as Read
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $('#notificationList').html(html);
        }
        
        function renderMetadata(metadata) {
            let html = '';
            
            for (const [key, value] of Object.entries(metadata)) {
                // Format the key for display (capitalize, replace underscores with spaces)
                const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                html += `<div><strong>${formattedKey}:</strong> ${value}</div>`;
            }
            
            return html;
        }
        
        function markAllAsRead() {
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'mark_all_as_read',
                    user_id: userId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Reload notifications
                        loadNotifications(true);
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'All notifications marked as read',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        console.error('Error marking all as read:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }
        
        // Expose functions to global scope
        window.toggleNotification = function(item) {
            // Prevent toggle if clicking the close button or mark as read button
            if (event.target.classList.contains('notification-close') || 
                event.target.closest('.mark-read-btn')) return;
            
            $(item).toggleClass('expanded');
            
            // If notification is unread and being expanded, mark it as read
            if (!$(item).hasClass('unread')) return;
            
            const notificationId = $(item).data('id');
            if ($(item).hasClass('expanded')) {
                // Mark as read when expanded
                $.ajax({
                    url: '../controller/notification_contr.php',
                    type: 'POST',
                    data: {
                        action: 'mark_as_read',
                        notification_id: notificationId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $(item).removeClass('unread');
                            $(item).find('.badge').remove();
                        }
                    }
                });
            }
        };
        
        window.markAsRead = function(event, notificationId) {
            event.stopPropagation();
            
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'mark_as_read',
                    notification_id: notificationId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const item = $(event.target).closest('.notification-item');
                        item.removeClass('unread');
                        item.find('.badge').remove();
                        
                        // Hide the mark as read button
                        $(event.target).closest('.mark-read-btn').fadeOut();
                    }
                }
            });
        };
        
        window.deleteNotification = function(event, notificationId) {
            event.stopPropagation();
            
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
                                // Remove the notification from the UI
                                const item = $(event.target).closest('.notification-item');
                                item.fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // Check if we have any notifications left
                                    if ($('.notification-item').length === 0) {
                                        $('#emptyNotifications').removeClass('d-none');
                                    }
                                });
                            }
                        }
                    });
                }
            });
        };
    });
</script>