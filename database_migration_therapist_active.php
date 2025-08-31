<?php
/**
 * Database Migration Script: Add is_active field to therapist table
 * 
 * This script adds an `is_active` boolean field to the therapist table
 * to properly manage therapist active/inactive status.
 * 
 * Run this script once to upgrade the database schema.
 */

require_once 'config/connection.php';

// Function to safely add column if it doesn't exist
function addIsActiveColumnIfNotExists() {
    global $php_fetch, $php_insert;
    
    echo "🔄 Checking therapist table schema...\n";
    
    try {
        // Try to fetch a therapist record to check existing columns
        $sample_therapist = $php_fetch('therapist', '*', [], 1);
        
        if ($sample_therapist && count($sample_therapist) > 0) {
            $existing_columns = array_keys($sample_therapist[0]);
            
            if (in_array('is_active', $existing_columns)) {
                echo "✅ Column 'is_active' already exists in therapist table.\n";
                return true;
            }
        }
        
        echo "⚠️  Column 'is_active' not found. Attempting to add it...\n";
        
        // Note: Since we're using Supabase through the connection wrapper,
        // we can't directly execute ALTER TABLE statements.
        // Instead, we'll rely on the fallback method using description markers
        // or recommend manual addition of the column in Supabase dashboard.
        
        echo "📝 MANUAL STEP REQUIRED:\n";
        echo "   Please add the following column to your 'therapist' table in Supabase:\n";
        echo "   \n";
        echo "   Column Name: is_active\n";
        echo "   Data Type: boolean\n";
        echo "   Default Value: true\n";
        echo "   Allow NULL: false\n";
        echo "   \n";
        echo "   SQL Command (if using SQL editor):\n";
        echo "   ALTER TABLE therapist ADD COLUMN is_active BOOLEAN DEFAULT TRUE NOT NULL;\n";
        echo "   \n";
        
        // Initialize existing therapists as active if the column gets added manually
        echo "📊 Migration Status:\n";
        echo "   - Current therapists will default to ACTIVE status\n";
        echo "   - The system will work with or without this column\n";
        echo "   - Fallback method uses description markers: [INACTIVE]\n";
        echo "   \n";
        
        return false; // Column not added automatically
        
    } catch (Exception $e) {
        echo "❌ Error checking therapist table: " . $e->getMessage() . "\n";
        return false;
    }
}

// Function to migrate existing status markers to is_active field
function migrateStatusMarkersToIsActive() {
    global $php_fetch, $php_update;
    
    echo "🔄 Migrating existing status markers...\n";
    
    try {
        // Get all therapists
        $therapists = $php_fetch('therapist', '*', []);
        
        if (!$therapists || count($therapists) === 0) {
            echo "📝 No therapists found to migrate.\n";
            return true;
        }
        
        $migrated_count = 0;
        $active_count = 0;
        $inactive_count = 0;
        
        foreach ($therapists as $therapist) {
            $therapist_id = $therapist['therapistid'];
            $desc = $therapist['therapist_desc'] ?? '';
            $is_active = strpos($desc, '[INACTIVE]') === false ? 1 : 0;
            
            if ($is_active) {
                $active_count++;
            } else {
                $inactive_count++;
            }
            
            try {
                // Try to update with is_active field
                $result = $php_update('therapist', 
                    ['is_active' => $is_active], 
                    ['therapistid' => $therapist_id]
                );
                
                if ($result) {
                    // Clean up description markers if update was successful
                    $clean_desc = str_replace(' [INACTIVE]', '', $desc);
                    $clean_desc = str_replace(' [ACTIVE]', '', $clean_desc);
                    
                    if ($clean_desc !== $desc) {
                        $php_update('therapist', 
                            ['therapist_desc' => $clean_desc], 
                            ['therapistid' => $therapist_id]
                        );
                    }
                    
                    $migrated_count++;
                }
            } catch (Exception $e) {
                echo "⚠️  Could not migrate therapist ID {$therapist_id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Migration completed!\n";
        echo "   - Total therapists processed: " . count($therapists) . "\n";
        echo "   - Successfully migrated: {$migrated_count}\n";
        echo "   - Active therapists: {$active_count}\n";
        echo "   - Inactive therapists: {$inactive_count}\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "❌ Error during migration: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
echo "🚀 Starting Therapist Status Migration\n";
echo "=====================================\n\n";

// Step 1: Check and add is_active column
$column_added = addIsActiveColumnIfNotExists();

echo "\n";

// Step 2: Migrate existing data (if column exists)
if ($column_added) {
    migrateStatusMarkersToIsActive();
} else {
    echo "🔧 System will use fallback method for status management.\n";
    echo "   The therapist management feature will work normally.\n";
}

echo "\n";
echo "✅ Migration script completed!\n";
echo "=====================================\n";

// Optional: Display current therapist status summary
try {
    $all_therapists = $php_fetch('therapist', 'therapistid, therapist_name, therapist_desc', []);
    
    if ($all_therapists) {
        echo "\n📊 Current Therapist Summary:\n";
        $active = 0;
        $inactive = 0;
        
        foreach ($all_therapists as $therapist) {
            $desc = $therapist['therapist_desc'] ?? '';
            if (isset($therapist['is_active'])) {
                // Use is_active field if available
                if ($therapist['is_active']) {
                    $active++;
                } else {
                    $inactive++;
                }
            } else {
                // Fallback to description markers
                if (strpos($desc, '[INACTIVE]') !== false) {
                    $inactive++;
                } else {
                    $active++;
                }
            }
        }
        
        echo "   - Total Therapists: " . count($all_therapists) . "\n";
        echo "   - Active: {$active}\n";
        echo "   - Inactive: {$inactive}\n";
    }
} catch (Exception $e) {
    echo "⚠️  Could not generate summary: " . $e->getMessage() . "\n";
}

echo "\n🎉 Ready to use Therapist Status Management!\n";
?>