<?php
// Database structure and permissions test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🗄️ Database Structure Test</h2>";

try {
    require_once 'config/connection.php';
    echo "✅ Connection loaded<br>";
    
    echo "<h3>1. Test Therapist Table Structure</h3>";
    
    // Try to fetch with minimal fields
    echo "<h4>Fetching existing therapists:</h4>";
    $therapists = $php_fetch('therapist', '*', []);
    
    if (is_array($therapists)) {
        echo "✅ Therapists table accessible, found " . count($therapists) . " records<br>";
        
        if (count($therapists) > 0) {
            echo "<h5>Sample therapist record structure:</h5>";
            echo "<pre>" . print_r($therapists[0], true) . "</pre>";
            
            echo "<h5>Available fields in therapist table:</h5>";
            $fields = array_keys($therapists[0]);
            echo "Fields: " . implode(', ', $fields) . "<br>";
        } else {
            echo "No existing therapists found<br>";
        }
    } else {
        echo "❌ Error fetching therapists:<br>";
        echo "<pre>" . print_r($therapists, true) . "</pre>";
    }
    
    echo "<h3>2. Test Services Table</h3>";
    
    $services = $php_fetch('booking_services', '*', []);
    
    if (is_array($services)) {
        echo "✅ Services table accessible, found " . count($services) . " records<br>";
        
        if (count($services) > 0) {
            echo "<h5>Sample service record:</h5>";
            echo "<pre>" . print_r($services[0], true) . "</pre>";
        }
    } else {
        echo "❌ Error fetching services:<br>";
        echo "<pre>" . print_r($services, true) . "</pre>";
    }
    
    echo "<h3>3. Test Minimal Insert</h3>";
    
    // Try the simplest possible insert
    $minimal_data = [
        'therapist_name' => 'Minimal Test ' . date('H:i:s')
    ];
    
    echo "Trying minimal insert with just therapist_name:<br>";
    echo "<pre>" . print_r($minimal_data, true) . "</pre>";
    
    $minimal_result = $php_insert('therapist', $minimal_data);
    echo "Minimal insert result:<br>";
    echo "<pre>" . print_r($minimal_result, true) . "</pre>";
    
    // Check for common error patterns
    if (is_array($minimal_result)) {
        if (isset($minimal_result['error'])) {
            echo "❌ Insert failed with error: " . $minimal_result['error'] . "<br>";
            if (isset($minimal_result['response'])) {
                echo "Error response: " . $minimal_result['response'] . "<br>";
            }
        } else {
            echo "✅ Minimal insert seems successful<br>";
        }
    }
    
    echo "<h3>4. Test Complete Insert</h3>";
    
    // Try with all required fields
    $complete_data = [
        'therapist_name' => 'Complete Test ' . date('H:i:s'),
        'therapist_desc' => 'Complete test description',
        'service_id' => '1',
        'rate' => 1500
    ];
    
    echo "Trying complete insert:<br>";
    echo "<pre>" . print_r($complete_data, true) . "</pre>";
    
    $complete_result = $php_insert('therapist', $complete_data);
    echo "Complete insert result:<br>";
    echo "<pre>" . print_r($complete_result, true) . "</pre>";
    
    // Verify by fetching again
    echo "<h3>5. Verification</h3>";
    $after_test = $php_fetch('therapist', '*', []);
    if (is_array($after_test)) {
        echo "Total therapists after tests: " . count($after_test) . "<br>";
        
        // Show the last few records
        if (count($after_test) > 0) {
            $recent = array_slice($after_test, -3); // Last 3 records
            echo "<h5>Most recent therapist records:</h5>";
            foreach ($recent as $index => $therapist) {
                echo "<strong>Record " . ($index + 1) . ":</strong><br>";
                echo "<pre>" . print_r($therapist, true) . "</pre>";
            }
        }
    }
    
    echo "<h3>6. Check Supabase Debug File</h3>";
    
    $debug_file = 'debug_supabase_insert.txt';
    if (file_exists($debug_file)) {
        echo "📄 Debug file contents:<br>";
        echo "<pre style='background: #f0f8ff; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
        echo htmlspecialchars(file_get_contents($debug_file));
        echo "</pre>";
    } else {
        echo "No debug file found yet<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Exception occurred:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<h5>Stack trace:</h5>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}
pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    overflow-x: auto;
}
h3 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
}
h4 {
    color: #555;
    margin-top: 20px;
}
h5 {
    color: #666;
    margin-top: 15px;
}
</style>