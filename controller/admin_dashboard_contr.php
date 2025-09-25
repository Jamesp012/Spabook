<?php
// Debug file to see what's happening (disabled in production)
// file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Admin dashboard controller called\n", FILE_APPEND);

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../model/booking_model.php';
date_default_timezone_set('Asia/Manila');

// Set content type to JSON
header('Content-Type: application/json');

// Custom error handler to return JSON errors instead of HTML
function jsonErrorHandler($errno, $errstr, $errfile, $errline)
{
    $error = [
        'status' => 'error',
        'message' => "PHP Error: $errstr in $errfile on line $errline",
        'code' => $errno
    ];

    // Log the error (disabled in production)
    // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Error: $errstr in $errfile on line $errline\n", FILE_APPEND);

    echo json_encode($error);
    exit;
}

// Set custom error handler
set_error_handler('jsonErrorHandler');

// Also handle fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');

        // Log the error (disabled in production)
        // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}\n", FILE_APPEND);

        echo json_encode([
            'status' => 'error',
            'message' => "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}",
            'code' => $error['type']
        ]);
    }
});

// Initialize model
$BookingModel = new BookingModel();

function response($data)
{
    // Log the response data (disabled in production)
    // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Response: " . json_encode($data) . "\n", FILE_APPEND);

    echo json_encode($data);
    exit;
}

/**
 * Calculate session duration between two dates
 * 
 * @param string $startDate Start date
 * @param string $endDate End date
 * @return string Formatted duration
 */
function calculateSessionDuration($startDate, $endDate)
{
    if (empty($startDate) || empty($endDate)) {
        return 'N/A';
    }

    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);

    if ($interval->days > 0) {
        return $interval->format('%d days');
    } elseif ($interval->h > 0) {
        return $interval->format('%h hours');
    } else {
        return $interval->format('%i minutes');
    }
}

/**
 * Calculate recovery potential based on booking data
 * 
 * @param array $booking Booking data
 * @return string Recovery potential (High, Medium, Low)
 */
