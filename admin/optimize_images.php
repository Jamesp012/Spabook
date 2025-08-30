<?php
// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/connection.php';

// Function to optimize an image
function optimizeImage($imagePath, $quality = 85) {
    if (!file_exists($imagePath)) {
        return false;
    }
    
    $info = getimagesize($imagePath);
    if (!$info) {
        return false;
    }
    
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($imagePath);
            // Handle transparency
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($imagePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($imagePath);
            } else {
                return false;
            }
            break;
        default:
            return false;
    }
    
    if (!$image) {
        return false;
    }
    
    // Create backup
    $backupPath = $imagePath . '.bak';
    if (!file_exists($backupPath)) {
        copy($imagePath, $backupPath);
    }
    
    // Optimize based on mime type
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($image, $imagePath, $quality);
            break;
        case 'image/png':
            // PNG quality is 0-9, not 0-100
            $pngQuality = floor(9 - (($quality / 100) * 9));
            imagepng($image, $imagePath, $pngQuality);
            break;
        case 'image/gif':
            imagegif($image, $imagePath);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                imagewebp($image, $imagePath, $quality);
            } else {
                return false;
            }
            break;
    }
    
    imagedestroy($image);
    return true;
}

// Function to get all images from Supabase storage
function getStorageImages() {
    global $projectUrl, $serviceRoleKey;
    
    $url = "$projectUrl/storage/v1/object/list/services-images";
    $headers = [
        "Authorization: Bearer $serviceRoleKey"
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    return [];
}

// Function to download an image from Supabase
function downloadImage($path) {
    global $projectUrl, $serviceRoleKey;
    
    $url = "$projectUrl/storage/v1/object/services-images/$path";
    $headers = [
        "Authorization: Bearer $serviceRoleKey"
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return $response;
    }
    
    return null;
}

// Function to upload an optimized image back to Supabase
function uploadOptimizedImage($path, $imageData) {
    global $projectUrl, $serviceRoleKey;
    
    $url = "$projectUrl/storage/v1/object/services-images/$path";
    
    // Detect MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($imageData);
    
    $headers = [
        "Authorization: Bearer $serviceRoleKey",
        "Content-Type: $mimeType"
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $imageData,
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

// Process form submission
$message = '';
$status = '';
$optimizedImages = [];
$failedImages = [];
$totalSaved = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quality = isset($_POST['quality']) ? intval($_POST['quality']) : 85;
    $tempDir = sys_get_temp_dir() . '/spabook_image_optimization';
    
    // Create temp directory if it doesn't exist
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // Get images from storage
    $images = getStorageImages();
    
    if (!empty($images)) {
        foreach ($images as $image) {
            $path = $image['name'];
            
            // Skip non-image files
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                continue;
            }
            
            // Download image
            $imageData = downloadImage($path);
            if ($imageData === null) {
                $failedImages[] = [
                    'path' => $path,
                    'reason' => 'Failed to download'
                ];
                continue;
            }
            
            // Save to temp file
            $tempFile = $tempDir . '/' . basename($path);
            file_put_contents($tempFile, $imageData);
            
            // Get original size
            $originalSize = filesize($tempFile);
            
            // Optimize image
            if (optimizeImage($tempFile, $quality)) {
                // Get new size
                $newSize = filesize($tempFile);
                $saved = $originalSize - $newSize;
                $totalSaved += $saved;
                
                // Upload optimized image back to Supabase
                $optimizedData = file_get_contents($tempFile);
                if (uploadOptimizedImage($path, $optimizedData)) {
                    $optimizedImages[] = [
                        'path' => $path,
                        'original_size' => $originalSize,
                        'new_size' => $newSize,
                        'saved' => $saved,
                        'saved_percent' => round(($saved / $originalSize) * 100, 2)
                    ];
                } else {
                    $failedImages[] = [
                        'path' => $path,
                        'reason' => 'Failed to upload optimized image'
                    ];
                }
            } else {
                $failedImages[] = [
                    'path' => $path,
                    'reason' => 'Failed to optimize'
                ];
            }
            
            // Clean up
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            if (file_exists($tempFile . '.bak')) {
                unlink($tempFile . '.bak');
            }
        }
        
        $message = "Image optimization complete. Optimized " . count($optimizedImages) . " images, saved " . round($totalSaved / 1024 / 1024, 2) . " MB.";
        $status = "success";
    } else {
        $message = "No images found in storage.";
        $status = "warning";
    }
}

// Include header
$pageTitle = 'Image Optimization';
include '../components/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Image Optimization</h1>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $status; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Optimize Images</h5>
                </div>
                <div class="card-body">
                    <p>This tool will optimize all images in the Supabase storage to reduce their file size while maintaining acceptable quality.</p>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="quality" class="form-label">Quality (1-100)</label>
                            <input type="range" class="form-range" id="quality" name="quality" min="1" max="100" value="85">
                            <div class="d-flex justify-content-between">
                                <span>Lower quality, smaller size</span>
                                <span id="qualityValue">85</span>
                                <span>Higher quality, larger size</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Optimize All Images</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Image Optimization Tips</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>JPEG (JPG)</strong> - Best for photographs and complex images with many colors</li>
                        <li><strong>PNG</strong> - Best for images with transparency or simple graphics with few colors</li>
                        <li><strong>WebP</strong> - Modern format with better compression than JPEG and PNG</li>
                        <li><strong>Quality Setting</strong> - 85 is a good balance between quality and file size</li>
                        <li><strong>Image Dimensions</strong> - Always resize images to the actual dimensions needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($optimizedImages)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Optimized Images</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Original Size</th>
                            <th>New Size</th>
                            <th>Saved</th>
                            <th>Saved %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($optimizedImages as $image): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($image['path']); ?></td>
                            <td><?php echo round($image['original_size'] / 1024, 2); ?> KB</td>
                            <td><?php echo round($image['new_size'] / 1024, 2); ?> KB</td>
                            <td><?php echo round($image['saved'] / 1024, 2); ?> KB</td>
                            <td><?php echo $image['saved_percent']; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th></th>
                            <th></th>
                            <th><?php echo round($totalSaved / 1024, 2); ?> KB</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($failedImages)): ?>
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0">Failed Images</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failedImages as $image): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($image['path']); ?></td>
                            <td><?php echo htmlspecialchars($image['reason']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    // Update quality value display
    document.getElementById('quality').addEventListener('input', function() {
        document.getElementById('qualityValue').textContent = this.value;
    });
</script>

<?php
// Include footer
include '../components/admin_footer.php';
?>