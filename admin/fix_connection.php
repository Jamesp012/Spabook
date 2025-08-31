<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Paths
$originalFile = '../config/connection.php';
$fixedFile = '../config/connection_fixed.php';
$backupFile = '../config/connection.php.bak';

// Create backup if it doesn't exist
if (!file_exists($backupFile) && file_exists($originalFile)) {
    copy($originalFile, $backupFile);
}

// Check if we're restoring or applying the fix
$action = $_GET['action'] ?? 'fix';

if ($action === 'restore' && file_exists($backupFile)) {
    // Restore from backup
    copy($backupFile, $originalFile);
    $message = "Original connection file restored successfully.";
    $alertType = "warning";
} elseif ($action === 'fix' && file_exists($fixedFile)) {
    // Apply the fix
    copy($fixedFile, $originalFile);
    $message = "Connection file fixed successfully.";
    $alertType = "success";
} else {
    $message = "Error: Required files not found.";
    $alertType = "danger";
}

// Include header
$pageTitle = 'Fix Connection';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Fix Connection</h1>
    
    <div class="alert alert-<?php echo $alertType; ?>">
        <?php echo $message; ?>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Connection File Management</h5>
        </div>
        <div class="card-body">
            <p>This tool helps you fix issues with the connection to Supabase.</p>
            
            <div class="mb-3">
                <a href="fix_connection.php?action=fix" class="btn btn-primary">Apply Fixed Connection</a>
                <a href="fix_connection.php?action=restore" class="btn btn-warning">Restore Original Connection</a>
            </div>
            
            <div class="alert alert-info">
                <strong>Note:</strong> If you're experiencing issues with the admin dashboard, try applying the fixed connection. If that doesn't work, you can always restore the original connection.
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Test Connection</h5>
        </div>
        <div class="card-body">
            <p>Use these links to test your connection to Supabase:</p>
            
            <div class="mb-3">
                <a href="../test_connection.php" target="_blank" class="btn btn-info">Test Original Connection</a>
                <a href="../test_fixed_connection.php" target="_blank" class="btn btn-info">Test Fixed Connection</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../components/admin_footer.php';
?>