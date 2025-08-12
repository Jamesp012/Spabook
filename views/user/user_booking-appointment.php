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
            
            <!-- Booking Status Section -->
            <div class="card mb-3" style="background-color: transparent; border: none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Booking Status</h5>
                </div>
                <div class="booking-status-scroll">
                    <ul class="list-group list-group-flush" id="bookingStatusList" style="overflow-y: auto; max-height: calc(100vh - 400px);">
                        <!-- Booking status will be loaded here -->
                    </ul>
                </div>
            </div>

            <!-- Recent Services Section -->
            <div class="card" style="background-color: transparent; border: none;">
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
                        
                    <ul class="list-group list-group-flush" id="recentServicesList" style="overflow-y: auto; max-height: calc(100vh - 400px);">
                        <!-- Recent services will be loaded here -->
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    $(document).ready(function () {
        loadServices();
        loadBookingStatus();
        loadRecentServices();
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
                    // Determine the correct image source format
                    let imageSrc;
                    if (service.service_picture.startsWith('http')) {
                        // It's a URL, use it directly
                        imageSrc = service.service_picture;
                    } else if (service.service_picture.startsWith('data:image')) {
                        // It's already a data URL, use it directly
                        imageSrc = service.service_picture;
                    } else {
                        // It's base64 data without prefix, add the prefix
                        imageSrc = `data:image/png;base64,${service.service_picture}`;
                    }

                    html += `
                        <div class="service-card" data-service-id="${service.service_id}" data-service-name="${service.service_name}" data-service-price="${service.price}" data-service-description="${service.description}" data-service-image="${imageSrc}">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                                <img src="data:image/png;base64,${service.service_picture}"
                                <img src="${imageSrc}"
                                    class="card-img-top img-fluid"
                                    style="height: 120px; object-fit: cover;"
                                    alt="${service.service_name}"
                                    onerror="this.src='../vendor/images/headMassage.png'">

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

    // Function to load booking status
    function loadBookingStatus() {
        const userId = sessionStorage.getItem('user_id');
        if (!userId) return;

        $.ajax({
            url: '../controller/booking_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_user_booking_status',
                user_id: userId
            },
            success: function(result) {
                let statusHtml = '';
                
                if (result === 'nodata' || !result || result.length === 0) {
                    statusHtml = `
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-calendar-x mb-2" style="font-size: 2rem;"></i>
                            <div>No active bookings</div>
                        </li>
                    `;
                } else {
                    result.forEach(booking => {
                        const statusClass = getStatusClass(booking.status);
                        const statusIcon = getStatusIcon(booking.status);
                        
                        statusHtml += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${booking.service_name || 'Service'}</h6>
                                    <small class="text-muted">${booking.booking_date || ''}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge ${statusClass} mb-1">
                                        <i class="bi ${statusIcon} me-1"></i>${booking.status}
                                    </span>
                                    <div class="text-muted small">₱${booking.total_amount || 0}</div>
                                </div>
                            </li>
                        `;
                    });
                }
                
                $('#bookingStatusList').html(statusHtml);
            },
            error: function() {
                $('#bookingStatusList').html(`
                    <li class="list-group-item text-center text-danger py-4">
                        <i class="bi bi-exclamation-circle mb-2" style="font-size: 2rem;"></i>
                        <div>Failed to load booking status</div>
                    </li>
                `);
            }
        });
    }

    // Function to load recent services
    function loadRecentServices() {
        const userId = sessionStorage.getItem('user_id');
        if (!userId) return;

        $.ajax({
            url: '../controller/booking_contr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_user_recent_services',
                user_id: userId
            },
            success: function(result) {
                let servicesHtml = '';
                
                if (result === 'nodata' || !result || result.length === 0) {
                    servicesHtml = `
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-clock-history mb-2" style="font-size: 2rem;"></i>
                            <div>No recent services</div>
                        </li>
                    `;
                } else {
                    result.forEach(service => {
                        const serviceDate = new Date(service.booking_date).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        
                        servicesHtml += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">${service.service_name}</div>
                                    <small class="text-muted">${serviceDate}</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold">₱${service.total_amount}</div>
                                    <small class="text-success">${service.status}</small>
                                </div>
                            </li>
                        `;
                    });
                }
                
                $('#recentServicesList').html(servicesHtml);
            },
            error: function() {
                $('#recentServicesList').html(`
                    <li class="list-group-item text-center text-danger py-4">
                        <i class="bi bi-exclamation-circle mb-2" style="font-size: 2rem;"></i>
                        <div>Failed to load recent services</div>
                    </li>
                `);
            }
        });
    }

    // Helper functions for status styling
    function getStatusClass(status) {
        switch(status?.toLowerCase()) {
            case 'pending': return 'bg-warning text-dark';
            case 'confirmed': 
            case 'accepted': return 'bg-success';
            case 'completed': return 'bg-primary';
            case 'cancelled': 
            case 'rejected': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function getStatusIcon(status) {
        switch(status?.toLowerCase()) {
            case 'pending': return 'bi-clock';
            case 'confirmed': 
            case 'accepted': return 'bi-check-circle';
            case 'completed': return 'bi-check2-all';
            case 'cancelled': 
            case 'rejected': return 'bi-x-circle';
            default: return 'bi-circle';
        }
    }

    // Initialize service cart (make it globally accessible)
    let serviceCart = [];
    window.serviceCart = window.serviceCart || []; // Preserve existing cart or create new
    serviceCart = window.serviceCart; // Sync local reference

    // Event delegation for service card clicks
    $(document).on('click', '.service-card', function() {
        const serviceId = $(this).data('service-id');
        const serviceName = $(this).data('service-name');
        const servicePrice = $(this).data('service-price');
        const serviceDescription = $(this).data('service-description');
        const serviceImage = $(this).data('service-image');
        
        // Store the selected element reference for visual feedback
        window.selectedServiceElement = $(this);
        
        // Open the booking modal with service details
        showGlobalModal('../views/modal/user_modal-booking.php', {
            id: serviceId,
            name: serviceName,
            price: servicePrice,
            description: serviceDescription,
            image: serviceImage
        });
    });

    // Function to update checkout badge (make it global)
    window.updateCheckoutBadge = function() {
        // Always use the global cart reference
        const cart = window.serviceCart || [];
        serviceCart = [...cart]; // Sync local reference with a fresh copy
        
        // Find checkout button with multiple selectors to ensure we get it
        let checkoutBtn = $('.btn-primary:contains("Check-out")');
        if (checkoutBtn.length === 0) {
            checkoutBtn = $('.btn-success:contains("Check-out")');
        }
        if (checkoutBtn.length === 0) {
            checkoutBtn = $('.btn:contains("Check-out")').first();
        }
        
        const totalItems = cart.length;
        const totalPrice = cart.reduce((sum, service) => sum + (service.price * service.people), 0);
        
        if (totalItems > 0) {
            checkoutBtn.html(`Check-out (${totalItems}) - ₱${totalPrice}`);
            checkoutBtn.removeClass('btn-primary').addClass('btn-success');
        } else {
            checkoutBtn.html('Check-out');
            checkoutBtn.removeClass('btn-success').addClass('btn-primary');
        }
        
        console.log('Cart updated - Items:', totalItems, 'Total:', totalPrice); // Debug log
    };

    // Checkout button click handler
    $(document).on('click', '.btn:contains("Check-out")', function(e) {
        e.preventDefault();
        const currentCart = window.serviceCart || [];
        
        if (currentCart.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Services Selected',
                text: 'Please select at least one service to proceed with checkout.',
            });
            return;
        }
        
        // Show checkout modal or proceed to checkout
        showGlobalModal('../views/modal/user_modal-checkout.php', {
            cart: currentCart
        });
    });

    // Initialize cart on page load
    window.updateCheckoutBadge();

    // Function to refresh all data sections
    function refreshAllData() {
        loadBookingStatus();
        loadRecentServices();
        window.updateCheckoutBadge();
    }

    // Make refresh function globally accessible
    window.refreshBookingData = refreshAllData;

    // Override the global modal ready function for checkout modal
    window.originalOnGlobalModalReady = window.onGlobalModalReady;
    window.onGlobalModalReady = function() {
        // Check if this is the new checkout modal with scheduling
        if ($('#servicesSchedulingContainer').length > 0) {
            // The new checkout modal will initialize itself
            console.log('New checkout modal detected - letting it self-initialize');
        }
        // Check if this is the old checkout modal (fallback)
        else if ($('#checkoutServiceList').length > 0) {
            populateOldCheckoutModal();
        } 
        else if (window.originalOnGlobalModalReady) {
            // Call original function for other modals
            window.originalOnGlobalModalReady();
        }
    };

    // Legacy checkout modal population (fallback)
    function populateOldCheckoutModal() {
        const cart = window.serviceCart || [];
        
        let serviceListHtml = '';
        let total = 0;
        
        cart.forEach((service, index) => {
            const serviceTotal = service.price * service.people;
            total += serviceTotal;
            
            serviceListHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${service.name}</h6>
                        <small class="text-muted">${service.people} person(s) × ₱${service.price}</small>
                    </div>
                    <div class="d-flex align-items-center ms-2">
                        <span class="badge bg-primary me-2">₱${serviceTotal}</span>
                        <button class="btn btn-sm btn-outline-danger remove-service-btn" data-index="${index}" title="Remove service">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </li>
            `;
        });
        
        $('#checkoutServiceList').html(serviceListHtml);
        $('#checkoutTotal').text(`₱${total}`);
        
        // Handle remove service buttons
        $('.remove-service-btn').off('click').on('click', function() {
            const index = $(this).data('index');
            window.serviceCart.splice(index, 1);
            window.updateCheckoutBadge();
            
            if (window.serviceCart.length === 0) {
                $('#globalModal').modal('hide');
                Swal.fire({
                    icon: 'info',
                    title: 'Cart Empty',
                    text: 'All services have been removed from your cart.',
                });
            } else {
                populateOldCheckoutModal();
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

    .booking-status-scroll ul,
    .recent-services-scroll ul {
        overflow-y: auto;
        max-height: 25vh;
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

    /* Empty state styling */
    .list-group-item.text-center.text-muted {
        border: 1px dashed #dee2e6;
        background-color: #f8f9fa;
    }

    .list-group-item.text-center.text-danger {
        border: 1px dashed #dc3545;
        background-color: #f8d7da;
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


        /* Adjust section heights on mobile */
        .booking-status-scroll ul,
        .recent-services-scroll ul {
            max-height: 20vh;
        }
    }

    /* Status badge animations */
    .badge {
        transition: all 0.2s ease-in-out;
    }

    .badge:hover {
        transform: scale(1.05);
    }

    /* List item hover effects */
    .list-group-item {
        transition: background-color 0.2s ease-in-out;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    /* Service card hover effects */
    .service-card {
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    .service-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .service-card.selected {
        border: 2px solid #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }

    /* Checkout button enhancement */
    .btn:contains("Check-out") {
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }

    .btn-success:contains("Check-out") {
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3); }
        50% { box-shadow: 0 4px 8px rgba(40, 167, 69, 0.5); }
        100% { box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3); }
    }

</style>
