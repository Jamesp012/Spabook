<?php
require_once '../model/booking_model.php';
require_once '../config/connection.php';
require_once '../utils/cache.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize cache
Cache::init();

// Check if notification table exists before including notification controller
$notificationTableExists = false;
try {
    $query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'notification'
    ) as exists";
    
    $result = $php_fetch($query);
    if (isset($result[0]['exists']) && $result[0]['exists'] === true) {
        $notificationTableExists = true;
        require_once '../controller/notification_contr.php';
    }
} catch (Exception $e) {
    // Silently fail if we can't check for the notification table
}

// Initialize model - the real DB functions are now loaded from connection.php
$BookingModel = new BookingModel();
// $php_insert, $php_update, $php_fetch are now available from connection.php

function response($data) {
    echo json_encode($data);
    exit;
}

/**
 * Create a booking status notification for the user
 */
function createBookingStatusNotification($user_id, $booking_id, $status) {
    global $php_insert, $notificationTableExists;
    
    if (!$notificationTableExists) return;
    
    $titles = [
        'Pending' => 'Booking Request Submitted',
        'Confirmed' => 'Booking Confirmed! 🎉',
        'Rejected' => 'Booking Request Declined',
        'Cancelled' => 'Booking Cancelled',
        'Completed' => 'Spa Session Completed ✨'
    ];
    
    $messages = [
        'Pending' => "Your booking request #$booking_id has been submitted and is awaiting admin approval. We'll notify you once it's reviewed.",
        'Confirmed' => "Great news! Your booking #$booking_id has been confirmed. Get ready for your relaxing spa experience!",
        'Rejected' => "We're sorry, but your booking request #$booking_id could not be confirmed at this time. Please contact us for alternative options.",
        'Cancelled' => "Your booking #$booking_id has been cancelled. If you didn't request this, please contact our support team.",
        'Completed' => "Thank you for choosing our spa! Your session from booking #$booking_id has been completed. We hope you enjoyed your experience!"
    ];
    
    $types = [
        'Pending' => 'info',
        'Confirmed' => 'success',
        'Rejected' => 'warning',
        'Cancelled' => 'warning',
        'Completed' => 'success'
    ];
    
    $php_insert('notification', [
        'user_id' => $user_id,
        'title' => $titles[$status] ?? "Booking Update",
        'message' => $messages[$status] ?? "Your booking #$booking_id status has been updated to: $status",
        'type' => $types[$status] ?? 'info',
        'metadata' => json_encode(['booking_id' => $booking_id, 'status' => $status])
    ]);
}

/**
 * Create a notification for admin about new booking
 */
function createAdminBookingNotification($booking_id, $user_name) {
    global $php_fetch, $php_insert, $notificationTableExists;
    
    if (!$notificationTableExists) return;
    
    // Get all admin users
    $admins = $php_fetch('users', 'user_id', ['role' => 'Admin']);
    
    foreach ($admins as $admin) {
        $php_insert('notification', [
            'user_id' => $admin['user_id'],
            'title' => 'New Booking Request',
            'message' => "$user_name has submitted a new booking request #$booking_id that requires your review.",
            'type' => 'info',
            'metadata' => json_encode(['booking_id' => $booking_id, 'type' => 'admin_new_booking'])
        ]);
    }
}

/**
 * Create appointment reminder notifications (1 day and 1 hour before)
 */