function calculateRecoveryPotential($booking)
{
    // This is a placeholder implementation
    // In a real system, you might use factors like:
    // - How recently the booking was cancelled
    // - Customer history
    // - Reason for cancellation
    // - Availability of the requested service/time

    $price = floatval($booking['total_price'] ?? 0);

    if ($price > 1000) {
        return 'High';
    } elseif ($price > 500) {
        return 'Medium';
    } else {
        return 'Low';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log the POST data (disabled in production)
        // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - POST data: " . json_encode($_POST) . "\n", FILE_APPEND);

        $action = $_POST['action'] ?? null;

        if (!$action) {
            response(['status' => 'error', 'message' => 'No POST action specified']);
        }

        switch ($action) {
            case 'get_dashboard_stats':
                getDashboardStats();
                break;

            case 'get_total_bookings':
                getTotalBookings();
                break;

            case 'get_appointment_history':
                getAppointmentHistory();
                break;

            case 'get_recovery_data':
                getRecoveryData();
                break;

            case 'update_booking_status':
                updateBookingStatus();
                break;

            case 'get_therapist_status':
                getTherapistStatus();
                break;

            case 'update_therapist_status':
                updateTherapistStatus();
                break;

            case 'toggle_all_therapists':
                toggleAllTherapists();
                break;

            case 'test_connection':
                response(['status' => 'success', 'message' => 'Controller connection successful', 'timestamp' => date('Y-m-d H:i:s')]);
                break;

            default:
                response(['status' => 'error', 'message' => 'Unknown action: ' . $action]);
        }
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n", FILE_APPEND);

        response([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    } catch (Error $e) {
        // Log the error
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n", FILE_APPEND);

        response([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}

/**
 * Get overall dashboard statistics
 */
function getDashboardStats()
{
    global $php_fetch;

    try {
        // Log the start of the function
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - getDashboardStats started\n", FILE_APPEND);

        // Get total bookings count
        $totalBookings = $php_fetch('booking', 'COUNT(*) as count', []);
        $totalBookingsCount = $totalBookings[0]['count'] ?? 0;

        // Log the total bookings count
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Total bookings count: " . json_encode($totalBookings) . "\n", FILE_APPEND);

        // Get accepted bookings count
        $acceptedBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Confirmed']);
        $acceptedBookingsCount = $acceptedBookings[0]['count'] ?? 0;

        // Get pending bookings count
        $pendingBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Pending']);
        $pendingBookingsCount = $pendingBookings[0]['count'] ?? 0;

        // Get completed appointments (assuming 'Completed' status exists)
        $completedBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Completed']);
        $completedBookingsCount = $completedBookings[0]['count'] ?? 0;

        // Get cancelled/rejected bookings
        $cancelledQuery = "SELECT COUNT(*) as count FROM booking WHERE booking_status IN ('Cancelled', 'Rejected')";
        $cancelledBookings = $php_fetch($cancelledQuery);
        $cancelledBookingsCount = $cancelledBookings[0]['count'] ?? 0;

        // Calculate recovery rate (completed vs total)
        $recoveryRate = $totalBookingsCount > 0 ? round(($completedBookingsCount / $totalBookingsCount) * 100, 1) : 0;

        // Create the response data
        $responseData = [
            'status' => 'success',
            'data' => [
                'total_bookings' => (int)$totalBookingsCount,
                'accepted_bookings' => (int)$acceptedBookingsCount,
                'pending_bookings' => (int)$pendingBookingsCount,
                'completed_bookings' => (int)$completedBookingsCount,
                'cancelled_bookings' => (int)$cancelledBookingsCount,
                'recovery_rate' => (float)$recoveryRate,
                'recovery_count' => (int)$completedBookingsCount
            ]
        ];

        // Log the response data
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - getDashboardStats response: " . json_encode($responseData) . "\n", FILE_APPEND);

        response($responseData);
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in getDashboardStats: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error fetching dashboard stats: ' . $e->getMessage()]);
    }
}

/**
 * Get detailed total bookings data with pagination
 */
function getTotalBookings()
{
    global $php_fetch, $BookingModel;

    // Log the POST data

    try {
        // Get pagination parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Get total count of bookings
        $totalCountResult = $php_fetch('booking', 'COUNT(*) as total', []);
        $totalCount = $totalCountResult[0]['total'] ?? 0;

        // Calculate total pages
        $totalPages = ceil($totalCount / $limit);

        // Get paginated bookings - use direct table access instead of raw SQL
        try {
            // Log the query attempt

            // Use the php_fetch function with proper parameters instead of raw SQL
            $bookings = $php_fetch('booking', '*', []);

            // Apply sorting, limit and offset manually if needed
            if (is_array($bookings) && !empty($bookings)) {
                // Sort by date_created DESC
                usort($bookings, function ($a, $b) {
                    return strtotime($b['date_created'] ?? 0) - strtotime($a['date_created'] ?? 0);
                });

                // Apply limit and offset
                $bookings = array_slice($bookings, $offset, $limit);
            }
        } catch (Exception $e) {
            // Log any exceptions
            $bookings = [];
        }

        // Log the bookings data
        if (is_array($bookings)) {
        } else {
            $bookings = []; // Set to empty array if not an array
        }

        if (empty($bookings)) {
            response([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $totalPages
                ]
            ]);
        }

        $result = [];
        foreach ($bookings as $booking) {
            // Get user details
            $user = null;
            if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                $userResult = $php_fetch('users', 'full_name, contact_number', ['user_id' => $booking['user_id']]);
                $user = $userResult ? $userResult[0] : null;
            }

            // Get booking details and services
            $booking_details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);

            $services = [];
            $serviceNames = [];
            if ($booking_details) {
                foreach ($booking_details as $detail) {
                    $service = $php_fetch('services', 'service_name, price', ['id' => $detail['service_id']]);
                    if ($service) {
                        $serviceName = $service[0]['service_name'];
                        $services[] = [
                            'name' => $serviceName,
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price']
                        ];
                        $serviceNames[] = $serviceName;
                    }
                }
            }

            // Determine correct payment status based on booking status
            $payment_status = 'Unpaid'; // Default
            if ($booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed') {
                $payment_status = 'Paid';
            } else if ($booking['booking_status'] === 'Pending') {
                $payment_status = 'Unpaid';
            }

            // Check if invoice exists for more accurate payment status (with safe access)
            $invoiceResult = $php_fetch('invoices', 'payment_status', ['booking_id' => $booking['bookingid']]);
            if (is_array($invoiceResult) && (!isset($invoiceResult['error']))) {
                // Typical Supabase response: array of rows
                if (isset($invoiceResult[0]) && is_array($invoiceResult[0]) && isset($invoiceResult[0]['payment_status'])) {
                    $payment_status = $invoiceResult[0]['payment_status'];
                    // Defensive: sometimes a single row may be returned as an associative array
                } elseif (isset($invoiceResult['payment_status'])) {
                    $payment_status = $invoiceResult['payment_status'];
                }
            }

            $result[] = [
                'bookingid' => $booking['bookingid'],
                'user_name' => $user ? $user['full_name'] : 'Unknown User',
                'user_phone' => $user ? $user['contact_number'] : '',
                'services_text' => implode(', ', $serviceNames),
                'services' => $services,
                'total_price' => $booking['total_price'],
                'booking_date' => date('M d, Y g:i A', strtotime($booking['date_created'] ?? 'now')),
                'booking_status' => $booking['booking_status'],
                'payment_status' => $payment_status,
                'payment_img' => $booking['payment_img'] ?? null
            ];
        }

        response([
            'status' => 'success',
            'data' => $result,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => $totalPages
            ]
        ]);
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in getTotalBookings: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error fetching total bookings: ' . $e->getMessage()]);
    }
}

/**
 * Get appointment history (completed and cancelled appointments)
 */
function getAppointmentHistory()
{
    global $php_fetch;

    try {
        // Get pagination parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Get total count of historical bookings (final states: Completed, Cancelled, Rejected)
        $historyQuery = "SELECT COUNT(*) as total FROM booking WHERE booking_status IN ('Completed', 'Cancelled', 'Rejected')";
        $historyCountResult = $php_fetch($historyQuery);
        $totalCount = $historyCountResult[0]['total'] ?? 0;

        // Calculate total pages
        $totalPages = ceil($totalCount / $limit);

        // Get paginated history bookings
        $query = "SELECT * FROM booking WHERE booking_status IN ('Completed', 'Cancelled', 'Rejected') ORDER BY date_created DESC LIMIT $limit OFFSET $offset";
        $allHistoryBookings = $php_fetch($query);

        if (empty($allHistoryBookings)) {
            response([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $totalPages
                ]
            ]);
        }

        $result = [];
        foreach ($allHistoryBookings as $booking) {
            // Get user details
            $user = null;
            if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                $userResult = $php_fetch('users', 'full_name, contact_number', ['user_id' => $booking['user_id']]);
                $user = $userResult ? $userResult[0] : null;
            }

            // Get booking details and services
            $booking_details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);

            $services = [];
            $serviceNames = [];
            if ($booking_details) {
                foreach ($booking_details as $detail) {
                    $service = $php_fetch('services', 'service_name, price', ['id' => $detail['service_id']]);
                    if ($service) {
                        $serviceName = $service[0]['service_name'];
                        $services[] = [
                            'name' => $serviceName,
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price']
                        ];
                        $serviceNames[] = $serviceName;
                    }
                }
            }

            // Determine completion date (use updated date if available, otherwise creation date)
            $completionDate = $booking['updated_at'] ?? $booking['date_created'] ?? date('Y-m-d H:i:s');

            // Determine correct payment status based on booking status
            $payment_status = 'Unpaid'; // Default
            if ($booking['booking_status'] === 'Completed') {
                $payment_status = 'Paid';
            } else if ($booking['booking_status'] === 'Cancelled' || $booking['booking_status'] === 'Rejected') {
                $payment_status = 'Unpaid';
            }

            // Check if invoice exists for more accurate payment status
            $invoice = $php_fetch('invoices', 'payment_status', ['booking_id' => $booking['bookingid']]);
            if ($invoice && count($invoice) > 0) {
                $payment_status = $invoice[0]['payment_status'];
            }

            $result[] = [
                'bookingid' => $booking['bookingid'],
                'user_name' => $user ? $user['full_name'] : 'Unknown User',
                'services_text' => implode(', ', $serviceNames),
                'services' => $services,
                'total_price' => $booking['total_price'],
                'completion_date' => date('M d, Y g:i A', strtotime($completionDate)),
                'booking_status' => $booking['booking_status'],
                'payment_status' => $payment_status,
                'duration' => calculateSessionDuration($booking['date_created'], $completionDate)
            ];
        }

        response([
            'status' => 'success',
            'data' => $result,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => $totalPages
            ]
        ]);
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in getAppointmentHistory: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error fetching appointment history: ' . $e->getMessage()]);
    }
}

