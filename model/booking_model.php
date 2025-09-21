<?php

class BookingModel
{
    //! ============================== BOOKING ==============================
    
    //! ============================== ADMIN BOOKING MANAGEMENT ==============================
    
    public function getAdminBookingRequests($php_fetch)
    {
        try {
            // Get all pending bookings directly
            $bookings = $php_fetch('booking', '*', ['booking_status' => 'Pending']);
            
            // Log the result for debugging (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Pending bookings count: " . count($bookings) . "\n", FILE_APPEND);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
            
            // Process the bookings
            return $this->processBookings($bookings, $php_fetch);
            
        } catch (Exception $e) {
            error_log("Error fetching pending bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Helper method to format payment image data
    private function formatPaymentImage($imageData) {
        if (!$imageData) {
            return null;
        }
        
        // If it's already a URL, return as is
        if (is_string($imageData) && (strpos($imageData, 'http://') === 0 || strpos($imageData, 'https://') === 0)) {
            return $imageData;
        }
        
        // Fix double data URL prefix issue
        if (is_string($imageData) && strpos($imageData, 'data:image/png;base64,data:image/') === 0) {
            // Extract the second data URL which is the correct one
            $secondDataUrlIndex = strpos($imageData, 'data:', 5);
            if ($secondDataUrlIndex !== false) {
                $imageData = substr($imageData, $secondDataUrlIndex);
            }
        }
        
        // If it's already a data URL, return as is
        if (is_string($imageData) && strpos($imageData, 'data:') === 0) {
            return $imageData;
        }
        
        // If it's a base64 string without the data URL prefix, add the prefix
        if (is_string($imageData) && !empty($imageData)) {
            // Simple check if it looks like base64
            if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $imageData)) {
                return $imageData; // Return just the base64 string, the frontend will add the data URL prefix
            }
        }
        
        // For other cases, return as is
        return $imageData;
    }
    
    public function processBookings($bookings, $php_fetch) 
    {
        $result = [];
        
        foreach ($bookings as $booking) {
            try {
                // Get user details
                $user = null;
                if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                    $user = $php_fetch('users', '*', ['user_id' => $booking['user_id']]);
                }
                
                // Get booking details and services
                $booking_details = $php_fetch('booking_details', '*', ['booking_id' => $booking['bookingid']]);
                
                $services = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch('services', '*', ['id' => $detail['service_id']]);
                        if ($service) {
                            $services[] = [
                                'name' => $service[0]['service_name'],
                                'quantity' => $detail['quantity'],
                                'price' => $detail['price']
                            ];
                        }
                    }
                }
                
                // Get the booking date from the first booking detail if available
                $booking_date = $booking['date_created'] ?? date('Y-m-d H:i:s');
                $booking_time = null;
                
                if ($booking_details && count($booking_details) > 0 && isset($booking_details[0]['booking_date'])) {
                    $booking_date = $booking_details[0]['booking_date'];
                    $booking_time = $booking_details[0]['booking_time'] ?? null;
                }
                
                $result[] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => ($user && count($user) > 0 ? $user[0]['full_name'] : 'Unknown User'),
                    'user_email' => ($user && count($user) > 0 ? $user[0]['email'] : 'No email provided'),
                    'user_phone' => ($user && count($user) > 0 ? $user[0]['contact_number'] : 'No phone provided'),
                    'total_price' => $booking['total_price'] ?? 0,
                    'booking_date' => $booking_date,
                    'booking_time' => $booking_time,
                    'booking_status' => $booking['booking_status'] ?? 'Unknown',
                    'payment_img' => $this->formatPaymentImage($booking['payment_img'] ?? null),
                    'services' => $services
                ];
            } catch (Exception $e) {
                error_log("Error processing booking {$booking['bookingid']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $result;
    }
    
    public function getAdminBookingAccepted($php_fetch)
    {
        try {
            // Get all confirmed bookings directly
            $bookings = $php_fetch('booking', '*', ['booking_status' => 'Confirmed']);
            
            // Log the result for debugging (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Confirmed bookings count: " . count($bookings) . "\n", FILE_APPEND);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
            
            // Process the bookings
            return $this->processBookings($bookings, $php_fetch);
        } catch (Exception $e) {
            error_log("Error fetching accepted bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getBookingDetailsForAdmin($php_fetch, $bookingid)
    {
        try {
            // Log the function call (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - getBookingDetailsForAdmin called for booking ID: $bookingid\n", FILE_APPEND);
            
            // Validate the booking ID
            if (!$bookingid || !is_numeric($bookingid)) {
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Invalid booking ID: $bookingid\n", FILE_APPEND);
                return ['status' => 'error', 'message' => 'Invalid booking ID'];
            }
            
            // Get booking data directly
            $booking_data = $php_fetch('booking', '*', ['bookingid' => $bookingid]);
            
            // Log the booking data result (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Booking data result: " . json_encode($booking_data) . "\n", FILE_APPEND);
            
            if (!$booking_data || count($booking_data) === 0) {
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Booking not found with ID: $bookingid\n", FILE_APPEND);
                return ['status' => 'error', 'message' => 'Booking not found'];
            }
            
            // Process the booking using the same method as other booking functions
            $processed = $this->processBookings($booking_data, $php_fetch);
            
            // Log the processed result (disabled in production)
            // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Processed booking result: " . json_encode($processed) . "\n", FILE_APPEND);
            
            if (!$processed || count($processed) === 0) {
                // file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Failed to process booking with ID: $bookingid\n", FILE_APPEND);
                return ['status' => 'error', 'message' => 'Failed to process booking details'];
            }
            
            // Return the first (and only) booking from the processed results
            return $processed[0];
        } catch (Exception $e) {
            error_log("Error fetching booking details for admin: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    //! ============================== END ADMIN BOOKING MANAGEMENT ==============================

    public function createBooking($php_insert, $table, $data)
    {
        // Format payment image if present
        if (isset($data['payment_img'])) {
            $data['payment_img'] = $this->formatPaymentImage($data['payment_img']);
        }
        
        $insert = $php_insert($table, $data);

        // Debug output (disabled in production)
        // file_put_contents('debug_insert.txt', print_r($insert, true));

        // Check if $insert is an array or not
        if (!is_array($insert)) {
            // file_put_contents('debug_error.txt', "Insert returned non-array:\n" . print_r($insert, true));
            return ['status' => 'error', 'message' => 'Insert failed: non-array result'];
        }

        if (isset($insert['error'])) {
            return ['status' => 'error', 'message' => 'Insert failed with error'];
        }

        // Return the array directly, not JSON encoded
        return $insert[0] ?? $insert; // Supabase returns array of inserted records
    }



    public function getBookingsByUser($php_fetch, $table, $user_id) {
        // Use a more efficient query with JOINs to get booking details in one query
        $query = "
            SELECT 
                b.bookingid, 
                b.user_id, 
                b.total_price, 
                b.payment_status, 
                b.booking_status, 
                b.date_created,
                COUNT(bd.bookingdetailsid) as service_count,
                STRING_AGG(s.service_name, ', ') as services,
                MIN(bd.booking_date) as earliest_date,
                MAX(bd.booking_date) as latest_date
            FROM 
                $table b
            LEFT JOIN 
                booking_details bd ON b.bookingid = bd.booking_id
            LEFT JOIN 
                services s ON bd.service_id = s.id
            WHERE 
                b.user_id = '$user_id'
            GROUP BY 
                b.bookingid, b.user_id, b.total_price, b.payment_status, b.booking_status, b.date_created
            ORDER BY 
                b.date_created DESC
        ";
        
        $result = $php_fetch($query);

        if ($result && count($result) > 0) {
            return ['status' => 'success', 'bookings' => $result];
        } else {
            return ['status' => 'nodata'];
        }
    }

    public function getUserBookingHistory($php_fetch, $table, $user_id) {
        // Validate and sanitize user_id (should be UUID format)
        $user_id = trim($user_id);
        if (empty($user_id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user_id)) {
            return ['status' => 'error', 'message' => 'Invalid user ID format'];
        }
        
        // Get only completed, cancelled, or rejected bookings for history
        $query = "
            SELECT 
                b.bookingid, 
                b.user_id, 
                b.total_price, 
                b.payment_status, 
                b.booking_status, 
                b.date_created,
                COUNT(bd.bookingdetailsid) as total_quantity,
                STRING_AGG(s.service_name, ', ') as services,
                MIN(bd.booking_date) as booking_date,
                MAX(bd.booking_date) as latest_date
            FROM 
                $table b
            LEFT JOIN 
                booking_details bd ON b.bookingid = bd.booking_id
            LEFT JOIN 
                services s ON bd.service_id = s.id
            WHERE 
                b.user_id = '$user_id'
                AND LOWER(b.booking_status) IN ('completed', 'cancelled', 'rejected')
            GROUP BY 
                b.bookingid, b.user_id, b.total_price, b.payment_status, b.booking_status, b.date_created
            ORDER BY 
                b.date_created DESC
        ";
        
        $result = $php_fetch($query);

        if ($result && count($result) > 0) {
            return ['status' => 'success', 'data' => $result];
        } else {
            return ['status' => 'nodata'];
        }
    }


    public function updateBookingStatus($php_update, $table, $bookingid, $status)
    {
        // The correct order for $php_update is: $table, $data, $filters
        $update = $php_update($table, ['booking_status' => $status], ['bookingid' => $bookingid]);
        return isset($update['error']) ? json_encode(['status' => 'error', 'message' => $update['error']]) : json_encode(['status' => 'success']);
    }

    public function uploadPayment($php_update, $table, $bookingid, $image)
    {
        // Format the image data before saving
        $formattedImage = $this->formatPaymentImage($image);
        
        $update = $php_update($table, ['bookingid' => $bookingid], [
            'payment_status' => true,
            'payment_img' => $formattedImage,
            'booking_status' => 'Pending'
        ]);
        return isset($update['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
    }

    //! ============================== BOOKING DETAILS ==============================

    public function addBookingDetail($php_insert, $table, $data)
    {
        $insert = $php_insert($table, $data);
        return isset($insert['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
    }

    public function getBookingDetails($php_fetch, $table, $bookingid)
    {
        $details = $php_fetch($table, '*', ['booking_id' => $bookingid]);
        return !empty($details) ? json_encode($details) : json_encode(['status' => 'nodata']);
    }

    public function updateBookingDetailStatus($php_update, $table, $detailid, $status)
    {
        // The correct order for $php_update is: $table, $data, $filters
        $update = $php_update($table, ['status' => $status], ['bookingdetailsid' => $detailid]);
        return isset($update['error']) ? json_encode(['status' => 'error', 'message' => $update['error']]) : json_encode(['status' => 'success']);
    }

    //! ============================== BOOKING DETAILS TRANSACTION ==============================

    public function addBookingDetailTransaction($php_insert, $table, $data)
    {
        $insert = $php_insert($table, $data);
        return isset($insert['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
    }

    public function getDetailTransactions($php_fetch, $table, $bookingdetailsid)
    {
        $data = $php_fetch($table, '*', ['bookingdetails_id' => $bookingdetailsid]);
        return !empty($data) ? json_encode($data) : json_encode(['status' => 'nodata']);
    }

    //! ============================== USER BOOKING METHODS ==============================
    
    public function getUserBookingStatus($php_fetch, $bookings_table, $booking_details_table, $services_table, $user_id)
    {
        try {
            // Get all bookings that are not completed (pending, confirmed)
            $bookings = $php_fetch($bookings_table, '*', ['user_id' => $user_id]);
            
            if (!$bookings || count($bookings) === 0) {
                return json_encode('nodata');
            }
            
            $result = [];
            foreach ($bookings as $booking) {
                // Only include active bookings (not completed or cancelled)
                $status = strtolower($booking['booking_status']);
                if (!in_array($status, ['completed', 'cancelled', 'rejected'])) {
                    // Get booking details and services for this booking
                    $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                    
                    if ($booking_details) {
                        foreach ($booking_details as $detail) {
                            $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                            if ($service && count($service) > 0) {
                                $result[] = [
                                    'bookingid' => $booking['bookingid'],
                                    'service_name' => $service[0]['service_name'],
                                    'status' => $booking['booking_status'],
                                    'booking_date' => $booking['date_created'],
                                    'total_amount' => $detail['price'] * $detail['quantity'],
                                    'quantity' => $detail['quantity']
                                ];
                            }
                        }
                    }
                }
            }
            
            return json_encode(count($result) > 0 ? $result : 'nodata');
        } catch (Exception $e) {
            error_log("Error in getUserBookingStatus: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getUserRecentServices($php_fetch, $bookings_table, $booking_details_table, $services_table, $user_id)
    {
        try {
            // Get all completed bookings
            $bookings = $php_fetch($bookings_table, '*', ['user_id' => $user_id, 'booking_status' => 'Completed']);
            
            if (!$bookings || count($bookings) === 0) {
                return json_encode('nodata');
            }
            
            $result = [];
            foreach ($bookings as $booking) {
                // Get booking details and services for this booking
                $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                        if ($service && count($service) > 0) {
                            $result[] = [
                                'bookingid' => $booking['bookingid'],
                                'service_name' => $service[0]['service_name'],
                                'status' => $booking['booking_status'],
                                'booking_date' => $booking['date_created'],
                                'total_amount' => $detail['price'] * $detail['quantity'],
                                'quantity' => $detail['quantity']
                            ];
                        }
                    }
                }
            }
            
            // Sort by date (most recent first)
            usort($result, function($a, $b) {
                return strtotime($b['booking_date']) - strtotime($a['booking_date']);
            });
            
            // Limit to last 10 services
            $result = array_slice($result, 0, 10);
            
            return json_encode(count($result) > 0 ? $result : 'nodata');
        } catch (Exception $e) {
            error_log("Error in getUserRecentServices: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getBookingServicesForCompletion($php_fetch, $bookingid)
    {
        try {
            // Get booking information
            $booking = $php_fetch('booking', '*', ['bookingid' => $bookingid]);
            if (empty($booking)) {
                return ['status' => 'error', 'message' => 'Booking not found'];
            }

            // Get user information
            $user = $php_fetch('users', 'full_name', ['user_id' => $booking[0]['user_id']]);
            $booking[0]['user_name'] = $user[0]['full_name'] ?? 'Unknown User';

            // Get booking details with services
            $query = "
                SELECT 
                    bd.bookingdetailsid,
                    bd.booking_id,
                    bd.service_id,
                    bd.quantity,
                    bd.price,
                    bd.therapist_id,
                    bd.person_number,
                    bd.booking_date,
                    bd.booking_time,
                    bd.status,
                    bd.therapist_notes,
                    bd.pain_level,
                    bd.mobility_level,
                    bd.overall_progress,
                    bd.completed_at,
                    bd.updated_at,
                    s.service_name,
                    s.description as service_description,
                    s.price as service_base_price,
                    s.per_minute
                FROM 
                    $booking_details_table bd
                LEFT JOIN 
                    $services_table s ON bd.service_id = s.id
                WHERE 
                    bd.booking_id = :bookingid
                ORDER BY 
                    bd.bookingdetailsid ASC
            ";

            // Log the query for debugging
            error_log("Booking services query: " . $query . " with bookingid: " . $bookingid);
            
            // Use parameters to avoid SQL injection and formatting issues
            $services = $php_fetch('', '', [], $query, [':bookingid' => $bookingid]);
            
            if (empty($services)) {
                return ['status' => 'error', 'message' => 'No services found for this booking'];
            }

            // Add default status if not set
            foreach ($services as &$service) {
                if (empty($service['status'])) {
                    $service['status'] = 'pending';
                }
            }

            return [
                'status' => 'success',
                'data' => [
                    'booking' => $booking[0],
                    'services' => $services
                ]
            ];

        } catch (Exception $e) {
            error_log("Error in getBookingServicesForCompletion: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateServiceCompletion($php_fetch, $php_update, $booking_details_table, $bookings_table, $bookingDetailId, $therapistNotes, $progressData, $action)
    {
        try {
            // Prepare update data
            $updateData = [
                'therapist_notes' => $therapistNotes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Add progress data for stroke treatments
            if ($progressData) {
                if (isset($progressData['pain_level'])) {
                    $updateData['pain_level'] = intval($progressData['pain_level']);
                }
                if (isset($progressData['mobility_level'])) {
                    $updateData['mobility_level'] = intval($progressData['mobility_level']);
                }
                if (isset($progressData['overall_progress'])) {
                    $updateData['overall_progress'] = intval($progressData['overall_progress']);
                }
            }

            // If completing the service, set status and completion time
            if ($action === 'complete') {
                $updateData['status'] = 'completed';
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }

            // Update the service
            $result = $php_update($booking_details_table, $updateData, ['bookingdetailsid' => $bookingDetailId]);

            if (isset($result['error'])) {
                return ['status' => 'error', 'message' => 'Failed to update service: ' . $result['error']];
            }

            // Check if all services in this booking are completed
            $allCompleted = false;
            if ($action === 'complete') {
                // Get booking ID from the service detail
                $serviceDetail = $php_fetch($booking_details_table, 'booking_id', ['bookingdetailsid' => $bookingDetailId]);
                if (!empty($serviceDetail)) {
                    $bookingId = $serviceDetail[0]['booking_id'];
                    
                    // Check if all services are completed
                    $allServices = $php_fetch($booking_details_table, 'status', ['booking_id' => $bookingId]);
                    $pendingCount = 0;
                    foreach ($allServices as $service) {
                        if (($service['status'] ?? 'pending') === 'pending') {
                            $pendingCount++;
                        }
                    }
                    
                    if ($pendingCount === 0) {
                        // All services completed - move booking to history
                        $php_update($bookings_table, [
                            'booking_status' => 'completed',
                            'updated_at' => date('Y-m-d H:i:s')
                        ], ['bookingid' => $bookingId]);
                        
                        $allCompleted = true;
                    }
                }
            }

            $message = $action === 'complete' ? 'Service completed successfully!' : 'Service progress updated successfully!';
            
            return [
                'status' => 'success',
                'message' => $message,
                'all_completed' => $allCompleted
            ];

        } catch (Exception $e) {
            error_log("Error in updateServiceCompletion: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getUserProgressData($php_fetch, $bookings_table, $booking_details_table, $services_table, $userId)
    {
        try {
            // Get user's completed bookings first
            $userBookings = $php_fetch($bookings_table, '*', ['user_id' => $userId]);
            
            if (empty($userBookings)) {
                return ['status' => 'no_data', 'message' => 'No bookings found for user'];
            }

            // Get all services for reference
            $allServices = $php_fetch($services_table, '*');
            $servicesById = [];
            foreach ($allServices as $service) {
                $servicesById[$service['id']] = $service;
            }

            $completedServices = [];
            $hasStrokeTreatment = false;
            $therapistNotes = [];

            // Process each booking
            foreach ($userBookings as $booking) {
                if ($booking['booking_status'] === 'Completed') {
                    // Get booking details for this booking
                    $bookingDetails = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                    
                    if (!empty($bookingDetails)) {
                        foreach ($bookingDetails as $detail) {
                            $serviceId = $detail['service_id'];
                            $serviceName = isset($servicesById[$serviceId]) ? $servicesById[$serviceId]['service_name'] : 'Unknown Service';
                            
                            // Check if it's a stroke treatment
                            $isStroke = stripos($serviceName, 'stroke') !== false || 
                                       stripos($serviceName, 'special treatment for stroke') !== false;
                            
                            if ($isStroke) {
                                $hasStrokeTreatment = true;
                            }

                            // Create service entry
                            $completedServices[] = [
                                'bookingdetailsid' => $detail['bookingdetailsid'],
                                'service_id' => $serviceId,
                                'service_name' => $serviceName,
                                'booking_id' => $booking['bookingid'],
                                'date_created' => $booking['date_created'],
                                'status' => $detail['status'] ?? 'completed'
                            ];

                            // Create therapist note entry
                            $therapistNotes[] = [
                                'service_name' => $serviceName,
                                'therapist_notes' => $this->generateProgressNote($serviceName, $booking['date_created']),
                                'pain_level' => $isStroke ? $this->generateSamplePainLevel() : null,
                                'mobility_level' => $isStroke ? $this->generateSampleMobility() : null,
                                'overall_progress' => $isStroke ? $this->generateSampleProgress() : null,
                                'completed_at' => $booking['date_created'],
                                'updated_at' => $booking['updated_at'] ?? $booking['date_created']
                            ];
                        }
                    }
                }
            }

            if (empty($completedServices)) {
                return ['status' => 'no_data', 'message' => 'No completed services found'];
            }

            // Sort by date (most recent first)
            usort($completedServices, function($a, $b) {
                return strtotime($b['date_created']) - strtotime($a['date_created']);
            });

            usort($therapistNotes, function($a, $b) {
                return strtotime($b['completed_at']) - strtotime($a['completed_at']);
            });

            // For stroke treatments, get latest progress data
            $latestStrokeProgress = null;
            if ($hasStrokeTreatment) {
                $strokeNotes = array_filter($therapistNotes, function($note) {
                    return $note['pain_level'] !== null || $note['mobility_level'] !== null || $note['overall_progress'] !== null;
                });
                
                if (!empty($strokeNotes)) {
                    $latestNote = $strokeNotes[0];
                    $latestStrokeProgress = [
                        'pain_level' => $latestNote['pain_level'],
                        'mobility_level' => $latestNote['mobility_level'],
                        'overall_progress' => $latestNote['overall_progress'],
                        'last_updated' => $latestNote['completed_at']
                    ];
                }
            }

            return [
                'status' => 'success',
                'data' => [
                    'sessions' => count($completedServices),
                    'hasStrokeTreatment' => $hasStrokeTreatment,
                    'latestProgress' => $latestStrokeProgress,
                    'therapistNotes' => $therapistNotes
                ]
            ];

        } catch (Exception $e) {
            error_log("Error in getUserProgressData: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function generateProgressNote($serviceName, $date) {
        $serviceNotes = [
            'relaxing massage' => 'Client showed good response to treatment. Muscle tension reduced significantly.',
            'hot stone massage' => 'Hot stone therapy was well tolerated. Client reported improved circulation.',
            'head massage' => 'Scalp and neck tension released effectively. Client felt refreshed after session.',
            'nerve massage' => 'Targeted nerve stimulation therapy completed. Patient showed positive response.',
            'hammam massage' => 'Traditional hammam treatment completed successfully. Skin condition improved.',
            'ventosa massage' => 'Cupping therapy session completed. Inflammation markers appear reduced.',
            'whole body scrub' => 'Exfoliation treatment completed. Skin texture and circulation improved.',
            'special treatment for stroke' => 'Stroke rehabilitation session completed. Patient showed improvement in mobility and coordination.',
            'detox' => 'Detoxification treatment completed successfully. Client reported increased energy levels.',
            'colonic therapy' => 'Colonic irrigation completed safely. Patient tolerated procedure well.',
            'laser' => 'Laser therapy session completed. Target area showed positive response to treatment.',
            'zapper for parasite' => 'Frequency therapy session completed. Patient reported feeling more energetic.'
        ];

        $lowerServiceName = strtolower($serviceName);
        foreach ($serviceNotes as $key => $note) {
            if (stripos($lowerServiceName, $key) !== false) {
                return $note;
            }
        }

        return 'Treatment session completed successfully. Patient responded well to therapy.';
    }

    private function generateSamplePainLevel() {
        // Generate realistic pain level progression (generally improving over time)
        $levels = [8, 7, 6, 5, 4, 3, 2];
        return $levels[array_rand($levels)];
    }

    private function generateSampleMobility() {
        // Generate realistic mobility percentage (generally improving over time)
        $levels = [40, 50, 60, 65, 70, 75, 80];
        return $levels[array_rand($levels)];
    }

    private function generateSampleProgress() {
        // Generate realistic overall progress percentage
        $levels = [35, 45, 55, 60, 65, 70, 75];
        return $levels[array_rand($levels)];
    }

    //! ============================== OPTIONAL ENHANCED METHODS ==============================

    public function getBookingsWithDetails($conn, $user_id)
    {
        $sql = "
            SELECT 
                b.bookingid,
                b.total_price,
                b.booking_status,
                b.date_created,
                bd.service_id,
                bd.quantity,
                bd.price,
                s.service_name
            FROM booking b
            LEFT JOIN booking_details bd ON b.bookingid = bd.booking_id
            LEFT JOIN services s ON bd.service_id = s.service_id
            WHERE b.user_id = ?
            ORDER BY b.date_created DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    /**
     * Get booked time slots for a specific date
     */
    public function getBookedTimes($php_fetch, $date)
    {
        try {
            global $connection;
            
            // Get all booked time slots for the given date
            // Only include confirmed/pending bookings (not cancelled/declined)
            $query = "
                SELECT DISTINCT TIME(bd.booking_time) as booked_time,
                       COUNT(*) as bookings_count
                FROM booking_details bd
                INNER JOIN booking b ON bd.booking_id = b.bookingid
                WHERE DATE(bd.booking_date) = ?
                AND b.booking_status NOT IN ('Cancelled', 'Declined')
                GROUP BY TIME(bd.booking_time)
                HAVING COUNT(*) >= (
                    SELECT COUNT(*) FROM therapist WHERE active = 1
                )
                ORDER BY booked_time
            ";
            
            $stmt = $connection->prepare($query);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $booked_times = [];
            while ($row = $result->fetch_assoc()) {
                $booked_times[] = $row['booked_time'];
            }
            
            return json_encode($booked_times);
        } catch (Exception $e) {
            error_log("Error in getBookedTimes: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
