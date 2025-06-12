<?php include_once '../include/header.php'; ?>

<div class="content_wrapper">
    <div class="app_sidebar_container d-flex">
        <?php include '../helper/user_apps.php'; ?>

        <!-- <div class="app_content_body">
            <!-- Dynamic content will load here -->
        </div> -->
    </div>
</div>

<!-- Modal container -->
<div id="modalContainer"></div>

<?php include_once '../include/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Load default content
        loadUserContent('user_booking-appointment.php');

        $('.app_sidebar_item').on('click', function (e) {
        e.preventDefault();
        $('.app_sidebar_item.active').removeClass('active');
        $(this).addClass('active');

        // ✅ FIX: Get data-content from the <a> inside this <li>
        const page = $(this).find('a').data('content');
        loadUserContent(page);

        const title = $(this).text().trim();
        $('.app_content_title').text(title);
    });


        // Load content dynamically
        function loadUserContent(page) {
            // 🧹 Cleanup modals and backdrops
            $('#modalContainer').empty();
            $('#userModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');

            $.ajax({
                url: `../views/user/${page}`,
                method: 'GET',
                success: function (response) {
                    $('.app_content_body').html(response);
                },
                error: function () {
                    $('.app_content_body').html('<div class="alert alert-danger">Failed to load content.</div>');
                }
            });
        }

        // Global modal handler (if any modal triggers in user views)
        $(document).on('click', '.btn-view', function (e) {
            e.preventDefault();

            const id = $(this).data('id');

            $('#modalContainer').empty();
            $('#userModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');

            $.get('user/user_modal.php', { id: id }, function (data) {
                $('#modalContainer').html(data);

                const modalEl = document.getElementById('userModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                $(modalEl).on('hidden.bs.modal', function () {
                    $('#userModal').remove();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                });
            }).fail(function () {
                alert('Failed to load modal.');
            });
        });
    });
</script>
