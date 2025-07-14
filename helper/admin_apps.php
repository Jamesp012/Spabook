<nav class="app_sidebar_nav app_sidebar_bg_it_asset d-flex flex-column justify-content-between sidebar-hidden" id="app_sidebar_nav" style="height: 100vh;">
    <div>
        <div class="header-box px-4 pt-3 pb-4 d-flex justify-content-between">
            <img src="../vendor/images/spabookwithtitle.png" alt="SpaBook Logo" class="app_sidebar_logo" style="height:50px;">
            <button class="btn app_close_sidebar_btn d-md-none d-block px-1 py-0 text-white">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>
        <div class="app_sidebar_link text-white fw-bold px-4 py-2 d-flex align-items-center" style="cursor: default;">
            <i class="pe-2 bi bi-person-gear fs-5 " style="font-size: 1.25rem;"></i>
            Admin Panel
        </div>

        <ul class="list-unstyled px-2">
            <li class="app_sidebar_item  active" data-content="admin_dashboard.php">
                <a href="#" class="app_sidebar_link"><i class="pe-2 bi bi-columns-gap"></i>Dashboard</a>
            </li>
            <li class="app_sidebar_item" data-content="admin_booking-request.php">
                <a href="#" class="app_sidebar_link"><i class="pe-2 fa-solid fa-file-pen"></i>Booking Request</a>
            </li>
            <li class="app_sidebar_item" data-content="admin_booking-accepted.php">
                <a href="#" class="app_sidebar_link"><i class="pe-2 bi bi-check2-square"></i>Booking Accepted</a>
            </li>
            <li class="app_sidebar_item" data-content="admin_manage-users.php">
                <a href="#" class="app_sidebar_link"><i class="pe-2 bi bi-people"></i>Manage Users</a>
            </li>
        </ul>
    </div>
    <div class="footer-box px-4 pt-3 pb-4">
        <button class="btn px-3 py-1 text-white w-100 app_sidebar_logout_btn" id="logout-btn">
            <i class=" bi bi-box-arrow-left"></i> Logout
        </button>
    </div>
</nav>
<div class="app_content_container">
    <nav class="navbar navbar-expand px-3 border-bottom" style="background-color: #C0967E;">
        <button class="btn app_open_sidebar_btn" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="app_content_title fs-25 fw-bold pe-2">Dashboard</span>
        <div class="ms-auto d-flex align-items-center">
            <!-- Notification Bell with Dropdown -->
            <div class="dropdown" id="notificationDropdownWrapper">
                <button class="btn position-relative me-2" style="background: none;" id="notificationBell">
                    <i class="fa-regular fa-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="font-size:10px;">
                        3
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="min-width: 340px; max-width: 400px; max-height: 400px; overflow-y: auto;" id="notificationDropdown">
                    <div class="d-flex justify-content-between align-items-center px-3 pt-2 pb-1 border-bottom">
                        <span class="fw-bold">Notifications</span>
                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" id="clearReadBtn" style="font-size: 0.85rem;">Clear Read</button>
                    </div>
                    <ul class="list-group list-group-flush" id="notificationList" style="cursor:pointer;">
                        <!-- Notifications will be injected here -->
                    </ul>
                </div>
            </div>
            <button class="btn" style="background: none;">
                <i class="pe-2 bi bi-person-square fs-5"></i>Admin
            </button>
        </div>
    </nav>
    <!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
 <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <ul class="list-group list-group-flush" id="notificationListModal">
          <!-- Notifications will be injected here -->
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger btn-sm" id="clearReadBtnModal">Clear Read Messages</button>
      </div>
    </div>
  </div>
