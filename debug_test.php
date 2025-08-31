<?php
// Simple diagnostic test file
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>SpaBook Debug Test</h2>";

try {
    echo "<h3>1. Testing config files...</h3>";
    
    // Test connection.php
    require_once 'config/connection.php';
    echo "✅ config/connection.php loaded successfully<br>";
    
    // Test credentials
    require_once 'config/credentials.php';
    echo "✅ config/credentials.php loaded successfully<br>";
    echo "Base URL: " . $baseUrl . "<br>";
    
    echo "<h3>2. Testing database connection...</h3>";
    
    // Test a simple Supabase fetch
    $testUsers = $php_fetch('users', 'user_id', ['limit' => 1]);
    if ($testUsers) {
        echo "✅ Supabase connection working<br>";
        echo "Sample data: " . print_r($testUsers, true) . "<br>";
    } else {
        echo "❌ Supabase connection failed<br>";
    }
    
    echo "<h3>3. Testing therapist model...</h3>";
    
    // Test therapist model
    require_once 'model/therapist_model.php';
    $TherapistModel = new TherapistModel();
    echo "✅ TherapistModel loaded successfully<br>";
    
    // Test getting all therapists
    $therapists = $TherapistModel->getAllTherapists($php_fetch, 'therapist');
    echo "Therapists result: " . $therapists . "<br>";
    
    echo "<h3>4. Testing user model...</h3>";
    
    // Test user model
    require_once 'model/user_model.php';
    $User = new User();
    echo "✅ User model loaded successfully<br>";
    
    echo "<h3>✅ All tests completed successfully!</h3>";
    
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
?>