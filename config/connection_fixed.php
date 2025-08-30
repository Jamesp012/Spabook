<?php
// connection_fixed.php - Enhanced version with better error handling
require_once __DIR__ . '/credentials.php';

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Log function for debugging
function logDebug($message) {
    $logFile = __DIR__ . '/../logs/supabase_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Shared function to call Supabase API
function supabaseRequest($method, $endpoint, $data = null)
{
    global $baseUrl, $apiKey;
    $url = "$baseUrl/$endpoint";

    logDebug("Request: $method $url");
    if ($data) {
        logDebug("Data: " . json_encode($data));
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

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Set timeout to prevent hanging
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'POST' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    if ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Debug logging
    logDebug("HTTP Code: $httpCode");
    if ($curlError) {
        logDebug("cURL Error: $curlError");
    }
    logDebug("Response: $response");

    if ($curlError) {
        return ['error' => 'cURL Error: ' . $curlError];
    }

    if ($httpCode >= 400) {
        return ['error' => 'HTTP Error: ' . $httpCode, 'response' => $response];
    }

    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logDebug("JSON Decode Error: " . json_last_error_msg());
        return ['error' => 'JSON Decode Error: ' . json_last_error_msg(), 'raw_response' => $response];
    }

    return $decodedResponse;
}

// Fetch (GET)
$php_fetch = function ($table, $select = '*', $filters = []) {
    try {
        // Handle special case for UPDATE operation
        if ($select === 'UPDATE') {
            $query = [];
            foreach ($filters as $key => $value) {
                $query[] = "$key=eq.$value";
            }
            $endpoint = "$table?" . implode('&', $query);
            return supabaseRequest('PATCH', $endpoint, $filters);
        }
        
        // Handle special case for COUNT operation
        if (is_string($select) && strpos($select, 'COUNT') !== false) {
            // Supabase REST API doesn't support COUNT(*) as count directly
            // Instead, we'll use a different approach
            $query = [];
            
            // Add filters
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
            
            // For count, we'll select the appropriate primary key column
        // For the booking table, use 'bookingid', for others use 'id'
        if ($table === 'booking') {
            $query['select'] = 'bookingid';
        } else {
            $query['select'] = 'id';
        }
            
            $result = supabaseRequest('GET', $table, $query);
            
            // If there's an error, return it
            if (isset($result['error'])) {
                return $result;
            }
            
            // Count the results manually
            $count = count($result);
            
            // Return in the expected format
            return [['count' => $count]];
        }
        
        // Normal GET operation
        $query = ['select' => $select];
        foreach ($filters as $key => $value) {
            // Check if key and value are not null before using str_contains
            if ($key !== null && str_contains($key, '!=')) {
                $field = trim(str_replace('!=', '', $key));
                $query[$field] = "neq.$value";
            } elseif ($value !== null && !str_contains((string)$value, '.')) {
                $query[$key] = "eq.$value";
            } else {
                $query[$key] = $value;
            }
        }
        return supabaseRequest('GET', $table, $query);
    } catch (Exception $e) {
        logDebug("Exception in php_fetch: " . $e->getMessage());
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
};

// Insert (POST)
$php_insert = function ($table, $data) {
    try {
        $result = supabaseRequest('POST', $table, $data);
        logDebug("Insert Result: " . json_encode($result));
        return $result;
    } catch (Exception $e) {
        logDebug("Exception in php_insert: " . $e->getMessage());
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
};

// Update (PATCH)
$php_update = function ($table, $data, $filters = []) {
    try {
        $query = [];
        foreach ($filters as $key => $value) {
            $query[] = "$key=eq.$value";
        }
        $endpoint = "$table?" . implode('&', $query);
        return supabaseRequest('PATCH', $endpoint, $data);
    } catch (Exception $e) {
        logDebug("Exception in php_update: " . $e->getMessage());
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
};

// Delete (DELETE)
$php_delete = function ($table, $filters = []) {
    try {
        // If filters is not an array (old format), convert it
        if (!is_array($filters)) {
            $filters = ['id' => $filters];
        }
        
        $query = [];
        foreach ($filters as $key => $value) {
            $query[] = "$key=eq.$value";
        }
        $endpoint = "$table?" . implode('&', $query);
        return supabaseRequest('DELETE', $endpoint);
    } catch (Exception $e) {
        logDebug("Exception in php_delete: " . $e->getMessage());
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
};

function uploadProfileImage($base64Image, $uuid, $folder, $bucket = 'services-images')
{
    global $projectUrl, $serviceRoleKey;

    try {
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
            logDebug("Failed to decode base64 image");
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
        logDebug("Uploading image: $filename");

        // Step 1: Delete existing image if it exists
        $deleteUrl = "$projectUrl/storage/v1/object/$bucket/$filename";
        $deleteHeaders = [
            "Authorization: Bearer $serviceRoleKey"
        ];

        $deleteCh = curl_init($deleteUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $deleteHeaders
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
            CURLOPT_HTTPHEADER => $uploadHeaders
        ]);

        $response = curl_exec($uploadCh);
        $httpCode = curl_getinfo($uploadCh, CURLINFO_HTTP_CODE);
        curl_close($uploadCh);

        logDebug("Upload response code: $httpCode");

        if ($httpCode >= 200 && $httpCode < 300) {
            return "$projectUrl/storage/v1/object/public/$bucket/$filename";
        }

        logDebug("Upload failed with HTTP code: $httpCode");
        return "../vendor/images/default_profile.png"; // Return default image on failure
    } catch (Exception $e) {
        logDebug("Exception in uploadProfileImage: " . $e->getMessage());
        return "../vendor/images/default_profile.png"; // Return default image on exception
    }
}