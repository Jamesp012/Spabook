<?php include_once '../include/header.php'; ?>

<div class="content_wrapper">
    <div class="app_sidebar_container d-flex">
        <?php include '../helper/admin_apps.php'; ?>

        <div class="app_content_body">

        </div>
    </div>
</div>

<div id="modalContainer"></div>
<?php include_once '../include/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Load initial content (Dashboard)
        loadContent('admin_dashboard.php');

        // Sidebar click event
        $('.app_sidebar_item').on('click', function (e) {
            e.preventDefault();
            $('.app_sidebar_item.active').removeClass('active');
            $(this).addClass('active');

            const page = $(this).data('content');
            loadContent(page);

            // Optional: Change title based on text
            const title = $(this).text().trim();
            $('.app_content_title').text(title);
        });

        function loadContent(page) {
            // 🧹 Remove previous modal/backdrop before loading new page
            $('#modalContainer').empty();
            $('#adminModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');

            $.ajax({
                url: `../views/admin/${page}`,
                method: 'GET',
                success: function (response) {
                    $('.app_content_body').html(response);
                },
                error: function () {
                    $('.app_content_body').html('<div class="alert alert-danger">Failed to load content.</div>');
                }
            });
        }

        // Global modal handler to avoid stacking
        $(document).on('click', '.btn-view', function (e) {
            e.preventDefault();

            const bookingId = $(this).data('id');

            // 🧹 Full cleanup
            $('#modalContainer').empty();
            $('#adminModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');

            // Load the modal
            $.get('admin/admin_modal.php', { id: bookingId }, function (data) {
                $('#modalContainer').html(data);

                const modalEl = document.getElementById('adminModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                // 🧹 Cleanup after modal is closed
                $(modalEl).on('hidden.bs.modal', function () {
                    $('#adminModal').remove();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                });
            }).fail(function () {
                alert('Failed to load modal.');
            });
        });

        // Dashboard-specific behavior
        window.loadDashboardData = function (type) {
            $('#active_dashboard').text(type);

            switch (type) {
                case 'Bookings':
                    $('#active_dashboard').text('Bookings');
                    break;
                case 'History':
                    $('#active_dashboard').text('Appointment History');
                    break;
                case 'Recovery':
                    $('#active_dashboard').text('Update Recovery');
                    break;
                default:
                    console.error("Unknown type: " + type);
            }

            $('.custom_card_hover').removeClass('active');
            if (type === 'Bookings') {
                $('.custom_card_hover').eq(0).addClass('active');
            } else if (type === 'History') {
                $('.custom_card_hover').eq(1).addClass('active');
            } else if (type === 'Recovery') {
                $('.custom_card_hover').eq(2).addClass('active');
            }
        };
    });
</script>
