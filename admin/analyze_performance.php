<?php
require_once '../config/connection.php';
require_once '../utils/performance.php';

// Enable performance monitoring
Performance::init(true);

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Function to get table sizes
function getTableSizes($php_fetch) {
    $query = "
        SELECT
            table_name,
            pg_size_pretty(pg_total_relation_size(quote_ident(table_name))) as total_size,
            pg_size_pretty(pg_relation_size(quote_ident(table_name))) as table_size,
            pg_size_pretty(pg_total_relation_size(quote_ident(table_name)) - pg_relation_size(quote_ident(table_name))) as index_size,
            pg_total_relation_size(quote_ident(table_name)) as raw_total_size
        FROM
            information_schema.tables
        WHERE
            table_schema = 'public'
        ORDER BY
            pg_total_relation_size(quote_ident(table_name)) DESC
    ";
    
    return $php_fetch($query);
}

// Function to get slow queries
function getSlowQueries($php_fetch) {
    $query = "
        SELECT
            query,
            calls,
            total_time,
            mean_time,
            rows
        FROM
            pg_stat_statements
        ORDER BY
            mean_time DESC
        LIMIT 10
    ";
    
    // This might fail if pg_stat_statements extension is not enabled
    try {
        return $php_fetch($query);
    } catch (Exception $e) {
        return [];
    }
}

// Function to get index usage
function getIndexUsage($php_fetch) {
    $query = "
        SELECT
            schemaname || '.' || relname as table,
            indexrelname as index,
            idx_scan as index_scans,
            idx_tup_read as tuples_read,
            idx_tup_fetch as tuples_fetched
        FROM
            pg_stat_user_indexes
        ORDER BY
            idx_scan DESC
        LIMIT 10
    ";
    
    return $php_fetch($query);
}

// Function to get table scans
function getTableScans($php_fetch) {
    $query = "
        SELECT
            relname as table,
            seq_scan as sequential_scans,
            seq_tup_read as sequential_tuples_read,
            idx_scan as index_scans,
            idx_tup_fetch as index_tuples_fetched
        FROM
            pg_stat_user_tables
        ORDER BY
            seq_scan DESC
        LIMIT 10
    ";
    
    return $php_fetch($query);
}

// Get performance data
Performance::startTimer('table_sizes');
$tableSizes = getTableSizes($php_fetch);
$tableSizesTime = Performance::endTimer('table_sizes');

Performance::startTimer('slow_queries');
$slowQueries = getSlowQueries($php_fetch);
$slowQueriesTime = Performance::endTimer('slow_queries');

Performance::startTimer('index_usage');
$indexUsage = getIndexUsage($php_fetch);
$indexUsageTime = Performance::endTimer('index_usage');

Performance::startTimer('table_scans');
$tableScans = getTableScans($php_fetch);
$tableScansTime = Performance::endTimer('table_scans');

// Get cache statistics
$cacheDir = '../cache/';
$cacheFiles = glob($cacheDir . '*.cache');
$cacheCount = count($cacheFiles);
$cacheSize = 0;

foreach ($cacheFiles as $file) {
    $cacheSize += filesize($file);
}

$cacheSize = round($cacheSize / 1024 / 1024, 2); // Convert to MB

// Get performance metrics
$metrics = Performance::getMetrics();

// Include header
$pageTitle = 'Database Performance Analysis';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Database Performance Analysis</h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Request Time:</th>
                            <td><?= number_format($metrics['request_time'] * 1000, 2) ?> ms</td>
                        </tr>
                        <tr>
                            <th>Query Count:</th>
                            <td><?= $metrics['query_count'] ?></td>
                        </tr>
                        <tr>
                            <th>Total Query Time:</th>
                            <td><?= number_format($metrics['total_query_time'] * 1000, 2) ?> ms</td>
                        </tr>
                        <tr>
                            <th>Average Query Time:</th>
                            <td><?= number_format($metrics['avg_query_time'] * 1000, 2) ?> ms</td>
                        </tr>
                        <tr>
                            <th>Max Query Time:</th>
                            <td><?= number_format($metrics['max_query_time'] * 1000, 2) ?> ms</td>
                        </tr>
                        <tr>
                            <th>Memory Usage:</th>
                            <td><?= number_format($metrics['memory_usage'], 2) ?> MB</td>
                        </tr>
                        <tr>
                            <th>Peak Memory Usage:</th>
                            <td><?= number_format($metrics['peak_memory_usage'], 2) ?> MB</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Cache Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Cache Files:</th>
                            <td><?= $cacheCount ?></td>
                        </tr>
                        <tr>
                            <th>Cache Size:</th>
                            <td><?= $cacheSize ?> MB</td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="clear_cache.php" class="btn btn-warning btn-sm">Clear Cache</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Table Sizes (<?= number_format($tableSizesTime * 1000, 2) ?> ms)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Total Size</th>
                                    <th>Table Size</th>
                                    <th>Index Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableSizes as $table): ?>
                                <tr>
                                    <td><?= $table['table_name'] ?></td>
                                    <td><?= $table['total_size'] ?></td>
                                    <td><?= $table['table_size'] ?></td>
                                    <td><?= $table['index_size'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($slowQueries)): ?>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">Slow Queries (<?= number_format($slowQueriesTime * 1000, 2) ?> ms)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Query</th>
                                    <th>Calls</th>
                                    <th>Total Time (ms)</th>
                                    <th>Mean Time (ms)</th>
                                    <th>Rows</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slowQueries as $query): ?>
                                <tr>
                                    <td><?= htmlspecialchars(substr($query['query'], 0, 100)) ?>...</td>
                                    <td><?= $query['calls'] ?></td>
                                    <td><?= number_format($query['total_time'], 2) ?></td>
                                    <td><?= number_format($query['mean_time'], 2) ?></td>
                                    <td><?= $query['rows'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Index Usage (<?= number_format($indexUsageTime * 1000, 2) ?> ms)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Index</th>
                                    <th>Index Scans</th>
                                    <th>Tuples Read</th>
                                    <th>Tuples Fetched</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($indexUsage as $index): ?>
                                <tr>
                                    <td><?= $index['table'] ?></td>
                                    <td><?= $index['index'] ?></td>
                                    <td><?= $index['index_scans'] ?></td>
                                    <td><?= $index['tuples_read'] ?></td>
                                    <td><?= $index['tuples_fetched'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Table Scans (<?= number_format($tableScansTime * 1000, 2) ?> ms)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Sequential Scans</th>
                                    <th>Sequential Tuples Read</th>
                                    <th>Index Scans</th>
                                    <th>Index Tuples Fetched</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableScans as $scan): ?>
                                <tr>
                                    <td><?= $scan['table'] ?></td>
                                    <td><?= $scan['sequential_scans'] ?></td>
                                    <td><?= $scan['sequential_tuples_read'] ?></td>
                                    <td><?= $scan['index_scans'] ?></td>
                                    <td><?= $scan['index_tuples_fetched'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../components/admin_footer.php';
?>