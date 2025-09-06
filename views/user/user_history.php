<div class="history-container container-fluid" style="max-height: calc(100vh - 130px); overflow-y: auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Booking History</h5>
        <button class="btn btn-outline-primary btn-sm" id="refresh-history">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    <div id="loading-history" class="text-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2 text-muted">Loading your booking history...</div>
    </div>

    <div id="history-list" class="history-list mt-2">
        <!-- History items will be loaded here -->
    </div>

    <div id="no-history" class="text-center py-5" style="display: none;">
        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
        <div class="mt-3 text-muted">
            <h6>No booking history found</h6>
            <p class="mb-0">Your completed and past bookings will appear here.</p>
        </div>
    </div>
</div>

<style>
    .history-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 0 10px;
    }

    .history-item {
        display: flex;
        background-color: #fff;
        padding: 16px 20px;
        border-radius: 8px;
        align-items: center;
        justify-content: space-between;
        font-size: 0.95rem;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border-left: 4px solid transparent;
        transition: all 0.2s ease;
    }

    .history-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .history-item.status-confirmed {
        border-left-color: #28a745;
    }

    .history-item.status-completed {
        border-left-color: #007bff;
    }

    .history-item.status-cancelled {
        border-left-color: #dc3545;
    }

    .history-item-content {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }

    .history-item-content>div {
        margin-bottom: 4px;
        min-width: 180px;
        text-align: left;
    }

    .service-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .booking-date {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .booking-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-confirmed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-completed {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .invoice-summary {
        margin-top: 12px;
        padding: 12px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .invoice-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        color: #6c757d;
    }

    .invoice-items {
        margin-bottom: 8px;
    }

    .invoice-item {
        display: flex;
        justify-content: space-between;
        padding: 2px 0;
    }

    .invoice-total {
        display: flex;
        justify-content: between;
        font-weight: 600;
        color: #2c3e50;
        border-top: 1px solid #dee2e6;
        padding-top: 8px;
    }

    @media (max-width: 767.98px) {
        .history-item {
            flex-direction: column;
            align-items: flex-start;
            font-size: 1rem;
            padding: 14px 16px;
        }

        .history-item-content {
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .history-list {
            padding: 0 5px;
        }

        .history-item-content>div {
            min-width: unset;
            width: 100%;
        }
    }
</style>

<script src="../../vendor/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        let currentUserId = null;

        function peso(v) {
            return '₱' + Number(v).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
        }

        function getStatusClass(status) {
            return 'status-' + status.toLowerCase().replace(/\s+/g, '-');
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-PH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return dateString;
            }
        }

        function loadInvoice(bookingId, container) {
            $.post('../../controller/booking_contr.php', {
                action: 'get_invoice_by_booking',
                booking_id: bookingId
            }, function(res) {
                if (res.status === 'success') {
                    const inv = res.invoice;
                    const items = res.items || [];

                    const itemsHtml = items.map(item => `
                    <div class="invoice-item">
                        <span>${item.description || ('Service #' + item.service_id)}</span>
                        <span>${peso(item.line_total)}</span>
                    </div>
                `).join('');

                    const invoiceHtml = `
                    <div class="invoice-summary">
                        <div class="invoice-meta">
                            <span>Invoice #${inv.invoice_id}</span>
                            <span class="booking-status ${getStatusClass(inv.payment_status)}">${inv.payment_status}</span>
                        </div>
                        <div class="invoice-items">
                            ${itemsHtml || '<em class="text-muted">No items</em>'}
                        </div>
                        <div class="invoice-total">
                            <span>Total:</span>
                            <span>${peso(inv.total)}</span>
                        </div>
                    </div>
                `;

                    $(container).append(invoiceHtml);
                }
            }, 'json').fail(function() {
                console.log('Failed to load invoice for booking ' + bookingId);
            });
        }

        function loadUserHistory(userId) {
            $('#loading-history').show();
            $('#history-list').empty();
            $('#no-history').hide();

            $.post('../../controller/booking_contr.php', {
                action: 'get_user_bookings',
                user_id: userId
            }, function(res) {
                $('#loading-history').hide();

                if (res.status === 'success' && res.data && res.data.length > 0) {
                    const bookings = res.data;

                    bookings.forEach(function(booking) {
                        const statusClass = getStatusClass(booking.booking_status);

                        const historyItem = $(`
                        <div class="history-item ${statusClass}" data-booking-id="${booking.bookingid}">
                            <div class="history-item-content">
                                <div>
                                    <div class="service-name">Services: ${booking.services || 'Multiple Services'}</div>
                                    <div class="text-muted small">Booking #${booking.bookingid}</div>
                                </div>
                                <div>
                                    <div>Quantity: ${booking.total_quantity || 1}</div>
                                    <div class="text-muted small">Amount: ${peso(booking.total_price || 0)}</div>
                                </div>
                                <div>
                                    <div class="booking-date">${formatDate(booking.booking_date)}</div>
                                    <div class="booking-status ${statusClass}">${booking.booking_status}</div>
                                </div>
                            </div>
                        </div>
                    `);

                        $('#history-list').append(historyItem);

                        // Load invoice for this booking if it's completed or confirmed
                        if (booking.booking_status === 'Completed' || booking.booking_status === 'Confirmed') {
                            loadInvoice(booking.bookingid, historyItem[0]);
                        }
                    });
                } else {
                    $('#no-history').show();
                }
            }, 'json').fail(function() {
                $('#loading-history').hide();
                $('#no-history').show();
                Swal.fire('Error', 'Failed to load booking history', 'error');
            });
        }

        function getCurrentUserId() {
            // Try to get user ID from session or local storage
            // This should match how the user ID is stored in your authentication system
            return new Promise((resolve, reject) => {
                // First try to get from supabase session
                if (typeof supabase !== 'undefined') {
                    supabase.auth.getSession().then(({
                        data: {
                            session
                        }
                    }) => {
                        if (session && session.user) {
                            resolve(session.user.id);
                        } else {
                            reject('No active session');
                        }
                    });
                } else {
                    // Fallback: try to get from a global variable or make an API call
                    $.post('../../controller/user_contr.php', {
                        action: 'get_current_user'
                    }, function(res) {
                        if (res.status === 'success' && res.user_id) {
                            resolve(res.user_id);
                        } else {
                            reject('Unable to get user ID');
                        }
                    }, 'json').fail(function() {
                        reject('Network error getting user ID');
                    });
                }
            });
        }

        // Event handlers
        $('#refresh-history').on('click', function() {
            if (currentUserId) {
                loadUserHistory(currentUserId);
            }
        });

        // Initialize
        getCurrentUserId().then(function(userId) {
            currentUserId = userId;
            loadUserHistory(userId);
        }).catch(function(error) {
            console.error('Error getting user ID:', error);
            $('#loading-history').hide();
            $('#no-history').show();
            $('#no-history .text-muted h6').text('Unable to load history');
            $('#no-history .text-muted p').text('Please try logging out and logging back in.');
        });
    });
</script>