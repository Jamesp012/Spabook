<?php
// Simple connectivity test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Simple Connectivity Test</h2>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

try {
    echo "<h3>1. Loading Connection...</h3>";
    require_once 'config/connection.php';
    echo "✅ Connection file loaded successfully<br>";
    
    echo "<h3>2. Testing Basic Functions...</h3>";
    
    // Test if functions exist
    if (isset($php_fetch) && is_callable($php_fetch)) {
        echo "✅ php_fetch function exists<br>";
    } else {
        echo "❌ php_fetch function missing or not callable<br>";
    }
    
    if (isset($php_insert) && is_callable($php_insert)) {
        echo "✅ php_insert function exists<br>";
    } else {
        echo "❌ php_insert function missing or not callable<br>";
    }
    
    echo "<h3>3. Test Basic Fetch...</h3>";
    
    // Test basic fetch
    if (isset($php_fetch)) {
        $services_test = $php_fetch('booking_services', '*', []);
        echo "Services fetch result type: " . gettype($services_test) . "<br>";
        
        if (is_array($services_test)) {
            echo "Services count: " . count($services_test) . "<br>";
            if (count($services_test) > 0) {
                echo "First service: <pre>" . print_r($services_test[0], true) . "</pre>";
            }
        } else {
            echo "Services result: <pre>" . print_r($services_test, true) . "</pre>";
        }
        
        $therapists_test = $php_fetch('therapist', '*', []);
        echo "Therapists fetch result type: " . gettype($therapists_test) . "<br>";
        
        if (is_array($therapists_test)) {
            echo "Therapists count: " . count($therapists_test) . "<br>";
            if (count($therapists_test) > 0) {
                echo "First therapist: <pre>" . print_r($therapists_test[0], true) . "</pre>";
            }
        } else {
            echo "Therapists result: <pre>" . print_r($therapists_test, true) . "</pre>";
        }
    }
    
    echo "<h3>4. Test Simple Insert...</h3>";
    
    if (isset($php_insert)) {
        $test_data = [
            'therapist_name' => 'Simple Test ' . date('H:i:s'),
            'therapist_desc' => 'Simple test therapist',
            'service_id' => '1',
            'rate' => 1000
        ];
        
        echo "Attempting to insert: <pre>" . print_r($test_data, true) . "</pre>";
        
        $insert_result = $php_insert('therapist', $test_data);
        echo "Insert result type: " . gettype($insert_result) . "<br>";
        echo "Insert result: <pre>" . print_r($insert_result, true) . "</pre>";
        
        // Check if insert worked by fetching again
        echo "<h4>Verification - Fetch after insert:</h4>";
        $after_insert = $php_fetch('therapist', '*', []);
        if (is_array($after_insert)) {
            echo "Total therapists now: " . count($after_insert) . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Exception:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "<h3>❌ Fatal Error:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<h3>5. Check Debug File</h3>";
$debug_file = 'debug_supabase_insert.txt';
if (file_exists($debug_file)) {
    echo "Debug file exists. Contents:<br>";
    echo "<pre>" . file_get_contents($debug_file) . "</pre>";
} else {
    echo "Debug file does not exist yet.<br>";
}

?>

<hr>
<h3>6. Manual Form Test</h3>
<form method="POST" action="simple_test.php">
    <input type="hidden" name="manual_test" value="1">
    
    <label>Therapist Name:</label><br>
    <input type="text" name="therapist_name" value="Form Test Therapist" required style="width:300px;padding:5px;"><br><br>
    
    <label>Description:</label><br>
    <textarea name="therapist_desc" style="width:300px;padding:5px;">Form test</textarea><br><br>
    
    <label>Service ID:</label><br>
    <input type="text" name="service_id" value="1" required style="width:300px;padding:5px;"><br><br>
    
    <label>Rate:</label><br>
    <input type="number" name="rate" value="1500" style="width:300px;padding:5px;"><br><br>
    
    <input type="submit" value="Test Insert via Form" style="padding:10px 20px;background:#007bff;color:white;border:none;border-radius:4px;">
</form>

<?php
if (isset($_POST['manual_test'])) {
    echo "<hr><h3>📝 Manual Form Result:</h3>";
    
    $form_data = [
        'therapist_name' => $_POST['therapist_name'],
        'therapist_desc' => $_POST['therapist_desc'], 
        'service_id' => $_POST['service_id'],
        'rate' => intval($_POST['rate'])
    ];
    
    echo "Form data prepared: <pre>" . print_r($form_data, true) . "</pre>";
    
    if (isset($php_insert)) {
        $form_result = $php_insert('therapist', $form_data);
        echo "Form insert result: <pre>" . print_r($form_result, true) . "</pre>";
        
        // Verify
        $verify = $php_fetch('therapist', '*', []);
        if (is_array($verify)) {
            echo "Total therapists after form insert: " . count($verify) . "<br>";
        }
    } else {
        echo "❌ php_insert function not available<br>";
    }
}
?>