/**
 * Get recovery data and update recovery functions
 */
function getRecoveryData()
{
    global $php_fetch;

    try {
        // Get pagination parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Get total count of recoverable bookings (Cancelled and Rejected)
        $recoverableQuery = "SELECT COUNT(*) as total FROM booking WHERE booking_status IN ('Cancelled', 'Rejected')";
        $recoverableCountResult = $php_fetch($recoverableQuery);
        $recoverableCount = $recoverableCountResult[0]['total'] ?? 0;

        // Get total count of recently recovered bookings (last 30 days)
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $recoveredCountQuery = "SELECT COUNT(*) as total FROM booking WHERE booking_status = 'Confirmed' AND date_created >= '$thirtyDaysAgo'";
        $recoveredCountResult = $php_fetch($recoveredCountQuery);
        $recoveredCount = $recoveredCountResult[0]['total'] ?? 0;

        // Calculate total pages for recoverable bookings
        $recoverableTotalPages = ceil($recoverableCount / $limit);

        // Calculate total pages for recovered bookings
        $recoveredTotalPages = ceil($recoveredCount / $limit);

        // Get paginated recoverable bookings
        $recoverableQuery = "SELECT * FROM booking WHERE booking_status IN ('Cancelled', 'Rejected') ORDER BY date_created DESC LIMIT $limit OFFSET $offset";
        $recoverableBookings = $php_fetch($recoverableQuery);

        // Get paginated recently recovered bookings
        $recoveredQuery = "SELECT * FROM booking WHERE booking_status = 'Confirmed' AND date_created >= '$thirtyDaysAgo' ORDER BY date_created DESC LIMIT $limit OFFSET $offset";
        $recentlyRecovered = $php_fetch($recoveredQuery);

        $result = [
            'recoverable' => [],
            'recently_recovered' => []
        ];

        // Process recoverable bookings
        if ($recoverableBookings) {
            foreach ($recoverableBookings as $booking) {
                // Get user details
                $user = null;
                if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                    $userResult = $php_fetch('users', 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                    $user = $userResult ? $userResult[0] : null;
                }

                // Get booking details and services
                $booking_details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);

                $serviceNames = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch('services', 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $serviceNames[] = $service[0]['service_name'];
                        }
                    }
                }

                $result['recoverable'][] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => $user ? $user['full_name'] : 'Unknown User',
                    'user_email' => $user ? $user['email'] : '',
                    'services_text' => implode(', ', $serviceNames),
                    'total_price' => $booking['total_price'],
                    'cancelled_date' => date('M d, Y g:i A', strtotime($booking['date_created'])),
                    'recovery_potential' => calculateRecoveryPotential($booking),
                    'can_recover' => true
                ];
            }
        }

        // Process recently recovered bookings
        if ($recentlyRecovered) {
            foreach ($recentlyRecovered as $booking) {
                // Get user details
                $user = null;
                if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                    $userResult = $php_fetch('users', 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                    $user = $userResult ? $userResult[0] : null;
                }

                // Get booking details and services
                $booking_details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);

                $serviceNames = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch('services', 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $serviceNames[] = $service[0]['service_name'];
                        }
                    }
                }

                $result['recently_recovered'][] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => $user ? $user['full_name'] : 'Unknown User',
                    'user_email' => $user ? $user['email'] : '',
                    'services_text' => implode(', ', $serviceNames),
                    'total_price' => $booking['total_price'],
                    'recovery_date' => date('M d, Y g:i A', strtotime($booking['updated_at'] ?? $booking['date_created'])),
                    'booking_status' => $booking['booking_status'],
                    'recovery_value' => $booking['total_price'] // Using total price as recovery value
                ];
            }
        }

        response([
            'status' => 'success',
            'data' => $result,
            'pagination' => [
                'recoverable' => [
                    'total' => $recoverableCount,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $recoverableTotalPages
                ],
                'recovered' => [
                    'total' => $recoveredCount,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $recoveredTotalPages
                ]
            ]
        ]);
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in getRecoveryData: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error fetching recovery data: ' . $e->getMessage()]);
    }
}

