<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🎯 Admin Therapist Load Test</h2>";

try {
    require_once 'config/connection.php';
    require_once 'model/therapist_model.php';
    
    $TherapistModel = new TherapistModel();
    
    echo "<h3>1. Test getAllTherapistsWithServices (what admin calls)</h3>";
    
    $result = $TherapistModel->getAllTherapistsWithServices($php_fetch, 'therapist', 'services');
    
    echo "Result type: " . gettype($result) . "<br>";
    echo "Raw result: <pre>" . htmlspecialchars($result) . "</pre>";
    
    $decoded = json_decode($result, true);
    if ($decoded !== null) {
        echo "<h4>Decoded Result:</h4>";
        echo "Type: " . gettype($decoded) . "<br>";
        
        if (is_array($decoded)) {
            echo "Count: " . count($decoded) . "<br>";
            
            if (count($decoded) > 0) {
                echo "<h5>First therapist:</h5>";
                echo "<pre>" . print_r($decoded[0], true) . "</pre>";
            }
        } else {
            echo "Non-array result: <pre>" . print_r($decoded, true) . "</pre>";
        }
    } else {
        echo "JSON decode failed: " . json_last_error_msg() . "<br>";
    }
    
    echo "<h3>2. Test getTherapistServices for each therapist</h3>";
    
    // Get all therapists first
    $therapists = $php_fetch('therapist', '*', []);
    
    if (is_array($therapists)) {
        foreach ($therapists as $therapist) {
            echo "<h4>Therapist {$therapist['therapistid']}: {$therapist['therapist_name']}</h4>";
            echo "Service ID field: '{$therapist['service_id']}'<br>";
            
            $services = $TherapistModel->getTherapistServices($therapist['therapistid'], $php_fetch);
            echo "Found services: " . count($services) . "<br>";
            
            if (!empty($services)) {
                echo "Services: <pre>" . print_r($services, true) . "</pre>";
            } else {
                echo "❌ No services found<br>";
                
                // Debug why
                echo "Checking if service exists in services table...<br>";
                $service_check = $php_fetch('services', '*', ['id' => $therapist['service_id']]);
                echo "Direct service check: <pre>" . print_r($service_check, true) . "</pre>";
            }
            echo "<hr>";
        }
    }
    
    echo "<h3>3. Test Direct Controller Call</h3>";
    
    // Simulate the exact AJAX call
    $_POST['action'] = 'get_all_therapists_admin';
    
    ob_start();
    include 'controller/therapist_contr.php';
    $controller_output = ob_get_clean();
    
    echo "Controller output: <pre>" . htmlspecialchars($controller_output) . "</pre>";
    
    $controller_decoded = json_decode($controller_output, true);
    if ($controller_decoded !== null) {
        echo "Controller decoded: <pre>" . print_r($controller_decoded, true) . "</pre>";
    } else {
        echo "Controller JSON decode failed: " . json_last_error_msg() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; overflow-x: auto; max-height: 300px; overflow-y: auto; }
h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
h4 { color: #555; margin-top: 20px; }
hr { margin: 10px 0; border: 1px solid #ddd; }
</style>