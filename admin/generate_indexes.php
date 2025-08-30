<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/connection.php';

// Function to get all tables
function getTables($php_fetch) {
    $query = "
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name
    ";
    
    return $php_fetch($query);
}

// Function to get columns for a table
function getColumns($php_fetch, $table) {
    $query = "
        SELECT 
            column_name,
            data_type,
            is_nullable,
            column_default
        FROM 
            information_schema.columns
        WHERE 
            table_schema = 'public'
            AND table_name = '$table'
        ORDER BY 
            ordinal_position
    ";
    
    return $php_fetch($query);
}

// Function to get indexes for a table
function getIndexes($php_fetch, $table) {
    $query = "
        SELECT
            i.relname as index_name,
            a.attname as column_name,
            ix.indisunique as is_unique,
            ix.indisprimary as is_primary
        FROM
            pg_class t,
            pg_class i,
            pg_index ix,
            pg_attribute a
        WHERE
            t.oid = ix.indrelid
            AND i.oid = ix.indexrelid
            AND a.attrelid = t.oid
            AND a.attnum = ANY(ix.indkey)
            AND t.relkind = 'r'
            AND t.relname = '$table'
        ORDER BY
            i.relname,
            a.attnum
    ";
    
    return $php_fetch($query);
}

// Get selected tables or all tables
$selectedTables = $_POST['tables'] ?? [];
$allTables = getTables($php_fetch);

if (empty($selectedTables)) {
    $tables = $allTables;
} else {
    $tables = array_filter($allTables, function($table) use ($selectedTables) {
        return in_array($table['table_name'], $selectedTables);
    });
}

// Generate SQL script
$sql = "-- Database Optimization SQL (Generated for Your Schema)\n";
$sql .= "-- Run this in your Supabase SQL Editor to improve database performance\n";
$sql .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

// Function to check if a column exists
$sql .= "-- Function to check if a column exists\n";
$sql .= "CREATE OR REPLACE FUNCTION column_exists(tbl text, col text) RETURNS boolean AS $$\n";
$sql .= "BEGIN\n";
$sql .= "    RETURN EXISTS (\n";
$sql .= "        SELECT FROM information_schema.columns \n";
$sql .= "        WHERE table_schema = 'public' \n";
$sql .= "        AND table_name = tbl \n";
$sql .= "        AND column_name = col\n";
$sql .= "    );\n";
$sql .= "END;\n";
$sql .= "$$ LANGUAGE plpgsql;\n\n";

// Common columns to index by table type
$commonIndexColumns = [
    'default' => ['id', 'user_id', 'status', 'created_at', 'updated_at'],
    'booking' => ['user_id', 'booking_status', 'date_created', 'payment_status'],
    'booking_details' => ['booking_id', 'service_id', 'therapist_id', 'booking_date', 'booking_time', 'status'],
    'services' => ['category_id', 'price', 'status'],
    'users' => ['role', 'email', 'first_name', 'last_name'],
    'therapists' => ['status', 'specialization'],
    'notifications' => ['user_id', 'is_read', 'created_at']
];

