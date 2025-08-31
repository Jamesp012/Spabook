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
    // Global variables for pagination
    var currentPage = 1;
    var currentLimit = 10;
    var currentAction = 'get_total_bookings';
    
    // Global functions for dashboard
    
    // Show dashboard loading state
    function showDashboardLoading() {
        $('#dashboard_loading').show();
        $('#dashboard_table_container').hide();
        $('#recovery_actions_container').hide();
        $('#dashboard_empty_state').hide();
    }
    
    // Load total bookings data
    function loadTotalBookings(page = 1, limit = 10) {
        console.log('Loading total bookings...');
        currentPage = page;
        currentLimit = limit;
        currentAction = 'get_total_bookings';
        
        // Show loading state
        $('#dashboard_loading').show();
        $('#dashboard_table_container').hide();
        $('#recovery_actions_container').hide();
        $('#dashboard_empty_state').hide();
        
        $.ajax({
            url: '../controller/admin_dashboard_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { 
                action: 'get_total_bookings',
                page: page,
                limit: limit
            },
            success: function(response) {
                console.log('Total bookings response:', response);
                $('#dashboard_loading').hide();
                
                if (response.status === 'success' && response.data && Array.isArray(response.data) && response.data.length > 0) {
                    displayBookingsTable(response.data, response.pagination);
                } else if (response.status === 'success' && response.data && Array.isArray(response.data) && response.data.length === 0) {
                    // Show empty state for successful response with no data
                    $('#dashboard_empty_state').show();
                    console.log('No booking data available (empty array)');
                } else {
                    // Show empty state for other cases
                    $('#dashboard_empty_state').show();
                    console.log('No booking data available or invalid response format:', response);
                    
                    // If there's an error message, display it
                    if (response.status === 'error' && response.message) {
                        console.error('Error from server:', response.message);
                        // You could display this error message to the user if needed
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#dashboard_loading').hide();
                $('#dashboard_empty_state').show();
                console.error('Error loading total bookings:', error);
                console.error('Status:', status);
                console.error('Response text:', xhr.responseText);
                
                // Try to parse the error response
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed error response:', errorResponse);
                        
                        // If there's a specific error message, you could display it
                        if (errorResponse.message) {
                            console.error('Server error message:', errorResponse.message);
                        }
                    }
                } catch (e) {
                    console.error('Could not parse error response as JSON');
                }
            }
        });
    }

    // Load appointment history data
    function loadAppointmentHistory(page = 1, limit = 10) {
        currentPage = page;
        currentLimit = limit;
        currentAction = 'get_appointment_history';
        
        $.ajax({
            url: '../controller/admin_dashboard_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { 
                action: 'get_appointment_history',
                page: page,
                limit: limit
            },
            success: function(response) {
                $('#dashboard_loading').hide();
                
                if (response.status === 'success' && response.data.length > 0) {
                    displayHistoryTable(response.data, response.pagination);
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
    function loadRecoveryData(page = 1, limit = 10) {
        currentPage = page;
        currentLimit = limit;
        currentAction = 'get_recovery_data';
        
        $.ajax({
            url: '../controller/admin_dashboard_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { 
                action: 'get_recovery_data',
                page: page,
                limit: limit
            },
            success: function(response) {
                $('#dashboard_loading').hide();
                
                if (response.status === 'success') {
                    displayRecoveryData(response.data, response.pagination);
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
    
    // Handle pagination click
    function handlePaginationClick(page) {
        // Show loading state
        showDashboardLoading();
        
        // Call the appropriate function based on current action
        switch (currentAction) {
            case 'get_total_bookings':
                loadTotalBookings(page, currentLimit);
                break;
            case 'get_appointment_history':
                loadAppointmentHistory(page, currentLimit);
                break;
            case 'get_recovery_data':
                loadRecoveryData(page, currentLimit);
                break;
            default:
                console.error("Unknown action for pagination: " + currentAction);
        }
    }
    
    // Helper function to get status class
    function getStatusClass(status) {
        switch (status) {
            case 'Confirmed':
                return 'bg-success';
            case 'Pending':
                return 'bg-warning text-dark';
            case 'Cancelled':
                return 'bg-danger';
            case 'Completed':
                return 'bg-info';
            default:
                return 'bg-secondary';
        }
    }
    
    // Helper function to get potential class
    function getPotentialClass(potential) {
        switch (potential) {
            case 'High':
                return 'bg-success';
            case 'Medium':
                return 'bg-warning text-dark';
            case 'Low':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }
    
    // Generate pagination HTML
    function generatePagination(pagination) {
        if (!pagination || pagination.total <= pagination.per_page) {
            return '';
        }
        
        let paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="handlePaginationClick(${pagination.current_page - 1})" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item disabled">
                    <a class="page-link" href="javascript:void(0)" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<li class="page-item active"><a class="page-link" href="javascript:void(0)">${i}</a></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="handlePaginationClick(${i})">${i}</a></li>`;
            }
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="handlePaginationClick(${pagination.current_page + 1})" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item disabled">
                    <a class="page-link" href="javascript:void(0)" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
        }
        
        paginationHtml += '</ul></nav>';
        
        return paginationHtml;
    }
    
    // Display bookings table
    function displayBookingsTable(bookings, pagination) {
        console.log('Displaying bookings table with data:', bookings);
        
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
                        <strong>${booking.user_name}</strong>
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
        
        // Add pagination
        const paginationHtml = generatePagination(pagination);
        $('#dashboard_table_container').html(`
            <table id="dashboard_data_table" class="table table-striped w-100">
                <thead class="table-secondary" id="table_header">${headers}</thead>
            </table>
            <div class="table-body-scroll">
                <table class="table table-striped w-100">
                    <tbody id="table_body">${rows}</tbody>
                </table>
            </div>
            ${paginationHtml}
        `);
        
        $('#dashboard_table_container').show();
    }

    // Display history table
    function displayHistoryTable(history, pagination) {
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
                        <strong>${appointment.user_name}</strong>
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
        
        // Add pagination
        const paginationHtml = generatePagination(pagination);
        $('#dashboard_table_container').html(`
            <table id="dashboard_data_table" class="table table-striped w-100">
                <thead class="table-secondary" id="table_header">${headers}</thead>
            </table>
            <div class="table-body-scroll">
                <table class="table table-striped w-100">
                    <tbody id="table_body">${rows}</tbody>
                </table>
            </div>
            ${paginationHtml}
        `);
        
        $('#dashboard_table_container').show();
    }

    // Display recovery data
    function displayRecoveryData(data, pagination) {
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
            
            // Add pagination for recoverable bookings
            if (pagination && pagination.recoverable) {
                const recoverablePagination = generatePagination(pagination.recoverable);
                recoverableHtml += recoverablePagination;
            }
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
                                <small class="text-success">Recovered: ${booking.recovery_date}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">₱${parseFloat(booking.total_price).toFixed(2)}</div>
                                <small class="text-muted">Value Recovered</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Add pagination for recovered bookings
            if (pagination && pagination.recovered) {
                const recoveredPagination = generatePagination(pagination.recovered);
                recoveredHtml += recoveredPagination;
            }
        } else {
            recoveredHtml = '<p class="text-muted">No recently recovered bookings.</p>';
        }
        
        $('#recovered_bookings').html(recoveredHtml);
    }
    
    // Load dashboard stats on page load
    function loadDashboardStats() {
        console.log('Loading dashboard stats...');
        
        // Show loading indicators
        $('#total_bookings_count').html('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
        $('#history_count').html('<div class="spinner-border spinner-border-sm text-success" role="status"><span class="visually-hidden">Loading...</span></div>');
        $('#recovery_count').html('<div class="spinner-border spinner-border-sm text-warning" role="status"><span class="visually-hidden">Loading...</span></div>');
        
        $.ajax({
            url: '../controller/admin_dashboard_contr.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'get_dashboard_stats' },
            success: function(response) {
                console.log('Dashboard stats response:', response);
                if (response && response.status === 'success' && response.data) {
                    updateDashboardStats(response.data);
                } else {
                    console.error('Error loading dashboard stats:', response ? response.message : 'No response');
                    showStatsError();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading dashboard stats:', error);
                console.error('Status:', status);
                console.error('Response text:', xhr.responseText);
                
                // Try to parse the response if it's JSON
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed error response:', errorResponse);
                    }
                } catch (e) {
                    console.error('Could not parse error response as JSON');
                }
                
                showStatsError();
            }
        });
    }

    // Update dashboard statistics cards
    function updateDashboardStats(data) {
        console.log("Updating dashboard stats with data:", data);
        
        // Total Bookings Card
        // Convert to number to ensure proper display
        const totalBookings = parseInt(data.total_bookings) || 0;
        $('#total_bookings_count').html(totalBookings);
        
        const acceptedBookings = parseInt(data.accepted_bookings) || 0;
        const pendingBookings = parseInt(data.pending_bookings) || 0;
        
        $('#bookings_subtitle').html(`
            <i class="pe-2 bi bi-check-circle text-success"></i>
            Accepted: ${acceptedBookings} | Pending: ${pendingBookings}
        `);

        // History Card
        const completedBookings = parseInt(data.completed_bookings) || 0;
        const cancelledBookings = parseInt(data.cancelled_bookings) || 0;
        const historyTotal = completedBookings + cancelledBookings;
        
        $('#history_count').html(historyTotal);
        $('#history_subtitle').html(`
            <i class="pe-2 bi bi-check-circle text-success"></i>
            Completed: ${completedBookings} | Cancelled: ${cancelledBookings}
        `);

        // Recovery Card
        const recoveryRate = parseFloat(data.recovery_rate) || 0;
        const recoveryCount = parseInt(data.recovery_count) || 0;
        
        $('#recovery_count').html(recoveryRate + '%');
        $('#recovery_subtitle').html(`
            <i class="pe-2 bi bi-arrow-repeat text-warning"></i>
            Recovery Rate: ${recoveryCount} recovered
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
    
    // Function to recover a cancelled booking
    function recoverBooking(bookingId) {
        if (confirm('Are you sure you want to recover this booking?')) {
            $.ajax({
                url: '../controller/admin_dashboard_contr.php',
                type: 'POST',
                dataType: 'json',
                data: { 
                    action: 'update_booking_status',
                    booking_id: bookingId,
                    status: 'Confirmed'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Reload recovery data
                        loadRecoveryData(currentPage, currentLimit);
                        // Show success message
                        alert('Booking successfully recovered!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error recovering booking:', error);
                    alert('Error recovering booking. Please try again.');
                }
            });
        }
    }
    
    // Dashboard data loading function
    function loadDashboardData(type) {
        // Update active card
        $('.custom_card_hover').removeClass('active');
        
        // Show loading state
        showDashboardLoading();
        
        // Reset to first page when changing dashboard type
        currentPage = 1;
        
        switch (type) {
            case 'Bookings':
                $('#active_dashboard').text('Total Bookings');
                $('#bookings-card').addClass('active');
                currentAction = 'get_total_bookings';
                loadTotalBookings(currentPage, currentLimit);
                break;
            case 'History':
                $('#active_dashboard').text('Appointment History');
                $('#history-card').addClass('active');
                currentAction = 'get_appointment_history';
                loadAppointmentHistory(currentPage, currentLimit);
                break;
            case 'Recovery':
                $('#active_dashboard').text('Recovery Management');
                $('#recovery-card').addClass('active');
                currentAction = 'get_recovery_data';
                loadRecoveryData(currentPage, currentLimit);
                break;
            default:
                console.error("Unknown type: " + type);
        }
    }
    
    // Function to load content
    function loadContent(page) {
        // 🧹 Remove previous modal/backdrop before loading new page
        $.ajax({
            url: `../views/admin/${page}`,
            method: 'GET',
            success: function (response) {
                $('.app_content_body').html(response);
                
                // If loading dashboard, initialize dashboard data
                if (page === 'admin_dashboard.php') {
                    // Load dashboard stats
                    loadDashboardStats();
                    
                    // Load initial data (Total Bookings by default)
                    setTimeout(function() {
                        loadTotalBookings(currentPage, currentLimit);
                    }, 100);
                }
            },
            error: function () {
                $('.app_content_body').html('<div class="alert alert-danger">Failed to load content.</div>');
            }
        });
    }
    
    // Document ready function
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
        
        // Items per page change event
        $(document).on('change', '#itemsPerPage, #recoveryItemsPerPage', function() {
            currentLimit = parseInt($(this).val());
            currentPage = 1; // Reset to first page when changing items per page
            
            // Reload data based on current action
            switch (currentAction) {
                case 'get_total_bookings':
                    loadTotalBookings(currentPage, currentLimit);
                    break;
                case 'get_appointment_history':
                    loadAppointmentHistory(currentPage, currentLimit);
                    break;
                case 'get_recovery_data':
                    loadRecoveryData(currentPage, currentLimit);
                    break;
                default:
                    // Default to total bookings if no action is set
                    loadTotalBookings(currentPage, currentLimit);
            }
        });
    });
    
    // Dashboard data loading function
    function loadDashboardData(type) {
        // Update active card
        $('.custom_card_hover').removeClass('active');
        
        // Show loading state
        $('#dashboard_loading').show();
        $('#dashboard_table_container').hide();
        $('#recovery_actions_container').hide();
        $('#dashboard_empty_state').hide();
        
        // Reset to first page when changing dashboard type
        currentPage = 1;
        
        switch (type) {
            case 'Bookings':
                $('#active_dashboard').text('Total Bookings');
                $('#bookings-card').addClass('active');
                currentAction = 'get_total_bookings';
                loadTotalBookings(currentPage, currentLimit);
                break;
            case 'History':
                $('#active_dashboard').text('Appointment History');
                $('#history-card').addClass('active');
                currentAction = 'get_appointment_history';
                loadAppointmentHistory(currentPage, currentLimit);
                break;
            case 'Recovery':
                $('#active_dashboard').text('Recovery Management');
                $('#recovery-card').addClass('active');
                currentAction = 'get_recovery_data';
                loadRecoveryData(currentPage, currentLimit);
                break;
            default:
                console.error("Unknown type: " + type);
        }
    }
    
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
        
        // Items per page change event
        $(document).on('change', '#itemsPerPage, #recoveryItemsPerPage', function() {
            currentLimit = parseInt($(this).val());
            currentPage = 1; // Reset to first page when changing items per page
            
            // Reload data based on current action
            switch (currentAction) {
                case 'get_total_bookings':
                    loadTotalBookings(currentPage, currentLimit);
                    break;
                case 'get_appointment_history':
                    loadAppointmentHistory(currentPage, currentLimit);
                    break;
                case 'get_recovery_data':
                    loadRecoveryData(currentPage, currentLimit);
                    break;
                default:
                    // Default to total bookings if no action is set
                    loadTotalBookings(currentPage, currentLimit);
            }
        });

        function loadContent(page) {
            // 🧹 Remove previous modal/backdrop before loading new page

            $.ajax({
                url: `../views/admin/${page}`,
                method: 'GET',
                success: function (response) {
                    $('.app_content_body').html(response);
                    
                    // If loading dashboard, initialize dashboard data
                    if (page === 'admin_dashboard.php') {
                        // Load dashboard stats
                        loadDashboardStats();
                        
                        // Load initial data (Total Bookings by default)
                        setTimeout(function() {
                            loadTotalBookings(currentPage, currentLimit);
                        }, 100);
                    }
                },
                error: function () {
                    $('.app_content_body').html('<div class="alert alert-danger">Failed to load content.</div>');
                }
            });
        }

        // Dashboard-specific behavior - moved to global scope

        // These functions have been moved to global scope

        // Use the global variables defined above
        
        // These functions have been moved to global scope

        // These functions have been moved to global scope
        
        // Display bookings table
        function displayBookingsTable(bookings, pagination) {
            console.log('Displaying bookings table with data:', bookings);
            
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
            
            // Add pagination
            const paginationHtml = generatePagination(pagination);
            $('#dashboard_table_container').html(`
                <table id="dashboard_data_table" class="table table-striped w-100">
                    <thead class="table-secondary" id="table_header">${headers}</thead>
                </table>
                <div class="table-body-scroll">
                    <table class="table table-striped w-100">
                        <tbody id="table_body">${rows}</tbody>
                    </table>
                </div>
                ${paginationHtml}
            `);
            
            $('#dashboard_table_container').show();
        }

        // Display history table
        function displayHistoryTable(history, pagination) {
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
            
            // Add pagination
            const paginationHtml = generatePagination(pagination);
            $('#dashboard_table_container').html(`
                <table id="dashboard_data_table" class="table table-striped w-100">
                    <thead class="table-secondary" id="table_header">${headers}</thead>
                </table>
                <div class="table-body-scroll">
                    <table class="table table-striped w-100">
                        <tbody id="table_body">${rows}</tbody>
                    </table>
                </div>
                ${paginationHtml}
            `);
            
            $('#dashboard_table_container').show();
        }

        // Display recovery data
        function displayRecoveryData(data, pagination) {
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
                
                // Add pagination for recoverable bookings
                if (pagination && pagination.recoverable) {
                    const recoverablePagination = generatePagination(pagination.recoverable);
                    recoverableHtml += recoverablePagination;
                }
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
                                    <small class="text-success">Recovered: ${booking.recovery_date}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">₱${parseFloat(booking.total_price).toFixed(2)}</div>
                                    <small class="text-muted">Value Recovered</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // Add pagination for recovered bookings
                if (pagination && pagination.recovered) {
                    const recoveredPagination = generatePagination(pagination.recovered);
                    recoveredHtml += recoveredPagination;
                }
            } else {
                recoveredHtml = '<p class="text-muted">No recently recovered bookings.</p>';
            }
            
            $('#recovered_bookings').html(recoveredHtml);
        }
        
        // Function to recover a cancelled booking
        function recoverBooking(bookingId) {
            if (confirm('Are you sure you want to recover this booking?')) {
                $.ajax({
                    url: '../controller/admin_dashboard_contr.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { 
                        action: 'update_booking_status',
                        booking_id: bookingId,
                        status: 'Confirmed'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Reload recovery data
                            loadRecoveryData(currentPage, currentLimit);
                            // Show success message
                            alert('Booking successfully recovered!');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error recovering booking:', error);
                        alert('Error recovering booking. Please try again.');
                    }
                });
            }
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
