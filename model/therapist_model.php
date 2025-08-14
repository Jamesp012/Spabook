<?php

class TherapistModel
{
    //! ============================== THERAPIST METHODS ==============================
    
    public function getTherapistsByService($php_fetch, $therapist_table, $service_id)
    {
        try {
            // Get all therapists first
            $all_therapists = $php_fetch($therapist_table, '*', []);
            
            if (!$all_therapists || count($all_therapists) === 0) {
                return json_encode('nodata');
            }
            
            $matching_therapists = [];
            
            // Filter therapists who can perform this service
            foreach ($all_therapists as $therapist) {
                $therapist_service_ids = $this->parseServiceIds($therapist['service_id']);
                
                if (in_array($service_id, $therapist_service_ids)) {
                    $matching_therapists[] = $therapist;
                }
            }
            
            if (count($matching_therapists) === 0) {
                return json_encode('nodata');
            }
            
            return json_encode($matching_therapists);
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
            error_log("Schema: therapistid, therapist_name, service_id, therapist_desc, rate");
            
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
            
            // Service IDs are already stored in the main therapist record
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
            error_log("Schema: therapistid, therapist_name, service_id, therapist_desc, rate");
            
            // Validate therapist_id
            if (!$therapist_id) {
                return json_encode(['status' => 'error', 'message' => 'Therapist ID is required']);
            }
            
            // No need to extract service_ids - data contains service_id directly for the schema
            
            // Update therapist (data already contains the correct schema fields)
            $update = $php_update($therapist_table, $data, ['therapistid' => $therapist_id]);
            
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
                $assigned_services = $this->getTherapistServices($therapist['therapistid'], $php_fetch);
                
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

    
    /**
     * Get all services assigned to a therapist
     */
    public function getTherapistServices($therapist_id, $php_fetch = null)
    {
        try {
            // Use passed parameter or get global
            if ($php_fetch === null) {
                global $php_fetch;
            }
            
            if (!$php_fetch) {
                error_log("php_fetch function not available in getTherapistServices");
                return [];
            }
            
            // Get therapist data to find their service_id field
            $therapist = $php_fetch('therapist', 'service_id', ['therapistid' => $therapist_id]);
            
            if (!$therapist || count($therapist) === 0 || empty($therapist[0]['service_id'])) {
                return [];
            }
            
            // Parse service IDs from the service_id text field
            $service_ids = $this->parseServiceIds($therapist[0]['service_id']);
            
            // Get service details for each service ID
            $services = [];
            foreach ($service_ids as $service_id) {
                if (!empty($service_id)) {
                    $service = $php_fetch('services', 'id,service_name,price', ['id' => $service_id]);
                    if ($service && count($service) > 0) {
                        $services[] = $service[0];
                    }
                }
            }
            
            return $services;
            
        } catch (Exception $e) {
            error_log("Error in getTherapistServices: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Parse service IDs from the service_id text field
     * Supports comma-separated values: "1,2,3" or JSON array: "[1,2,3]"
     */
    private function parseServiceIds($service_id_field)
    {
        if (empty($service_id_field)) {
            return [];
        }
        
        // Try to parse as JSON first
        if (substr($service_id_field, 0, 1) === '[') {
            $json_parsed = json_decode($service_id_field, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_parsed)) {
                return array_map('intval', $json_parsed);
            }
        }
        
        // Parse as comma-separated values
        if (strpos($service_id_field, ',') !== false) {
            $ids = explode(',', $service_id_field);
            return array_map('trim', array_map('intval', array_filter($ids)));
        }
        
        // Single service ID
        return [intval(trim($service_id_field))];
    }
    
    /**
     * Convert array of service IDs to string for storage
     */
    private function formatServiceIds($service_ids)
    {
        if (empty($service_ids) || !is_array($service_ids)) {
            return '';
        }
        
        // Filter and convert to integers
        $clean_ids = array_filter(array_map('intval', $service_ids), function($id) {
            return $id > 0;
        });
        
        if (empty($clean_ids)) {
            return '';
        }
        
        // Store as comma-separated values for simplicity
        return implode(',', $clean_ids);
    }
    
    /**
     * Assign multiple services to a therapist (updates existing record)
     */
    public function assignServicesToTherapist($therapist_id, $service_ids)
    {
        try {
            global $php_update;
            
            error_log("=== ASSIGNING SERVICES TO THERAPIST (EXISTING SCHEMA) ===");
            error_log("Therapist ID: " . $therapist_id);
            error_log("Service IDs: " . print_r($service_ids, true));
            
            // Format service IDs as comma-separated string
            $formatted_service_ids = $this->formatServiceIds($service_ids);
            
            // Update the therapist record
            $data = ['service_id' => $formatted_service_ids];
            $result = $php_update('therapist', $data, ['therapistid' => $therapist_id]);
            
            error_log("Service assignment result: " . print_r($result, true));
            
            if (isset($result['error'])) {
                error_log("Error updating therapist services: " . $result['error']);
                return false;
            }
            
            error_log("=== SERVICES ASSIGNED SUCCESSFULLY ===");
            return true;
            
        } catch (Exception $e) {
            error_log("Error in assignServicesToTherapist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a single service to a therapist (appends to existing services)
     */
    public function addServiceToTherapist($therapist_id, $service_id)
    {
        try {
            global $php_fetch;
            
            // Get current services
            $current_services = $this->getTherapistServices($therapist_id, $php_fetch);
            $current_service_ids = array_column($current_services, 'id');
            
            // Add new service if not already present
            if (!in_array($service_id, $current_service_ids)) {
                $current_service_ids[] = $service_id;
                return $this->assignServicesToTherapist($therapist_id, $current_service_ids);
            }
            
            return true; // Already has this service
            
        } catch (Exception $e) {
            error_log("Error in addServiceToTherapist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a single service from a therapist
     */
    public function removeServiceFromTherapist($therapist_id, $service_id)
    {
        try {
            // Get current services
            global $php_fetch;
            $current_services = $this->getTherapistServices($therapist_id, $php_fetch);
            $current_service_ids = array_column($current_services, 'id');
            
            // Remove the service
            $updated_service_ids = array_filter($current_service_ids, function($id) use ($service_id) {
                return $id != $service_id;
            });
            
            return $this->assignServicesToTherapist($therapist_id, $updated_service_ids);
            
        } catch (Exception $e) {
            error_log("Error in removeServiceFromTherapist: " . $e->getMessage());
            return false;
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