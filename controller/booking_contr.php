<?php
require_once '../model/booking_model.php';
require_once '../config/connection.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize model - the real DB functions are now loaded from connection.php
$BookingModel = new BookingModel();
// $php_insert, $php_update, $php_fetch are now available from connection.php

function response($data) {
    echo json_encode($data);
    exit;
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

            file_put_contents('debug_booking.txt', print_r($bookingData, true));

            if (isset($bookingData['bookingid'])) {
                $bookingId = $bookingData['bookingid'];

                foreach ($services as $service) {
                    $BookingModel->addBookingDetail($php_insert, 'booking_details', [
                        'booking_id' => $bookingId,
                        'service_id' => $service['id'],
                        'quantity' => $service['people'],
                        'price' => $service['price']
                    ]);
                }

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

            response($BookingModel->updateBookingStatus(
                $php_update,
                'booking',
                $_POST['bookingid'],
                $_POST['booking_status']
            ));
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

            response($BookingModel->addBookingDetailTransaction(
                $php_insert,
                'booking_details_transaction',
                [
                    'bookingdetails_id' => $_POST['bookingdetails_id'],
                    'status' => $_POST['status'],
                    'notes' => $_POST['notes'],
                    'date_from' => $_POST['date_from'],
                    'date_to' => $_POST['date_to']
                ]
            ));
            break;

        case 'get_user_bookings':
            if (!isset($_POST['user_id'])) {
                response(['status' => 'error', 'message' => 'Missing user_id']);
            }

            response($BookingModel->getBookingsByUser(
                $php_fetch,
                'booking',
                $_POST['user_id']
            ));
            break;

        // Admin booking management actions
        case 'get_admin_booking_requests':
            try {
                $result = $BookingModel->getAdminBookingRequests($php_fetch, 'booking', 'users', 'booking_details', 'services');
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_requests: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_admin_booking_accepted':
            try {
                $result = $BookingModel->getAdminBookingAccepted($php_fetch, 'booking', 'users', 'booking_details', 'services');
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_accepted: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_booking_details_admin':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->getBookingDetailsForAdmin($php_fetch, 'booking', 'users', 'booking_details', 'services', $bookingid);
            response($result);
            break;

        case 'accept_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Confirmed');
            response(json_decode($result, true));
            break;

        case 'decline_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Rejected');
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

<?php
require_once '../model/booking_model.php';
require_once '../config/connection.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize model - the real DB functions are now loaded from connection.php
$BookingModel = new BookingModel();
// $php_insert, $php_update, $php_fetch are now available from connection.php

function response($data) {
    echo json_encode($data);
    exit;
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

            file_put_contents('debug_booking.txt', print_r($bookingData, true));

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

            response($BookingModel->updateBookingStatus(
                $php_update,
                'booking',
                $_POST['bookingid'],
                $_POST['booking_status']
            ));
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

            response($BookingModel->addBookingDetailTransaction(
                $php_insert,
                'booking_details_transaction',
                [
                    'bookingdetails_id' => $_POST['bookingdetails_id'],
                    'status' => $_POST['status'],
                    'notes' => $_POST['notes'],
                    'date_from' => $_POST['date_from'],
                    'date_to' => $_POST['date_to']
                ]
            ));
            break;

        case 'get_user_bookings':
            if (!isset($_POST['user_id'])) {
                response(['status' => 'error', 'message' => 'Missing user_id']);
            }

            response($BookingModel->getBookingsByUser(
                $php_fetch,
                'booking',
                $_POST['user_id']
            ));
            break;

        // Admin booking management actions
        case 'get_admin_booking_requests':
            try {
                $result = $BookingModel->getAdminBookingRequests($php_fetch, 'booking', 'users', 'booking_details', 'services');
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_requests: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_admin_booking_accepted':
            try {
                $result = $BookingModel->getAdminBookingAccepted($php_fetch, 'booking', 'users', 'booking_details', 'services');
                response($result);
            } catch (Exception $e) {
                error_log("Error in get_admin_booking_accepted: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_booking_details_admin':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->getBookingDetailsForAdmin($php_fetch, 'booking', 'users', 'booking_details', 'services', $bookingid);
            response($result);
            break;

        case 'accept_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Confirmed');
            response(json_decode($result, true));
            break;

        case 'decline_booking':
            $bookingid = $_POST['bookingid'] ?? null;
            if (!$bookingid) {
                response(['status' => 'error', 'message' => 'Booking ID is required']);
            }
            $result = $BookingModel->updateBookingStatus($php_update, 'booking', $bookingid, 'Rejected');
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
