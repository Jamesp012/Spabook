<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.service-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.service-card:hover {
    transform: translateY(-2px);
}

.service-card.selected {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
}
</style>

<div class="container-fluid h-100 overflow-hidden overflow-auto">
    <div class="row g-3 mt-2">


        <!-- Left Side: Services Grid -->
        <div class="col-lg-8 col-md-7 col-sm-12">
            <div id="services_container" class="d-flex flex-wrap gap-3 overflow-auto" style="max-height: calc(100vh - 160px);"></div>
        </div>

        <!-- Right Side: Status and Recent Services -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <button class="btn btn-primary w-100 mb-3">Check-out</button>
            <div class="card mt-4 mt-md-0" style="background-color: transparent; border: none;">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <div class="recent-services-scroll">
                    <ul class="list-group list-group-flush" style="overflow-y: auto; max-height: calc(100vh - 250px);">
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li> 
                        <li class="list-group-item">Shiatsu - June 2, 2025</li> 
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li> 
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    $(document).ready(function () {
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
                        <div class="card text-center border-0 shadow-sm p-4 rounded-4 bg-light w-100">
                            <div class="card-body">
                                <i class="bi bi-info-circle text-secondary mb-2" style="font-size: 2rem;"></i>
                                <h5 class="card-title mb-2">No Services Available</h5>
                                <p class="card-text text-muted">Please check back later or contact support for assistance.</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                let html = '';
                result.forEach(service => {
                    html += `
                        <div class="service-card">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                                <img src="data:image/png;base64,${service.service_picture}"
                                    class="card-img-top img-fluid"
                                    style="height: 120px; object-fit: cover;"
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
        });
    }
</script>

<style>
    .responsive-col {
        flex: 0 0 100%;
    }

    .service-img-wrapper {
        width: 100%;
        height: 150px;
        overflow: hidden;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .service-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (min-width: 576px) {
        .responsive-col {
            flex: 0 0 50%;
        }

        /* .service-img-wrapper {
        height: 100%;
    } */
    }


    @media (min-width: 768px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }

        /* .service-img-wrapper {
        height: 100%
    } */
    }


    @media (min-width: 992px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }
    }

    @media (min-width: 1200px) {
        .responsive-col {
            flex: 0 0 33.3333%;
        }
    }
    #services_container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .service-card {
        flex: 1 1 250px; /* grow, shrink, base width */
        max-width: 100%;
        min-width: 200px;
    }

    .card:hover {
        border-radius: 1.5rem;
        box-shadow: 0 0 20px rgba(0, 0, 0, 1) !important;
        transition: box-shadow 0.2s ease-in-out;
    }

    .card-img-top {
        height: 120px;
        object-fit: cover;
    }

    .card-body {
        padding: 1rem;
    }

    .booking-status-scroll ul,
    .recent-services-scroll ul {
        overflow-y: auto;
        max-height: 40vh;
        padding-right: 8px;
        scrollbar-width: thin;
        scrollbar-color: #ccc transparent;
    }

    .booking-status-scroll ul::-webkit-scrollbar,
    .recent-services-scroll ul::-webkit-scrollbar {
        width: 6px;
    }

    .booking-status-scroll ul::-webkit-scrollbar-thumb,
    .recent-services-scroll ul::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 3px;
    }

    @media (max-width: 991.98px) {
        .col-lg-8,
        .col-lg-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .service-card {
            flex: 1 1 100%;
        }
    }

</style>
