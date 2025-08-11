    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-12 px-md-4">
                <!-- Services Grid -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-grid me-1"></i>Services Collection
                        </h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm" onclick="addServices()">
                                <i class="bi bi-plus-circle me-1"></i>Add New Service
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadServices()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div id="loadingServices" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted">Loading services...</div>
                        </div>
                        
                        <!-- Services Container -->
                        <div class="row g-4" id="services_container" style="display: none;">
                            <!-- Services will be loaded here -->
                        </div>
                        
                        <!-- No Data State -->
                        <div id="noServices" class="text-center py-5" style="display: none;">
                            <i class="bi bi-leaf fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Services Found</h5>
                            <p class="text-muted">Start by adding your first service to the collection.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    let servicesData = [];
    
    $(document).ready(function() {
        loadServices();
    });

    function loadServices() {
        // Show loading state
        $('#loadingServices').show();
        $('#services_container').hide();
        $('#noServices').hide();
        
        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_services'
            },
            success: function(result) {
                $('#loadingServices').hide();
                
                if (result === 'nodata' || !result || result.length === 0) {
                    $('#noServices').show();
                    servicesData = [];
                } else {
                    servicesData = result;
                    renderServices(result);
                    $('#services_container').show();
                }
            },
            error: function() {
                $('#loadingServices').hide();
                $('#noServices').show();
                Swal.fire('Error!', 'Failed to load services.', 'error');
            }
        });
    }
    
    function renderServices(services) {
        let html = '';
        services.forEach(service => {
            // Determine the correct image source format
            let imageSrc;
            if (service.service_picture.startsWith('http')) {
                imageSrc = service.service_picture;
            } else if (service.service_picture.startsWith('data:image')) {
                imageSrc = service.service_picture;
            } else {
                imageSrc = `data:image/png;base64,${service.service_picture}`;
            }

            html += `
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4" data-service-id="${service.id}">
                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative service-card">
                        <div class="position-relative">
                            <img src="${imageSrc}" 
                                class="card-img-top" 
                                style="height: 220px; object-fit: cover;" 
                                alt="${service.service_name}"
                                onerror="this.src='../vendor/images/headMassage.png'">
                            <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-2">
                                <span class="badge bg-primary">${service.per_minute} minutes</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title mb-2 text-dark">${service.service_name}</h5>
                            <p class="card-text text-muted small mb-3" style="line-height: 1.4;">
                                ${service.description.length > 80 ? service.description.substring(0, 80) + '...' : service.description}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h5 text-primary fw-bold mb-0">₱${parseFloat(service.price).toLocaleString()}</span>
                                    <small class="text-muted d-block">per session</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Duration</small>
                                    <div class="fw-semibold">${service.per_minute} min</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#services_container').html(html);
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

<style>
    .service-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid rgba(0,0,0,0.08) !important;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    
    .bg-gradient-dark {
        background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.4), transparent);
    }
    
    .card-img-top {
        transition: transform 0.3s ease;
    }
    
    .service-card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    /* Custom scrollbar for services container */
    .card-body {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
    }
    
    .card-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .card-body::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .card-body::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 3px;
    }
    
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    
    /* Loading animation */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .spinner-border {
        animation: pulse 2s infinite;
    }
    
    /* Badge styling */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .service-card:hover {
            transform: none;
        }
        
        .card-img-top {
            height: 180px !important;
        }
    }
</style>