<nav class="app_sidebar_nav app_sidebar_bg_it_asset d-flex flex-column justify-content-between" id="app_sidebar_nav" style="height: 100vh; overflow: hidden;">
    <div>
        <div class="header-box px-4 pt-3 pb-4 d-flex justify-content-between">
            <img src="../vendor/images/spabookwithtitle.png" alt="SpaBook Logo" class="app_sidebar_logo" style="height:50px;">
            <button class="btn app_close_sidebar_btn d-md-none d-block px-1 py-0 text-white">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>
        <ul class="list-unstyled px-2">
            <li class="app_sidebar_item active">
                <a href="#" class="app_sidebar_link" data-content="user_booking-appointment.php"><i class="pe-2 bi bi-calendar4-week"></i>Book appointment</a>
            </li>
            <li class="app_sidebar_item">
                <a href="#" class="app_sidebar_link" data-content="user_progress-tracker.php"><i class="pe-2 fa-solid fa-arrow-up-right-dots"></i>Progress Tracker</a>
            </li>
            <li class="app_sidebar_item">
                <a href="#" class="app_sidebar_link" data-content="user_services.php"><i class="pe-2 fa-solid fa-hand-sparkles"></i>Services</a>
            </li>
            <li class="app_sidebar_item">
                <a href="#" class="app_sidebar_link" data-content="user_history.php"><i class="pe-2 bi bi-clock-history"></i>History</a>
            </li>
            <li class="app_sidebar_item">
                <a href="#" class="app_sidebar_link" data-content="user_notification.php"><i class="pe-2 bi bi-bell"></i>Notification</a>
            </li>
        </ul>
    </div>
    <div class="footer-box px-4 pt-3 pb-4">
        <button class="btn px-3 py-1 text-white w-100 app_sidebar_logout_btn" onclick="window.location.href='../index.php';">
            <i class="bi bi-box-arrow-left"></i> Logout
        </button>
    </div>
</nav>

<!-- 🟩 Content container: nav + content below it -->
<div class="app_content_container flex-grow-1">
    <!-- 🔷 Top navbar -->
    <nav class="navbar navbar-expand px-3 border-bottom" style="background-color: #C0967E;">
        <button class="btn app_open_sidebar_btn" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="app_content_title fs-25 fw-bold pe-2">Book appointment</span>
    </nav>

    <!-- 🔷 Page content will be injected here -->
    <div class="app_content_body p-4" id="app_content_body" style="height: calc(100vh - 100px); overflow: hidden;">
        <!-- AJAX-loaded content will appear here -->
    </div>
</div>

<script src="../vendor/js/jquery.min.js"></script>
<script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar item activation + title update
    $('.app_sidebar_nav ul li').on('click', function() {
        $('.app_sidebar_nav ul li.active').removeClass('active');
        $(this).addClass('active');
        $('.app_content_title').text($(this).text().trim());
    });

    // Sidebar toggle open
    const app_sidebar_toggle = document.querySelector('.app_open_sidebar_btn');
    app_sidebar_toggle.addEventListener('click', function() {
        document.querySelector('.app_sidebar_nav').classList.toggle('active');
    });

    // Sidebar close on small screen
    $('.app_close_sidebar_btn').on('click', function() {
        $('.app_sidebar_nav').removeClass('active');
    });
</script>