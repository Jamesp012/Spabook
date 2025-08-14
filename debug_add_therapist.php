<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Debug Add Therapist</h2>";

try {
    // Load dependencies
    require_once 'config/connection.php';
    require_once 'model/therapist_model.php';
    
    $TherapistModel = new TherapistModel();
    
    echo "<h3>1. Testing Connection and Basic Functions</h3>";
    
    // Test 1: Check if $php_insert is available
    if (is_callable($php_insert)) {
        echo "✅ php_insert function is available<br>";
    } else {
        echo "❌ php_insert function is NOT available<br>";
    }
    
    // Test 2: Try to fetch existing therapists
    echo "<h4>Existing Therapists:</h4>";
    $existing = $php_fetch('therapist', '*', []);
    echo "<pre>" . print_r($existing, true) . "</pre>";
    
    // Test 3: Try simple insert
    echo "<h3>2. Testing Simple Insert</h3>";
    
    $test_data = [
        'therapist_name' => 'Test Therapist ' . date('H:i:s'),
        'therapist_desc' => 'This is a test therapist created at ' . date('Y-m-d H:i:s'),
        'service_id' => '1,2', // Multiple services as comma-separated
        'rate' => 1500
    ];
    
    echo "Test data to insert:<br>";
    echo "<pre>" . print_r($test_data, true) . "</pre>";
    
    // Try direct insert
    echo "<h4>Direct php_insert call:</h4>";
    $direct_result = $php_insert('therapist', $test_data);
    echo "Result: <pre>" . print_r($direct_result, true) . "</pre>";
    
    // Try through model
    echo "<h4>Through TherapistModel:</h4>";
    $model_result = $TherapistModel->addTherapist($php_insert, 'therapist', $test_data);
    echo "Result: <pre>" . $model_result . "</pre>";
    
    // Test 4: Check what happened
    echo "<h3>3. Verification - Check if therapist was added</h3>";
    $after_insert = $php_fetch('therapist', '*', []);
    echo "<pre>" . print_r($after_insert, true) . "</pre>";
    
    // Test 5: Test service fetch
    echo "<h3>4. Available Services</h3>";
    $services = $php_fetch('booking_services', '*', []);
    echo "Services: <pre>" . print_r($services, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error occurred:</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h3>❌ Fatal error occurred:</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<h3>5. Manual Test Form</h3>";
?>

<form method="POST" action="">
    <h4>Test Add Therapist</h4>
    <input type="hidden" name="manual_test" value="1">
    
    <label>Therapist Name:</label><br>
    <input type="text" name="therapist_name" value="Manual Test Therapist" required><br><br>
    
    <label>Description:</label><br>
    <textarea name="therapist_desc">Manual test therapist</textarea><br><br>
    
    <label>Service IDs (comma-separated):</label><br>
    <input type="text" name="service_id" value="1,2" required><br><br>
    
    <label>Rate:</label><br>
    <input type="number" name="rate" value="1500"><br><br>
    
    <input type="submit" value="Add Test Therapist">
</form>

<?php
if (isset($_POST['manual_test'])) {
    echo "<h4>Manual Form Submission Result:</h4>";
    
    $form_data = [
        'therapist_name' => $_POST['therapist_name'],
        'therapist_desc' => $_POST['therapist_desc'],
        'service_id' => $_POST['service_id'],
        'rate' => intval($_POST['rate'])
    ];
    
    echo "Form data: <pre>" . print_r($form_data, true) . "</pre>";
    
    // Test the controller path
    $_POST['action'] = 'add_therapist';
    $_POST['service_ids'] = explode(',', $_POST['service_id']);
    
    echo "<h5>Simulating Controller Call:</h5>";
    echo "POST data: <pre>" . print_r($_POST, true) . "</pre>";
    
    // Direct model call
    $manual_result = $TherapistModel->addTherapist($php_insert, 'therapist', $form_data);
    echo "Manual result: <pre>" . $manual_result . "</pre>";
}
?>

<style>
form { 
    background: #f8f9fa; 
    padding: 20px; 
    border-radius: 8px; 
    margin: 20px 0;
    border: 1px solid #dee2e6;
}
form input, form textarea { 
    width: 300px; 
    padding: 8px; 
    margin: 5px 0;
}
form input[type="submit"] { 
    background: #007bff; 
    color: white; 
    border: none; 
    padding: 10px 20px; 
    border-radius: 4px; 
    cursor: pointer;
}
</style>