<?php
// Test script to get booking schema
header('Content-Type: application/json');

// Include the necessary files
require_once __DIR__ . '/config/connection.php';

try {
    // Get all bookings to see the schema
    $bookings = $php_fetch('booking', '*', []);
    
    // Get the first booking to see the schema
    $firstBooking = !empty($bookings) ? $bookings[0] : null;
    
    // Output the results
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking schema retrieved',
        'data' => [
            'first_booking' => $firstBooking,
            'booking_count' => count($bookings)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}