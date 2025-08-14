<?php
require_once 'config/connection.php';

// Test 1: Check current therapist data
echo "<h2>Current Therapist Data</h2>";
$therapists = $php_fetch('therapist', '*');

if ($therapists && count($therapists) > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr>";
    
    // Get all available fields from first therapist
    $fields = array_keys($therapists[0]);
    foreach ($fields as $field) {
        echo "<th>$field</th>";
    }
    echo "</tr>";
    
    // Show first 3 therapists
    for ($i = 0; $i < min(3, count($therapists)); $i++) {
        echo "<tr>";
        foreach ($fields as $field) {
            $value = $therapists[$i][$field] ?? 'NULL';
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if is_active column exists
    $hasIsActive = isset($therapists[0]['is_active']);
    echo "<p><strong>Has is_active column:</strong> " . ($hasIsActive ? 'YES' : 'NO') . "</p>";
    
    if ($hasIsActive) {
        echo "<p style='color: green;'>✅ Database has is_active column - using proper status management</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Database missing is_active column - using description markers</p>";
        echo "<p><strong>Solution:</strong> Run the database migration SQL or manually add the column</p>";
    }
    
} else {
    echo "<p>No therapists found or error fetching data</p>";
}

// Test 2: Test the update function
if (isset($_GET['test_update']) && isset($_GET['therapist_id'])) {
    $therapist_id = $_GET['therapist_id'];
    $new_status = $_GET['status'] === '1' ? 1 : 0;
    
    echo "<h2>Testing Status Update</h2>";
    echo "<p>Therapist ID: $therapist_id</p>";
    echo "<p>New Status: " . ($new_status ? 'ACTIVE' : 'INACTIVE') . "</p>";
    
    // Simulate the controller logic
    $therapist = $php_fetch('therapist', '*', ['therapistid' => $therapist_id]);
    if ($therapist && count($therapist) > 0) {
        $currentDesc = $therapist[0]['therapist_desc'] ?? '';
        $statusNote = $new_status ? '' : ' [INACTIVE]';
        $cleanDesc = preg_replace('/ \[INACTIVE\]$/', '', $currentDesc);
        $newDesc = trim($cleanDesc) . $statusNote;
        
        echo "<p><strong>Current Description:</strong> '$currentDesc'</p>";
        echo "<p><strong>New Description:</strong> '$newDesc'</p>";
        
        // Try the update
        $updated = $php_update('therapist', ['therapist_desc' => $newDesc], ['therapistid' => $therapist_id]);
        
        echo "<p><strong>Update Result:</strong></p>";
        echo "<pre>" . print_r($updated, true) . "</pre>";
        
        if ($updated !== null && (!isset($updated['error']))) {
            echo "<p style='color: green;'>✅ Update appears successful!</p>";
        } else {
            echo "<p style='color: red;'>❌ Update failed!</p>";
        }
    }
}

// Show available therapists for testing
echo "<h2>Test Update Links</h2>";
if ($therapists && count($therapists) > 0) {
    echo "<p>Click to test status updates:</p>";
    foreach (array_slice($therapists, 0, 3) as $therapist) {
        $id = $therapist['therapistid'];
        $name = $therapist['therapist_name'];
        $isInactive = strpos($therapist['therapist_desc'] ?? '', '[INACTIVE]') !== false;
        $currentStatus = $isInactive ? 'INACTIVE' : 'ACTIVE';
        $newStatus = $isInactive ? '1' : '0';
        $newStatusText = $isInactive ? 'ACTIVATE' : 'DEACTIVATE';
        
        echo "<p>";
        echo "<strong>$name</strong> (ID: $id) - Current: $currentStatus - ";
        echo "<a href='?test_update=1&therapist_id=$id&status=$newStatus' style='color: blue;'>$newStatusText</a>";
        echo "</p>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>