function createAppointmentReminders() {
    global $php_fetch, $php_insert, $notificationTableExists;
    
    if (!$notificationTableExists) return;
    
    date_default_timezone_set('Asia/Manila');
    
    // Get confirmed bookings that need reminders
    $query = "
        SELECT DISTINCT
            b.bookingid,
            b.user_id,
            bd.booking_date,
            bd.booking_time,
            STRING_AGG(s.service_name, ', ') as services,
            CONCAT(bd.booking_date, ' ', bd.booking_time) as appointment_datetime
        FROM booking b
        JOIN booking_details bd ON b.bookingid = bd.booking_id
        JOIN services s ON bd.service_id = s.id
        WHERE b.booking_status = 'Confirmed'
            AND bd.booking_date IS NOT NULL
            AND bd.booking_time IS NOT NULL
            AND bd.booking_date >= CURRENT_DATE
        GROUP BY b.bookingid, b.user_id, bd.booking_date, bd.booking_time
    ";
    
    $upcomingBookings = $php_fetch($query);
    
    foreach ($upcomingBookings as $booking) {
        $appointmentDateTime = strtotime($booking['appointment_datetime']);
        $now = time();
        $timeDiff = $appointmentDateTime - $now;
        
        $bookingId = $booking['bookingid'];
        $userId = $booking['user_id'];
        
        // Check if we've already sent reminders for this booking
        $existingReminders = $php_fetch('notification', 'notificationid', [
            'user_id' => $userId,
            'metadata' => json_encode(['booking_id' => $bookingId, 'type' => 'reminder'])
        ]);
        
        // 1 day reminder (between 23-25 hours before)
        if ($timeDiff >= 23 * 3600 && $timeDiff <= 25 * 3600) {
            $dayReminderExists = false;
            foreach ($existingReminders as $reminder) {
                $reminderMeta = json_decode($reminder['metadata'] ?? '{}', true);
                if (isset($reminderMeta['reminder_type']) && $reminderMeta['reminder_type'] === '1_day') {
                    $dayReminderExists = true;
                    break;
                }
            }
            
            if (!$dayReminderExists) {
                $php_insert('notification', [
                    'user_id' => $userId,
                    'title' => 'Spa Appointment Tomorrow! 🌸',
                    'message' => "Don't forget! You have a spa appointment tomorrow at " . date('g:i A', strtotime($booking['booking_time'])) . " for: " . $booking['services'] . ". We can't wait to pamper you!",
                    'type' => 'info',
                    'metadata' => json_encode([
                        'booking_id' => $bookingId,
                        'type' => 'reminder',
                        'reminder_type' => '1_day',
                        'appointment_date' => $booking['booking_date'],
                        'appointment_time' => $booking['booking_time']
                    ])
                ]);
            }
        }
        
        // 1 hour reminder (between 50-70 minutes before)
        if ($timeDiff >= 50 * 60 && $timeDiff <= 70 * 60) {
            $hourReminderExists = false;
            foreach ($existingReminders as $reminder) {
                $reminderMeta = json_decode($reminder['metadata'] ?? '{}', true);
                if (isset($reminderMeta['reminder_type']) && $reminderMeta['reminder_type'] === '1_hour') {
                    $hourReminderExists = true;
                    break;
                }
            }
            
            if (!$hourReminderExists) {
                $php_insert('notification', [
                    'user_id' => $userId,
                    'title' => 'Spa Appointment in 1 Hour! ⏰',
                    'message' => "Your spa appointment is in about 1 hour at " . date('g:i A', strtotime($booking['booking_time'])) . ". Time to start relaxing! Services: " . $booking['services'],
                    'type' => 'warning',
                    'metadata' => json_encode([
                        'booking_id' => $bookingId,
                        'type' => 'reminder',
                        'reminder_type' => '1_hour',
                        'appointment_date' => $booking['booking_date'],
                        'appointment_time' => $booking['booking_time']
                    ])
                ]);
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if (!$action) {
        response(['status' => 'error', 'message' => 'No POST action specified']);
    }

    switch ($action) {
        case 'create_booking':
            $user_id = $_POST['user_id'] ?? null;
            $total_price = $_POST['total_price'] ?? null;
            $payment_img = $_POST['payment_img'] ?? null;
            $services = isset($_POST['services']) ? json_decode($_POST['services'], true) : [];

            if (!$user_id || !$total_price || !$payment_img || empty($services)) {
                response(['status' => 'error', 'message' => 'Missing booking or services data']);
            }

            $bookingData = $BookingModel->createBooking($php_insert, 'booking', [
                'user_id' => $user_id,
                'total_price' => $total_price,
                'payment_status' => true,
                'payment_img' => $payment_img,
                'booking_status' => 'Pending'
                // date_created will be set automatically by Supabase DEFAULT NOW()
            ]);

            // file_put_contents('debug_booking.txt', print_r($bookingData, true));

            if (isset($bookingData['bookingid'])) {
                $bookingId = $bookingData['bookingid'];

                foreach ($services as $service) {
                    // If service has therapist assignments, create multiple booking details (one per person/therapist)
                    if (isset($service['therapists']) && is_array($service['therapists']) && count($service['therapists']) > 0) {
                        // Create separate booking detail for each person with their assigned therapist
                        for ($person = 1; $person <= $service['people']; $person++) {
                            // Find therapist for this person
                            $assignedTherapist = null;
                            foreach ($service['therapists'] as $therapist) {
                                if ($therapist['person'] == $person) {
                                    $assignedTherapist = $therapist;
                                    break;
                                }
                            }
                            
                            $BookingModel->addBookingDetail($php_insert, 'booking_details', [
                                'booking_id' => $bookingId,
                                'service_id' => $service['id'],
                                'quantity' => 1, // 1 person per detail when therapists are assigned
                                'price' => $service['price'],
                                'therapist_id' => $assignedTherapist ? $assignedTherapist['therapistId'] : null,
                                'person_number' => $person,
                                'booking_date' => $service['selectedDate'] ?? null,
                                'booking_time' => $service['selectedTime'] ?? null
                            ]);
                        }
                    } else {
                        // No specific therapist assignments, create single booking detail
                        $BookingModel->addBookingDetail($php_insert, 'booking_details', [
                            'booking_id' => $bookingId,
                            'service_id' => $service['id'],
                            'quantity' => $service['people'],
                            'price' => $service['price'],
                            'therapist_id' => null,
                            'person_number' => null,
                            'booking_date' => $service['selectedDate'] ?? null,
                            'booking_time' => $service['selectedTime'] ?? null
                        ]);
                    }
                }
                
                // Create notification for user if notification system is available
                if ($notificationTableExists) {
                    // Get user name for admin notification using parameterized query
                    $userData = $php_fetch('users', 'first_name, last_name', ['user_id' => $user_id]);
                    $userName = isset($userData[0]) ? $userData[0]['first_name'] . ' ' . $userData[0]['last_name'] : 'A customer';
                    
                    // Create user notification
                    createBookingStatusNotification($user_id, $bookingId, 'Pending');
                    
                    // Send admin notification
                    createAdminBookingNotification($bookingId, $userName);
                }
                
                // Invalidate caches
                Cache::delete("user_bookings_$user_id");
                Cache::delete("admin_booking_requests");
                Cache::delete("admin_booking_accepted");

                response(['status' => 'success', 'bookingid' => $bookingId]);
            } else {
                response(['status' => 'error', 'message' => 'Booking creation failed']);
            }
            break;


        case 'addBookingDetail':
            if (!isset($_POST['booking_id'], $_POST['service_id'], $_POST['quantity'], $_POST['price'])) {
                response(['status' => 'error', 'message' => 'Missing booking detail fields']);
            }

            response($BookingModel->addBookingDetail(
                $php_insert,
                'booking_details',
                [
                    'booking_id' => $_POST['booking_id'],
                    'service_id' => $_POST['service_id'],
                    'quantity' => $_POST['quantity'],
                    'price' => $_POST['price']
                ]
            ));
            break;

        case 'updateBookingStatus':
            if (!isset($_POST['bookingid'], $_POST['booking_status'])) {
                response(['status' => 'error', 'message' => 'Missing bookingid or booking_status']);
            }
            
            $bookingId = $_POST['bookingid'];
            $newStatus = $_POST['booking_status'];
            
            // Get user_id for the booking
            $bookingData = $php_fetch('booking', 'user_id', ['bookingid' => $bookingId]);
            
            $result = $BookingModel->updateBookingStatus(
                $php_update,
                'booking',
                $bookingId,
                $newStatus
            );
            
            // Create notification for the user if booking exists and notification system is available
            if ($notificationTableExists && isset($bookingData[0]['user_id'])) {
                $userId = $bookingData[0]['user_id'];
                createBookingStatusNotification($userId, $bookingId, $newStatus);
                
                // Invalidate user's booking caches
                Cache::delete("user_bookings_$userId");
                Cache::delete("user_booking_history_$userId");
            }

            // Auto-generate invoice on confirmation
            if (strtolower($newStatus) === 'confirmed') {
                try {
                    // Check if invoice exists
                    $existing = $php_fetch('invoices', '*', ['booking_id' => $bookingId]);
                    if (!$existing || count($existing) === 0) {
                        // Get booking and user
                        $bookingRow = $php_fetch('booking', '*', ['bookingid' => $bookingId]);
                        $userIdForInvoice = $bookingRow[0]['user_id'] ?? ($bookingData[0]['user_id'] ?? null);

                        // Create invoice base row
                        $invoice = $php_insert('invoices', [
                            'booking_id' => $bookingId,
                            'user_id' => $userIdForInvoice,
                            'subtotal' => 0,
                            'discount' => 0,
                            'total' => 0,
                            'payment_status' => 'Unpaid',
                            'issued_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $invoiceId = $invoice['invoice_id'] ?? null;
                        if ($invoiceId) {
                            // Load booking details and services for line items
                            $details = $php_fetch('booking_details', '*', ['booking_id' => $bookingId]);
                            $subtotal = 0;
                            foreach ($details as $d) {
                                $service = $php_fetch('services', '*', ['id' => $d['service_id']]);
                                $serviceName = $service[0]['service_name'] ?? ('Service #' . $d['service_id']);
                                $qty = intval($d['quantity'] ?? 1);
                                $unit = floatval($d['price'] ?? 0);
                                $line = $qty * $unit;
                                $subtotal += $line;
                                $php_insert('invoice_items', [
                                    'invoice_id' => $invoiceId,
                                    'booking_detail_id' => $d['bookingdetailsid'] ?? null,
                                    'service_id' => $d['service_id'],
                                    'description' => $serviceName,
                                    'quantity' => $qty,
                                    'unit_price' => $unit,
                                    'line_total' => $line
                                ]);
                            }
                            // Update invoice totals
                            $php_update('invoices', [
                                'subtotal' => $subtotal,
                                'discount' => 0,
                                'total' => $subtotal,
                                'updated_at' => date('Y-m-d H:i:s')
                            ], ['invoice_id' => $invoiceId]);
                        }
                    }
                } catch (Exception $e) {
                    error_log('Invoice generation error: ' . $e->getMessage());
                }
            }

            // Invalidate admin booking caches
            Cache::delete("admin_booking_requests");
            Cache::delete("admin_booking_accepted");
            Cache::delete("booking_details_admin_$bookingId");
            
            response($result);
            break;

        case 'uploadPayment':
            if (!isset($_POST['bookingid'], $_POST['payment_img'])) {
                response(['status' => 'error', 'message' => 'Missing bookingid or payment_img']);
            }

            response($BookingModel->uploadPayment(
                $php_update,
                'booking',
                $_POST['bookingid'],
                $_POST['payment_img']
            ));
            break;

        case 'updateBookingDetailStatus':
            if (!isset($_POST['bookingdetailsid'], $_POST['status'])) {
                response(['status' => 'error', 'message' => 'Missing booking detail ID or status']);
            }

            response($BookingModel->updateBookingDetailStatus(
                $php_update,
                'booking_details',
                $_POST['bookingdetailsid'],
                $_POST['status']
            ));
            break;

        case 'addBookingDetailTransaction':
            if (!isset($_POST['bookingdetails_id'], $_POST['status'], $_POST['notes'], $_POST['date_from'], $_POST['date_to'])) {
                response(['status' => 'error', 'message' => 'Missing transaction fields']);
            }

            $result = $BookingModel->addBookingDetailTransaction(
                $php_insert,
                'booking_details_transaction',
                [
                    'bookingdetails_id' => $_POST['bookingdetails_id'],
                    'status' => $_POST['status'],
                    'notes' => $_POST['notes'],
                    'date_from' => $_POST['date_from'],
                    'date_to' => $_POST['date_to']
                ]
            );
            // Compute commission based on actual hours if therapist exists
            try {
                $detailId = $_POST['bookingdetails_id'];
                $detailRow = $php_fetch('booking_details', '*', ['bookingdetailsid' => $detailId]);
                $therapistId = $detailRow[0]['therapist_id'] ?? null;
                if ($therapistId) {
                    $from = strtotime($_POST['date_from']);
                    $to = strtotime($_POST['date_to']);
                    if ($from && $to && $to > $from) {
                        $hours = ($to - $from) / 3600.0;
                        $rate = 50.0;
                        $amount = round($hours * $rate, 2);
                        // Upsert-like: if exists update, else insert
                        $existing = $php_fetch('therapist_commissions', '*', ['booking_detail_id' => $detailId, 'therapist_id' => $therapistId]);
                        if ($existing && count($existing) > 0) {
                            $php_update('therapist_commissions', [
                                'hours' => $hours,
                                'rate_per_hour' => $rate,
                                'commission_amount' => $amount,
                                'computed_at' => date('Y-m-d H:i:s')
                            ], ['commission_id' => $existing[0]['commission_id']]);
                        } else {
                            $php_insert('therapist_commissions', [
                                'booking_detail_id' => $detailId,
                                'therapist_id' => $therapistId,
                                'hours' => $hours,
                                'rate_per_hour' => $rate,
                                'commission_amount' => $amount
                            ]);
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('Commission compute error: ' . $e->getMessage());
            }
            response($result);
            break;

        case 'get_user_bookings':
            if (!isset($_POST['user_id'])) {
                response(['status' => 'error', 'message' => 'Missing user_id']);
            }
            
            $user_id = $_POST['user_id'];
            
            // Cache user bookings for 30 seconds
            $cacheKey = "user_bookings_$user_id";
            $result = Cache::remember($cacheKey, function() use ($BookingModel, $php_fetch, $user_id) {
                return $BookingModel->getBookingsByUser($php_fetch, 'booking', $user_id);
            }, 30);

            response($result);
            break;

        case 'get_user_booking_history':
            if (!isset($_POST['user_id'])) {
                response(['status' => 'error', 'message' => 'Missing user_id']);
            }
            
            $user_id = $_POST['user_id'];
            
            // Cache user booking history for 60 seconds (longer since history changes less frequently)
            $cacheKey = "user_booking_history_$user_id";
            $result = Cache::remember($cacheKey, function() use ($BookingModel, $php_fetch, $user_id) {
                return $BookingModel->getUserBookingHistory($php_fetch, 'booking', $user_id);
            }, 60);

            response($result);
            break;

        // Admin booking management actions
        case 'get_admin_booking_requests':
            try {
                // Cache admin booking requests for 30 seconds
                // This is a frequently accessed endpoint that's expensive to compute
                $cacheKey = "admin_booking_requests";
                $result = Cache::remember($cacheKey, function() use ($BookingModel, $php_fetch) {
                    return $BookingModel->getAdminBookingRequests($php_fetch);
                }, 30);
                
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_requests: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_admin_booking_accepted':
            try {
                // Cache admin accepted bookings for 30 seconds
                $cacheKey = "admin_booking_accepted";
                $result = Cache::remember($cacheKey, function() use ($BookingModel, $php_fetch) {
                    return $BookingModel->getAdminBookingAccepted($php_fetch);
                }, 30);
                
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_accepted: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_booking_details_admin':
            $bookingid = $_POST['bookingid'] ?? null;
            
            // Log the request (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - get_booking_details_admin request for booking ID: $bookingid\n", FILE_APPEND);
            
            if (!$bookingid) {
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Error: Booking ID is required\n", FILE_APPEND);
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            
            try {
                // Get booking details directly without caching for debugging
                $result = $BookingModel->getBookingDetailsForAdmin($php_fetch, $bookingid);
                
                // Log the result (disabled in production)
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - get_booking_details_admin result: " . json_encode($result) . "\n", FILE_APPEND);
                
                response($result);
            } catch (Exception $e) {
                // Log the error (disabled in production)
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Error in get_booking_details_admin: " . $e->getMessage() . "\n", FILE_APPEND);
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_sales_summary':
            // period: daily|weekly|monthly; default daily
            $period = $_POST['period'] ?? 'daily';
            $now = date('Y-m-d');
            $start = $now;
            if ($period === 'weekly') { $start = date('Y-m-d', strtotime('monday this week')); }
            if ($period === 'monthly') { $start = date('Y-m-01'); }
            try {
                // Sales = sum(invoice.total) for invoices issued since start
                $salesQuery = "SELECT COALESCE(SUM(total),0) AS total_sales FROM invoices WHERE issued_at >= '$start'";
                $salesRes = $php_fetch($salesQuery);
                $totalSales = $salesRes[0]['total_sales'] ?? 0;

                // Commissions = sum(therapist_commissions.commission_amount) for booking_details linked to bookings created since start
                $commQuery = "SELECT COALESCE(SUM(tc.commission_amount),0) AS total_commission
                               FROM therapist_commissions tc
                               JOIN booking_details bd ON bd.bookingdetailsid = tc.booking_detail_id
                               JOIN booking b ON b.bookingid = bd.booking_id
                               WHERE b.date_created >= '$start'";
                $commRes = $php_fetch($commQuery);
                $totalCommission = $commRes[0]['total_commission'] ?? 0;

                response(['status' => 'success', 'period' => $period, 'start' => $start, 'sales' => (float)$totalSales, 'commission' => (float)$totalCommission, 'net' => (float)$totalSales - (float)$totalCommission]);
            } catch (Exception $e) {
                response(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'get_therapist_commissions':
            // Get detailed therapist commission data for the sales report
            $period = $_POST['period'] ?? 'daily';
            $now = date('Y-m-d');
            $start = $now;
            if ($period === 'weekly') { $start = date('Y-m-d', strtotime('monday this week')); }
            if ($period === 'monthly') { $start = date('Y-m-01'); }
            
            try {
                // Get therapist commission details with therapist names
                $therapistQuery = "SELECT 
                    t.therapistid as therapist_id,
                    t.therapist_name as first_name,
                    '' as last_name,
                    COALESCE(SUM(tc.hours), 0) AS total_hours,
                    COALESCE(SUM(tc.commission_amount), 0) AS total_commission,
                    COUNT(DISTINCT CASE WHEN tc.booking_detail_id IS NOT NULL THEN tc.booking_detail_id END) AS services_rendered
                FROM therapist t
                LEFT JOIN therapist_commissions tc ON t.therapistid = tc.therapist_id
                LEFT JOIN booking_details bd ON bd.bookingdetailsid = tc.booking_detail_id
                LEFT JOIN booking b ON b.bookingid = bd.booking_id AND b.date_created >= '$start'
                GROUP BY t.therapistid, t.therapist_name
                ORDER BY total_commission DESC, t.therapist_name ASC";
                
                $therapistRes = $php_fetch($therapistQuery);
                
                response([
                    'status' => 'success', 
                    'period' => $period, 
                    'start' => $start, 
                    'therapists' => $therapistRes
                ]);
            } catch (Exception $e) {
                response(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'update_invoice_payment_status':
            if (!isset($_POST['booking_id'], $_POST['payment_status'])) {
                response(['status' => 'error', 'message' => 'Missing booking_id or payment_status']);
            }
            $bookingId = $_POST['booking_id'];
            $paymentStatus = $_POST['payment_status']; // Unpaid | Down Payment | Paid | Refunded
            try {
                $rows = $php_update('invoices', ['payment_status' => $paymentStatus, 'updated_at' => date('Y-m-d H:i:s')], ['booking_id' => $bookingId]);
                response(['status' => 'success', 'rows' => $rows]);
            } catch (Exception $e) {
                response(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'get_invoice_by_booking':
            $bookingId = $_POST['booking_id'] ?? null;
            if (!$bookingId) { response(['status' => 'error', 'message' => 'booking_id required']); }
            try {
                $inv = $php_fetch('invoices', '*', ['booking_id' => $bookingId]);
                if (!$inv || count($inv) === 0) { response(['status' => 'nodata']); }
                $invoice = $inv[0];
                $items = $php_fetch('invoice_items', '*', ['invoice_id' => $invoice['invoice_id']]);
                response(['status' => 'success', 'invoice' => $invoice, 'items' => $items]);
            } catch (Exception $e) {
                response(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'get_booking_services':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            
            $result = $BookingModel->getBookingServicesForCompletion($php_fetch, $bookingid);
            response($result);
            break;

        case 'update_service_completion':
            $bookingDetailId = $_POST['booking_detail_id'] ?? null;
            $therapistNotes = $_POST['therapist_notes'] ?? null;
            $progressData = isset($_POST['progress_data']) ? json_decode($_POST['progress_data'], true) : null;
            $action = $_POST['completion_action'] ?? null;
            
            if (!$bookingDetailId || !$therapistNotes || !$action) {
                response(['status' => 'error', 'message' => 'Missing required parameters']);
            }
            
            $result = $BookingModel->updateServiceCompletion($php_fetch, $php_update, 'booking_details', 'booking', $bookingDetailId, $therapistNotes, $progressData, $action);
            response($result);
            break;

        case 'get_user_progress':
            $userId = $_POST['user_id'] ?? null;
            if (!$userId) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            
            $result = $BookingModel->getUserProgressData($php_fetch, 'booking', 'booking_details', 'services', $userId);
            response($result);
            break;

        case 'accept_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            
            // Get user_id for the booking
            $bookingData = $php_fetch('booking', 'user_id', ['bookingid' => $bookingid]);
            
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Confirmed');
            
            // Create notification for the user if booking exists and notification system is available
            if ($notificationTableExists && isset($bookingData[0]['user_id'])) {
                $userId = $bookingData[0]['user_id'];
                createBookingStatusNotification($userId, $bookingid, 'Confirmed');
                
                // Invalidate user's booking cache
                Cache::delete("user_bookings_$userId");
            }
            
            // Invalidate admin booking caches
            Cache::delete("admin_booking_requests");
            Cache::delete("admin_booking_accepted");
            Cache::delete("booking_details_admin_$bookingid");
            
            response(json_decode($result, true));
            break;

        case 'decline_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            
            // Get user_id for the booking
            $bookingData = $php_fetch('booking', 'user_id', ['bookingid' => $bookingid]);
            
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Rejected');
            
            // Create notification for the user if booking exists and notification system is available
            if ($notificationTableExists && isset($bookingData[0]['user_id'])) {
                $userId = $bookingData[0]['user_id'];
                createBookingStatusNotification($userId, $bookingid, 'Rejected');
                
                // Invalidate user's booking cache
                Cache::delete("user_bookings_$userId");
            }
            
            // Invalidate admin booking caches
            Cache::delete("admin_booking_requests");
            Cache::delete("admin_booking_accepted");
            Cache::delete("booking_details_admin_$bookingid");
            
            response(json_decode($result, true));
            break;

        case 'complete_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            
            // Get user_id for the booking
            $bookingData = $php_fetch('booking', 'user_id', ['bookingid' => $bookingid]);
            
            // Update booking status to Completed
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Completed');
            
            // Update booking updated_at timestamp
            $php_update('booking', ['updated_at' => date('Y-m-d H:i:s')], ['bookingid' => $bookingid]);
            
            // Create or update invoice to mark as paid
            $invoice = $php_fetch('invoices', '*', ['booking_id' => $bookingid]);
            if ($invoice && count($invoice) > 0) {
                // Update existing invoice
                $php_update('invoices', [
                    'payment_status' => 'Paid',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['booking_id' => $bookingid]);
            } else {
                // Create new invoice if not exists
                $bookingDetails = $php_fetch('booking', '*', ['bookingid' => $bookingid]);
                if ($bookingDetails && count($bookingDetails) > 0) {
                    $booking = $bookingDetails[0];
                    $php_insert('invoices', [
                        'booking_id' => $bookingid,
                        'user_id' => $booking['user_id'],
                        'subtotal' => $booking['total_price'],
                        'total' => $booking['total_price'],
                        'payment_status' => 'Paid',
                        'issued_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // Create notification for the user if booking exists and notification system is available
            if ($notificationTableExists && isset($bookingData[0]['user_id'])) {
                $userId = $bookingData[0]['user_id'];
                createBookingStatusNotification($userId, $bookingid, 'Completed');
                
                // Invalidate user's booking cache
                Cache::delete("user_bookings_$userId");
            }
            
            // Invalidate admin booking caches
            Cache::delete("admin_booking_requests");
            Cache::delete("admin_booking_accepted");
            Cache::delete("admin_booking_history");
            Cache::delete("booking_details_admin_$bookingid");
            
            response(json_decode($result, true));
            break;

        case 'get_user_booking_status':
            $user_id = $_POST['user_id'] ?? null;
            if (!$user_id) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            try {
                $result = $BookingModel->getUserBookingStatus($php_fetch, 'booking', 'booking_details', 'services', $user_id);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_user_booking_status: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_user_recent_services':
            $user_id = $_POST['user_id'] ?? null;
            if (!$user_id) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            try {
                $result = $BookingModel->getUserRecentServices($php_fetch, 'booking', 'booking_details', 'services', $user_id);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_user_recent_services: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_booked_times':
            $date = $_POST['date'] ?? null;
            
            if (!$date) {
                response(['status' => 'error', 'message' => 'Date is required']);
            }
            
            try {
                $result = $BookingModel->getBookedTimes($php_fetch, $date);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_booked_times: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'send_appointment_reminders':
            // This can be called manually or via cron job
            try {
                createAppointmentReminders();
                response(['status' => 'success', 'message' => 'Appointment reminders processed']);
            } catch (Exception $e) {
                response(['status' => 'error', 'message' => 'Error processing reminders: ' . $e->getMessage()]);
            }
            break;

        case 'cancel_booking_user':
            // Allow users to cancel their own pending bookings
            $bookingid = $_POST['bookingid'] ?? null;
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$bookingid || !$user_id) {
                response(['status' => 'error', 'message' => 'Booking ID and User ID are required']);
            }
            
            // Verify the booking belongs to the user and is in a cancellable state
            $bookingData = $php_fetch('booking', '*', ['bookingid' => $bookingid, 'user_id' => $user_id]);
            
            if (!$bookingData || count($bookingData) === 0) {
                response(['status' => 'error', 'message' => 'Booking not found or access denied']);
            }
            
            $currentStatus = strtolower($bookingData[0]['booking_status']);
            if (!in_array($currentStatus, ['pending', 'confirmed'])) {
                response(['status' => 'error', 'message' => 'Booking cannot be cancelled in its current state']);
            }
            
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Cancelled');
            
            // Create notification for the user
            if ($notificationTableExists) {
                createBookingStatusNotification($user_id, $bookingid, 'Cancelled');
                
                // Invalidate user's booking cache
                Cache::delete("user_bookings_$user_id");
                Cache::delete("user_booking_history_$user_id");
            }
            
            // Invalidate admin booking caches
            Cache::delete("admin_booking_requests");
            Cache::delete("admin_booking_accepted");
            
            response(json_decode($result, true));
            break;

        default:
            response(['status' => 'error', 'message' => 'Unknown POST action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if (!$action) {
        response(['status' => 'error', 'message' => 'No GET action specified']);
    }

    switch ($action) {
        case 'getBookingsByUser':
            if (!isset($_GET['user_id'])) {
                response(['status' => 'error', 'message' => 'Missing user_id']);
            }

            response($BookingModel->getBookingsByUser(
                $php_fetch,
                'booking',
                $_GET['user_id']
            ));
            break;

        case 'getBookingDetails':
            if (!isset($_GET['booking_id'])) {
                response(['status' => 'error', 'message' => 'Missing booking_id']);
            }

            response($BookingModel->getBookingDetails(
                $php_fetch,
                'booking_details',
                $_GET['booking_id']
            ));
            break;

        case 'getDetailTransactions':
            if (!isset($_GET['bookingdetails_id'])) {
                response(['status' => 'error', 'message' => 'Missing bookingdetails_id']);
            }

            response($BookingModel->getDetailTransactions(
                $php_fetch,
                'booking_details_transaction',
                $_GET['bookingdetails_id']
            ));
            break;

        default:
            response(['status' => 'error', 'message' => 'Unknown GET action']);
    }
}
