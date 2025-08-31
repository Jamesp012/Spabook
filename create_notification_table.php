<?php
require_once 'config/connection.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Output as plain text
header('Content-Type: text/plain');

echo "Checking database connection and tables...\n\n";

// Check if we can query the users table
echo "Checking users table:\n";
try {
    $usersQuery = "SELECT * FROM users LIMIT 1";
    $usersResult = $php_fetch($usersQuery);
    if (empty($usersResult)) {
        echo "Users table exists but is empty or query returned no results.\n";
    } else {
        echo "Users table exists and has data.\n";
    }
} catch (Exception $e) {
    echo "Error querying users table: " . $e->getMessage() . "\n";
}

// Check if the notification table exists
echo "\nChecking if notification table exists:\n";
try {
    $query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'notification'
    ) as exists";
    
    $result = $php_fetch($query);
    if (isset($result[0]['exists'])) {
        if ($result[0]['exists'] === true) {
            echo "Notification table exists.\n";
        } else {
            echo "Notification table does not exist.\n";
            echo "Please create the notification table in your Supabase dashboard with the following SQL:\n\n";
            echo "CREATE TABLE notification (\n";
            echo "    notificationid SERIAL PRIMARY KEY,\n";
            echo "    user_id uuid NOT NULL,\n";
            echo "    title VARCHAR(255) NOT NULL,\n";
            echo "    message TEXT,\n";
            echo "    type VARCHAR(50),\n";
            echo "    is_read BOOLEAN DEFAULT FALSE,\n";
            echo "    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,\n";
            echo "    read_at TIMESTAMPTZ,\n";
            echo "    metadata JSONB,\n";
            echo "    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE\n";
            echo ");\n";
        }
    } else {
        echo "Could not determine if notification table exists.\n";
    }
} catch (Exception $e) {
    echo "Error checking notification table: " . $e->getMessage() . "\n";
}

// Try to query the notification table
echo "\nTrying to query notification table:\n";
try {
    $notificationQuery = "SELECT * FROM notification LIMIT 1";
    $notificationResult = $php_fetch($notificationQuery);
    if (empty($notificationResult)) {
        echo "Notification table exists but is empty or query returned no results.\n";
    } else {
        echo "Notification table exists and has data.\n";
        var_dump($notificationResult);
    }
} catch (Exception $e) {
    echo "Error querying notification table: " . $e->getMessage() . "\n";
}

// Check the structure of the booking_contr.php file
echo "\nChecking booking_contr.php for issues:\n";
$bookingContrPath = __DIR__ . '/controller/booking_contr.php';
if (file_exists($bookingContrPath)) {
    $bookingContr = file_get_contents($bookingContrPath);
    
    // Check for circular dependencies
    if (strpos($bookingContr, "require_once '../controller/notification_contr.php'") !== false &&
        strpos(file_get_contents(__DIR__ . '/controller/notification_contr.php'), "require_once '../controller/booking_contr.php'") !== false) {
        echo "Warning: Circular dependency detected between booking_contr.php and notification_contr.php\n";
    } else {
        echo "No circular dependencies detected.\n";
    }
    
    // Check for syntax errors
    $tempFile = __DIR__ . '/temp_syntax_check.php';
    file_put_contents($tempFile, $bookingContr);
    exec("php -l $tempFile 2>&1", $output, $returnCode);
    unlink($tempFile);
    
    if ($returnCode === 0) {
        echo "No syntax errors detected in booking_contr.php\n";
    } else {
        echo "Syntax errors detected in booking_contr.php:\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "booking_contr.php not found\n";
}

echo "\nDone checking database and files.\n";
?>
?>