<?php
/**
 * Cron job script for sending appointment reminders
 * This script should be run every hour to check for upcoming appointments
 * 
 * Usage:
 * - Manual: php cron_appointment_reminders.php
 * - Cron: 0 * * * * php /path/to/Spabook/cron_appointment_reminders.php
 * - Windows Task Scheduler: Run every hour
 */

require_once 'config/connection.php';
require_once 'utils/cache.php';
require_once 'model/notification_model.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Initialize cache
Cache::init();

// Check if notification table exists
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
    }
} catch (Exception $e) {
    echo "Error checking notification table: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$notificationTableExists) {
    echo "Notification table does not exist. Exiting.\n";
    exit(1);
}

/**
 * Create appointment reminder notifications (1 day and 1 hour before)
 */
function createAppointmentReminders() {
    global $php_fetch, $php_insert;
    
    echo "Starting appointment reminder check at " . date('Y-m-d H:i:s') . "\n";
    
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
    
    echo "Found " . count($upcomingBookings) . " upcoming confirmed bookings\n";
    
    $remindersSent = 0;
    
    foreach ($upcomingBookings as $booking) {
        $appointmentDateTime = strtotime($booking['appointment_datetime']);
        $now = time();
        $timeDiff = $appointmentDateTime - $now;
        
        $bookingId = $booking['bookingid'];
        $userId = $booking['user_id'];
        
        echo "Checking booking #$bookingId for user $userId - Time difference: " . ($timeDiff / 3600) . " hours\n";
        
        // Check if we've already sent reminders for this booking
        $existingQuery = "SELECT notificationid, metadata FROM notification 
                         WHERE user_id = '$userId' 
                         AND metadata::jsonb @> '{\"booking_id\": $bookingId, \"type\": \"reminder\"}'";
        $existingReminders = $php_fetch($existingQuery);
        
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
                echo "Sent 1-day reminder for booking #$bookingId\n";
                $remindersSent++;
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
                echo "Sent 1-hour reminder for booking #$bookingId\n";
                $remindersSent++;
            }
        }
    }
    
    echo "Total reminders sent: $remindersSent\n";
    echo "Appointment reminder check completed at " . date('Y-m-d H:i:s') . "\n\n";
    
    return $remindersSent;
}

// Run the reminder check
try {
    $remindersSent = createAppointmentReminders();
    
    // Log to file if available
    $logFile = __DIR__ . '/logs/appointment_reminders.log';
    if (is_dir(dirname($logFile))) {
        file_put_contents($logFile, 
            date('Y-m-d H:i:s') . " - Reminder check completed. Sent: $remindersSent reminders\n", 
            FILE_APPEND | LOCK_EX
        );
    }
    
    exit(0);
} catch (Exception $e) {
    echo "Error running appointment reminders: " . $e->getMessage() . "\n";
    
    // Log error if available
    $logFile = __DIR__ . '/logs/appointment_reminders.log';
    if (is_dir(dirname($logFile))) {
        file_put_contents($logFile, 
            date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", 
            FILE_APPEND | LOCK_EX
        );
    }
    
    exit(1);
}
?>