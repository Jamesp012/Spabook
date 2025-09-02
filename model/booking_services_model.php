<?php

class BookingServices
{
    //! ============================================================ SERVICES SECTION ============================================================
    public function fetchServices($php_fetch)
    {
        $item_data = array();
        $service_data = $php_fetch('services', 'id,service_name, description, price, per_minute, service_picture', ['order' => 'service_name.asc']);
        if (!empty($service_data)) {
            foreach ($service_data as $row) {
                // Optimize image data - you could implement image compression here
                $optimized_image = $this->optimizeImageData($row['service_picture']);
                
                $item_data[] = array(
                    'id' => $row['id'], // Use 'id' to match frontend expectations
                    'service_name' => $row['service_name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'per_minute' => $row['per_minute'],
                    'service_picture' => $optimized_image
                );
            }

            return json_encode($item_data);
        } else {
            return json_encode('nodata');
        }
    }

    private function optimizeImageData($imageData) {
        // For now, just return the original data
        // In the future, you could implement image compression here
        return $imageData;
    }
    public function getServiceById($php_fetch, $serviceid)
    {
        // Fetch service data by ID
        $service_data = $php_fetch('services', 'id, service_name, description, price, per_minute, service_picture', ['id' => $serviceid]);
        if (!empty($service_data) && isset($service_data[0])) {
            // Return the first row only
            return json_encode($service_data[0]);
        } else {
            // No rows found; return null or an empty object
            return json_encode(['status' => 'error', 'message' => 'Service not found']);
        }
    }

    public function addService($php_fetch, $php_insert, $image, $name, $description, $price, $duration)
    {
        try {
            // Check if service already exists
            $check_service = $php_fetch('services', 'id', ['service_name' => $name]);
            if (is_array($check_service) && isset($check_service[0]['id'])) {
                return json_encode(['status' => 'error', 'message' => 'Service already exists']);
            }
            
            // Insert new service
            $insert_service = $php_insert('services', [
                'service_name' => $name,
                'description' => $description,
                'price' => $price,
                'per_minute' => $duration,
                'service_picture' => $image
            ]);
            
            if (isset($insert_service['error'])) {
                return json_encode(['status' => 'error', 'message' => 'Failed to add service']);
            } else {
                return json_encode(['status' => 'success', 'message' => 'Service added successfully']);
            }
        } catch (Exception $e) {
            error_log("Error in addService: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }


    public function updateService($php_fetch, $php_update, $serviceid, $image, $name, $description, $price, $duration)
    {
        try {
            // Check if service exists
            $existing = $php_fetch('services', 'id', ['id' => $serviceid]);

            if (!is_array($existing) || !isset($existing[0]['id'])) {
                return json_encode(['status' => 'error', 'message' => 'Service not found']);
            }

            // Check for name conflict (duplicate name in other services)
            $conflict = $php_fetch('services', 'id', [
                'service_name' => $name,
                'id' => "neq.$serviceid"
            ]);

            if (is_array($conflict) && isset($conflict[0]['id'])) {
                return json_encode(['status' => 'error', 'message' => 'Service name already exists']);
            }

            // Proceed with update
            $update_data = [
                'service_name' => $name,
                'description' => $description,
                'price' => $price,
                'per_minute' => $duration,
                'service_picture' => $image
            ];
            
            $update_service = $php_update('services', $update_data, ['id' => $serviceid]);

            if (isset($update_service['error'])) {
                error_log("Service update error: " . json_encode($update_service['error']));
                return json_encode(['status' => 'error', 'message' => 'Failed to update service']);
            }

            return json_encode(['status' => 'success', 'message' => 'Service updated successfully']);
        } catch (Exception $e) {
            error_log("Service update exception: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function deleteService($php_delete, $serviceid)
    {
        try {
            // Proceed with deletion
            $delete_service = $php_delete('services', ['id' => $serviceid]);

            if (isset($delete_service['error'])) {
                return json_encode(['status' => 'error', 'message' => 'Failed to delete service']);
            }

            return json_encode(['status' => 'success', 'message' => 'Service deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteService: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    //! ============================================================ SERVICES SECTION END ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

}
