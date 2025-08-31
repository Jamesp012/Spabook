<?php
// Test script to insert sample data
header('Content-Type: application/json');

// Include the necessary files
require_once __DIR__ . '/config/connection.php';

// Function to create a test booking
function createTestBooking($php_insert, $status, $userId = 1) {
    // Check the database schema to see what fields are required and what data types they expect
    $bookingData = [
        'user_id' => $userId,
        'total_price' => 100,
        'payment_status' => 'Pending',
        'booking_status' => $status,
        'date_created' => date('Y-m-d H:i:s')
    ];
    
    // Log the booking data for debugging
    file_put_contents(__DIR__ . '/logs/debug.log', date('Y-m-d H:i:s') . " - Booking data: " . json_encode($bookingData) . "\n", FILE_APPEND);
    
    $result = $php_insert('booking', $bookingData);
    
    // Log the result for debugging
    file_put_contents(__DIR__ . '/logs/debug.log', date('Y-m-d H:i:s') . " - Insert result: " . json_encode($result) . "\n", FILE_APPEND);
    
    if (isset($result['error'])) {
        throw new Exception('Failed to create test booking: ' . $result['error']);
    }
    
    return $result[0]['bookingid'];
}

try {
    // Create test bookings with different statuses
    $pendingBookingId = createTestBooking($php_insert, 'Pending');
    $confirmedBookingId = createTestBooking($php_insert, 'Confirmed');
    
    // Output the results
    echo json_encode([
        'status' => 'success',
        'message' => 'Sample data inserted successfully',
        'data' => [
            'pending_booking_id' => $pendingBookingId,
            'confirmed_booking_id' => $confirmedBookingId
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}