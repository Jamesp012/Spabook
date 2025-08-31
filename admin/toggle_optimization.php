<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$connectionFile = '../config/connection.php';
$originalFile = '../config/connection.original.php';
$optimizedFile = '../config/connection_optimized.php';

$action = $_GET['action'] ?? '';

if ($action === 'enable') {
    // Backup original if not already backed up
    if (!file_exists($originalFile)) {
        copy($connectionFile, $originalFile);
    }
    
    // Replace with optimized version
    copy($optimizedFile, $connectionFile);
    
    $message = "Performance optimizations enabled successfully!";
    $status = "success";
} elseif ($action === 'disable') {
    // Restore original if backup exists
    if (file_exists($originalFile)) {
        copy($originalFile, $connectionFile);
        $message = "Performance optimizations disabled. Original connection file restored.";
        $status = "warning";
    } else {
        $message = "Error: Original connection file backup not found.";
        $status = "danger";
    }
} else {
    // Check current status
    $currentContent = file_get_contents($connectionFile);
    $optimizedContent = file_exists($optimizedFile) ? file_get_contents($optimizedFile) : '';
    
    $isOptimized = (strpos($currentContent, 'connection_optimized') !== false || 
                   (strpos($currentContent, 'Performance::') !== false && 
                    strpos($currentContent, 'Cache::') !== false));
    
    $status = $isOptimized ? "success" : "warning";
    $message = $isOptimized ? 
               "Performance optimizations are currently ENABLED." : 
               "Performance optimizations are currently DISABLED.";
}

// Include header
$pageTitle = 'Toggle Performance Optimizations';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Database Performance Optimizations</h1>
    
    <div class="alert alert-<?php echo $status; ?>">
        <?php echo $message; ?>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Performance Optimization Controls</h5>
        </div>
        <div class="card-body">
            <p>These controls allow you to enable or disable the performance optimizations for the database connection.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Optimized Version</h5>
                            <p>Features:</p>
                            <ul>
                                <li>Connection pooling</li>
                                <li>Request caching</li>
                                <li>Performance monitoring</li>
                                <li>Automatic cache invalidation</li>
                                <li>Batch fetching</li>
                                <li>Timeout handling</li>
                            </ul>
                            <a href="?action=enable" class="btn btn-success">Enable Optimizations</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Original Version</h5>
                            <p>Features:</p>
                            <ul>
                                <li>Basic Supabase API connectivity</li>
                                <li>Simple CRUD operations</li>
                                <li>No performance enhancements</li>
                                <li>No caching</li>
                                <li>No connection pooling</li>
                            </ul>
                            <a href="?action=disable" class="btn btn-warning">Disable Optimizations</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <p><strong>Note:</strong> After toggling optimizations, you should clear the cache to ensure a clean state.</p>
                <a href="clear_cache.php" class="btn btn-info">Clear Cache</a>
                <a href="analyze_performance.php" class="btn btn-primary">Analyze Performance</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../components/admin_footer.php';
?>