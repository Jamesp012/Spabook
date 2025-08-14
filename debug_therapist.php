<?php
require_once 'config/connection.php';
require_once 'model/therapist_model.php';

// Test therapist loading functionality
$therapistModel = new TherapistModel();

echo "<h2>🔍 Debug Therapist Loading</h2>";

// Test 1: Check if therapists exist in database
echo "<h3>1. Check Therapists Table</h3>";
try {
    $therapists = $php_fetch('therapist', '*');
    
    if ($therapists && count($therapists) > 0) {
        echo "<p style='color: green;'>✅ Found " . count($therapists) . " therapist(s) in database</p>";
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Service ID</th><th>Description</th><th>Rate</th></tr>";
        
        foreach (array_slice($therapists, 0, 5) as $therapist) {
            echo "<tr>";
            echo "<td>" . ($therapist['therapistid'] ?? 'N/A') . "</td>";
            echo "<td>" . ($therapist['therapist_name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($therapist['service_id'] ?? 'N/A') . "</td>";
            echo "<td>" . (isset($therapist['therapist_desc']) ? substr($therapist['therapist_desc'], 0, 30) . '...' : 'N/A') . "</td>";
            echo "<td>" . ($therapist['rate'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No therapists found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error accessing therapist table: " . $e->getMessage() . "</p>";
}

// Test 2: Check services table
echo "<h3>2. Check Services Table</h3>";
try {
    $services = $php_fetch('services', 'id, service_name');
    
    if ($services && count($services) > 0) {
        echo "<p style='color: green;'>✅ Found " . count($services) . " service(s) in database</p>";
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Service ID</th><th>Service Name</th></tr>";
        
        foreach (array_slice($services, 0, 5) as $service) {
            echo "<tr>";
            echo "<td>" . ($service['id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($service['service_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No services found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error accessing services table: " . $e->getMessage() . "</p>";
}

// Test 3: Test getTherapistsByService for each service
echo "<h3>3. Test Therapists by Service</h3>";
try {
    if (isset($services) && $services && count($services) > 0) {
        foreach (array_slice($services, 0, 3) as $service) {
            $service_id = $service['id'];
            echo "<h4>Service: {$service['service_name']} (ID: {$service_id})</h4>";
            
            $result = $therapistModel->getTherapistsByService($php_fetch, 'therapist', $service_id);
            $therapists_for_service = json_decode($result, true);
            
            if ($therapists_for_service === 'nodata') {
                echo "<p style='color: orange;'>⚠️ No therapists available for this service</p>";
            } elseif (is_array($therapists_for_service)) {
                echo "<p style='color: green;'>✅ Found " . count($therapists_for_service) . " therapist(s) for this service</p>";
                
                foreach ($therapists_for_service as $therapist) {
                    echo "<div style='margin-left: 20px;'>- {$therapist['therapist_name']}</div>";
                }
            } else {
                echo "<p style='color: red;'>❌ Error: " . print_r($therapists_for_service, true) . "</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing therapists by service: " . $e->getMessage() . "</p>";
}

// Test 4: Test AJAX endpoint directly
echo "<h3>4. Test AJAX Endpoint</h3>";
if (isset($services) && $services && count($services) > 0) {
    $test_service_id = $services[0]['id'];
    echo "<p>Testing with service ID: {$test_service_id}</p>";
    
    // Simulate the AJAX call
    $_POST['action'] = 'get_therapists_by_service';
    $_POST['service_id'] = $test_service_id;
    
    ob_start();
    try {
        // Include the controller to test it
        include 'controller/therapist_contr.php';
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Controller error: " . $e->getMessage() . "</p>";
    }
    $controller_output = ob_get_clean();
    
    if (!empty($controller_output)) {
        echo "<p>Controller output:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo htmlspecialchars($controller_output);
        echo "</pre>";
    }
}

// Test 5: Check service_id format in therapist table
echo "<h3>5. Service ID Format Analysis</h3>";
if (isset($therapists) && $therapists && count($therapists) > 0) {
    echo "<p>Analyzing service_id formats in therapist table:</p>";
    
    foreach (array_slice($therapists, 0, 5) as $therapist) {
        $service_id = $therapist['service_id'] ?? 'NULL';
        echo "<div style='margin-left: 20px;'>Therapist: {$therapist['therapist_name']} | Service ID: '{$service_id}' | Type: " . gettype($service_id) . "</div>";
        
        // Test parseServiceIds method if possible
        if ($service_id && $service_id !== 'NULL') {
            try {
                $reflection = new ReflectionClass('TherapistModel');
                $method = $reflection->getMethod('parseServiceIds');
                $method->setAccessible(true);
                $parsed_ids = $method->invoke($therapistModel, $service_id);
                echo "<div style='margin-left: 40px; color: blue;'>Parsed IDs: " . print_r($parsed_ids, true) . "</div>";
            } catch (Exception $e) {
                echo "<div style='margin-left: 40px; color: red;'>Parse error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h3 { color: #0056b3; margin-top: 30px; }
h4 { color: #17a2b8; margin-top: 20px; }
pre { max-height: 200px; overflow-y: auto; }
</style>