// Process each table
foreach ($tables as $table) {
    $tableName = $table['table_name'];
    $columns = getColumns($php_fetch, $tableName);
    $indexes = getIndexes($php_fetch, $tableName);
    
    // Get existing indexed columns
    $existingIndexColumns = [];
    foreach ($indexes as $index) {
        $existingIndexColumns[] = $index['column_name'];
    }
    
    // Determine which columns to index
    $columnsToIndex = [];
    
    // Add table-specific common columns
    foreach ($commonIndexColumns as $tableType => $indexColumns) {
        if ($tableType === 'default' || strpos($tableName, $tableType) !== false) {
            foreach ($indexColumns as $columnName) {
                $columnsToIndex[] = $columnName;
            }
        }
    }
    
    // Add table section to SQL
    $sql .= "-- {$tableName} table indexes\n";
    $sql .= "DO $$\n";
    $sql .= "BEGIN\n";
    
    // Add column indexes
    $columnNames = array_column($columns, 'column_name');
    foreach ($columnsToIndex as $columnName) {
        if (in_array($columnName, $columnNames) && !in_array($columnName, $existingIndexColumns)) {
            $sql .= "    IF column_exists('{$tableName}', '{$columnName}') THEN\n";
            $sql .= "        EXECUTE 'CREATE INDEX IF NOT EXISTS idx_{$tableName}_{$columnName} ON {$tableName}({$columnName})';\n";
            $sql .= "    END IF;\n";
            $sql .= "    \n";
        }
    }
    
    // Add combined indexes
    if (in_array('booking_status', $columnNames) && in_array('date_created', $columnNames)) {
        $sql .= "    IF column_exists('{$tableName}', 'booking_status') AND column_exists('{$tableName}', 'date_created') THEN\n";
        $sql .= "        EXECUTE 'CREATE INDEX IF NOT EXISTS idx_{$tableName}_combined ON {$tableName}(booking_status, date_created DESC)';\n";
        $sql .= "    END IF;\n";
        $sql .= "    \n";
    }
    
    if (in_array('first_name', $columnNames) && in_array('last_name', $columnNames)) {
        $sql .= "    IF column_exists('{$tableName}', 'first_name') AND column_exists('{$tableName}', 'last_name') THEN\n";
        $sql .= "        EXECUTE 'CREATE INDEX IF NOT EXISTS idx_{$tableName}_name ON {$tableName}(first_name, last_name)';\n";
        $sql .= "    END IF;\n";
        $sql .= "    \n";
    }
    
    if (in_array('user_id', $columnNames) && in_array('is_read', $columnNames)) {
        $sql .= "    IF column_exists('{$tableName}', 'user_id') AND column_exists('{$tableName}', 'is_read') THEN\n";
        $sql .= "        EXECUTE 'CREATE INDEX IF NOT EXISTS idx_{$tableName}_user_unread ON {$tableName}(user_id, is_read) WHERE is_read = false';\n";
        $sql .= "    END IF;\n";
        $sql .= "    \n";
    }
    
    $sql .= "END\n";
    $sql .= "$$;\n\n";
}

// Add foreign key constraints
$sql .= "-- Add any missing foreign key constraints (if needed)\n";
$sql .= "DO $$\n";
$sql .= "BEGIN\n";
$sql .= "    IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'booking_details') \n";
$sql .= "       AND column_exists('booking_details', 'booking_id')\n";
$sql .= "       AND EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'booking')\n";
$sql .= "       AND column_exists('booking', 'bookingid') THEN\n";
$sql .= "        \n";
$sql .= "        IF NOT EXISTS (\n";
$sql .= "            SELECT 1 FROM information_schema.table_constraints \n";
$sql .= "            WHERE constraint_name = 'fk_booking_details_booking_id' \n";
$sql .= "            AND table_name = 'booking_details'\n";
$sql .= "        ) THEN\n";
$sql .= "            BEGIN\n";
$sql .= "                ALTER TABLE booking_details \n";
$sql .= "                ADD CONSTRAINT fk_booking_details_booking_id \n";
$sql .= "                FOREIGN KEY (booking_id) REFERENCES booking(bookingid) ON DELETE CASCADE;\n";
$sql .= "            EXCEPTION\n";
$sql .= "                WHEN others THEN\n";
$sql .= "                    -- Do nothing if constraint already exists or can't be added\n";
$sql .= "            END;\n";
$sql .= "        END IF;\n";
$sql .= "    END IF;\n";
$sql .= "END\n";
$sql .= "$$;\n";

// Include header
$pageTitle = 'Generated Index Script';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Generated Index Script</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">SQL Script for Your Database Schema</h5>
        </div>
        <div class="card-body">
            <p>Copy this SQL script and run it in your Supabase SQL Editor to create optimized indexes for your database schema.</p>
            
            <div class="alert alert-info">
                <strong>Note:</strong> This script checks for column existence before creating indexes, so it's safe to run even if some columns don't exist in your database.
            </div>
            
            <div class="mb-3">
                <label for="sql" class="form-label">SQL Script</label>
                <textarea id="sql" class="form-control" rows="20" readonly><?php echo $sql; ?></textarea>
            </div>
            
            <button id="copyBtn" class="btn btn-primary">Copy to Clipboard</button>
            <a href="analyze_schema.php" class="btn btn-secondary">Back to Schema Analysis</a>
            
            <div class="mt-3">
                <div id="copySuccess" class="alert alert-success d-none">
                    SQL script copied to clipboard!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('copyBtn').addEventListener('click', function() {
        var sqlText = document.getElementById('sql');
        sqlText.select();
        document.execCommand('copy');
        
        var copySuccess = document.getElementById('copySuccess');
        copySuccess.classList.remove('d-none');
        
        setTimeout(function() {
            copySuccess.classList.add('d-none');
        }, 3000);
    });
</script>

<?php
// Include footer
include '../components/admin_footer.php';
?>