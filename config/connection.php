<?php
// connection.php
require_once __DIR__ . '/credentials.php';

// Shared function to call Supabase API
function supabaseRequest($method, $endpoint, $data = null)
{
    global $baseUrl, $apiKey;
    $url = "$baseUrl/$endpoint";

    // Log the request details
    $logMessage = date('Y-m-d H:i:s') . " - Supabase Request: Method=$method, Endpoint=$endpoint\n";
    file_put_contents(__DIR__ . '/../logs/debug.log', $logMessage, FILE_APPEND);

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
    file_put_contents('debug_curl.txt', "URL: $url\nHTTP Code: $httpCode\nCURL Error: $curlError\nResponse: $response\n\n", FILE_APPEND);
    
    // Log the response details
    $responseLogMessage = date('Y-m-d H:i:s') . " - Supabase Response: HTTP Code=$httpCode, Error=$curlError\n";
    file_put_contents(__DIR__ . '/../logs/debug.log', $responseLogMessage, FILE_APPEND);

    if ($curlError) {
        $errorMessage = 'cURL Error: ' . $curlError;
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - $errorMessage\n", FILE_APPEND);
        return ['error' => $errorMessage];
    }

    if ($httpCode >= 400) {
        $errorMessage = 'HTTP Error: ' . $httpCode;
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - $errorMessage, Response: $response\n", FILE_APPEND);
        return ['error' => $errorMessage, 'response' => $response];
    }

    // Parse the JSON response
    $decodedResponse = json_decode($response, true);
    
    // Check if JSON parsing failed
    if ($response && $decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        $errorMessage = 'JSON Parse Error: ' . json_last_error_msg();
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - $errorMessage, Response: $response\n", FILE_APPEND);
        return ['error' => $errorMessage, 'raw_response' => $response];
    }
    
    // Return empty array if response is null or empty
    if ($decodedResponse === null) {
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Empty response converted to empty array\n", FILE_APPEND);
        return [];
    }
    
    return $decodedResponse;
}

// Fetch (GET)
$php_fetch = function ($table, $select = '*', $filters = []) {
    // Log the fetch call
    $logMessage = date('Y-m-d H:i:s') . " - php_fetch called: Table=$table, Select=" . (is_string($select) ? $select : json_encode($select)) . ", Filters=" . json_encode($filters) . "\n";
    file_put_contents(__DIR__ . '/../logs/debug.log', $logMessage, FILE_APPEND);
    
    // Handle raw SQL query case (for backward compatibility)
    if (is_string($table) && (strpos($table, 'SELECT') !== false || strpos($table, 'select') !== false)) {
        // This is a raw SQL query - not supported directly by Supabase REST API
        // For now, we'll parse it to extract the table name and conditions
        
        // Log that we're handling a raw SQL query
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Raw SQL query detected: $table\n", FILE_APPEND);
        
        // Extract table name from the query (simple parsing)
        preg_match('/FROM\s+([^\s,]+)/i', $table, $tableMatches);
        if (!empty($tableMatches[1])) {
            $extractedTable = trim($tableMatches[1]);
            
            // Extract WHERE conditions (very simple parsing)
            $whereConditions = [];
            if (preg_match('/WHERE\s+(.*?)(?:ORDER BY|LIMIT|$)/is', $table, $whereMatches)) {
                $whereClause = trim($whereMatches[1]);
                
                // Extract specific conditions (very basic)
                if (preg_match('/bookingid\s*=\s*[\'"]?(\d+)[\'"]?/i', $whereClause, $idMatches)) {
                    $bookingId = $idMatches[1];
                    $whereConditions['bookingid'] = "eq.$bookingId";
                    
                    // Log that we're using a specific booking ID
                    file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Extracted booking ID from SQL: $bookingId\n", FILE_APPEND);
                    
                    // For booking details, we'll make a direct call
                    $result = supabaseRequest('GET', $extractedTable, [
                        'select' => '*',
                        'bookingid' => "eq.$bookingId"
                    ]);
                    
                    // Log the result
                    file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Raw SQL query result count: " . (is_array($result) ? count($result) : 'not an array') . "\n", FILE_APPEND);
                    
                    return $result;
                }
            }
            
            // If we couldn't extract specific conditions, just get all records from the table
            file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Falling back to getting all records from table: $extractedTable\n", FILE_APPEND);
            return supabaseRequest('GET', $extractedTable, ['select' => '*']);
        }
        
        // If we couldn't parse the query, return an empty array
        file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - Could not parse SQL query, returning empty array\n", FILE_APPEND);
        return [];
    }
    
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
            if ($key !== null && is_string($key) && str_contains($key, '!=')) {
                $field = trim(str_replace('!=', '', $key));
                $query[$field] = "neq.$value";
            } elseif ($value !== null && is_string($value) && !str_contains($value, '.')) {
                $query[$key] = "eq.$value";
            } elseif ($value !== null && !is_string($value)) {
                // Handle non-string values (like numbers or booleans)
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
        
        // Log the count result for debugging
        file_put_contents('debug_count.txt', "Table: $table\nFilters: " . print_r($filters, true) . "\nCount: $count\n\n", FILE_APPEND);
        
        // Return in the expected format
        return [['count' => $count]];
    }
    
    // Normal GET operation
    $query = ['select' => $select];
    foreach ($filters as $key => $value) {
        // Check if key and value are not null before using str_contains
        if ($key !== null && is_string($key) && str_contains($key, '!=')) {
            $field = trim(str_replace('!=', '', $key));
            $query[$field] = "neq.$value";
        } elseif ($value !== null && is_string($value) && !str_contains($value, '.')) {
            $query[$key] = "eq.$value";
        } elseif ($value !== null && !is_string($value)) {
            // Handle non-string values (like numbers or booleans)
            $query[$key] = "eq.$value";
        } else {
            $query[$key] = $value;
        }
    }
    
    $result = supabaseRequest('GET', $table, $query);
    
    // Log the result
    file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . " - php_fetch result count: " . (is_array($result) ? count($result) : 'not an array') . "\n", FILE_APPEND);
    
    return $result;
};


// Insert (POST)
$php_insert = function ($table, $data) {
    $result = supabaseRequest('POST', $table, $data);
    file_put_contents('debug_supabase_insert.txt', "Table: $table\nData: " . print_r($data, true) . "\nResult: " . print_r($result, true));
    return $result;
};

// Update (PATCH)
$php_update = function ($table, $data, $filters = []) {
    $query = [];
    foreach ($filters as $key => $value) {
        $query[] = "$key=eq.$value";
    }
    $endpoint = "$table?" . implode('&', $query);
    return supabaseRequest('PATCH', $endpoint, $data);
};

// Delete (DELETE)
$php_delete = function ($table, $filters = []) {
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
};


function uploadProfileImage($base64Image, $uuid, $folder, $bucket = 'services-images')
{
    global $projectUrl, $serviceRoleKey;

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

    if ($httpCode >= 200 && $httpCode < 300) {
        return "$projectUrl/storage/v1/object/public/$bucket/$filename";
    }

    return "../vendor/images/default_profile.png"; // Return default image on failure
}
