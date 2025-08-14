<?php
// Direct controller test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🎯 Direct Controller Test</h2>";

// Simulate POST data
$_POST = [
    'action' => 'add_therapist',
    'therapist_name' => 'Direct Test Therapist',
    'therapist_desc' => 'Testing controller directly',
    'service_ids' => ['1', '2'],
    'rate' => 1800
];

echo "<h3>Simulated POST data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>Loading controller...</h3>";

// Capture output
ob_start();

try {
    // Include the controller directly
    include 'controller/therapist_contr.php';
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
}

$output = ob_get_clean();

echo "<h3>Controller output:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Also check if response was JSON
echo "<h3>JSON decode test:</h3>";
$json_result = json_decode($output, true);
if ($json_result !== null) {
    echo "✅ Valid JSON response:<br>";
    echo "<pre>" . print_r($json_result, true) . "</pre>";
} else {
    echo "❌ Not valid JSON or empty response<br>";
    echo "JSON last error: " . json_last_error_msg() . "<br>";
}
?>

<hr>
<h3>🔍 Check Log Files</h3>

<?php
// Check various log locations
$log_locations = [
    'debug_supabase_insert.txt',
    'C:\\xampp\\php\\logs\\php_error.log',
    'C:\\xampp\\apache\\logs\\error.log',
    'error.log'
];

foreach ($log_locations as $log_file) {
    if (file_exists($log_file)) {
        echo "<h4>📄 Log: " . $log_file . "</h4>";
        $log_content = file_get_contents($log_file);
        
        // Show only recent entries (last 2000 characters)
        if (strlen($log_content) > 2000) {
            $log_content = "...\n" . substr($log_content, -2000);
        }
        
        echo "<pre style='background: #f8f9fa; padding: 10px; max-height: 300px; overflow-y: auto; border: 1px solid #ddd;'>" . 
             htmlspecialchars($log_content) . "</pre>";
    }
}
?>

<hr>
<h3>📊 Environment Check</h3>

<?php
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
echo "Current Directory: " . getcwd() . "<br>";

echo "<h4>File Permissions Check:</h4>";
$files_to_check = [
    'config/connection.php',
    'controller/therapist_contr.php', 
    'model/therapist_model.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} exists and is readable<br>";
    } else {
        echo "❌ {$file} NOT found<br>";
    }
}
?>