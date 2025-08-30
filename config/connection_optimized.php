<?php
// connection_optimized.php
require_once __DIR__ . '/credentials.php';

// Include performance monitoring if available
if (file_exists(__DIR__ . '/../utils/performance.php')) {
    require_once __DIR__ . '/../utils/performance.php';
    // Initialize performance monitoring
    if (class_exists('Performance')) {
        Performance::init(false); // Don't enable logging by default
    }
}

// Include cache if available
if (file_exists(__DIR__ . '/../utils/cache.php')) {
    require_once __DIR__ . '/../utils/cache.php';
    // Initialize cache
    if (class_exists('Cache')) {
        Cache::init();
    }
}

// Connection pooling - store and reuse curl handles
$curlHandles = [];

// Shared function to call Supabase API with optimizations
function supabaseRequest($method, $endpoint, $data = null)
{
    global $baseUrl, $apiKey, $curlHandles;
    $url = "$baseUrl/$endpoint";
    $startTime = microtime(true);
    
    // Generate a cache key for GET requests if Cache class exists
    $cacheKey = null;
    if ($method === 'GET' && class_exists('Cache')) {
        $cacheKey = 'supabase_' . md5($url . json_encode($data));
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            // Log the cached query if Performance class exists
            if (class_exists('Performance')) {
                Performance::logQuery("CACHED: $method $url " . json_encode($data), 0);
            }
            return $cachedResult;
        }
    }

    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    if ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }

    if ($method === 'POST' || $method === 'PATCH') {
        $headers[] = "Prefer: return=representation";
    }
    
    // Use connection pooling - reuse curl handles
    $handleKey = md5($url);
    if (isset($curlHandles[$handleKey])) {
        $ch = $curlHandles[$handleKey];
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        $ch = curl_init($url);
        $curlHandles[$handleKey] = $ch;
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Set timeout to prevent hanging requests
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Enable HTTP/2 if available
    if (defined('CURL_HTTP_VERSION_2_0')) {
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    }

    if ($method === 'POST' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        // Reset POSTFIELDS if this handle was previously used for POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
    }

    if ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    } else if ($method === 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    // Don't close the handle - keep it for reuse
    // curl_close($ch);
    
    // Calculate query time
    $queryTime = microtime(true) - $startTime;
    
    // Log the query if Performance class exists
    if (class_exists('Performance')) {
        Performance::logQuery("$method $url " . json_encode($data), $queryTime);
    }

    if ($curlError) {
        error_log("cURL Error: $curlError");
        return ['error' => 'cURL Error: ' . $curlError];
    }

    if ($httpCode >= 400) {
        error_log("HTTP Error $httpCode: $response");
        return ['error' => 'HTTP Error: ' . $httpCode, 'response' => $response];
    }
    
    $result = json_decode($response, true);
    
    // Cache GET results if Cache class exists
    if ($method === 'GET' && $cacheKey && class_exists('Cache') && !isset($result['error'])) {
        // Cache for 30 seconds by default
        Cache::set($cacheKey, $result, 30);
    }

    return $result;
}

// Clean up curl handles on script termination
register_shutdown_function(function() {
    global $curlHandles;
    foreach ($curlHandles as $handle) {
        curl_close($handle);
    }
});

// Optimized fetch function with caching
$php_fetch = function ($table, $select = '*', $filters = []) {
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('php_fetch');
    }
    
    // Generate cache key
    $cacheKey = null;
    if (class_exists('Cache')) {
        $cacheKey = 'fetch_' . md5($table . json_encode($select) . json_encode($filters));
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    
    $query = ['select' => $select];
    foreach ($filters as $key => $value) {
        if (str_contains($key, '!=')) {
            $field = trim(str_replace('!=', '', $key));
            $query[$field] = "neq.$value";
        } elseif (!str_contains($value, '.')) {
            $query[$key] = "eq.$value";
        } else {
            $query[$key] = $value;
        }
    }
    
    $result = supabaseRequest('GET', $table, $query);
    
    // Cache the result if Cache class exists
    if ($cacheKey && class_exists('Cache') && !isset($result['error'])) {
        Cache::set($cacheKey, $result, 30);
    }
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('php_fetch');
    }
    
    return $result;
};

// Optimized insert function
$php_insert = function ($table, $data) {
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('php_insert');
    }
    
    $result = supabaseRequest('POST', $table, $data);
    
    // Invalidate cache for this table if Cache class exists
    if (class_exists('Cache')) {
        $cacheFiles = glob(__DIR__ . '/../cache/*.cache');
        foreach ($cacheFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, $table) !== false) {
                unlink($file);
            }
        }
    }
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('php_insert');
    }
    
    return $result;
};

