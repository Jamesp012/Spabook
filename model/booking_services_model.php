<?php

class BookingServices
{
    //! ============================================================ SERVICES SECTION ============================================================
    public function fetchServices($php_fetch, $table)
    {
        $item_data = array();
        $service_data = $php_fetch($table, 'id,service_name, description, price, per_minute, service_picture', ['order' => 'service_name.asc']);
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
    public function getServiceById($php_fetch, $table, $serviceid)
    {
        // Fetch service data by ID
        $item_data = array();
        $service_data = $php_fetch($table, 'id, service_name, description, price, per_minute, service_picture', ['id' => $serviceid]);
        if (!empty($service_data)) {
            foreach ($service_data as $row) {
                $item_data[] = array(
                    'id' => $row['id'],
                    'service_name' => $row['service_name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'per_minute' => $row['per_minute'],
                    'service_picture' => $row['service_picture']
                );
            }

            // Encode the first row only
            return json_encode($service_data[0]);
        } else {
            // No rows found; return null or an empty object
            return json_encode('nodata');
        }
    }

    public function addService($php_fetch, $php_insert, $table, $image, $name, $description, $price, $duration)
    {
        // Check if service already exists
        $check_service = $php_fetch($table, 'id', ['service_name' => $name]);
        if (is_array($check_service) && isset($check_service[0]['id'])) {
            return json_encode('exists');
        } else {
            // Insert new service
            $insert_service = $php_insert($table, [
                'service_name' => $name,
                'description' => $description,
                'price' => $price,
                'per_minute' => $duration,
                'service_picture' => $image
            ]);
            if (isset($insert_service['error'])) {
                // Handle error
                return json_encode('error');
            } else {
                // Service added successfully
                return json_encode('success');
            }
        }
    }


    public function updateService($php_fetch, $php_update, $table, $serviceid, $image, $name, $description, $price, $duration)
    {
        // Check if service exists
        $existing = $php_fetch($table, 'id', ['id' => $serviceid]);

        if (!is_array($existing) || !isset($existing[0]['id'])) {
            return json_encode('notfound'); // service doesn't exist
        }

        // Check for name conflict (duplicate name in other services)
        $conflict = $php_fetch($table, 'id', [
            'service_name' => $name,
            'id!=' => $serviceid
        ]);

        if (is_array($conflict) && isset($conflict[0]['id'])) {
            return json_encode('duplicate'); // another service with same name exists
        }

        // Proceed with update
        $update_data = [
            'service_name' => $name,
            'description' => $description,
            'price' => $price,
            'per_minute' => $duration,
            'service_picture' => $image,
            'type' => $type,
            'stock' => $stock
        ];
        
        $update_service = $php_update($table, $update_data, ['id' => $serviceid]);

        if (isset($update_service['error'])) {
            return json_encode('error');
        }

        return json_encode('success');
    }

    function deleteService($php_delete, $table, $serviceid)
    {
        // Proceed with deletion
        $delete_service = $php_delete($table, $serviceid);

        if (isset($delete_service['error'])) {
            return json_encode('error');
        }

        return json_encode('success');
    }

    //! ============================================================ SERVICES SECTION END ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

}
