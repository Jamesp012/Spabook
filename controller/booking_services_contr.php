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
            $image = $_POST['image'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration'];
            echo $bookingServices->addService($php_fetch, $php_insert, 'services', $image, $name, $description, $price, $duration);
            break;

        case 'update_service':
            $serviceid = $_POST['serviceid'];
            $image = $_POST['image'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration'];
            echo $bookingServices->updateService($php_fetch, $php_update, 'services', $serviceid, $image, $name, $description, $price, $duration);
            break;

        case 'get_service_by_id':
            $serviceid = $_POST['serviceid'];
            echo $bookingServices->getServiceById($php_fetch, 'services', $serviceid);
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
