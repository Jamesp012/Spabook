<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-1">
            <div class="col-12 col-sm col-md col-lg mb-2">
                <button type="button" class="btn btn-light col-3 fs-18 rounded-3" id="add_location" onclick="addServices();"><i class="fa-solid fa-square-plus p-r-8"></i>Add Services</button>
            </div>
            <div class="container-fluid h-100" style="max-height: calc(100vh - 310px); overflow-y: auto;">
                <div class="row g-3 mt-2" id="services_container"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        loadServices();
    });
    // Views Script
    function loadServices() {
        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_services'
            },
            success: result => {

                if (result === 'nodata') {
                    $('#services_container').html(`
                        <div class="card text-center border-0 shadow-sm p-4 rounded-4 bg-light">
                            <div class="card-body">
                                <i class="bi bi-info-circle text-secondary mb-2" style="font-size: 2rem;"></i>
                                <h5 class="card-title mb-2">No Services Available</h5>
                                <p class="card-text text-muted">Please check back later or contact support for assistance.</p>
                            </div>  
                        </div>
                    `);
                    return;

                } else {
                    let html = '';
                    result.forEach(service => {
                        html += `
                       <div class="col-md-4 p-2">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                                  <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle shadow-sm"
                                            onclick="editService(${service.id})" title="Edit Service">
                                    <i class="bi bi-pencil-fill text-primary"></i>
                                    </button>
                                     <button class="btn btn-sm btn-light position-absolute shadow" 
                                            style="top: 45px; right: 0.5rem;" 
                                            onclick="deleteService(${service.id})" title="Delete Service">
                                    <i class="bi bi-trash-fill text-danger"></i>
                                    </button>
                                <img src="data:image/png;base64,${service.service_picture}" 
                                    class="card-img-top img-fluid" 
                                    style="height: 200px; object-fit: cover;" 
                                    alt="${service.service_name}">

                                <div class="card-body bg-white">
                                    <h5 class="card-title">${service.service_name}</h5>
                                    <p class="card-text small mb-2">${service.description}</p>
                                    <p class="card-text fw-bold text-primary mb-0">
                                        ₱ ${service.price} / ${service.per_minute} min
                                    </p>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    $('#services_container').html(html);
                }

            },
        });
    }

    function addServices() {
        showGlobalModal('modal/admin_modal-manage-services.php');
    }

    function editService(id) {
        showGlobalModal('modal/admin_modal-manage-services.php?serviceid=' + id);
    }

    function deleteService(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This service will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../controller/booking_services_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete_service',
                        serviceid: id
                    },
                    success: function(response) {
                        if (response === 'success') {
                            Swal.fire('Deleted!', 'The service has been deleted.', 'success');
                            loadServices();
                        } else {
                            Swal.fire('Error!', 'Failed to delete the service.', 'error');
                        }
                    }
                });
            }
        });
    }
</script>