<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test User Management Interface</title>
    <link href="vendor/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/Fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>🧪 User Management Interface Test</h2>
        <p class="text-muted">This will help verify that your user management interface is working correctly.</p>
        
        <div class="card">
            <div class="card-body">
                <h5>📊 Data Summary:</h5>
                <ul>
                    <li>👑 <strong>Admins Found:</strong> 1 user ("Spa Book" with role "Admin")</li>
                    <li>👥 <strong>Regular Users:</strong> 5 users with role "User"</li>
                    <li>🌿 <strong>Therapists:</strong> 10 therapists in therapist table</li>
                    <li>📊 <strong>Total Expected:</strong> 16 users in unified table</li>
                </ul>
                
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>System Updated!</h6>
                    <p class="mb-0">The main "User Management" now uses the unified legacy system that displays all users in one table with filters.</p>
                </div>
                
                <h5 class="mt-4">🔗 Test Links:</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-cog fa-2x text-primary mb-2"></i>
                                <h6>Admin Panel</h6>
                                <a href="views/admin_home_page.php" class="btn btn-primary btn-sm">Open Admin Panel</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h6>Direct User Management</h6>
                                <a href="views/admin/admin_manage-users.php" class="btn btn-info btn-sm">Open User Management</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-bug fa-2x text-success mb-2"></i>
                                <h6>Debug Script</h6>
                                <a href="debug_users.php" class="btn btn-success btn-sm">View Debug Info</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-lightbulb me-2"></i>Testing Steps:</h6>
                    <ol>
                        <li><strong>Open Admin Panel</strong> - Login as admin and navigate to "User Management"</li>
                        <li><strong>Check Unified Table</strong> - Should show all 16 users in one table:
                            <ul>
                                <li>Use "Role Filter" to view specific types (Users/Admins/Therapists)</li>
                                <li>Search functionality to find specific users</li>
                                <li>All user types displayed with appropriate badges</li>
                            </ul>
                        </li>
                        <li><strong>Check Console</strong> - Open browser DevTools (F12) to see debug logs</li>
                        <li><strong>Test Actions</strong> - Try editing, viewing, and deleting different user types</li>
                        <li><strong>Test Filters</strong> - Use role and status filters to verify data separation</li>
                    </ol>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>If Table Is Empty:</h6>
                    <p>Check the browser console (F12 → Console tab) for these debug messages:</p>
                    <ul>
                        <li><code>Loaded users from users table: 7</code></li>
                        <li><code>Loaded therapists from therapist table: 10</code></li>
                        <li><code>Total unified users loaded: 16</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>