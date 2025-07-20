<div class="container-fluid h-100 overflow-hidden" style="min-height: calc(100vh - 610px); overflow-y: hidden;">
    <div class="row g-3 mt-2">
        
        <!-- Left Side: Services Grid -->
        <div class="col-lg-8 col-md-7 col-sm-12">
            <div id="services_container" class="d-flex flex-wrap gap-3"></div>
        </div>

        <!-- Right Side: Recent Services Card -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card mt-4 mt-md-0" style="background-color: transparent; border: none;">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <div class="recent-services-scroll">
                    <ul class="list-group list-group-flush" style="overflow-y: auto; max-height: calc(100vh - 230px);">
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

    /* Each card flexes and adapts */
    .service-card {
        flex: 1 1 250px; /* grow, shrink, base width */
        max-width: 100%;
    }

    /* Optional for clean visuals */
    .card-body {
        padding: 1rem;
    }

    @media (max-width: 768px) {
        .service-card {
            flex: 1 1 100%;
        }
    }

</style>