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

        // Dashboard-specific behavior
        window.loadDashboardData = function (type) {
            // Update active card
            $('.custom_card_hover').removeClass('active');
            
            // Show loading state
            showDashboardLoading();
            
            switch (type) {
                case 'Bookings':
                    $('#active_dashboard').text('Total Bookings');
                    $('#bookings-card').addClass('active');
                    loadTotalBookings();
                    break;
                case 'History':
                    $('#active_dashboard').text('Appointment History');
                    $('#history-card').addClass('active');
                    loadAppointmentHistory();
                    break;
                case 'Recovery':
                    $('#active_dashboard').text('Recovery Management');
                    $('#recovery-card').addClass('active');
                    loadRecoveryData();
                    break;
                default:
                    console.error("Unknown type: " + type);
            }
        };

        // Load dashboard stats on page load
        function loadDashboardStats() {
            $.ajax({
                url: '../controller/admin_dashboard_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_dashboard_stats' },
                success: function(response) {
                    if (response.status === 'success') {
                        updateDashboardStats(response.data);
                    } else {
                        console.error('Error loading dashboard stats:', response.message);
                        showStatsError();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading dashboard stats:', error);
                    showStatsError();
                }
            });
        }

        // Update dashboard statistics cards
        function updateDashboardStats(data) {
            // Total Bookings Card
            $('#total_bookings_count').html(data.total_bookings);
            $('#bookings_subtitle').html(`
                <i class="pe-2 bi bi-check-circle text-success"></i>
                Accepted: ${data.accepted_bookings} | Pending: ${data.pending_bookings}
            `);

            // History Card
            $('#history_count').html(data.completed_bookings + data.cancelled_bookings);
            $('#history_subtitle').html(`
                <i class="pe-2 bi bi-check-circle text-success"></i>
                Completed: ${data.completed_bookings} | Cancelled: ${data.cancelled_bookings}
            `);

            // Recovery Card
            $('#recovery_count').html(data.recovery_rate + '%');
            $('#recovery_subtitle').html(`
                <i class="pe-2 bi bi-arrow-repeat text-warning"></i>
                Recovery Rate: ${data.recovery_count} recovered
            `);
        }

        // Show stats loading error
        function showStatsError() {
            $('#total_bookings_count').html('<i class="bi bi-exclamation-circle text-danger"></i>');
            $('#bookings_subtitle').html('<i class="pe-2 bi bi-exclamation-triangle text-danger"></i>Error loading');
            
            $('#history_count').html('<i class="bi bi-exclamation-circle text-danger"></i>');
            $('#history_subtitle').html('<i class="pe-2 bi bi-exclamation-triangle text-danger"></i>Error loading');
            
            $('#recovery_count').html('<i class="bi bi-exclamation-circle text-danger"></i>');
            $('#recovery_subtitle').html('<i class="pe-2 bi bi-exclamation-triangle text-danger"></i>Error loading');
        }

        // Show dashboard loading state
        function showDashboardLoading() {
            $('#dashboard_loading').show();
            $('#dashboard_table_container').hide();
            $('#recovery_actions_container').hide();
            $('#dashboard_empty_state').hide();
        }

        // Load total bookings data
        function loadTotalBookings() {
            $.ajax({
                url: '../controller/admin_dashboard_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_total_bookings' },
                success: function(response) {
                    $('#dashboard_loading').hide();
                    
                    if (response.status === 'success' && response.data.length > 0) {
                        displayBookingsTable(response.data);
                    } else {
                        $('#dashboard_empty_state').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#dashboard_loading').hide();
                    $('#dashboard_empty_state').show();
                    console.error('Error loading total bookings:', error);
                }
            });
        }

        // Load appointment history data
        function loadAppointmentHistory() {
            $.ajax({
                url: '../controller/admin_dashboard_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_appointment_history' },
                success: function(response) {
                    $('#dashboard_loading').hide();
                    
                    if (response.status === 'success' && response.data.length > 0) {
                        displayHistoryTable(response.data);
                    } else {
                        $('#dashboard_empty_state').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#dashboard_loading').hide();
                    $('#dashboard_empty_state').show();
                    console.error('Error loading appointment history:', error);
                }
            });
        }

        // Load recovery data
        function loadRecoveryData() {
            $.ajax({
                url: '../controller/admin_dashboard_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_recovery_data' },
                success: function(response) {
                    $('#dashboard_loading').hide();
                    
                    if (response.status === 'success') {
                        displayRecoveryData(response.data);
                    } else {
                        $('#dashboard_empty_state').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#dashboard_loading').hide();
                    $('#dashboard_empty_state').show();
                    console.error('Error loading recovery data:', error);
                }
            });
        }

        // Display bookings table
        function displayBookingsTable(bookings) {
            const headers = `
                <tr>
                    <th>Client</th>
                    <th>Services</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Payment</th>
                </tr>
            `;
            
            let rows = '';
            bookings.forEach(booking => {
                const statusClass = getStatusClass(booking.booking_status);
                const paymentClass = booking.payment_status === 'Paid' ? 'text-success' : 'text-warning';
                
                rows += `
                    <tr>
                        <td data-label="Client">
                            <strong>${booking.user_name}</strong><br>
                            <small class="text-muted">${booking.user_email}</small>
                        </td>
                        <td data-label="Services">${booking.services_text}</td>
                        <td data-label="Date & Time">${booking.booking_date}</td>
                        <td data-label="Status">
                            <span class="badge ${statusClass}">${booking.booking_status}</span>
                        </td>
                        <td data-label="Amount">₱${parseFloat(booking.total_price).toFixed(2)}</td>
                        <td data-label="Payment">
                            <span class="${paymentClass}">${booking.payment_status}</span>
                        </td>
                    </tr>
                `;
            });
            
            $('#table_header').html(headers);
            $('#table_body').html(rows);
            $('#dashboard_table_container').show();
        }

        // Display history table
        function displayHistoryTable(history) {
            const headers = `
                <tr>
                    <th>Client</th>
                    <th>Services</th>
                    <th>Completion Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Duration</th>
                </tr>
            `;
            
            let rows = '';
            history.forEach(appointment => {
                const statusClass = getStatusClass(appointment.booking_status);
                
                rows += `
                    <tr>
                        <td data-label="Client">
                            <strong>${appointment.user_name}</strong><br>
                            <small class="text-muted">${appointment.user_email}</small>
                        </td>
                        <td data-label="Services">${appointment.services_text}</td>
                        <td data-label="Completion Date">${appointment.completion_date}</td>
                        <td data-label="Status">
                            <span class="badge ${statusClass}">${appointment.booking_status}</span>
                        </td>
                        <td data-label="Amount">₱${parseFloat(appointment.total_price).toFixed(2)}</td>
                        <td data-label="Duration">${appointment.duration}</td>
                    </tr>
                `;
            });
            
            $('#table_header').html(headers);
            $('#table_body').html(rows);
            $('#dashboard_table_container').show();
        }

        // Display recovery data
        function displayRecoveryData(data) {
            // Show recovery actions container
            $('#recovery_actions_container').show();
            
            // Display recoverable bookings
            let recoverableHtml = '';
            if (data.recoverable && data.recoverable.length > 0) {
                data.recoverable.forEach(booking => {
                    const potentialClass = getPotentialClass(booking.recovery_potential);
                    recoverableHtml += `
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${booking.user_name}</h6>
                                    <p class="mb-1 text-muted">${booking.services_text}</p>
                                    <small class="text-muted">Cancelled: ${booking.cancelled_date}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">₱${parseFloat(booking.total_price).toFixed(2)}</div>
                                    <span class="badge ${potentialClass}">${booking.recovery_potential}</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-success btn-sm" onclick="recoverBooking(${booking.bookingid})">
                                    <i class="bi bi-arrow-repeat me-1"></i>Recover
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                recoverableHtml = '<p class="text-muted">No recoverable bookings found.</p>';
            }
            
            $('#recoverable_bookings').html(recoverableHtml);
            
            // Display recently recovered bookings
            let recoveredHtml = '';
            if (data.recently_recovered && data.recently_recovered.length > 0) {
                data.recently_recovered.forEach(booking => {
                    recoveredHtml += `
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${booking.user_name}</h6>
                                    <p class="mb-1 text-muted">${booking.services_text}</p>
                                    <small class="text-success">Recovered: ${booking.recovered_date}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">₱${parseFloat(booking.recovery_value).toFixed(2)}</div>
                                    <small class="text-muted">Value Recovered</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                recoveredHtml = '<p class="text-muted">No recently recovered bookings.</p>';
            }
            
            $('#recovered_bookings').html(recoveredHtml);
        }

        // Helper function to get status class
        function getStatusClass(status) {
            switch(status) {
                case 'Confirmed': return 'bg-success';
                case 'Pending': return 'bg-warning';
                case 'Cancelled': return 'bg-danger';
                case 'Completed': return 'bg-primary';
                default: return 'bg-secondary';
            }
        }

        // Helper function to get recovery potential class
        function getPotentialClass(potential) {
            switch(potential) {
                case 'High': return 'bg-success';
                case 'Medium': return 'bg-warning';
                case 'Low': return 'bg-secondary';
                default: return 'bg-secondary';
            }
        }

        // Recover booking function
        window.recoverBooking = function(bookingId) {
            if (confirm('Are you sure you want to recover this booking?')) {
                $.ajax({
                    url: '../controller/admin_dashboard_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { 
                        action: 'update_booking_status',
                        booking_id: bookingId,
                        new_status: 'Confirmed'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Booking recovered successfully!');
                            loadRecoveryData(); // Reload recovery data
                            loadDashboardStats(); // Reload stats
                        } else {
                            alert('Error recovering booking: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error recovering booking: ' + error);
                    }
                });
            }
        };

        // Load dashboard stats on page load
        loadDashboardStats();
        
        // Load default dashboard data (bookings)
        loadDashboardData('Bookings');

        // Function to open the therapist status modal
        window.openTherapistStatusModal = function() {
            showGlobalModal('../views/modal/admin_modal-therapist-status.php', {}, function() {
                console.log('✅ Therapist Status Modal loaded successfully');
            });
        };
    });
</script>

<style>
    /* Responsive Table Heights */
    .table-body-scroll {
        border-top: 1px solid #dee2e6;
        overflow-y: auto;
        /* Dynamic height: Full viewport minus header, nav, footer, and padding */
        max-height: calc(100vh - 200px);
        /* Minimum height to prevent too small tables */
        min-height: 300px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767px) {
        .table-body-scroll {
            /* Smaller height on mobile to save space */
            max-height: calc(100vh - 250px);
            min-height: 250px;
        }
    }
    
    @media (min-width: 1400px) {
        .table-body-scroll {
            /* More space on large screens */
            max-height: calc(100vh - 180px);
            min-height: 400px;
        }
    }
    
    .table-body-scroll .table {
        margin-bottom: 0;
    }
    
    .table-body-scroll::-webkit-scrollbar {
        width: 8px;
    }
    
    .table-body-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-body-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .table-body-scroll::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