</div>   
    <script src="../vendor/js/jquery.min.js"></script>
    <script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadPage(section) {
                $('.app_content_body').load(`admin/${section}.php`);
            }

            // Load default section (Dashboard)
            loadPage('dashboard');



            $('.app_sidebar_nav ul li').on('click', function() {
                // Hide the sidebar on mobile
                $('.app_sidebar_nav ul li.active').removeClass('active');

                // Update active item
                $('.app_sidebar_nav ul li.active').removeClass('active');
                $(this).addClass('active');

                // Update title
                $('.app_content_title').text($(this).text().trim());

                // Auto-hide sidebar on mobile when navigating
                if (window.innerWidth < 768) {
                    $('.app_sidebar_nav').removeClass('active');
                }
            });

            // Sidebar toggling
            document.querySelector('.app_open_sidebar_btn').addEventListener('click', function() {
                document.querySelector('.app_sidebar_nav').classList.toggle('active');
            });

            $('.app_close_sidebar_btn').on('click', function() {
                $('.app_sidebar_nav').removeClass('active');
            });


        });
    </script>
    <script type="module">
        $('#logout-btn').on('click', async () => {
            await supabase.auth.signOut();
            window.location.href = '../index.php';
        });
    </script>
    <script>
        $(document).ready(function() {
            // Sample notifications
            let notifications = [{
                    id: 1,
                    message: "New booking request from Jane Doe.",
                    read: false
                },
                {
                    id: 2,
                    message: "Payment received from Juan Dela Cruz.",
                    read: false
                },
                {
                    id: 3,
                    message: "Booking #1234 has been accepted.",
                    read: true
                },
                {
                    id: 4,
                    message: "User Maria Santos updated her profile.",
                    read: false
                }
            ];

            function renderNotifications() {
                const $list = $('#notificationList');
                $list.empty();
                let unreadCount = 0;
                notifications.forEach(n => {
                    if (!n.read) unreadCount++;
                    $list.append(`
                <li class="list-group-item d-flex align-items-center ${n.read ? '' : 'bg-light'} border-0 border-bottom" data-id="${n.id}">
                    <span class="me-2">
                        ${n.read 
                            ? '<i class="bi bi-dot text-secondary"></i>' 
                            : '<i class="bi bi-dot text-primary"></i>'}
                    </span>
                    <span class="flex-grow-1 ${n.read ? 'text-muted' : 'fw-bold'}">${n.message}</span>
                    ${n.read ? '<span class="badge bg-secondary ms-2">Read</span>' : '<span class="badge bg-primary ms-2">Unread</span>'}
                </li>
            `);
                });
                // Update badge
                if (unreadCount > 0) {
                    $('#notificationBadge').text(unreadCount).show();
                } else {
                    $('#notificationBadge').hide();
                }
            }

            // Show dropdown and render notifications when bell is clicked
            $('#notificationBell').on('click', function() {
                setTimeout(renderNotifications, 100); // slight delay to ensure dropdown is open
            });

            // Mark as read on click
            $('#notificationList').on('click', 'li', function(e) {
                const id = $(this).data('id');
                notifications = notifications.map(n => n.id === id ? {
                    ...n,
                    read: true
                } : n);
                renderNotifications();
                e.stopPropagation(); // Prevent dropdown from closing
            });

            // Clear read messages
            $('#clearReadBtn').on('click', function(e) {
                notifications = notifications.filter(n => !n.read);
                renderNotifications();
                e.stopPropagation(); // Prevent dropdown from closing
            });

            // Initial badge update
            renderNotifications();
        });
    </script>
    <script>
        $(document).ready(function() {
            // Sample notifications
            let notifications = [{
                    id: 1,
                    message: "New booking request from Jane Doe.",
                    read: false
                },
                {
                    id: 2,
                    message: "Payment received from Juan Dela Cruz.",
                    read: false
                },
                {
                    id: 3,
                    message: "Booking #1234 has been accepted.",
                    read: true
                },
                {
                    id: 4,
                    message: "User Maria Santos updated her profile.",
                    read: false
                }
            ];

            function renderNotificationsModal() {
                const $list = $('#notificationListModal');
                $list.empty();
                let unreadCount = 0;
                notifications.forEach(n => {
                    if (!n.read) unreadCount++;
                    $list.append(`
                <li class="list-group-item d-flex align-items-center ${n.read ? '' : 'bg-light'} border-0 border-bottom" data-id="${n.id}">
                    <span class="me-2">
                        ${n.read 
                            ? '<i class="bi bi-dot text-secondary"></i>' 
                            : '<i class="bi bi-dot text-primary"></i>'}
                    </span>
                    <span class="flex-grow-1 ${n.read ? 'text-muted' : 'fw-bold'}">${n.message}</span>
                    ${n.read ? '<span class="badge bg-secondary ms-2">Read</span>' : '<span class="badge bg-primary ms-2">Unread</span>'}
                </li>
            `);
                });
                // Update badge
                if (unreadCount > 0) {
                    $('#notificationBadge').text(unreadCount).show();
                } else {
                    $('#notificationBadge').hide();
                }
            }

            // Show modal and render notifications when bell is clicked
            $('#notificationBell').on('click', function() {
                renderNotificationsModal();
                var modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                modal.show();
            });

            // Mark as read on click
            $('#notificationListModal').on('click', 'li', function(e) {
                const id = $(this).data('id');
                notifications = notifications.map(n => n.id === id ? {
                    ...n,
                    read: true
                } : n);
                renderNotificationsModal();
            });

            // Clear read messages
            $('#clearReadBtnModal').on('click', function(e) {
                notifications = notifications.filter(n => !n.read);
                renderNotificationsModal();
            });

            // Initial badge update
            renderNotificationsModal();
        });
    </script>