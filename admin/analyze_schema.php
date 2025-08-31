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

// Function to get foreign keys for a table
function getForeignKeys($php_fetch, $table) {
    $query = "
        SELECT
            tc.constraint_name,
            kcu.column_name,
            ccu.table_name AS foreign_table_name,
            ccu.column_name AS foreign_column_name
        FROM
            information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
              ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
              ON ccu.constraint_name = tc.constraint_name
              AND ccu.table_schema = tc.table_schema
        WHERE
            tc.constraint_type = 'FOREIGN KEY'
            AND tc.table_name = '$table'
    ";
    
    return $php_fetch($query);
}

// Get all tables
$tables = getTables($php_fetch);

// Get selected table
$selectedTable = $_GET['table'] ?? null;
$columns = [];
$indexes = [];
$foreignKeys = [];

if ($selectedTable) {
    $columns = getColumns($php_fetch, $selectedTable);
    $indexes = getIndexes($php_fetch, $selectedTable);
    $foreignKeys = getForeignKeys($php_fetch, $selectedTable);
}

// Generate SQL for missing indexes
$missingIndexesSQL = "";
if ($selectedTable && !empty($columns)) {
    // Common columns to index
    $commonIndexColumns = [
        'id', 'user_id', 'status', 'created_at', 'updated_at', 'date_created',
        'booking_id', 'service_id', 'category_id', 'therapist_id', 'email',
        'booking_status', 'payment_status', 'booking_date', 'booking_time'
    ];
    
    // Check which columns exist and don't have indexes
    $existingIndexColumns = [];
    foreach ($indexes as $index) {
        $existingIndexColumns[] = $index['column_name'];
    }
    
    foreach ($columns as $column) {
        $columnName = $column['column_name'];
        if (in_array($columnName, $commonIndexColumns) && !in_array($columnName, $existingIndexColumns)) {
            $missingIndexesSQL .= "CREATE INDEX IF NOT EXISTS idx_{$selectedTable}_{$columnName} ON {$selectedTable}({$columnName});\n";
        }
    }
    
    // Check for common combined indexes
    if (in_array('booking_status', array_column($columns, 'column_name')) && 
        in_array('date_created', array_column($columns, 'column_name'))) {
        $missingIndexesSQL .= "CREATE INDEX IF NOT EXISTS idx_{$selectedTable}_combined ON {$selectedTable}(booking_status, date_created DESC);\n";
    }
    
    if (in_array('first_name', array_column($columns, 'column_name')) && 
        in_array('last_name', array_column($columns, 'column_name'))) {
        $missingIndexesSQL .= "CREATE INDEX IF NOT EXISTS idx_{$selectedTable}_name ON {$selectedTable}(first_name, last_name);\n";
    }
}

// Include header
$pageTitle = 'Database Schema Analysis';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Database Schema Analysis</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tables</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($tables as $table): ?>
                        <a href="?table=<?php echo $table['table_name']; ?>" class="list-group-item list-group-item-action <?php echo $selectedTable === $table['table_name'] ? 'active' : ''; ?>">
                            <?php echo $table['table_name']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if ($selectedTable): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Table: <?php echo $selectedTable; ?></h5>
                </div>
                <div class="card-body">
                    <h6>Columns</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Column Name</th>
                                    <th>Data Type</th>
                                    <th>Nullable</th>
                                    <th>Default</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($columns as $column): ?>
                                <tr>
                                    <td><?php echo $column['column_name']; ?></td>
                                    <td><?php echo $column['data_type']; ?></td>
                                    <td><?php echo $column['is_nullable'] === 'YES' ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $column['column_default']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <h6 class="mt-4">Indexes</h6>
                    <?php if (empty($indexes)): ?>
                    <div class="alert alert-warning">No indexes found for this table.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Index Name</th>
                                    <th>Column</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($indexes as $index): ?>
                                <tr>
                                    <td><?php echo $index['index_name']; ?></td>
                                    <td><?php echo $index['column_name']; ?></td>
                                    <td>
                                        <?php if ($index['is_primary'] === 't'): ?>
                                        <span class="badge bg-primary">Primary Key</span>
                                        <?php elseif ($index['is_unique'] === 't'): ?>
                                        <span class="badge bg-info">Unique</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Index</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-4">Foreign Keys</h6>
                    <?php if (empty($foreignKeys)): ?>
                    <div class="alert alert-warning">No foreign keys found for this table.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Constraint Name</th>
                                    <th>Column</th>
                                    <th>References</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foreignKeys as $fk): ?>
                                <tr>
                                    <td><?php echo $fk['constraint_name']; ?></td>
                                    <td><?php echo $fk['column_name']; ?></td>
                                    <td><?php echo $fk['foreign_table_name'] . '.' . $fk['foreign_column_name']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($missingIndexesSQL)): ?>
                    <h6 class="mt-4">Suggested Indexes</h6>
                    <div class="alert alert-info">
                        <p>The following indexes are recommended for this table:</p>
                        <pre class="mb-0"><code><?php echo htmlspecialchars($missingIndexesSQL); ?></code></pre>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <p>Select a table from the list to view its schema.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Generate Safe Index Creation Script</h5>
        </div>
        <div class="card-body">
            <p>This tool will generate a SQL script to create indexes for your database schema. The script will check for column existence before creating indexes.</p>
            
            <form method="post" action="generate_indexes.php">
                <div class="mb-3">
                    <label for="tables" class="form-label">Select Tables</label>
                    <select id="tables" name="tables[]" class="form-select" multiple size="5">
                        <?php foreach ($tables as $table): ?>
                        <option value="<?php echo $table['table_name']; ?>"><?php echo $table['table_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple tables. Leave empty to include all tables.</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Generate Index Script</button>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include '../components/admin_footer.php';
?>