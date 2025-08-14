<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Fix Services Issue (Updated)</h2>";

try {
    require_once 'config/connection.php';
    
    echo "<h3>1. Current Services and Therapists:</h3>";
    $services = $php_fetch('services', '*', []);
    echo "Found " . count($services) . " services:<br>";
    foreach ($services as $service) {
        echo "ID: {$service['id']} - Name: {$service['service_name']}<br>";
    }
    
    $therapists = $php_fetch('therapist', 'therapistid, therapist_name, service_id', []);
    echo "<br>Therapists and their service IDs:<br>";
    foreach ($therapists as $therapist) {
        echo "ID: {$therapist['therapistid']} - {$therapist['therapist_name']} - Service ID: '{$therapist['service_id']}'<br>";
    }
    
    echo "<h3>2. Add Basic Services (Let Supabase assign IDs):</h3>";
    
    $basic_services = [
        ['service_name' => 'Relaxing Massage', 'description' => 'Full body relaxing massage', 'price' => 1500, 'per_minute' => 30],
        ['service_name' => 'Hot Stone Massage', 'description' => 'Therapeutic hot stone massage', 'price' => 2000, 'per_minute' => 45],
        ['service_name' => 'Facial Treatment', 'description' => 'Deep cleansing facial treatment', 'price' => 1200, 'per_minute' => 60],
        ['service_name' => 'Body Scrub', 'description' => 'Exfoliating body scrub treatment', 'price' => 1800, 'per_minute' => 40],
        ['service_name' => 'Head Massage', 'description' => 'Stress relief head massage', 'price' => 800, 'per_minute' => 20]
    ];
    
    $new_service_ids = [];
    
    foreach ($basic_services as $service) {
        // Check if service name exists
        $existing = $php_fetch('services', '*', ['service_name' => $service['service_name']]);
        
        if (empty($existing)) {
            echo "Adding service: {$service['service_name']}<br>";
            $result = $php_insert('services', $service);
            
            if (isset($result['error'])) {
                echo "❌ Failed to add {$service['service_name']}: " . print_r($result, true) . "<br>";
            } else {
                echo "✅ {$service['service_name']} added successfully<br>";
                if (is_array($result) && isset($result[0]['id'])) {
                    $new_service_ids[] = $result[0]['id'];
                    echo "  → New ID: {$result[0]['id']}<br>";
                }
            }
        } else {
            echo "⚪ Service '{$service['service_name']}' already exists<br>";
            $new_service_ids[] = $existing[0]['id'];
        }
    }
    
    echo "<h3>3. Updated Services List:</h3>";
    $updated_services = $php_fetch('services', '*', []);
    echo "Total services now: " . count($updated_services) . "<br>";
    foreach ($updated_services as $service) {
        echo "ID: {$service['id']} - Name: {$service['service_name']}<br>";
    }
    
    echo "<h3>4. Fix Therapist Service Assignments:</h3>";
    
    if (!empty($new_service_ids)) {
        // Use the first new service ID for therapists with service_id "1"
        $default_service_id = $new_service_ids[0];
        echo "Using service ID {$default_service_id} as default for therapists with missing services<br><br>";
        
        foreach ($therapists as $therapist) {
            if ($therapist['service_id'] == '1') {
                echo "Updating therapist '{$therapist['therapist_name']}' from service ID '1' to '{$default_service_id}'<br>";
                
                $update_result = $php_update('therapist', ['service_id' => (string)$default_service_id], ['therapistid' => $therapist['therapistid']]);
                
                if (isset($update_result['error'])) {
                    echo "❌ Failed to update: " . print_r($update_result, true) . "<br>";
                } else {
                    echo "✅ Updated successfully<br>";
                }
            }
        }
    }
    
    echo "<h3>5. Alternative Fix: Update All to Use Existing Service 69:</h3>";
    echo '<button onclick="updateAllToService69()" style="background: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Update All Therapists to Use Service 69</button><br><br>';
    
    echo "<h3>6. Test Results:</h3>";
    
    require_once 'model/therapist_model.php';
    $TherapistModel = new TherapistModel();
    
    $result = $TherapistModel->getAllTherapistsWithServices($php_fetch, 'therapist', 'services');
    $decoded = json_decode($result, true);
    
    if (is_array($decoded) && count($decoded) > 0) {
        echo "✅ Admin loading working!<br>";
        echo "Total therapists: " . count($decoded) . "<br>";
        
        $with_services = array_filter($decoded, function($t) { return $t['service_count'] > 0; });
        echo "Therapists with services: " . count($with_services) . "<br>";
        
        $without_services = array_filter($decoded, function($t) { return $t['service_count'] == 0; });
        echo "Therapists without services: " . count($without_services) . "<br>";
        
        if (count($without_services) > 0) {
            echo "<h4>Therapists still without services:</h4>";
            foreach ($without_services as $t) {
                echo "- {$t['therapist_name']} (service_id: '{$t['service_id']}')<br>";
            }
        }
        
        if (count($with_services) > 0) {
            echo "<h4>Therapists with services:</h4>";
            foreach ($with_services as $t) {
                echo "- {$t['therapist_name']} → {$t['services_display']}<br>";
            }
        }
    }
    
    echo "<h3>7. Try Admin Interface:</h3>";
    echo '<a href="views/admin/admin_manage-therapists.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 0; display: inline-block;">Go to Admin Therapist Management</a><br>';
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>

<script>
function updateAllToService69() {
    if (confirm('This will update all therapists to use service ID 69 (testing langasd). Continue?')) {
        fetch('fix_services.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=update_all_to_69'
        })
        .then(response => response.text())
        .then(result => {
            alert('Updated! Refresh the page to see results.');
            location.reload();
        });
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
button { margin: 5px 0; }
</style>

<?php
// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_all_to_69') {
    try {
        require_once 'config/connection.php';
        
        $therapists = $php_fetch('therapist', 'therapistid, therapist_name, service_id', []);
        $updated_count = 0;
        
        foreach ($therapists as $therapist) {
            if ($therapist['service_id'] != '69') {
                $update_result = $php_update('therapist', ['service_id' => '69'], ['therapistid' => $therapist['therapistid']]);
                if (!isset($update_result['error'])) {
                    $updated_count++;
                }
            }
        }
        
        echo "Updated {$updated_count} therapists to use service ID 69";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}
?>