<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Function to minify JavaScript
function minifyJS($js) {
    // Remove comments
    $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
    $js = preg_replace('!//.*!', '', $js);
    
    // Remove whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    
    // Remove whitespace around operators
    $js = preg_replace('/\s*([=+\-*\/,;:?&|!{}\[\]()<>])\s*/', '$1', $js);
    
    // Remove unnecessary semicolons
    $js = preg_replace('/;+/', ';', $js);
    
    return trim($js);
}

// Function to scan directory for JS files
function scanDirectoryForJS($dir) {
    $jsFiles = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'js') {
            $jsFiles[] = $file->getPathname();
        }
    }
    
    return $jsFiles;
}

// Process form submission
$message = '';
$status = '';
$optimizedFiles = [];
$totalSaved = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'analyze') {
        // Scan for JS files
        $jsFiles = scanDirectoryForJS('../');
        
        foreach ($jsFiles as $file) {
            $originalContent = file_get_contents($file);
            $originalSize = strlen($originalContent);
            
            $minifiedContent = minifyJS($originalContent);
            $minifiedSize = strlen($minifiedContent);
            
            $saved = $originalSize - $minifiedSize;
            $savedPercent = $originalSize > 0 ? round(($saved / $originalSize) * 100, 2) : 0;
            
            $optimizedFiles[] = [
                'path' => str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $file)),
                'original_size' => $originalSize,
                'minified_size' => $minifiedSize,
                'saved' => $saved,
                'saved_percent' => $savedPercent
            ];
            
            $totalSaved += $saved;
        }
        
        usort($optimizedFiles, function($a, $b) {
            return $b['saved'] - $a['saved'];
        });
        
        $message = "Analysis complete. Found " . count($optimizedFiles) . " JavaScript files. Potential savings: " . round($totalSaved / 1024, 2) . " KB.";
        $status = "success";
    } elseif ($action === 'optimize') {
        // Get selected files
        $selectedFiles = $_POST['files'] ?? [];
        
        foreach ($selectedFiles as $file) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
            
            if (file_exists($fullPath)) {
                $originalContent = file_get_contents($fullPath);
                $originalSize = strlen($originalContent);
                
                // Create backup
                $backupPath = $fullPath . '.bak';
                if (!file_exists($backupPath)) {
                    file_put_contents($backupPath, $originalContent);
                }
                
                // Minify and save
                $minifiedContent = minifyJS($originalContent);
                file_put_contents($fullPath, $minifiedContent);
                
                $minifiedSize = strlen($minifiedContent);
                $saved = $originalSize - $minifiedSize;
                $savedPercent = $originalSize > 0 ? round(($saved / $originalSize) * 100, 2) : 0;
                
                $optimizedFiles[] = [
                    'path' => $file,
                    'original_size' => $originalSize,
                    'minified_size' => $minifiedSize,
                    'saved' => $saved,
                    'saved_percent' => $savedPercent
                ];
                
                $totalSaved += $saved;
            }
        }
        
        $message = "Optimization complete. Optimized " . count($optimizedFiles) . " JavaScript files. Saved " . round($totalSaved / 1024, 2) . " KB.";
        $status = "success";
    } elseif ($action === 'restore') {
        // Get selected files
        $selectedFiles = $_POST['files'] ?? [];
        $restoredCount = 0;
        
        foreach ($selectedFiles as $file) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
            $backupPath = $fullPath . '.bak';
            
            if (file_exists($backupPath)) {
                // Restore from backup
                copy($backupPath, $fullPath);
                unlink($backupPath);
                $restoredCount++;
            }
        }
        
        $message = "Restore complete. Restored " . $restoredCount . " JavaScript files to their original versions.";
        $status = "warning";
    }
}

// Include header
$pageTitle = 'JavaScript Optimization';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">JavaScript Optimization</h1>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $status; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">JavaScript Optimization Tools</h5>
        </div>
        <div class="card-body">
            <p>This tool will analyze and optimize JavaScript files to reduce their size and improve page load times.</p>
            
            <form method="post" action="">
                <input type="hidden" name="action" value="analyze">
                <button type="submit" class="btn btn-primary">Analyze JavaScript Files</button>
            </form>
            
            <?php if (!empty($optimizedFiles)): ?>
            <hr>
            
            <form method="post" action="">
                <div class="table-responsive mt-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>File</th>
                                <th>Original Size</th>
                                <th>Minified Size</th>
                                <th>Saved</th>
                                <th>Saved %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($optimizedFiles as $file): ?>
                            <tr>
                                <td><input type="checkbox" name="files[]" value="<?php echo htmlspecialchars($file['path']); ?>"></td>
                                <td><?php echo htmlspecialchars($file['path']); ?></td>
                                <td><?php echo round($file['original_size'] / 1024, 2); ?> KB</td>
                                <td><?php echo round($file['minified_size'] / 1024, 2); ?> KB</td>
                                <td><?php echo round($file['saved'] / 1024, 2); ?> KB</td>
                                <td><?php echo $file['saved_percent']; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th>Total</th>
                                <th></th>
                                <th></th>
                                <th><?php echo round($totalSaved / 1024, 2); ?> KB</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" name="action" value="optimize" class="btn btn-success">Optimize Selected Files</button>
                    <button type="submit" name="action" value="restore" class="btn btn-warning">Restore Selected Files</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">JavaScript Optimization Tips</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Minification</strong> - Removes whitespace, comments, and unnecessary characters</li>
                <li><strong>Concatenation</strong> - Combine multiple JS files into one to reduce HTTP requests</li>
                <li><strong>Defer Loading</strong> - Use the <code>defer</code> attribute to load JS after HTML parsing</li>
                <li><strong>Async Loading</strong> - Use the <code>async</code> attribute for non-critical JS files</li>
                <li><strong>Code Splitting</strong> - Only load the JavaScript needed for the current page</li>
                <li><strong>Tree Shaking</strong> - Remove unused code from your JavaScript bundles</li>
            </ul>
            
            <div class="alert alert-info mt-3">
                <strong>Note:</strong> This tool performs basic minification. For more advanced optimization, consider using tools like Webpack, Rollup, or Terser.
            </div>
        </div>
    </div>
</div>

<script>
    // Select all checkbox functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="files[]"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
</script>

<?php
// Include footer
include '../components/admin_footer.php';
?>