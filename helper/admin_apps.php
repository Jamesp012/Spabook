<nav class="app_sidebar_nav app_sidebar_bg_it_asset d-flex flex-column justify-content-between" id="app_sidebar_nav" style="height: 100vh;">
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
        </ul>
    </div>
    <div class="footer-box px-4 pt-3 pb-4">
        <button class="btn px-3 py-1 text-white w-100 app_sidebar_logout_btn" onclick="window.location.href='../index.php';">
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
            <button class="btn position-relative me-2" style="background: none;">
                <i class="fa-regular fa-bell fs-5"></i>
                <!-- Example notification badge -->
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;">
                    3
                </span>
            </button>
            <button class="btn" style="background: none;">
                <i class="pe-2 bi bi-person-square fs-5"></i>Admin
            </button>
        </div>
    </nav>
    <script src="../vendor/js/jquery.min.js"></script>
    <script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function () {
        function loadPage(section) {
            $('.app_content_body').load(`pages/${section}.html`);
        }

        // Load default section (Dashboard)
        loadPage('dashboard');

        $('.app_sidebar_nav ul li').on('click', function () {
            // Update active item
            $('.app_sidebar_nav ul li.active').removeClass('active');
            $(this).addClass('active');

            // Update title
            $('.app_content_title').text($(this).text().trim());

            // Load content dynamically
            const section = $(this).data('content');
            loadPage(section);
        });

        // Sidebar toggling
        document.querySelector('.app_open_sidebar_btn').addEventListener('click', function () {
            document.querySelector('.app_sidebar_nav').classList.toggle('active');
        });

        $('.app_close_sidebar_btn').on('click', function () {
            $('.app_sidebar_nav').removeClass('active');
        });
    });
</script>
