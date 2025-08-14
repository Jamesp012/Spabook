<?php
require_once '../config/connection.php';
require_once '../model/booking_model.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize model
$BookingModel = new BookingModel();

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
}

/**
 * Get overall dashboard statistics
 */
function getDashboardStats() {
    global $php_fetch;
    
    try {
        // Get total bookings count
        $totalBookings = $php_fetch('booking', 'COUNT(*) as count', []);
        $totalBookingsCount = $totalBookings[0]['count'] ?? 0;
        
        // Get accepted bookings count
        $acceptedBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Confirmed']);
        $acceptedBookingsCount = $acceptedBookings[0]['count'] ?? 0;
        
        // Get pending bookings count
        $pendingBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Pending']);
        $pendingBookingsCount = $pendingBookings[0]['count'] ?? 0;
        
        // Get completed appointments (assuming 'Completed' status exists)
        $completedBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Completed']);
        $completedBookingsCount = $completedBookings[0]['count'] ?? 0;
        
        // Get cancelled bookings
        $cancelledBookings = $php_fetch('booking', 'COUNT(*) as count', ['booking_status' => 'Cancelled']);
        $cancelledBookingsCount = $cancelledBookings[0]['count'] ?? 0;
        
        // Calculate recovery rate (completed vs total)
        $recoveryRate = $totalBookingsCount > 0 ? round(($completedBookingsCount / $totalBookingsCount) * 100, 1) : 0;
        
        response([
            'status' => 'success',
            'data' => [
                'total_bookings' => $totalBookingsCount,
                'accepted_bookings' => $acceptedBookingsCount,
                'pending_bookings' => $pendingBookingsCount,
                'completed_bookings' => $completedBookingsCount,
                'cancelled_bookings' => $cancelledBookingsCount,
                'recovery_rate' => $recoveryRate,
                'recovery_count' => $completedBookingsCount
            ]
        ]);
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error fetching dashboard stats: ' . $e->getMessage()]);
    }
}

/**
 * Get detailed total bookings data
 */
