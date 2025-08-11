<?php

class TherapistModel
{
    //! ============================== THERAPIST METHODS ==============================
    
    public function getTherapistsByService($php_fetch, $therapist_table, $service_id)
    {
        try {
            // Use JOIN to get therapists assigned to the specific service
            $query = "
                SELECT t.* 
                FROM therapist t
                INNER JOIN therapist_services ts ON t.therapistid = ts.therapist_id
                WHERE ts.service_id = ? AND t.active = 1
                ORDER BY t.therapist_name
            ";
            
            global $connection;
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $therapists = [];
            while ($row = $result->fetch_assoc()) {
                $therapists[] = $row;
            }
            
            if (count($therapists) === 0) {
                return json_encode('nodata');
            }
            
            return json_encode($therapists);
        } catch (Exception $e) {
            error_log("Error in getTherapistsByService: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getAllTherapists($php_fetch, $therapist_table)
    {
        try {
            $therapists = $php_fetch($therapist_table, '*', []);
            
            if (!$therapists || count($therapists) === 0) {
                return json_encode('nodata');
            }
            
            return json_encode($therapists);
        } catch (Exception $e) {
            error_log("Error in getAllTherapists: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getTherapistById($php_fetch, $therapist_table, $therapist_id)
    {
        try {
            $therapist = $php_fetch($therapist_table, '*', ['therapistid' => $therapist_id]);
            
            if (!$therapist || count($therapist) === 0) {
                return json_encode('nodata');
            }
            
            return json_encode($therapist[0]);
        } catch (Exception $e) {
            error_log("Error in getTherapistById: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function addTherapist($php_insert, $therapist_table, $data)
    {
        try {
            error_log("=== THERAPIST MODEL - addTherapist ===");
            error_log("Input data: " . print_r($data, true));
            error_log("Schema: therapistid, therapist_name, service_id, therapist_desc");
            
            // Validate required fields for the actual schema
            if (!isset($data['therapist_name']) || empty($data['therapist_name'])) {
                return json_encode(['status' => 'error', 'message' => 'therapist_name is required']);
            }
            
            if (!isset($data['service_id']) || empty($data['service_id'])) {
                return json_encode(['status' => 'error', 'message' => 'service_id is required']);
            }
            
            error_log("Data validated for schema compatibility");
            
            // Insert therapist (data already contains the correct schema fields)
            $insert = $php_insert($therapist_table, $data);
            
            error_log("Insert result: " . print_r($insert, true));
            
            // Check for Supabase error response
            if (isset($insert['error'])) {
                $errorMsg = $insert['error'];
                $response = $insert['response'] ?? 'No response details';
                error_log("Supabase insert error: " . $errorMsg);
                error_log("Supabase response: " . $response);
                
                // Try to extract more meaningful error from response
                if (is_string($response)) {
                    $decoded_response = json_decode($response, true);
                    if ($decoded_response && isset($decoded_response['message'])) {
                        $errorMsg .= ': ' . $decoded_response['message'];
                    }
                }
                
                return json_encode(['status' => 'error', 'message' => 'Database insert failed: ' . $errorMsg]);
            }
            
            // Check if we got a valid response array
            if (!is_array($insert) || empty($insert)) {
                error_log("Invalid insert response: " . print_r($insert, true));
                return json_encode(['status' => 'error', 'message' => 'Invalid response from database']);
            }
            
            // For Supabase, extract the therapist ID
            $therapist_id = null;
            if (isset($insert[0]['therapistid'])) {
                // Supabase returns array of records
                $therapist_id = $insert[0]['therapistid'];
            } elseif (isset($insert['therapistid'])) {
                // Single record response
                $therapist_id = $insert['therapistid'];
            } elseif (isset($insert[0]['id'])) {
                // Alternative ID field
                $therapist_id = $insert[0]['id'];
            } elseif (isset($insert['id'])) {
                // Single record with ID
                $therapist_id = $insert['id'];
            }
            
            error_log("Extracted therapist ID: " . $therapist_id);
            
            if (!$therapist_id) {
                error_log("Failed to extract therapist ID from insert result");
                error_log("Available keys in response: " . print_r(array_keys($insert), true));
                if (isset($insert[0])) {
                    error_log("Keys in first record: " . print_r(array_keys($insert[0]), true));
                }
                return json_encode(['status' => 'error', 'message' => 'Failed to get therapist ID from database response']);
            }
            
            // No separate service assignment needed - service_id is already in the therapist table
            error_log("=== THERAPIST MODEL - SUCCESS ===");
            return json_encode(['status' => 'success', 'therapist_id' => $therapist_id]);
        } catch (Exception $e) {
            error_log("Exception in addTherapist: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return json_encode(['status' => 'error', 'message' => 'Model error: ' . $e->getMessage()]);
        }
    }
    
    public function updateTherapist($php_update, $therapist_table, $therapist_id, $data)
    {
        try {
            error_log("=== THERAPIST MODEL - updateTherapist ===");
            error_log("Therapist ID: " . $therapist_id);
            error_log("Update data: " . print_r($data, true));
            error_log("Schema: therapistid, therapist_name, service_id, therapist_desc");
            
            // Validate therapist_id
            if (!$therapist_id) {
                return json_encode(['status' => 'error', 'message' => 'Therapist ID is required']);
            }
            
            // No need to extract service_ids - data contains service_id directly for the schema
            
            // Update therapist (data already contains the correct schema fields)
            $update = $php_update($therapist_table, ['therapistid' => $therapist_id], $data);
            
            error_log("Update result: " . print_r($update, true));
            
            if (isset($update['error'])) {
                error_log("Update error: " . $update['error']);
                return json_encode(['status' => 'error', 'message' => 'Update failed: ' . $update['error']]);
            }
            
            // No separate service assignment needed - service_id is directly in therapist table
            
            error_log("=== THERAPIST MODEL - UPDATE SUCCESS ===");
            return json_encode(['status' => 'success']);
        } catch (Exception $e) {
            error_log("Exception in updateTherapist: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return json_encode(['status' => 'error', 'message' => 'Model error: ' . $e->getMessage()]);
        }
    }
    
    public function deleteTherapist($php_update, $therapist_table, $therapist_id)
    {
        try {
            error_log("=== THERAPIST MODEL - deleteTherapist ===");
            error_log("Therapist ID: " . $therapist_id);
            
            // Since 'active' column doesn't exist in schema, we need a different approach
            // For now, we'll skip deletion or implement based on actual requirements
            // TODO: Implement proper deletion strategy for the actual schema
            
            error_log("Delete operation not implemented for current schema");
            return json_encode(['status' => 'error', 'message' => 'Delete operation not available for current schema']);
        } catch (Exception $e) {
            error_log("Error in deleteTherapist: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    //! ============================== ADMIN THERAPIST METHODS ==============================
    
    public function getAllTherapistsWithServices($php_fetch, $therapist_table, $services_table)
    {
        try {
            // Get all therapists
            $therapists = $php_fetch($therapist_table, '*', []);
            
            if (!$therapists || count($therapists) === 0) {
                return json_encode('nodata');
            }
            
            // Enrich therapists with service information
            $enrichedTherapists = [];
            foreach ($therapists as $therapist) {
                // Get all assigned services for this therapist
                $assigned_services = $this->getTherapistServices($therapist['therapistid']);
                
                $enrichedTherapist = $therapist;
                
                // Set services information
                if (!empty($assigned_services)) {
                    $service_names = array_column($assigned_services, 'service_name');
                    $enrichedTherapist['service_names'] = $service_names;
                    $enrichedTherapist['services_display'] = implode(', ', $service_names);
                    $enrichedTherapist['service_count'] = count($service_names);
                    $enrichedTherapist['assigned_services'] = $assigned_services;
                } else {
                    $enrichedTherapist['service_names'] = [];
                    $enrichedTherapist['services_display'] = 'No services assigned';
                    $enrichedTherapist['service_count'] = 0;
                    $enrichedTherapist['assigned_services'] = [];
                }
                
                // Keep backward compatibility
                $enrichedTherapist['service_name'] = $enrichedTherapist['services_display'];
                
                // Set default values if not present
                $enrichedTherapist['active'] = $enrichedTherapist['active'] ?? true;
                $enrichedTherapist['date_added'] = $enrichedTherapist['created_at'] ?? $enrichedTherapist['date_created'] ?? null;
                
                $enrichedTherapists[] = $enrichedTherapist;
            }
            
            return json_encode($enrichedTherapists);
        } catch (Exception $e) {
            error_log("Error in getAllTherapistsWithServices: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getTherapistStats($php_fetch, $therapist_table, $services_table)
    {
        try {
            // Get all therapists
            $therapists = $php_fetch($therapist_table, '*', []);
            
            $stats = [
                'total' => 0,
                'active' => 0,
                'services' => 0,
                'recent' => 0
            ];
            
            if ($therapists && count($therapists) > 0) {
                $stats['total'] = count($therapists);
                
                // Count active therapists
                $activeCount = 0;
                $recentCount = 0;
                $serviceIds = [];
                $oneWeekAgo = date('Y-m-d', strtotime('-7 days'));
                
                foreach ($therapists as $therapist) {
                    // Count active
                    if (!isset($therapist['active']) || $therapist['active'] == true) {
                        $activeCount++;
                    }
                    
                    // Count unique services
                    if ($therapist['service_id'] && !in_array($therapist['service_id'], $serviceIds)) {
                        $serviceIds[] = $therapist['service_id'];
                    }
                    
                    // Count recent additions (last 7 days)
                    $dateAdded = $therapist['created_at'] ?? $therapist['date_created'] ?? null;
                    if ($dateAdded && strtotime($dateAdded) >= strtotime($oneWeekAgo)) {
                        $recentCount++;
                    }
                }
                
                $stats['active'] = $activeCount;
                $stats['services'] = count($serviceIds);
                $stats['recent'] = $recentCount;
            }
            
            return json_encode($stats);
        } catch (Exception $e) {
            error_log("Error in getTherapistStats: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    //! ============================== MULTIPLE SERVICES METHODS ==============================
    
    /**
     * Assign multiple services to a therapist
     */
    public function assignServicesToTherapist($php_insert, $therapist_id, $service_ids)
    {
        try {
            global $connection;
            
            foreach ($service_ids as $service_id) {
                // Check if assignment already exists
                $check_query = "SELECT id FROM therapist_services WHERE therapist_id = ? AND service_id = ?";
                $check_stmt = $connection->prepare($check_query);
                $check_stmt->bind_param("ii", $therapist_id, $service_id);
                $check_stmt->execute();
                $exists = $check_stmt->get_result()->fetch_assoc();
                
                if (!$exists) {
                    // Insert new assignment
                    $php_insert('therapist_services', [
                        'therapist_id' => $therapist_id,
                        'service_id' => $service_id
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in assignServicesToTherapist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update therapist service assignments (replace all)
     */
    public function updateTherapistServices($therapist_id, $service_ids)
    {
        try {
            global $connection;
            
            // Remove all existing assignments
            $delete_query = "DELETE FROM therapist_services WHERE therapist_id = ?";
            $delete_stmt = $connection->prepare($delete_query);
            $delete_stmt->bind_param("i", $therapist_id);
            $delete_stmt->execute();
            
            // Add new assignments
            if (!empty($service_ids)) {
                $insert_query = "INSERT INTO therapist_services (therapist_id, service_id) VALUES (?, ?)";
                $insert_stmt = $connection->prepare($insert_query);
                
                foreach ($service_ids as $service_id) {
                    $insert_stmt->bind_param("ii", $therapist_id, $service_id);
                    $insert_stmt->execute();
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in updateTherapistServices: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all services assigned to a therapist
     */
    public function getTherapistServices($therapist_id)
    {
        try {
            global $connection;
            
            $query = "
                SELECT s.id, s.service_name
                FROM services s
                INNER JOIN therapist_services ts ON s.id = ts.service_id
                WHERE ts.therapist_id = ?
                ORDER BY s.service_name
            ";
            
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $therapist_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            
            return $services;
        } catch (Exception $e) {
            error_log("Error in getTherapistServices: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available therapists for a specific service, date, and time
     */
    public function getAvailableTherapists($php_fetch, $service_id, $date, $time)
    {
        try {
            global $connection;
            
            // Get therapists who are:
            // 1. Assigned to the service
            // 2. Active
            // 3. Not already booked at the specified date and time
            $query = "
                SELECT t.* 
                FROM therapist t
                INNER JOIN therapist_services ts ON t.therapistid = ts.therapist_id
                WHERE ts.service_id = ? 
                AND t.active = 1
                AND t.therapistid NOT IN (
                    SELECT DISTINCT bd.therapist_id
                    FROM booking_details bd
                    INNER JOIN booking b ON bd.booking_id = b.bookingid
                    WHERE DATE(bd.booking_date) = ? 
                    AND TIME(bd.booking_time) = ?
                    AND bd.therapist_id IS NOT NULL
                    AND b.booking_status NOT IN ('Cancelled', 'Declined')
                )
                ORDER BY t.therapist_name
            ";
            
            $stmt = $connection->prepare($query);
            $stmt->bind_param("iss", $service_id, $date, $time);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $therapists = [];
            while ($row = $result->fetch_assoc()) {
                $therapists[] = $row;
            }
            
            if (count($therapists) === 0) {
                return json_encode('nodata');
            }
            
            return json_encode($therapists);
        } catch (Exception $e) {
            error_log("Error in getAvailableTherapists: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
?>