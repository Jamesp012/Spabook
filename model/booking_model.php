<?php

class BookingModel
{
    //! ============================== BOOKING ==============================
    
    //! ============================== ADMIN BOOKING MANAGEMENT ==============================
    
    public function getAdminBookingRequests($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table)
    {
        try {
            // Get all pending bookings
            $bookings = $php_fetch($bookings_table, '*', ['booking_status' => 'Pending']);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
        } catch (Exception $e) {
            error_log("Error fetching pending bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        $result = [];
        foreach ($bookings as $booking) {
            try {
                // Get user details using correct field name
                $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                
                // Get booking details and services
                $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                
                $services = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $services[] = [
                                'name' => $service[0]['service_name'],
                                'quantity' => $detail['quantity'],
                                'price' => $detail['price']
                            ];
                        }
                    }
                }
                
                $result[] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => ($user ? $user[0]['full_name'] : 'Unknown User'),
                    'user_email' => ($user ? $user[0]['email'] : ''),
                    'user_phone' => ($user ? $user[0]['contact_number'] : ''),
                    'total_price' => $booking['total_price'],
                    'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                    'booking_status' => $booking['booking_status'],
                    'payment_img' => $booking['payment_img'] ?? null,
                    'services' => $services
                ];
            } catch (Exception $e) {
                error_log("Error processing booking {$booking['bookingid']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $result;
    }
    
    public function getAdminBookingAccepted($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table)
    {
        try {
            // Get all confirmed bookings
            $bookings = $php_fetch($bookings_table, '*', ['booking_status' => 'Confirmed']);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
        } catch (Exception $e) {
            error_log("Error fetching confirmed bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        $result = [];
        foreach ($bookings as $booking) {
            try {
                // Get user details
                $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                
                // Get booking details and services
                $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                
                $services = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $services[] = [
                                'name' => $service[0]['service_name'],
                                'quantity' => $detail['quantity'],
                                'price' => $detail['price']
                            ];
                        }
                    }
                }
                
                $result[] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => ($user ? $user[0]['full_name'] : 'Unknown User'),
                    'user_email' => ($user ? $user[0]['email'] : ''),
                    'user_phone' => ($user ? $user[0]['contact_number'] : ''),
                    'total_price' => $booking['total_price'],
                    'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                    'booking_status' => $booking['booking_status'],
                    'payment_img' => $booking['payment_img'],
                    'services' => $services
                ];
            } catch (Exception $e) {
                error_log("Error processing booking {$booking['bookingid']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $result;
    }
    
    public function getBookingDetailsForAdmin($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table, $bookingid)
    {
        try {
            // Get booking details
            $booking = $php_fetch($bookings_table, '*', ['bookingid' => $bookingid]);
            
            if (!$booking || count($booking) === 0) {
                return ['status' => 'error', 'message' => 'Booking not found'];
            }
            
            $booking = $booking[0];
            
            // Get user details
            $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
            
            // Get booking details and services
            $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $bookingid]);
            
            $services = [];
            if ($booking_details) {
                foreach ($booking_details as $detail) {
                    $service = $php_fetch($services_table, 'service_name, description, per_minute', ['id' => $detail['service_id']]);
                    if ($service) {
                        $services[] = [
                            'name' => $service[0]['service_name'],
                            'description' => $service[0]['description'] ?? 'No description available',
                            'duration' => $service[0]['per_minute'] ?? 0,
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price']
                        ];
                    }
                }
            }
            
            return [
                'bookingid' => $booking['bookingid'],
                'user_name' => ($user ? $user[0]['full_name'] : 'Unknown User'),
                'user_email' => ($user ? $user[0]['email'] : ''),
                'user_phone' => ($user ? $user[0]['contact_number'] : ''),
                'total_price' => $booking['total_price'],
                'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                'booking_status' => $booking['booking_status'],
                'payment_img' => $booking['payment_img'] ?? null,
                'services' => $services
            ];
        } catch (Exception $e) {
            error_log("Error fetching booking details for admin: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    //! ============================== END ADMIN BOOKING MANAGEMENT ==============================

    public function createBooking($php_insert, $table, $data)
    {
        $insert = $php_insert($table, $data);

        // Debug output
        file_put_contents('debug_insert.txt', print_r($insert, true));

        // Check if $insert is an array or not
        if (!is_array($insert)) {
            file_put_contents('debug_error.txt', "Insert returned non-array:\n" . print_r($insert, true));
            return ['status' => 'error', 'message' => 'Insert failed: non-array result'];
        }

        if (isset($insert['error'])) {
            return ['status' => 'error', 'message' => 'Insert failed with error'];
        }

        // Return the array directly, not JSON encoded
        return $insert[0] ?? $insert; // Supabase returns array of inserted records
    }



    public function getBookingsByUser($php_fetch, $table, $user_id) {
        $result = $php_fetch($table, '*', ['user_id' => $user_id]);

        if ($result && count($result) > 0) {
            return $result;
        } else {
            return ['status' => 'nodata'];
        }
    }


    public function updateBookingStatus($php_update, $table, $bookingid, $status)
    {
        $update = $php_update($table, ['bookingid' => $bookingid], ['booking_status' => $status]);
        return isset($update['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
    }

    public function uploadPayment($php_update, $table, $bookingid, $image)
    {
        $update = $php_update($table, ['bookingid' => $bookingid], [
            'payment_status' => true,
            'payment_img' => $image,
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
        $update = $php_update($table, ['bookingdetailsid' => $detailid], ['status' => $status]);
        return isset($update['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
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
}

<?php

class BookingModel
{
    //! ============================== BOOKING ==============================
    
    //! ============================== ADMIN BOOKING MANAGEMENT ==============================
    
    public function getAdminBookingRequests($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table)
    {
        try {
            // Get all pending bookings
            $bookings = $php_fetch($bookings_table, '*', ['booking_status' => 'Pending']);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
        } catch (Exception $e) {
            error_log("Error fetching pending bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        $result = [];
        foreach ($bookings as $booking) {
            try {
                // Check if user_id exists and is not empty
                $user = null;
                if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                    $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                    // Check if user fetch returned an error (empty user_id case)
                    if (isset($user['error'])) {
                        $user = null;
                    }
                }
                
                // Get booking details and services
                $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                
                $services = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $services[] = [
                                'name' => $service[0]['service_name'],
                                'quantity' => $detail['quantity'],
                                'price' => $detail['price']
                            ];
                        }
                    }
                }
                
                $result[] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => ($user && count($user) > 0 ? $user[0]['full_name'] : 'Unknown User'),
                    'user_email' => ($user && count($user) > 0 ? $user[0]['email'] : ''),
                    'user_phone' => ($user && count($user) > 0 ? $user[0]['contact_number'] : ''),
                    'total_price' => $booking['total_price'],
                    'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                    'booking_status' => $booking['booking_status'],
                    'payment_img' => $booking['payment_img'] ?? null,
                    'services' => $services
                ];
            } catch (Exception $e) {
                error_log("Error processing booking {$booking['bookingid']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $result;
    }
    
    public function getAdminBookingAccepted($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table)
    {
        try {
            // Get all confirmed bookings
            $bookings = $php_fetch($bookings_table, '*', ['booking_status' => 'Confirmed']);
            
            if (!$bookings || count($bookings) === 0) {
                return ['status' => 'nodata'];
            }
        } catch (Exception $e) {
            error_log("Error fetching confirmed bookings: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        $result = [];
        foreach ($bookings as $booking) {
            try {
                // Check if user_id exists and is not empty
                $user = null;
                if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                    $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                    // Check if user fetch returned an error (empty user_id case)
                    if (isset($user['error'])) {
                        $user = null;
                    }
                }
                
                // Get booking details and services
                $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $booking['bookingid']]);
                
                $services = [];
                if ($booking_details) {
                    foreach ($booking_details as $detail) {
                        $service = $php_fetch($services_table, 'service_name', ['id' => $detail['service_id']]);
                        if ($service) {
                            $services[] = [
                                'name' => $service[0]['service_name'],
                                'quantity' => $detail['quantity'],
                                'price' => $detail['price']
                            ];
                        }
                    }
                }
                
                $user_name = ($user && count($user) > 0 ? $user[0]['full_name'] : 'Unknown User');
                
                $result[] = [
                    'bookingid' => $booking['bookingid'],
                    'user_name' => $user_name,
                    'user_email' => ($user && count($user) > 0 ? $user[0]['email'] : ''),
                    'user_phone' => ($user && count($user) > 0 ? $user[0]['contact_number'] : ''),
                    'total_price' => $booking['total_price'],
                    'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                    'booking_status' => $booking['booking_status'],
                    'payment_img' => $booking['payment_img'],
                    'services' => $services
                ];
            } catch (Exception $e) {
                error_log("Error processing booking {$booking['bookingid']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $result;
    }
    
    public function getBookingDetailsForAdmin($php_fetch, $bookings_table, $users_table, $booking_details_table, $services_table, $bookingid)
    {
        try {
            // Get booking details
            $booking = $php_fetch($bookings_table, '*', ['bookingid' => $bookingid]);
            
            if (!$booking || count($booking) === 0) {
                return ['status' => 'error', 'message' => 'Booking not found'];
            }
            
            $booking = $booking[0];
            
            // Check if user_id exists and is not empty
            $user = null;
            if (isset($booking['user_id']) && !empty($booking['user_id'])) {
                $user = $php_fetch($users_table, 'full_name, email, contact_number', ['user_id' => $booking['user_id']]);
                // Check if user fetch returned an error (empty user_id case)
                if (isset($user['error'])) {
                    $user = null;
                }
            }
            
            // Get booking details and services
            $booking_details = $php_fetch($booking_details_table, '*', ['booking_id' => $bookingid]);
            
            $services = [];
            if ($booking_details) {
                foreach ($booking_details as $detail) {
                    $service = $php_fetch($services_table, 'service_name, description, per_minute', ['id' => $detail['service_id']]);
                    if ($service) {
                        $services[] = [
                            'name' => $service[0]['service_name'],
                            'description' => $service[0]['description'] ?? 'No description available',
                            'duration' => $service[0]['per_minute'] ?? 0,
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price']
                        ];
                    }
                }
            }
            
            return [
                'bookingid' => $booking['bookingid'],
                'user_name' => ($user && count($user) > 0 ? $user[0]['full_name'] : 'Unknown User'),
                'user_email' => ($user && count($user) > 0 ? $user[0]['email'] : ''),
                'user_phone' => ($user && count($user) > 0 ? $user[0]['contact_number'] : ''),
                'total_price' => $booking['total_price'],
                'booking_date' => $booking['date_created'] ?? date('Y-m-d H:i:s'),
                'booking_status' => $booking['booking_status'],
                'payment_img' => $booking['payment_img'] ?? null,
                'services' => $services
            ];
        } catch (Exception $e) {
            error_log("Error fetching booking details for admin: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    //! ============================== END ADMIN BOOKING MANAGEMENT ==============================

    public function createBooking($php_insert, $table, $data)
    {
        $insert = $php_insert($table, $data);

        // Debug output
        file_put_contents('debug_insert.txt', print_r($insert, true));

        // Check if $insert is an array or not
        if (!is_array($insert)) {
            file_put_contents('debug_error.txt', "Insert returned non-array:\n" . print_r($insert, true));
            return ['status' => 'error', 'message' => 'Insert failed: non-array result'];
        }

        if (isset($insert['error'])) {
            return ['status' => 'error', 'message' => 'Insert failed with error'];
        }

        // Return the array directly, not JSON encoded
        return $insert[0] ?? $insert; // Supabase returns array of inserted records
    }



    public function getBookingsByUser($php_fetch, $table, $user_id) {
        $result = $php_fetch($table, '*', ['user_id' => $user_id]);

        if ($result && count($result) > 0) {
            return $result;
        } else {
            return ['status' => 'nodata'];
        }
    }


    public function updateBookingStatus($php_update, $table, $bookingid, $status)
    {
        $update = $php_update($table, ['bookingid' => $bookingid], ['booking_status' => $status]);
        return isset($update['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
    }

    public function uploadPayment($php_update, $table, $bookingid, $image)
    {
        $update = $php_update($table, ['bookingid' => $bookingid], [
            'payment_status' => true,
            'payment_img' => $image,
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
        $update = $php_update($table, ['bookingdetailsid' => $detailid], ['status' => $status]);
        return isset($update['error']) ? json_encode(['status' => 'error']) : json_encode(['status' => 'success']);
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
