<?php
require_once 'config/connection.php';

echo "<h2>🔍 Therapist Table Analysis</h2>";

try {
    // Check table structure
    echo "<h3>1. Table Structure</h3>";
    $structure = $php_fetch_direct("DESCRIBE therapist");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($structure as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>" . ($column['Key'] ?? '') . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($column['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check data
    echo "<h3>2. Current Data</h3>";
    $therapists = $php_fetch('therapist', '*');
    
    if ($therapists && count($therapists) > 0) {
        echo "<p style='color: green;'>✅ Found " . count($therapists) . " therapist record(s)</p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Name</th><th>Service ID</th><th>Description</th><th>Rate</th><th>Actions</th></tr>";
        
        foreach ($therapists as $therapist) {
            echo "<tr>";
            echo "<td>" . ($therapist['therapistid'] ?? 'NULL') . "</td>";
            echo "<td>" . ($therapist['therapist_name'] ?? 'NULL') . "</td>";
            echo "<td style='font-weight: bold; color: blue;'>" . ($therapist['service_id'] ?? 'NULL') . "</td>";
            echo "<td>" . (isset($therapist['therapist_desc']) ? substr($therapist['therapist_desc'], 0, 40) . '...' : 'NULL') . "</td>";
            echo "<td>" . ($therapist['rate'] ?? 'NULL') . "</td>";
            echo "<td>";
            echo "<button onclick=\"testTherapist(" . ($therapist['therapistid'] ?? 0) . ")\" style='padding: 2px 8px; margin: 2px;'>Test</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No therapist records found!</p>";
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h4>🚨 Problem Identified</h4>";
        echo "<p><strong>No therapists in database!</strong> This is why therapists are not displaying in the booking modal.</p>";
        echo "<h5>Quick Fix Options:</h5>";
        echo "<ol>";
        echo "<li><strong>Add therapists via Admin Panel:</strong> Go to Admin → Manage Therapists → Add Therapist</li>";
        echo "<li><strong>Add sample data:</strong> <button onclick='addSampleTherapists()' style='background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px;'>Add Sample Therapists</button></li>";
        echo "</ol>";
        echo "</div>";
    }
    
    // Check services for reference
    echo "<h3>3. Available Services (for reference)</h3>";
    $services = $php_fetch('services', 'id, service_name');
    
    if ($services && count($services) > 0) {
        echo "<p style='color: green;'>✅ Found " . count($services) . " service(s)</p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Service ID</th><th>Service Name</th></tr>";
        
        foreach ($services as $service) {
            echo "<tr>";
            echo "<td style='font-weight: bold;'>" . $service['id'] . "</td>";
            echo "<td>" . $service['service_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Note:</strong> Therapists need to have service_id values that match the Service IDs above.</p>";
    } else {
        echo "<p style='color: red;'>❌ No services found!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<script src="vendor/js/jquery.min.js"></script>
<script>
function testTherapist(therapistId) {
    console.log('Testing therapist:', therapistId);
    alert('Testing therapist ID: ' + therapistId + '\n\nCheck browser console for detailed results.');
    
    // Test the therapist by ID endpoint
    $.ajax({
        url: 'controller/therapist_contr.php',
        type: 'POST',
        data: {
            action: 'get_therapist_by_id',
            therapist_id: therapistId
        },
        success: function(response) {
            console.log('Therapist test result:', response);
        },
        error: function(xhr, status, error) {
            console.error('Therapist test error:', error);
        }
    });
}

function addSampleTherapists() {
    if (!confirm('This will add 3 sample therapists to your database. Continue?')) {
        return;
    }
    
    // Get first service ID for assignment
    $.ajax({
        url: 'controller/booking_services_contr.php',
        type: 'POST',
        data: { action: 'fetch_services' },
        success: function(services) {
            if (services && services.length > 0) {
                const serviceId = services[0].id;
                addTherapist('Dr. Sarah Johnson', serviceId, 'Licensed massage therapist with 10+ years experience', 800);
                addTherapist('Maria Santos', serviceId, 'Certified spa specialist and wellness expert', 650);
                addTherapist('James Wilson', serviceId, 'Professional therapeutic massage practitioner', 750);
            } else {
                alert('No services found. Please add services first.');
            }
        }
    });
}

function addTherapist(name, serviceId, desc, rate) {
    $.ajax({
        url: 'controller/therapist_contr.php',
        type: 'POST',
        data: {
            action: 'add_therapist',
            therapist_name: name,
            service_id: serviceId,
            therapist_desc: desc,
            rate: rate
        },
        success: function(response) {
            console.log('Added therapist:', name, response);
            if (response.status === 'success') {
                location.reload(); // Refresh to show new data
            }
        }
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h3 { color: #0056b3; margin-top: 30px; }
</style>