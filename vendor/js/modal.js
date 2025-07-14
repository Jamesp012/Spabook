function showGlobalModal(contentUrl, params = {}) {
    // Remove any existing backdrops and reset body scroll
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');

    // Show loading spinner
    $('#globalModalContent').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('globalModal'));
    modal.show();

    // Load actual modal content
    $.get(contentUrl, params, function (data) {
        $('#globalModalContent').html(data);
    }).fail(function (xhr) {
        console.error("Modal load failed", xhr.status, xhr.statusText);
        $('#globalModalContent').html(`
            <div class="alert alert-danger text-center p-4">Failed to load modal content.</div>
        `);
    });

    $('#globalModal').on('hidden.bs.modal', function () {
        $('#globalModalContent').html(''); // Clear leftover content
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

}