function getTotalBookings() {
    global $php_fetch, $BookingModel;
    
    try {
        // Get all bookings with user and service details
        $bookings = $php_fetch('booking', '*', []);
        
        if (!$bookings || count($bookings) === 0) {
            response(['status' => 'success', 'data' => []]);
        }
        
        $result = [];
        foreach ($bookings as $booking) {
            // Get user details
            $user = null;
            if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                $userResult = $php_fetch('users', 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
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
            
            $result[] = [
                'bookingid' => $booking['bookingid'],
                'user_name' => $user ? $user['full_name'] : 'Unknown User',
                'user_email' => $user ? $user['email'] : '',
                'user_phone' => $user ? $user['contact_number'] : '',
                'services_text' => implode(', ', $serviceNames),
                'services' => $services,
                'total_price' => $booking['total_price'],
                'booking_date' => date('M d, Y g:i A', strtotime($booking['date_created'] ?? 'now')),
                'booking_status' => $booking['booking_status'],
                'payment_status' => $booking['payment_status'] ? 'Paid' : 'Unpaid',
                'payment_img' => $booking['payment_img'] ?? null
            ];
        }
        
        // Sort by date (newest first)
        usort($result, function($a, $b) {
            return strtotime($b['booking_date']) - strtotime($a['booking_date']);
        });
        
        response(['status' => 'success', 'data' => $result]);
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error fetching total bookings: ' . $e->getMessage()]);
    }
}

/**
 * Get appointment history (completed and cancelled appointments)
 */
function getAppointmentHistory() {
    global $php_fetch;
    
    try {
        // Get completed and cancelled bookings
        $completedBookings = $php_fetch('booking', '*', ['booking_status' => 'Completed']);
        $cancelledBookings = $php_fetch('booking', '*', ['booking_status' => 'Cancelled']);
        
        $allHistoryBookings = array_merge($completedBookings ?: [], $cancelledBookings ?: []);
        
        if (empty($allHistoryBookings)) {
            response(['status' => 'success', 'data' => []]);
        }
        
        $result = [];
        foreach ($allHistoryBookings as $booking) {
            // Get user details
            $user = null;
            if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                $userResult = $php_fetch('users', 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
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
            
            $result[] = [
                'bookingid' => $booking['bookingid'],
                'user_name' => $user ? $user['full_name'] : 'Unknown User',
                'user_email' => $user ? $user['email'] : '',
                'services_text' => implode(', ', $serviceNames),
                'services' => $services,
                'total_price' => $booking['total_price'],
                'completion_date' => date('M d, Y g:i A', strtotime($completionDate)),
                'booking_status' => $booking['booking_status'],
                'payment_status' => $booking['payment_status'] ? 'Paid' : 'Unpaid',
                'duration' => calculateSessionDuration($booking['date_created'], $completionDate)
            ];
        }
        
        // Sort by completion date (newest first)
        usort($result, function($a, $b) {
            return strtotime($b['completion_date']) - strtotime($a['completion_date']);
        });
        
        response(['status' => 'success', 'data' => $result]);
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error fetching appointment history: ' . $e->getMessage()]);
    }
}

/**
 * Get recovery data and update recovery functions
 */
function getRecoveryData() {
    global $php_fetch;
    
    try {
        // Get bookings that can be recovered (cancelled or failed)
        $recoverableBookings = $php_fetch('booking', '*', ['booking_status' => 'Cancelled']);
        
        // Get recently recovered bookings (those that were updated from cancelled to confirmed)
        $recentlyRecovered = $php_fetch('booking', '*', ['booking_status' => 'Confirmed']);
        
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
        
        // Process recently recovered bookings (limit to last 30 days)
        if ($recentlyRecovered) {
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            
            foreach ($recentlyRecovered as $booking) {
                $bookingDate = $booking['date_created'] ?? date('Y-m-d');
                
                if ($bookingDate >= $thirtyDaysAgo) {
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
                        'services_text' => implode(', ', $serviceNames),
                        'total_price' => $booking['total_price'],
                        'recovered_date' => date('M d, Y g:i A', strtotime($bookingDate)),
                        'recovery_value' => $booking['total_price']
                    ];
                }
            }
        }
        
        response(['status' => 'success', 'data' => $result]);
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error fetching recovery data: ' . $e->getMessage()]);
    }
}

/**
 * Update booking status for recovery
 */
function updateBookingStatus() {
    global $php_update;
    
    $booking_id = $_POST['booking_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;
    
    if (!$booking_id || !$new_status) {
        response(['status' => 'error', 'message' => 'Missing booking ID or status']);
    }
    
    try {
        $result = $php_update('booking', 
            ['booking_status' => $new_status, 'updated_at' => date('Y-m-d H:i:s')], 
            ['bookingid' => $booking_id]
        );
        
        if ($result) {
            response(['status' => 'success', 'message' => 'Booking status updated successfully']);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update booking status']);
        }
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error updating booking status: ' . $e->getMessage()]);
    }
}

/**
 * Helper function to calculate session duration
 */
function calculateSessionDuration($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    
    if ($interval->days > 0) {
        return $interval->days . ' days';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hours';
    } else {
        return $interval->i . ' minutes';
    }
}

/**
 * Helper function to calculate recovery potential
 */
function calculateRecoveryPotential($booking) {
    $total_price = floatval($booking['total_price'] ?? 0);
    $payment_status = $booking['payment_status'] ?? false;
    $booking_date = $booking['date_created'] ?? date('Y-m-d');
    
    // Calculate days since booking
    $daysSinceBooking = (time() - strtotime($booking_date)) / (60 * 60 * 24);
    
    // Base recovery potential on payment status, booking value, and recency
    $potential = 'Low';
    
    if ($payment_status && $total_price > 1000 && $daysSinceBooking < 7) {
        $potential = 'High';
    } elseif ($payment_status || $total_price > 500) {
        $potential = 'Medium';
    }
    
    return $potential;
}

/**
 * Get therapist status data for management
 */
function getTherapistStatus() {
    global $php_fetch;
    
    try {
        error_log("🔄 getTherapistStatus: Starting therapist fetch...");
        
        // Get all therapists with their service information
        $therapists = $php_fetch('therapist', '*', []);
        
        error_log("📊 getTherapistStatus: Found " . (is_array($therapists) ? count($therapists) : 0) . " therapists");
        
        if (!$therapists || count($therapists) === 0) {
            error_log("⚠️ getTherapistStatus: No therapists found, returning empty array");
            response(['status' => 'success', 'data' => []]);
        }
        
        // Optimize: Get all services at once to avoid multiple queries
        $allServices = [];
        try {
            $services = $php_fetch('services', 'id, service_name', []);
            if ($services) {
                foreach ($services as $service) {
                    $allServices[$service['id']] = $service['service_name'];
                }
            }
            error_log("📋 getTherapistStatus: Loaded " . count($allServices) . " services");
        } catch (Exception $e) {
            error_log("⚠️ getTherapistStatus: Could not load services: " . $e->getMessage());
        }
        
        $result = [];
        foreach ($therapists as $therapist) {
            try {
                // Get service names for this therapist using cached services
                $serviceNames = [];
                if (isset($therapist['service_id']) && $therapist['service_id']) {
                    // Handle comma-separated service IDs
                    $serviceIds = array_map('trim', explode(',', $therapist['service_id']));
                    
                    foreach ($serviceIds as $serviceId) {
                        if (!empty($serviceId) && isset($allServices[$serviceId])) {
                            $serviceNames[] = $allServices[$serviceId];
                        }
                    }
                }
                
                // Check for active status - try is_active field first, then fallback to description markers
                $isActive = 1; // Default to active for backward compatibility
                
                if (isset($therapist['is_active'])) {
                    // Use the is_active field if it exists
                    $isActive = $therapist['is_active'] ? 1 : 0;
                } else {
                    // Fallback: check description for status markers
                    $desc = $therapist['therapist_desc'] ?? '';
                    if (strpos($desc, '[INACTIVE]') !== false) {
                        $isActive = 0;
                    } else {
                        $isActive = 1;
                    }
                }
                
                $result[] = [
                    'therapistid' => $therapist['therapistid'],
                    'therapist_name' => $therapist['therapist_name'] ?? 'Unknown',
                    'therapist_desc' => $therapist['therapist_desc'] ?? '',
                    'services' => $serviceNames,
                    'services_text' => implode(', ', $serviceNames),
                    'service_count' => count($serviceNames),
                    'is_active' => $isActive ? true : false,
                    'status_text' => $isActive ? 'Active' : 'Inactive',
                    'created_date' => $therapist['created_at'] ?? $therapist['date_created'] ?? 'Unknown'
                ];
            } catch (Exception $e) {
                error_log("⚠️ getTherapistStatus: Error processing therapist {$therapist['therapistid']}: " . $e->getMessage());
                // Continue with other therapists, don't fail completely
            }
        }
        
        // Sort by therapist name
        usort($result, function($a, $b) {
            return strcasecmp($a['therapist_name'], $b['therapist_name']);
        });
        
        error_log("✅ getTherapistStatus: Successfully processed " . count($result) . " therapists");
        response(['status' => 'success', 'data' => $result]);
        
    } catch (Exception $e) {
        error_log("❌ getTherapistStatus: Fatal error: " . $e->getMessage());
        response(['status' => 'error', 'message' => 'Error fetching therapist status: ' . $e->getMessage()]);
    }
}

/**
 * Update individual therapist status
 */
function updateTherapistStatus() {
    global $php_update, $php_fetch;
    
    $therapist_id = $_POST['therapist_id'] ?? null;
    $is_active = $_POST['is_active'] ?? null;
    
    if (!$therapist_id || $is_active === null) {
        response(['status' => 'error', 'message' => 'Missing therapist ID or status']);
    }
    
    // Convert to boolean/integer
    $activeStatus = $is_active === 'true' || $is_active === '1' || $is_active === 1 ? 1 : 0;
    
    try {
        // Check if therapist exists
        $therapist = $php_fetch('therapist', 'therapist_name', ['therapistid' => $therapist_id]);
        
        if (!$therapist || count($therapist) === 0) {
            response(['status' => 'error', 'message' => 'Therapist not found']);
        }
        
        $therapistName = $therapist[0]['therapist_name'];
        
        // Try to update with is_active field first
        try {
            $result = $php_update('therapist', 
                ['is_active' => $activeStatus, 'updated_at' => date('Y-m-d H:i:s')], 
                ['therapistid' => $therapist_id]
            );
        } catch (Exception $e) {
            // If is_active field doesn't exist, we'll handle it differently
            // For now, we'll use a workaround with the existing schema
            // This could be storing status in therapist_desc or another approach
            $statusNote = $activeStatus ? '' : ' [INACTIVE]';
            $currentDesc = $php_fetch('therapist', 'therapist_desc', ['therapistid' => $therapist_id]);
            $desc = $currentDesc[0]['therapist_desc'] ?? '';
            
            // Remove existing status markers
            $desc = str_replace(' [INACTIVE]', '', $desc);
            $desc = str_replace(' [ACTIVE]', '', $desc);
            
            // Add new status marker
            $desc .= $statusNote;
            
            $result = $php_update('therapist', 
                ['therapist_desc' => $desc, 'updated_at' => date('Y-m-d H:i:s')], 
                ['therapistid' => $therapist_id]
            );
        }
        
        if ($result) {
            response([
                'status' => 'success', 
                'message' => "Therapist '{$therapistName}' " . ($activeStatus ? 'activated' : 'deactivated') . " successfully"
            ]);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update therapist status']);
        }
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error updating therapist status: ' . $e->getMessage()]);
    }
}

/**
 * Toggle all therapists to active or inactive
 */
function toggleAllTherapists() {
    global $php_update, $php_fetch;
    
    $is_active = $_POST['is_active'] ?? null;
    
    if ($is_active === null) {
        response(['status' => 'error', 'message' => 'Missing active status']);
    }
    
    // Convert to boolean/integer
    $activeStatus = $is_active === 'true' || $is_active === '1' || $is_active === 1 ? 1 : 0;
    
    try {
        // Get all therapists first
        $therapists = $php_fetch('therapist', 'therapistid, therapist_name, therapist_desc', []);
        
        if (!$therapists || count($therapists) === 0) {
            response(['status' => 'error', 'message' => 'No therapists found']);
        }
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($therapists as $therapist) {
            try {
                // Try to update with is_active field first
                try {
                    $result = $php_update('therapist', 
                        ['is_active' => $activeStatus, 'updated_at' => date('Y-m-d H:i:s')], 
                        ['therapistid' => $therapist['therapistid']]
                    );
                } catch (Exception $e) {
                    // Fallback method using description field
                    $statusNote = $activeStatus ? '' : ' [INACTIVE]';
                    $desc = $therapist['therapist_desc'] ?? '';
                    
                    // Remove existing status markers
                    $desc = str_replace(' [INACTIVE]', '', $desc);
                    $desc = str_replace(' [ACTIVE]', '', $desc);
                    
                    // Add new status marker
                    $desc .= $statusNote;
                    
                    $result = $php_update('therapist', 
                        ['therapist_desc' => $desc, 'updated_at' => date('Y-m-d H:i:s')], 
                        ['therapistid' => $therapist['therapistid']]
                    );
                }
                
                if ($result) {
                    $updatedCount++;
                } else {
                    $errors[] = $therapist['therapist_name'];
                }
            } catch (Exception $e) {
                $errors[] = $therapist['therapist_name'] . ' (Error: ' . $e->getMessage() . ')';
            }
        }
        
        if ($updatedCount > 0) {
            $message = "Successfully " . ($activeStatus ? 'activated' : 'deactivated') . " {$updatedCount} therapist(s)";
            if (!empty($errors)) {
                $message .= ". Errors with: " . implode(', ', $errors);
            }
            response(['status' => 'success', 'message' => $message, 'updated_count' => $updatedCount]);
        } else {
            response(['status' => 'error', 'message' => 'Failed to update any therapists. Errors: ' . implode(', ', $errors)]);
        }
    } catch (Exception $e) {
        response(['status' => 'error', 'message' => 'Error toggling all therapists: ' . $e->getMessage()]);
    }
}
?>