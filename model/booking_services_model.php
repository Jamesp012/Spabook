<?php

class BookingServices
{
    //! ============================================================ SERVICES SECTION ============================================================
    public function fetchServices($php_fetch, $table)
    {
        $item_data = array();
        $ervice_data = $php_fetch($table, 'id,service_name, description, price, per_minute, service_picture');
        if (!empty($ervice_data)) {
            foreach ($ervice_data as $row) {
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
            return json_encode($item_data);
        } else {
            // No rows found; return null or an empty object
            return json_encode('nodata');
        }
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
        $check_service = $php_fetch($table, 'id', [
            'service_name' => $name,
            'id !=' => $serviceid
        ]);
        if (is_array($check_service) && isset($check_service[0]['id'])) {
            // Update service
            $update_service = $php_update($table, [
                'service_name' => $name,
                'description' => $description,
                'price' => $price,
                'per_minute' => $duration,
                'service_picture' => $image
            ], ['id' => $serviceid]);
            if (isset($update_service['error'])) {
                // Handle error
                return json_encode('error');
            } else {
                // Service updated successfully
                return json_encode('success');
            }
        } else {
            // Service does not exist
            return json_encode('notfound');
        }
    }

    //! ============================================================ SERVICES SECTION END ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

    //! ============================================================ ADMIN SECTION ============================================================

}
