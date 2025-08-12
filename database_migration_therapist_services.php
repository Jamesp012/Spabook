<?php
/**
 * Database Migration: Multiple Services per Therapist
 * This script migrates from single service assignment to multiple services assignment
 */

require_once 'config/connection.php';

echo "<h2>🗄️ Database Migration: Multiple Services per Therapist</h2>";

try {
    echo "<h3>Step 1: Creating therapist_services junction table</h3>";
    
    // Check if junction table already exists
    $check_table = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'therapist_services' AND table_schema = DATABASE()";
    
    // For MySQL/MariaDB - create junction table
    $create_junction_table = "
        CREATE TABLE IF NOT EXISTS therapist_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            therapist_id INT NOT NULL,
            service_id INT NOT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_assignment (therapist_id, service_id),
            FOREIGN KEY (therapist_id) REFERENCES therapist(therapistid) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    // Execute table creation
    if ($connection->query($create_junction_table)) {
        echo "✅ therapist_services junction table created successfully<br>";
    } else {
        echo "❌ Error creating junction table: " . $connection->error . "<br>";
    }
    
    echo "<h3>Step 2: Migrating existing single service assignments</h3>";
    
    // Get all existing therapists with their current service assignment
    $existing_therapists = $php_fetch('therapist', 'therapistid, service_id', []);
    
    if (!empty($existing_therapists)) {
        $migrated_count = 0;
        
        foreach ($existing_therapists as $therapist) {
            if ($therapist['service_id']) {
                // Check if this assignment already exists
                $existing_assignment = $php_fetch('therapist_services', 'id', [
                    'therapist_id' => $therapist['therapistid'],
                    'service_id' => $therapist['service_id']
                ]);
                
                if (empty($existing_assignment)) {
                    // Insert the existing assignment into junction table
                    $result = $php_insert('therapist_services', [
                        'therapist_id' => $therapist['therapistid'],
                        'service_id' => $therapist['service_id']
                    ]);
                    
                    if (!isset($result['error'])) {
                        $migrated_count++;
                        echo "✅ Migrated therapist ID {$therapist['therapistid']} → service ID {$therapist['service_id']}<br>";
                    } else {
                        echo "❌ Failed to migrate therapist ID {$therapist['therapistid']}: " . $result['error'] . "<br>";
                    }
                } else {
                    echo "⚠️ Assignment already exists: therapist ID {$therapist['therapistid']} → service ID {$therapist['service_id']}<br>";
                }
            }
        }
        
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Migration completed: {$migrated_count} assignments migrated</strong>";
        echo "</div>";
        
    } else {
        echo "<p>⚠️ No existing therapists found to migrate</p>";
    }
    
    echo "<h3>Step 3: Verification</h3>";
    
    // Verify the junction table data
    $junction_data = $php_fetch('therapist_services', '*', []);
    
    if (!empty($junction_data)) {
        echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 3px; margin: 10px 0;'>";
        echo "<strong>📊 Current therapist-service assignments:</strong><br><br>";
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th>Assignment ID</th><th>Therapist ID</th><th>Service ID</th><th>Assigned At</th></tr>";
        
        foreach ($junction_data as $assignment) {
            echo "<tr>";
            echo "<td>{$assignment['id']}</td>";
            echo "<td>{$assignment['therapist_id']}</td>";
            echo "<td>{$assignment['service_id']}</td>";
            echo "<td>{$assignment['assigned_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    echo "<h3>Step 4: What's Next?</h3>";
    echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🚀 Migration Complete! Now you can:</h4>";
    echo "<ol>";
    echo "<li><strong>Go to Admin Panel → Manage Therapists</strong></li>";
    echo "<li><strong>Edit any therapist</strong></li>";
    echo "<li><strong>Select multiple services using checkboxes</strong></li>";
    echo "<li><strong>Save - therapist will be assigned to multiple services</strong></li>";
    echo "<li><strong>In booking, users will see therapists for any of their assigned services</strong></li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>ℹ️ Optional:</strong> After testing, you may want to remove the old 'service_id' column from the therapist table<br>";
    echo "But keep it for now as a backup until you're sure everything works correctly.";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Migration Error:</strong><br>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

echo "<p><a href='views/admin_home_page.php'>← Go to Admin Panel</a></p>";
?>