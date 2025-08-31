<?php
require_once 'config/connection.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Output as plain text
header('Content-Type: text/plain');

// Check if we can query the notification table
echo "Trying to query notification table:\n";
try {
    $notificationQuery = "SELECT * FROM notification LIMIT 1";
    $notificationResult = $php_fetch($notificationQuery);
    var_dump($notificationResult);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Check if the notification table exists
echo "\n\nChecking if notification table exists:\n";
try {
    $query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'notification'
    ) as exists";
    
    $result = $php_fetch($query);
    var_dump($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Check if $php_fetch is working correctly
echo "\n\nChecking if php_fetch is working with a known table (users):\n";
try {
    $usersQuery = "SELECT * FROM users LIMIT 1";
    $usersResult = $php_fetch($usersQuery);
    var_dump($usersResult);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>