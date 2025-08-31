<?php
require_once 'config/connection.php';

echo "<h2>🔧 Services Database Test</h2>";

try {
    // Test if services table exists and has data
    $services = $php_fetch('services', 'id, service_name, description, price, per_minute', []);
    
    echo "<h3>📊 Services in Database:</h3>";
    
    if (!empty($services)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Found " . count($services) . " services:</strong><br><br>";
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Service Name</th><th>Description</th><th>Price</th><th>Duration</th>";
        echo "</tr>";
        
        foreach ($services as $service) {
            echo "<tr>";
            echo "<td>{$service['id']}</td>";
            echo "<td><strong>{$service['service_name']}</strong></td>";
            echo "<td>{$service['description']}</td>";
            echo "<td>₱{$service['price']}</td>";
            echo "<td>{$service['per_minute']} minutes</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        echo "<p style='color: green;'><strong>✅ Services are available! Your therapist modal should be able to load these services.</strong></p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ No services found in database!</strong><br><br>";
        echo "This is why you can't select services for therapists. You need to add services first.";
        echo "</div>";
        
        echo "<h3>🚀 How to Add Services:</h3>";
        echo "<ol>";
        echo "<li><strong>Go to Admin Panel → Manage Services</strong></li>";
        echo "<li><strong>Click 'Add Services' button</strong></li>";
        echo "<li><strong>Fill in service details (name, description, price, duration)</strong></li>";
        echo "<li><strong>Save the service</strong></li>";
        echo "<li><strong>Then come back to Manage Therapists</strong></li>";
        echo "</ol>";
        
        echo "<p style='color: orange;'><strong>⚠️ You must have services before you can assign therapists to them!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Test the new endpoint
echo "<hr>";
echo "<h3>🧪 Testing Services API Endpoint:</h3>";

try {
    echo "<p><strong>Testing POST request to booking_services_contr.php...</strong></p>";
    
    // Simulate the POST request that the modal makes
    $_POST['action'] = 'get_services';
    
    ob_start();
    include 'controller/booking_services_contr.php';
    $api_result = ob_get_clean();
    
    echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 3px; font-family: monospace;'>";
    echo "<strong>API Response:</strong><br>";
    echo htmlspecialchars($api_result);
    echo "</div>";
    
    $decoded = json_decode($api_result, true);
    if ($decoded && is_array($decoded)) {
        echo "<p style='color: green;'><strong>✅ API endpoint is working correctly!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ API endpoint returned unexpected format.</strong></p>";
    }
    
    unset($_POST['action']);
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ API test failed: " . $e->getMessage() . "</strong></p>";
}

echo "<hr>";
echo "<p><a href='views/admin_home_page.php'>← Back to Admin Panel</a></p>";
?>