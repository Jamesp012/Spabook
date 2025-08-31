<div class="notification-badge-container">
    <a href="<?php echo isset($isAdmin) && $isAdmin ? 'admin_notification.php' : 'user_notification.php'; ?>" class="notification-badge-link">
        <div class="position-relative d-inline-block">
            <i class="bi bi-bell<?php echo isset($isAdmin) && $isAdmin ? '-fill' : ''; ?> fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                <span id="notificationCount">0</span>
                <span class="visually-hidden">unread notifications</span>
            </span>
        </div>
    </a>
</div>

<script>
    $(document).ready(function() {
        // Get user ID from session storage
        const userId = sessionStorage.getItem('user_id');
        
        if (userId) {
            // Initial load
            updateNotificationCount();
            
            // Set interval to check for new notifications every minute
            setInterval(updateNotificationCount, 60000);
        }
        
        function updateNotificationCount() {
            $.ajax({
                url: '../controller/notification_contr.php',
                type: 'POST',
                data: {
                    action: 'get_unread_count',
                    user_id: userId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const count = response.count;
                        
                        // Update the badge
                        $('#notificationCount').text(count);
                        
                        // Show/hide the badge based on count
                        if (count > 0) {
                            $('.notification-count').removeClass('d-none');
                        } else {
                            $('.notification-count').addClass('d-none');
                        }
                    }
                }
            });
        }
    });
</script>

<style>
    .notification-badge-link {
        color: inherit;
        text-decoration: none;
    }
    
    .notification-badge-container {
        cursor: pointer;
        padding: 8px;
    }
    
    .notification-count {
        font-size: 0.6rem;
        padding: 0.25rem 0.4rem;
    }
</style>