/**
 * Update booking status
 */
function updateBookingStatus()
{
    global $php_fetch;

    try {
        $bookingId = $_POST['booking_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$bookingId || !$newStatus) {
            response(['status' => 'error', 'message' => 'Missing booking ID or status']);
        }

        // Update booking status
        $result = $php_fetch('booking', 'UPDATE', [
            'booking_status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['bookingid' => $bookingId]);

        if ($result) {
            response(['status' => 'success', 'message' => 'Booking status updated successfully']);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update booking status']);
        }
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in updateBookingStatus: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error updating booking status: ' . $e->getMessage()]);
    }
}

/**
 * Get therapist status
 */
function getTherapistStatus()
{
    global $php_fetch;

    try {
        // Check if therapists table exists
        $therapists = $php_fetch('therapists', '*', []);

        if (!$therapists) {
            response(['status' => 'error', 'message' => 'No therapists found']);
        }

        $result = [];
        foreach ($therapists as $therapist) {
            $result[] = [
                'id' => $therapist['id'],
                'name' => $therapist['name'] ?? 'Unknown',
                'status' => $therapist['status'] ?? 'Unavailable',
                'specialization' => $therapist['specialization'] ?? '',
                'image' => $therapist['image'] ?? null
            ];
        }

        response(['status' => 'success', 'data' => $result]);
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in getTherapistStatus: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error fetching therapist status: ' . $e->getMessage()]);
    }
}

/**
 * Update therapist status
 */
function updateTherapistStatus()
{
    global $php_fetch;

    try {
        $therapistId = $_POST['therapist_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$therapistId || !$newStatus) {
            response(['status' => 'error', 'message' => 'Missing therapist ID or status']);
        }

        // Update therapist status
        $result = $php_fetch('therapists', 'UPDATE', [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $therapistId]);

        if ($result) {
            response(['status' => 'success', 'message' => 'Therapist status updated successfully']);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update therapist status']);
        }
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in updateTherapistStatus: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error updating therapist status: ' . $e->getMessage()]);
    }
}

/**
 * Toggle all therapists status
 */
function toggleAllTherapists()
{
    global $php_fetch;

    try {
        $newStatus = $_POST['status'] ?? null;

        if (!$newStatus) {
            response(['status' => 'error', 'message' => 'Missing status']);
        }

        // Update all therapists status
        $result = $php_fetch('therapists', 'UPDATE', [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], []);

        if ($result) {
            response(['status' => 'success', 'message' => 'All therapists status updated successfully']);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update therapists status']);
        }
    } catch (Exception $e) {
        // Log the exception
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Exception in toggleAllTherapists: " . $e->getMessage() . "\n", FILE_APPEND);

        response(['status' => 'error', 'message' => 'Error updating therapists status: ' . $e->getMessage()]);
    }
}
