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
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    // Prevent duplicate global variables
    if (typeof serviceCart === 'undefined') {
        var serviceCart = [];
    }
    if (typeof selectedServiceElement === 'undefined') {
        var selectedServiceElement = null;
    }
    if (typeof servicesCache === 'undefined') {
        var servicesCache = null;
        var servicesCacheTime = null;
    }
    if (typeof bookingAppointmentInitialized === 'undefined') {
        var bookingAppointmentInitialized = false;
    }

    function calculateTotalPrice(cart) {
        return cart.reduce((sum, item) => sum + (item.price * item.people), 0);
    }

    function renderServices(result) {
        // Check if container exists
        if ($('#services_container').length === 0) {
            console.log('Services container not found, skipping render');
            return;
        }
        
        if (result === 'nodata') {
            $('#services_container').html(`
                <div class="card service-card text-center border-0 shadow-sm p-4 rounded-4 bg-light w-100">
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
                <div class="service-card clickable-service" data-service='${JSON.stringify(service)}'>
                    <div class="card h-100 shadow-sm border border-2 border-transparent rounded-4 overflow-hidden position-relative"> 
                        <div class="card-img-top position-relative" style="height: 200px; background: #f8f9fa;">
                            <img src="data:image/png;base64,${service.service_picture}" 
                                 class="img-fluid w-100 h-100" 
                                 style="object-fit: cover; opacity: 0; transition: opacity 0.3s;"
                                 alt="${service.service_name}"
                                 loading="lazy"
                                 onload="this.style.opacity=1; this.nextElementSibling.style.display='none';">
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-white">
                            <h5 class="card-title">${service.service_name}</h5>
                            <p class="card-text small mb-2">${service.description}</p>
                            <p class="card-text fw-bold text-primary mb-0">
                                ₱ ${service.price} / ${service.per_minute} min
                            </p>
                        </div>
                    </div>
                </div>`;
        });
        $('#services_container').html(html);
    }

    function loadServices(forceRefresh = false) {
        // Check if container exists
        if ($('#services_container').length === 0) {
            console.log('Services container not found, skipping load');
            return;
        }
        
        // Check cache first (cache for 5 minutes)
        const cacheExpiry = 5 * 60 * 1000; // 5 minutes in milliseconds
        const now = new Date().getTime();
        
        if (!forceRefresh && servicesCache && servicesCacheTime && (now - servicesCacheTime < cacheExpiry)) {
            console.log('Loading services from cache');
            renderServices(servicesCache);
            return;
        }

        // Show loading state immediately
        $('#services_container').html(`
            <div class="d-flex justify-content-center align-items-center w-100" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Loading services...</p>
                </div>
            </div>
        `);

        $.ajax({
            url: '../controller/booking_services_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'fetch_services' },
            success: result => {
                // Cache the result
                servicesCache = result;
                servicesCacheTime = new Date().getTime();
                console.log('Services loaded from server and cached');
                
                renderServices(result);
            },
            error: (xhr, status, error) => {
                console.error('Failed to fetch services:', error);
                $('#services_container').html('<p class="text-danger">Failed to load services. Try again later.</p>');
            }
        });
    }

    // Declare USER_ID once at the top level - check if it already exists
    if (typeof USER_ID === 'undefined') {
        var USER_ID = window.user_id || sessionStorage.getItem('user_id');
    }

    $(document).ready(function () {
        if (!USER_ID) {
            console.error('USER_ID is not available.');
            return;
        }

        // Always try to load services (will use cache if available)
        loadServices();
        
        // Always load booking status to show current data
        loadBookingStatus(USER_ID);
        
        // Mark as initialized to prevent duplicate event handlers
        bookingAppointmentInitialized = true;
    });

    // Event delegation
    $(document).off('click', '.clickable-service').on('click', '.clickable-service', function () {
        const service = $(this).data('service');
        selectedServiceElement = $(this); // Safe to assign

        console.log("Sending to modal:", service);

        showGlobalModal('./modal/user_modal-booking.php', {
            id: service.service_id,
            name: service.service_name,
            description: service.description,
            price: service.price,
            per_minute: service.per_minute,
            bundles: service.bundles,
            extraNotes: service.notes || ""
        }, function () {
            $('#selected-service-name').text(service.service_name);
            $('#selected-service-price')
                .text(`₱${service.price} / ${service.per_minute} min`)
                .data('price', service.price);
        });

    });

    function updateCheckoutBadge() {
        const badge = $('#cartBadge');
        const count = serviceCart.length;
        if (count > 0) {
            badge.text(count).removeClass('d-none');
        } else {
            badge.addClass('d-none');
        }
    }

    // USER MODAL CHECKOUT SCRIPT

    $('#checkOutBtn').on('click', function () {
        showGlobalModal('./modal/user_modal-checkout.php', {}, function () {
            // ← Inside this callback, everything is safe to bind
            console.log('✅ Checkout modal loaded');
            
            // Load cart items...
            const container = $('#checkoutServiceList');
            const totalContainer = $('#checkoutTotal');
            container.empty();
            let total = 0;

            serviceCart.forEach((service, index) => {
                const subtotal = service.price * service.people;
                total += subtotal;

                const item = `
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <strong>${service.name}</strong> <span class="text-muted">× ${service.people}</span><br>
                            <small class="text-muted">₱${service.price} each</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱${subtotal}</div>
                            <button class="btn btn-sm btn-outline-danger mt-2 remove-service-btn" data-index="${index}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </li>
                `;
                container.append(item);
            });

            totalContainer.text(`₱${total}`);

            // ✅ Proceed button bind — THIS is what was likely missing
            $('#proceedToPaymentBtn').off('click').on('click', function () {
                $('#globalModal').modal('hide'); // optional smoother transition

                showGlobalModal('./modal/user_modal-payment.php', {}, function () {
                    const fileInput = $('#receiptUpload');
                    const previewContainer = $('#previewContainer');
                    const previewImage = $('#receiptPreview');

                    fileInput.on('change', function (e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                previewImage.attr('src', e.target.result);
                                previewContainer.removeClass('d-none');
                            };
                            reader.readAsDataURL(file);
                        } else {
                            previewContainer.addClass('d-none');
                        }
                    });

                    $('#paymentForm').on('submit', function (e) {
                        e.preventDefault();

                        const file = $('#receiptUpload')[0].files[0];
                        const reader = new FileReader();

                        reader.onloadend = function () {
                            const base64Image = reader.result.split(',')[1]; // Strip data:image/...;base64,

                            const bookingPayload = {
                                action: 'create_booking',
                                user_id: USER_ID, // ← Inject this from your PHP session or JS variable
                                total_price: calculateTotalPrice(serviceCart),
                                payment_img: base64Image,
                                services: JSON.stringify(serviceCart)
                            };

                            $.ajax({
                                url: '../controller/booking_contr.php',
                                type: 'POST',
                                data: bookingPayload,
                                success: function (res) {
                                    console.log("Booking response:", res); // Debugging line
                                    const response = typeof res === 'string' ? JSON.parse(res) : res;
                                    if (response.status === 'success') {
                                        alert('✅ Booking Successful!');
                                        serviceCart = [];
                                        updateCheckoutBadge();
                                        $('#globalModal').modal('hide');
                                        loadBookingStatus(USER_ID); // ← Refresh the booking status area
                                    } else {
                                        alert('❌ Booking failed.');
                                    }
                                },
                                error: function (xhr, status, errMsg) {
                                    console.error("AJAX Error:", errMsg);
                                    console.error("Status:", status);
                                    console.error("Response:", xhr.responseText); // important!
                                    alert('❌ Network error during booking.\n\nDetails: ' + errMsg);
                                }
                            });
                        };

                        if (file) {
                            reader.readAsDataURL(file);
                        } else {
                            alert('⚠️ Please upload a receipt.');
                        }
                    });

                });
            });

            // Bind remove button
            container.off('click', '.remove-service-btn').on('click', '.remove-service-btn', function () {
                const index = $(this).data('index');
                serviceCart.splice(index, 1);
                updateCheckoutBadge();
                $('#checkOutBtn').trigger('click'); // reopen with updated cart
            });
        });
    });

    // Booking Status Load Function
    function loadBookingStatus(USER_ID) {
        // Check if booking status container exists
        if ($('#booking-status-list').length === 0) {
            console.log('Booking status container not found, skipping load');
            return;
        }
        
        console.log("Loading bookings for:", USER_ID);
        $.ajax({
            url: '../controller/booking_contr.php',
            type: 'POST',
            data: {
                action: 'get_user_bookings',
                user_id: USER_ID
            },
            dataType: 'json',
            success: function (data) {
                console.log("Booking data:", data); // Add this for clarity
                const $list = $('#booking-status-list');
                $list.empty();

                if (data.status === 'nodata' || (Array.isArray(data) && data.length === 0)) {
                    $list.append('<li class="list-group-item text-muted">No bookings yet.</li>');
                    return;
                }

                data.forEach(booking => {
                    let statusClass = 'bg-secondary';
                    if (booking.booking_status === 'Confirmed') statusClass = 'bg-success';
                    else if (booking.booking_status === 'Rejected') statusClass = 'bg-danger';

                    $list.append(`
                        <li class="list-group-item">
                            Booking ID #${booking.bookingid}
                            <span class="badge ${statusClass} float-end">${booking.booking_status}</span>
                        </li>
                    `);
                });
            },
            error: function () {
                console.error('Error loading booking status');
            }
        });
    }










</script>


<style>

    #services_container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .service-card {
        flex: 1 1 250px;
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

        .booking-status-scroll ul,
        .recent-services-scroll ul {
            max-height: 30vh;
        }
    }

    @media (max-width: 575.98px) {
        .booking-status-scroll ul,
        .recent-services-scroll ul {
            max-height: 25vh;
        }
    }
</style>
