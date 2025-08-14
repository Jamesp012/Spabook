<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Add Service Process</h2>";

try {
    require_once 'config/connection.php';
    require_once 'model/booking_services_model.php';
    
    $bookingServices = new BookingServices();
    
    echo "<h3>1. Test Service Model Directly</h3>";
    
    // Test with minimal data first
    $test_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='; // 1x1 transparent PNG
    $test_name = 'Debug Test Service ' . date('H:i:s');
    $test_description = 'Debug test service';
    $test_price = 1000;
    $test_duration = 60;
    
    echo "Testing with data:<br>";
    echo "Name: $test_name<br>";
    echo "Description: $test_description<br>";
    echo "Price: $test_price<br>";
    echo "Duration: $test_duration<br>";
    echo "Image length: " . strlen($test_image) . " characters<br><br>";
    
    // Test image upload function first
    echo "<h4>1a. Test Image Upload Function</h4>";
    
    if (function_exists('uploadProfileImage')) {
        echo "✅ uploadProfileImage function exists<br>";
        
        try {
            $clean_name = str_replace(' ', '_', $test_name);
            $uploaded_image = uploadProfileImage($test_image, $clean_name, 'services_images');
            echo "✅ Image upload result: $uploaded_image<br>";
        } catch (Exception $e) {
            echo "❌ Image upload error: " . $e->getMessage() . "<br>";
            $uploaded_image = null;
        }
    } else {
        echo "❌ uploadProfileImage function not found<br>";
        $uploaded_image = null;
    }
    
    echo "<h4>1b. Test Direct Service Addition</h4>";
    
    // Use a simple image URL if upload failed
    if (!$uploaded_image) {
        $uploaded_image = '../vendor/images/default.png';
        echo "Using default image: $uploaded_image<br>";
    }
    
    $result = $bookingServices->addService($php_fetch, $php_insert, 'services', $uploaded_image, $test_name, $test_description, $test_price, $test_duration);
    
    echo "Add service result: $result<br>";
    
    $decoded_result = json_decode($result, true);
    echo "Decoded result: ";
    var_dump($decoded_result);
    echo "<br>";
    
    if ($result === '"success"') {
        echo "✅ Service added successfully!<br>";
    } elseif ($result === '"exists"') {
        echo "⚠️ Service already exists<br>";
    } elseif ($result === '"error"') {
        echo "❌ Database error occurred<br>";
    } else {
        echo "❓ Unexpected result: $result<br>";
    }
    
    echo "<h3>2. Check Current Services</h3>";
    
    $current_services = $php_fetch('services', '*', []);
    echo "Total services in database: " . count($current_services) . "<br>";
    
    foreach ($current_services as $service) {
        echo "ID: {$service['id']} - Name: {$service['service_name']}<br>";
    }
    
    echo "<h3>3. Test Controller Simulation</h3>";
    
    // Simulate the exact POST data the modal sends
    $_POST = [
        'action' => 'add_service',
        'image' => $test_image,
        'name' => 'Controller Test ' . date('H:i:s'),
        'description' => 'Controller test service',
        'price' => '1500',
        'duration' => '45'
    ];
    
    echo "Simulating controller POST with data:<br>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    ob_start();
    try {
        // Include the controller logic
        $imagebase64 = $_POST['image'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $cleanName = str_replace(' ', '_', $name);
        
        if (function_exists('uploadProfileImage')) {
            $imageupload = uploadProfileImage($imagebase64, $cleanName, 'services_images');
        } else {
            $imageupload = '../vendor/images/default.png';
        }
        
        echo $bookingServices->addService($php_fetch, $php_insert, 'services', $imageupload, $name, $description, $price, $duration);
        
    } catch (Exception $e) {
        echo "Controller error: " . $e->getMessage();
    }
    $controller_output = ob_get_clean();
    
    echo "Controller output: $controller_output<br>";
    
    echo "<h3>4. Check Image Upload Function Details</h3>";
    
    if (file_exists('helper/upload_helper.php')) {
        echo "✅ Upload helper exists<br>";
        include_once 'helper/upload_helper.php';
        
        // Check if function was loaded
        if (function_exists('uploadProfileImage')) {
            echo "✅ Function loaded successfully<br>";
        } else {
            echo "❌ Function not loaded after include<br>";
        }
    } else {
        echo "❌ Upload helper file not found<br>";
    }
    
    echo "<h3>5. Manual Service Insert Test</h3>";
    
    // Try direct insert without image upload
    $manual_service = [
        'service_name' => 'Manual Test ' . date('H:i:s'),
        'description' => 'Manual insert test',
        'price' => 2000,
        'per_minute' => 30,
        'service_picture' => '../vendor/images/default.png'
    ];
    
    echo "Manual insert data:<br>";
    echo "<pre>" . print_r($manual_service, true) . "</pre>";
    
    $manual_result = $php_insert('services', $manual_service);
    
    echo "Manual insert result:<br>";
    echo "<pre>" . print_r($manual_result, true) . "</pre>";
    
    if (isset($manual_result['error'])) {
        echo "❌ Manual insert failed: " . $manual_result['error'] . "<br>";
    } else {
        echo "✅ Manual insert succeeded!<br>";
    }
    
    echo "<h3>6. Final Services Check</h3>";
    
    $final_services = $php_fetch('services', '*', []);
    echo "Total services now: " . count($final_services) . "<br>";
    
    foreach ($final_services as $service) {
        echo "ID: {$service['id']} - Name: {$service['service_name']}<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Critical Error:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; overflow-x: auto; }
h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
h4 { color: #555; margin-top: 20px; }
</style>