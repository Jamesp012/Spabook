<?php
require_once '../model/notification_model.php';
require_once '../config/connection.php';
require_once '../utils/cache.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize cache
Cache::init();

// Initialize model
$NotificationModel = new NotificationModel();

function response($data) {
    echo json_encode($data);
    exit;
}

/**
 * Format timestamp to relative time (e.g., "2 hours ago")
 * 
 * @param string $timestamp The timestamp to format
 * @return string The formatted relative time
 */
function formatRelativeTime($timestamp) {
    $timestamp = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $timestamp;
    
    if ($time_difference < 60) {
        return "Just now";
    } elseif ($time_difference < 3600) {
        $minutes = floor($time_difference / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 86400) {
        $hours = floor($time_difference / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 604800) {
        $days = floor($time_difference / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 2592000) {
        $weeks = floor($time_difference / 604800);
        return $weeks . " week" . ($weeks > 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 31536000) {
        $months = floor($time_difference / 2592000);
        return $months . " month" . ($months > 1 ? "s" : "") . " ago";
    } else {
        $years = floor($time_difference / 31536000);
        return $years . " year" . ($years > 1 ? "s" : "") . " ago";
    }
}

/**
 * Format timestamp to a readable date and time
 * 
 * @param string $timestamp The timestamp to format
 * @return string The formatted date and time
 */
function formatDateTime($timestamp) {
    return date('F j, Y \a\t g:i A', strtotime($timestamp));
}

/**
 * Create a notification
 * 
 * @param string $user_id The user ID
 * @param string $title The notification title
 * @param string $message The notification message
 * @param string $type The notification type (info, warning, error, success)
 * @param array $metadata Optional additional data
 * @return array The result of the operation
 */
function createNotification($user_id, $title, $message, $type = 'info', $metadata = []) {
    global $NotificationModel, $php_insert;
    
    $data = [
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'is_read' => false,
        'metadata' => json_encode($metadata)
    ];
    
    return $NotificationModel->createNotification($php_insert, 'notification', $data);
}

/**
 * Create a booking status notification
 * 
 * @param string $user_id The user ID
 * @param string $booking_id The booking ID
 * @param string $status The new booking status
 * @return array The result of the operation
 */
function createBookingStatusNotification($user_id, $booking_id, $status) {
    $title = '';
    $message = '';
    $type = 'info';
    
    switch ($status) {
        case 'Pending':
            $title = 'Booking Submitted';
            $message = "Your booking #$booking_id has been submitted and is pending approval.";
            $type = 'info';
            break;
        case 'Confirmed':
            $title = 'Booking Confirmed';
            $message = "Great news! Your booking #$booking_id has been confirmed.";
            $type = 'success';
            break;
        case 'Rejected':
            $title = 'Booking Rejected';
            $message = "We're sorry, but your booking #$booking_id has been rejected.";
            $type = 'error';
            break;
        case 'Completed':
            $title = 'Booking Completed';
            $message = "Your booking #$booking_id has been marked as completed. Thank you for choosing our services!";
            $type = 'success';
            break;
        case 'Cancelled':
            $title = 'Booking Cancelled';
            $message = "Your booking #$booking_id has been cancelled.";
            $type = 'warning';
            break;
        default:
            $title = 'Booking Update';
            $message = "Your booking #$booking_id status has been updated to $status.";
            $type = 'info';
    }
    
    $metadata = [
        'booking_id' => $booking_id,
        'status' => $status
    ];
    
    return createNotification($user_id, $title, $message, $type, $metadata);
}

/**
 * Create an admin notification for a new booking
 * 
 * @param string $booking_id The booking ID
 * @param string $user_name The user's name
 * @return array The result of the operation
 */
function createAdminBookingNotification($booking_id, $user_name) {
    global $NotificationModel, $php_insert, $php_fetch;
    
    // Get admin users (you might need to adjust this query based on your user roles system)
    $query = "SELECT user_id FROM users WHERE role = 'admin'";
    $admins = $php_fetch($query);
    
    $results = [];
    
    foreach ($admins as $admin) {
        $data = [
            'user_id' => $admin['user_id'],
            'title' => 'New Booking Request',
            'message' => "A new booking #$booking_id has been submitted by $user_name and requires your review.",
            'type' => 'info',
            'is_read' => false,
            'metadata' => json_encode(['booking_id' => $booking_id])
        ];
        
        $results[] = $NotificationModel->createNotification($php_insert, 'notification', $data);
    }
    
    return $results;
}

// Check if notification table exists before processing requests
try {
    $tableCheck = $php_fetch("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'notification'
    ) as exists");
    
    if (!isset($tableCheck[0]['exists']) || $tableCheck[0]['exists'] !== true) {
        response(['status' => 'error', 'message' => 'Notification table does not exist. Please create the notification table first.']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Database connection error: ' . $e->getMessage()]);
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    if (!$action) {
        response(['status' => 'error', 'message' => 'No action specified']);
    }
    
    switch ($action) {
        case 'get_user_notifications':
            $user_id = $_POST['user_id'] ?? null;
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
            $unread_only = isset($_POST['unread_only']) ? (bool)$_POST['unread_only'] : false;
            
            if (!$user_id) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            
            // Create a cache key based on parameters
            $cacheKey = "user_notifications_{$user_id}_{$limit}_" . ($unread_only ? 'unread' : 'all');
            
            // Use cache with 1 minute expiry for notifications
            // Don't cache if we're only looking at unread notifications (those change frequently)
            if ($unread_only) {
                $notifications = $NotificationModel->getUserNotifications($php_fetch, 'notification', $user_id, $limit, $unread_only);
            } else {
                $notifications = Cache::remember($cacheKey, function() use ($NotificationModel, $php_fetch, $user_id, $limit, $unread_only) {
                    return $NotificationModel->getUserNotifications($php_fetch, 'notification', $user_id, $limit, $unread_only);
                }, 60); // 1 minute cache
            }
            
            // Only parse metadata - timestamps are now calculated in SQL
            foreach ($notifications as &$notification) {
                if ($notification['read_at']) {
                    $notification['read_at_formatted'] = formatDateTime($notification['read_at']);
                }
                
                // Parse metadata if it exists
                if (isset($notification['metadata']) && !empty($notification['metadata'])) {
                    $notification['metadata'] = json_decode($notification['metadata'], true);
                }
            }
            
            response(['status' => 'success', 'notifications' => $notifications]);
            break;
            
        case 'mark_as_read':
            $notification_id = $_POST['notification_id'] ?? null;
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$notification_id) {
                response(['status' => 'error', 'message' => 'Notification ID is required']);
            }
            
            $result = $NotificationModel->markAsRead($php_update, 'notification', $notification_id);
            
            // Invalidate relevant caches
            if ($user_id) {
                Cache::delete("unread_count_$user_id");
                Cache::delete("user_notifications_{$user_id}_20_all");
                Cache::delete("user_notifications_{$user_id}_20_unread");
            }
            
            response(['status' => 'success', 'result' => $result]);
            break;
            
        case 'mark_all_as_read':
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$user_id) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            
            $result = $NotificationModel->markAllAsRead($php_update, 'notification', $user_id);
            
            // Invalidate all user-related caches
            Cache::delete("unread_count_$user_id");
            Cache::delete("user_notifications_{$user_id}_20_all");
            Cache::delete("user_notifications_{$user_id}_20_unread");
            
            response(['status' => 'success', 'result' => $result]);
            break;
            
        case 'delete_notification':
            $notification_id = $_POST['notification_id'] ?? null;
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$notification_id) {
                response(['status' => 'error', 'message' => 'Notification ID is required']);
            }
            
            $result = $NotificationModel->deleteNotification($php_delete, 'notification', $notification_id);
            
            // Invalidate relevant caches
            if ($user_id) {
                Cache::delete("unread_count_$user_id");
                Cache::delete("user_notifications_{$user_id}_20_all");
                Cache::delete("user_notifications_{$user_id}_20_unread");
            }
            
            response(['status' => 'success', 'result' => $result]);
            break;
            
        case 'get_unread_count':
            $user_id = $_POST['user_id'] ?? null;
            
            if (!$user_id) {
                response(['status' => 'error', 'message' => 'User ID is required']);
            }
            
            // Use cache with 30 second expiry for unread count
            $cacheKey = "unread_count_$user_id";
            $count = Cache::remember($cacheKey, function() use ($NotificationModel, $php_fetch, $user_id) {
                return $NotificationModel->getUnreadCount($php_fetch, 'notification', $user_id);
            }, 30);
            
            response(['status' => 'success', 'count' => $count]);
            break;
            
        case 'create_notification':
            $user_id = $_POST['user_id'] ?? null;
            $title = $_POST['title'] ?? null;
            $message = $_POST['message'] ?? null;
            $type = $_POST['type'] ?? 'info';
            $metadata = isset($_POST['metadata']) ? json_decode($_POST['metadata'], true) : [];
            
            if (!$user_id || !$title || !$message) {
                response(['status' => 'error', 'message' => 'User ID, title, and message are required']);
            }
            
            $result = createNotification($user_id, $title, $message, $type, $metadata);
            response(['status' => 'success', 'result' => $result]);
            break;
            
        case 'create_booking_notification':
            $user_id = $_POST['user_id'] ?? null;
            $booking_id = $_POST['booking_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$user_id || !$booking_id || !$status) {
                response(['status' => 'error', 'message' => 'User ID, booking ID, and status are required']);
            }
            
            $result = createBookingStatusNotification($user_id, $booking_id, $status);
            response(['status' => 'success', 'result' => $result]);
            break;
            
        case 'get_admin_notifications':
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
            
            $notifications = $NotificationModel->getAdminNotifications($php_fetch, 'notification', $limit);
            
            // Only parse metadata - timestamps are now calculated in SQL
            foreach ($notifications as &$notification) {
                if ($notification['read_at']) {
                    $notification['read_at_formatted'] = formatDateTime($notification['read_at']);
                }
                
                // Parse metadata if it exists
                if (isset($notification['metadata']) && !empty($notification['metadata'])) {
                    $notification['metadata'] = json_decode($notification['metadata'], true);
                }
            }
            
            response(['status' => 'success', 'notifications' => $notifications]);
            break;
            
        default:
            response(['status' => 'error', 'message' => 'Unknown action']);
    }
}
?>