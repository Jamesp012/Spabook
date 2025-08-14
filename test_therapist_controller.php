<?php
/**
 * Test script for debugging therapist controller issues
 * Visit this file directly in browser: http://localhost/Spabook/test_therapist_controller.php
 */

echo "<h1>🧪 Therapist Controller Test</h1>";
echo "<p>Testing the admin dashboard controller...</p>";

echo "<h2>🔄 Testing Connection</h2>";

// Test basic connection
$test_data = [
    'action' => 'test_connection'
];

echo "<h3>Test Connection Request:</h3>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Spabook/controller/admin_dashboard_contr.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Connection Test Response:</h3>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
}
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test therapist status
echo "<hr><h2>📊 Testing Therapist Status</h2>";

$therapist_data = [
    'action' => 'get_therapist_status'
];

echo "<h3>Get Therapist Status Request:</h3>";
echo "<pre>" . print_r($therapist_data, true) . "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Spabook/controller/admin_dashboard_contr.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($therapist_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Therapist Status Response:</h3>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";
if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
}

// Try to decode JSON
$json_data = json_decode($response, true);
if ($json_data) {
    echo "<h4>Parsed JSON Response:</h4>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<h4>Raw Response (not valid JSON):</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";
echo "<h2>💡 Debugging Tips</h2>";
echo "<ul>";
echo "<li>Check if the controller file exists at: <code>controller/admin_dashboard_contr.php</code></li>";
echo "<li>Check PHP error logs for any fatal errors</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify database connection in <code>config/connection.php</code></li>";
echo "<li>Check if there are any therapists in the database</li>";
echo "</ul>";

echo "<h2>📋 Server Info</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

// Check if controller file exists
$controller_path = __DIR__ . '/controller/admin_dashboard_contr.php';
echo "<p><strong>Controller File Exists:</strong> " . (file_exists($controller_path) ? '✅ Yes' : '❌ No') . "</p>";
if (file_exists($controller_path)) {
    echo "<p><strong>Controller File Size:</strong> " . filesize($controller_path) . " bytes</p>";
    echo "<p><strong>Controller Last Modified:</strong> " . date('Y-m-d H:i:s', filemtime($controller_path)) . "</p>";
}

// Check if config file exists
$config_path = __DIR__ . '/config/connection.php';
echo "<p><strong>Config File Exists:</strong> " . (file_exists($config_path) ? '✅ Yes' : '❌ No') . "</p>";

echo "<hr>";
echo "<h2>🔗 Quick Links</h2>";
echo "<ul>";
echo "<li><a href='controller/admin_dashboard_contr.php' target='_blank'>Direct Controller Access</a></li>";
echo "<li><a href='views/admin_home_page.php'>Admin Dashboard</a></li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
code { background: #e7e7e7; padding: 2px 4px; border-radius: 3px; }
hr { margin: 20px 0; }
</style>