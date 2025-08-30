<?php
// Run the test
if (isset($_POST['action'])) {
    date_default_timezone_set('Asia/Manila');
    require_once '../config/connection.php';
    require_once '../model/booking_services_model.php';
    $bookingServices = new BookingServices();
    $action = trim($_POST['action']);
    $current_date = date('Y-m-d');
    $timestamp = new DateTime('now');
    $current_datetimestamp = $timestamp->format('Y-m-d H:i:s');
    switch ($action) {
        case 'fetch_services':
            echo $bookingServices->fetchServices($php_fetch, 'services');
            break;

        case 'add_service':
            $imagebase64 = $_POST['image'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration'];
            $cleanName = str_replace(' ', '_', $name);
            $imageupload = uploadProfileImage($imagebase64, $cleanName, 'services_images');
            echo $bookingServices->addService($php_fetch, $php_insert, 'services', $imageupload, $name, $description, $price, $duration);
            break;

        case 'update_service':
            $serviceid = $_POST['serviceid'];
            $imagebase64 = $_POST['image'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration'] ?? 0;
            $cleanName = str_replace(' ', '_', $name);
            
            // Handle image update logic
            if ($imagebase64 && strpos($imagebase64, 'data:') === 0) {
                // New image uploaded (base64 data)
                $imageupload = uploadProfileImage($imagebase64, $cleanName, 'services_images');
                if ($imageupload === false) {
                    echo json_encode('image_upload_failed');
                    break;
                }
            } else {
                // No new image or existing URL - keep the current image
                $imageupload = $imagebase64; // Use existing image URL
            }
            
            // Remove type and stock parameters since they don't exist in services table
            echo $bookingServices->updateService($php_fetch, $php_update, 'services', $serviceid, $imageupload, $name, $description, $price, $duration);
            break;

        case 'delete_service':
            $serviceid = $_POST['serviceid'];
            echo $bookingServices->deleteService($php_delete, 'services', $serviceid);
            break;
        case 'get_service_by_id':
            $serviceid = $_POST['serviceid'];
            echo $bookingServices->getServiceById($php_fetch, 'services', $serviceid);
            break;

        case 'get_services':
            // Get simplified services list for dropdowns
            $service_data = $php_fetch('services', 'id, service_name', ['order' => 'service_name.asc']);
            if (!empty($service_data)) {
                echo json_encode($service_data);
            } else {
                echo json_encode([]);
            }
            break;

        case 'load_image_base64':
            $image_data = $_POST['image'];
            $image_array_1 = explode(";", $image_data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $image_data = base64_decode($image_array_2[1]);
            $photo_base64 = base64_encode($image_data);
            echo $photo_base64;
            break;
    }
}
