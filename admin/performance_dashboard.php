<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/connection.php';
require_once '../utils/performance.php';
require_once '../utils/cache.php';

// Initialize performance monitoring
Performance::init(true);

// Get cache statistics
$cacheDir = '../cache/';
$cacheFiles = glob($cacheDir . '*.cache');
$cacheCount = count($cacheFiles);
$cacheSize = 0;
$cacheStats = [];

foreach ($cacheFiles as $file) {
    $cacheSize += filesize($file);
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    
    if ($data) {
        $key = basename($file, '.cache');
        $expires = isset($data['expires']) ? date('Y-m-d H:i:s', $data['expires']) : 'Unknown';
        $size = round(filesize($file) / 1024, 2);
        
        $cacheStats[] = [
            'key' => $key,
            'expires' => $expires,
            'size' => $size
        ];
    }
}

$cacheSize = round($cacheSize / 1024 / 1024, 2); // Convert to MB

// Get performance log statistics
$logFile = '../logs/performance.log';
$logStats = [];
$slowQueries = [];
$avgRequestTime = 0;
$totalRequests = 0;

if (file_exists($logFile)) {
    $log = file_get_contents($logFile);
    $lines = explode("\n", $log);
    
    $requestTimes = [];
    $queryTimes = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'Request completed') !== false) {
            preg_match('/Time: ([\d\.]+)ms/', $line, $matches);
            if (isset($matches[1])) {
                $requestTimes[] = floatval($matches[1]);
                $totalRequests++;
            }
        } elseif (strpos($line, 'Query #') !== false) {
            preg_match('/Query #\d+ \| ([\d\.]+)ms \| (.+)$/', $line, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $queryTime = floatval($matches[1]);
                $queryText = $matches[2];
                
                $queryTimes[] = $queryTime;
                
                if ($queryTime > 500) { // Slow queries > 500ms
                    $slowQueries[] = [
                        'time' => $queryTime,
                        'query' => $queryText
                    ];
                }
            }
        }
    }
    
    // Calculate statistics
    $avgRequestTime = count($requestTimes) > 0 ? array_sum($requestTimes) / count($requestTimes) : 0;
    $maxRequestTime = count($requestTimes) > 0 ? max($requestTimes) : 0;
    $avgQueryTime = count($queryTimes) > 0 ? array_sum($queryTimes) / count($queryTimes) : 0;
    $maxQueryTime = count($queryTimes) > 0 ? max($queryTimes) : 0;
    
    $logStats = [
        'total_requests' => $totalRequests,
        'avg_request_time' => $avgRequestTime,
        'max_request_time' => $maxRequestTime,
        'total_queries' => count($queryTimes),
        'avg_query_time' => $avgQueryTime,
        'max_query_time' => $maxQueryTime,
        'slow_queries' => count($slowQueries)
    ];
}

// Sort slow queries by time (descending)
usort($slowQueries, function($a, $b) {
    return $b['time'] - $a['time'];
});

// Limit to top 10 slow queries
$slowQueries = array_slice($slowQueries, 0, 10);

// Get current optimization status
$connectionFile = '../config/connection.php';
$optimizedFile = '../config/connection_optimized.php';
$currentContent = file_exists($connectionFile) ? file_get_contents($connectionFile) : '';
$optimizedContent = file_exists($optimizedFile) ? file_get_contents($optimizedFile) : '';

$isOptimized = (strpos($currentContent, 'connection_optimized') !== false || 
               (strpos($currentContent, 'Performance::') !== false && 
                strpos($currentContent, 'Cache::') !== false));

// Include header
$pageTitle = 'Performance Dashboard';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Performance Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">System Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Performance Optimizations:</span>
                        <span class="badge bg-<?php echo $isOptimized ? 'success' : 'warning'; ?>">
                            <?php echo $isOptimized ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Cache System:</span>
                        <span class="badge bg-<?php echo class_exists('Cache') ? 'success' : 'danger'; ?>">
                            <?php echo class_exists('Cache') ? 'Available' : 'Not Available'; ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Performance Monitoring:</span>
                        <span class="badge bg-<?php echo class_exists('Performance') ? 'success' : 'danger'; ?>">
                            <?php echo class_exists('Performance') ? 'Available' : 'Not Available'; ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Database Indexes:</span>
                        <span class="badge bg-info">See Database Analysis</span>
                    </div>
                    
                    <div class="mt-4">
                        <a href="toggle_optimization.php" class="btn btn-primary">Manage Optimizations</a>
                        <a href="clear_cache.php" class="btn btn-warning">Clear Cache</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Cache Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo $cacheCount; ?></h3>
                                    <p class="mb-0">Cached Items</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo $cacheSize; ?> MB</h3>
                                    <p class="mb-0">Cache Size</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="analyze_performance.php" class="btn btn-success">View Cache Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($logStats)): ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo $logStats['total_requests']; ?></h3>
                                    <p class="mb-0">Total Requests</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo round($logStats['avg_request_time'], 2); ?> ms</h3>
                                    <p class="mb-0">Avg Request Time</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo $logStats['total_queries']; ?></h3>
                                    <p class="mb-0">Total Queries</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo round($logStats['avg_query_time'], 2); ?> ms</h3>
                                    <p class="mb-0">Avg Query Time</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        No performance log data available. Enable performance logging to see metrics.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($slowQueries)): ?>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">Slow Queries (>500ms)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time (ms)</th>
                                    <th>Query</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slowQueries as $query): ?>
                                <tr>
                                    <td><?php echo round($query['time'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($query['query']); ?></td>
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
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Optimization Tools</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Database Optimization</h5>
                                    <p class="card-text">Analyze and optimize database performance with indexes and query improvements.</p>
                                    <a href="analyze_performance.php" class="btn btn-primary">Database Analysis</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Image Optimization</h5>
                                    <p class="card-text">Optimize images to reduce file size while maintaining quality.</p>
                                    <a href="optimize_images.php" class="btn btn-primary">Optimize Images</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">JavaScript Optimization</h5>
                                    <p class="card-text">Minify JavaScript files to improve page load times.</p>
                                    <a href="optimize_js.php" class="btn btn-primary">Optimize JavaScript</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">CSS Optimization</h5>
                                    <p class="card-text">Minify CSS files to reduce file size and improve load times.</p>
                                    <a href="optimize_css.php" class="btn btn-primary">Optimize CSS</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Cache Management</h5>
                                    <p class="card-text">Manage the cache system to improve performance.</p>
                                    <a href="clear_cache.php" class="btn btn-primary">Manage Cache</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Connection Optimization</h5>
                                    <p class="card-text">Toggle between optimized and original database connection.</p>
                                    <a href="toggle_optimization.php" class="btn btn-primary">Connection Settings</a>
                                </div>
                            </div>
                        </div>
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