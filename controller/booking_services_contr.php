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
            echo $bookingServices->fetchServices($php_fetch);
            break;

        case 'add_service':
            if (empty($_POST['image']) || empty($_POST['name']) || !isset($_POST['price']) || !isset($_POST['duration'])) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields: image, name, price, duration']);
                break;
            }

            $imagebase64 = $_POST['image'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price']);
            $duration = intval($_POST['duration']);
            $commission = floatval($_POST['commission']);

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Service name cannot be empty']);
                break;
            }

            $cleanName = str_replace(' ', '_', $name);
            $imageupload = uploadProfileImage($imagebase64, $cleanName, 'services_images');

            if ($imageupload === false) {
                echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
                break;
            }

            echo $bookingServices->addService($php_fetch, $php_insert, $imageupload, $name, $description, $price, $duration, $commission);
            break;

        case 'update_service':
            if (empty($_POST['serviceid']) || empty($_POST['name']) || !isset($_POST['price']) || !isset($_POST['duration'])) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields: serviceid, name, price, duration']);
                break;
            }

            $serviceid = intval($_POST['serviceid']);
            $imagebase64 = $_POST['image'] ?? '';
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price']);
            $duration = intval($_POST['duration']);
            $commission = floatval($_POST['commission'] ?? 0);

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Service name cannot be empty']);
                break;
            }

            $cleanName = str_replace(' ', '_', $name);

            // Handle image update logic
            if ($imagebase64 && strpos($imagebase64, 'data:') === 0) {
                // New image uploaded (base64 data)
                $imageupload = uploadProfileImage($imagebase64, $cleanName, 'services_images');
                if ($imageupload === false) {
                    echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
                    break;
                }
            } else {
                // No new image or existing URL - keep the current image
                $imageupload = $imagebase64; // Use existing image URL
            }

            echo $bookingServices->updateService($php_fetch, $php_update, $serviceid, $imageupload, $name, $description, $price, $duration, $commission);
            break;

        case 'delete_service':
            if (empty($_POST['serviceid'])) {
                echo json_encode(['status' => 'error', 'message' => 'Service ID is required']);
                break;
            }

            $serviceid = intval($_POST['serviceid']);
            echo $bookingServices->deleteService($php_delete, $serviceid);
            break;

        case 'get_service_by_id':
            if (empty($_POST['serviceid'])) {
                echo json_encode(['status' => 'error', 'message' => 'Service ID is required']);
                break;
            }

            $serviceid = intval($_POST['serviceid']);
            echo $bookingServices->getServiceById($php_fetch, $serviceid);
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

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}