// Optimized update function
$php_update = function ($table, $data, $filters = []) {
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('php_update');
    }
    
    $query = [];
    foreach ($filters as $key => $value) {
        $query[] = "$key=eq.$value";
    }
    $endpoint = "$table?" . implode('&', $query);
    $result = supabaseRequest('PATCH', $endpoint, $data);
    
    // Invalidate cache for this table if Cache class exists
    if (class_exists('Cache')) {
        $cacheFiles = glob(__DIR__ . '/../cache/*.cache');
        foreach ($cacheFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, $table) !== false) {
                unlink($file);
            }
        }
    }
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('php_update');
    }
    
    return $result;
};

// Optimized delete function
$php_delete = function ($table, $filters = []) {
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('php_delete');
    }
    
    // If filters is not an array (old format), convert it
    if (!is_array($filters)) {
        $filters = ['id' => $filters];
    }
    
    $query = [];
    foreach ($filters as $key => $value) {
        $query[] = "$key=eq.$value";
    }
    $endpoint = "$table?" . implode('&', $query);
    $result = supabaseRequest('DELETE', $endpoint);
    
    // Invalidate cache for this table if Cache class exists
    if (class_exists('Cache')) {
        $cacheFiles = glob(__DIR__ . '/../cache/*.cache');
        foreach ($cacheFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, $table) !== false) {
                unlink($file);
            }
        }
    }
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('php_delete');
    }
    
    return $result;
};

// Optimized batch fetch function
$php_batch_fetch = function ($table, $id_column, $ids, $select = '*') {
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('php_batch_fetch');
    }
    
    // Remove duplicates
    $ids = array_unique($ids);
    
    if (empty($ids)) {
        return [];
    }
    
    // Generate cache key
    $cacheKey = null;
    if (class_exists('Cache')) {
        $cacheKey = 'batch_fetch_' . md5($table . $id_column . json_encode($ids) . $select);
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    
    // Build query for Supabase
    $query = [
        'select' => $select,
        $id_column => 'in.(' . implode(',', $ids) . ')'
    ];
    
    $result = supabaseRequest('GET', $table, $query);
    
    // Cache the result if Cache class exists
    if ($cacheKey && class_exists('Cache') && !isset($result['error'])) {
        Cache::set($cacheKey, $result, 30);
    }
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('php_batch_fetch');
    }
    
    return $result;
};

// Optimized image upload function
function uploadProfileImage($base64Image, $uuid, $folder, $bucket = 'services-images')
{
    global $projectUrl, $serviceRoleKey;
    
    // Start timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::startTimer('uploadProfileImage');
    }

    // Handle data URL format (data:image/png;base64,...)
    if (strpos($base64Image, 'data:') === 0) {
        // Split by comma to get just the base64 part
        $parts = explode(',', $base64Image, 2);
        if (count($parts) === 2) {
            $base64Image = $parts[1];
        }
    }

    // Decode base64 image
    $imageData = base64_decode($base64Image);
    if ($imageData === false) {
        return false;
    }

    // Detect MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($imageData);

    // Determine file extension
    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'bin',
    };

    $filename = "$folder/$uuid.$extension";

    // Step 1: Delete existing image if it exists
    $deleteUrl = "$projectUrl/storage/v1/object/$bucket/$filename";
    $deleteHeaders = [
        "Authorization: Bearer $serviceRoleKey"
    ];

    $deleteCh = curl_init($deleteUrl);
    curl_setopt_array($deleteCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => $deleteHeaders,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10
    ]);

    curl_exec($deleteCh);
    curl_close($deleteCh);
    // (ignore delete errors; continue to upload)

    // Step 2: Upload new image
    $uploadUrl = "$projectUrl/storage/v1/object/$bucket/$filename";
    $uploadHeaders = [
        "Authorization: Bearer $serviceRoleKey",
        "Content-Type: $mimeType"
    ];

    $uploadCh = curl_init($uploadUrl);
    curl_setopt_array($uploadCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST', // POST: create new; PUT: overwrite
        CURLOPT_POSTFIELDS => $imageData,
        CURLOPT_HTTPHEADER => $uploadHeaders,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 30 // Longer timeout for uploads
    ]);

    $response = curl_exec($uploadCh);
    $httpCode = curl_getinfo($uploadCh, CURLINFO_HTTP_CODE);
    curl_close($uploadCh);
    
    // End timer if Performance class exists
    if (class_exists('Performance')) {
        Performance::endTimer('uploadProfileImage');
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return "$projectUrl/storage/v1/object/public/$bucket/$filename";
    }

    return "../vendor/images/default_profile.png"; // Return default image on failure
}