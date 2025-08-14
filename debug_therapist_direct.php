<?php
/**
 * Direct database test for therapist data
 * This bypasses the controller to test database access directly
 */

echo "<h1>🔍 Direct Therapist Database Test</h1>";

try {
    // Include the connection file
    require_once 'config/connection.php';
    
    echo "<p>✅ Connection file loaded successfully</p>";
    
    // Test if $php_fetch function exists
    if (function_exists('php_fetch')) {
        echo "<p>✅ php_fetch function exists</p>";
    } else {
        echo "<p>❌ php_fetch function not found</p>";
        exit;
    }
    
    echo "<h2>📊 Testing Therapist Table</h2>";
    
    // Test basic therapist fetch
    $therapists = $php_fetch('therapist', '*', []);
    
    if ($therapists) {
        echo "<p>✅ Successfully fetched therapist data</p>";
        echo "<p><strong>Number of therapists found:</strong> " . count($therapists) . "</p>";
        
        // Show first therapist as sample
        if (count($therapists) > 0) {
            echo "<h3>📋 Sample Therapist Record:</h3>";
            echo "<pre>" . print_r($therapists[0], true) . "</pre>";
            
            echo "<h3>🔧 Available Columns:</h3>";
            $columns = array_keys($therapists[0]);
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li><code>$column</code></li>";
            }
            echo "</ul>";
        }
        
        // Test all therapists summary
        echo "<h3>👥 All Therapists Summary:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>ID</th>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>Name</th>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>Service IDs</th>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>Description</th>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>Has is_active?</th>";
        echo "</tr>";
        
        foreach ($therapists as $therapist) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . ($therapist['therapistid'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . ($therapist['therapist_name'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . ($therapist['service_id'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . substr($therapist['therapist_desc'] ?? 'N/A', 0, 50) . "...</td>";
            echo "<td style='padding: 8px;'>" . (isset($therapist['is_active']) ? '✅ Yes' : '❌ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p>⚠️ No therapist data found or query failed</p>";
    }
    
    echo "<h2>🔧 Testing Services Table</h2>";
    
    // Test services fetch
    $services = $php_fetch('services', 'id, service_name', []);
    
    if ($services) {
        echo "<p>✅ Successfully fetched services data</p>";
        echo "<p><strong>Number of services found:</strong> " . count($services) . "</p>";
        
        echo "<h3>📋 Available Services:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>ID</th>";
        echo "<th style='padding: 8px; background: #f0f0f0;'>Service Name</th>";
        echo "</tr>";
        
        foreach ($services as $service) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . ($service['id'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 8px;'>" . ($service['service_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p>⚠️ No services data found or query failed</p>";
    }
    
    echo "<h2>🎯 Simulation of Controller Logic</h2>";
    
    if ($therapists && $services) {
        // Simulate the controller logic
        $allServices = [];
        foreach ($services as $service) {
            $allServices[$service['id']] = $service['service_name'];
        }
        
        $result = [];
        foreach ($therapists as $therapist) {
            $serviceNames = [];
            if (isset($therapist['service_id']) && $therapist['service_id']) {
                $serviceIds = array_map('trim', explode(',', $therapist['service_id']));
                foreach ($serviceIds as $serviceId) {
                    if (!empty($serviceId) && isset($allServices[$serviceId])) {
                        $serviceNames[] = $allServices[$serviceId];
                    }
                }
            }
            
            // Check for active status
            $isActive = 1; // Default to active
            if (isset($therapist['is_active'])) {
                $isActive = $therapist['is_active'] ? 1 : 0;
            } else {
                $desc = $therapist['therapist_desc'] ?? '';
                if (strpos($desc, '[INACTIVE]') !== false) {
                    $isActive = 0;
                }
            }
            
            $result[] = [
                'therapistid' => $therapist['therapistid'],
                'therapist_name' => $therapist['therapist_name'] ?? 'Unknown',
                'therapist_desc' => $therapist['therapist_desc'] ?? '',
                'services' => $serviceNames,
                'services_text' => implode(', ', $serviceNames),
                'service_count' => count($serviceNames),
                'is_active' => $isActive ? true : false,
                'status_text' => $isActive ? 'Active' : 'Inactive'
            ];
        }
        
        echo "<p>✅ Controller simulation completed successfully</p>";
        echo "<p><strong>Processed therapists:</strong> " . count($result) . "</p>";
        
        echo "<h3>📊 Simulated Result (JSON):</h3>";
        echo "<pre>" . json_encode(['status' => 'success', 'data' => $result], JSON_PRETTY_PRINT) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>💡 Next Steps</h2>";
echo "<ul>";
echo "<li>If this test works but the controller doesn't, check the controller file for syntax errors</li>";
echo "<li>If no therapists are found, add some test data to the therapist table</li>";
echo "<li>Check the browser console for JavaScript errors in the modal</li>";
echo "<li>Use the network tab to see the actual AJAX request/response</li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
table { margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f0f0f0; font-weight: bold; }
code { background: #e7e7e7; padding: 2px 4px; border-radius: 3px; }
</style>