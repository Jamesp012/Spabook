<?php
// connection.php
require_once '../config/credentials.php';

// Shared function to call Supabase API
function supabaseRequest($method, $endpoint, $data = null)
{
    global $baseUrl, $apiKey;
    $url = "$baseUrl/$endpoint";

    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    if ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = "Prefer: return=representation";
    }

    if ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Fetch (GET)
$php_fetch = function ($table, $select = '*', $filters = []) {
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
    // $params = array_merge(['select' => $select], $filters);
    return supabaseRequest('GET', $table, $query);
};


// Insert (POST)
$php_insert = function ($table, $data) {
    return supabaseRequest('POST', $table, $data);
};

// Update (PATCH)
$php_update = function ($table, $filters = [], $data) {
    $query = [];
    foreach ($filters as $key => $value) {
        $query[] = "$key=eq.$value";
    }
    $endpoint = "$table?" . implode('&', $query);
    return supabaseRequest('PATCH', $endpoint, $data);
};

// Delete (DELETE)
$php_delete = function ($table, $id) {
    $endpoint = "$table?id=eq.$id";
    return supabaseRequest('DELETE', $endpoint);
};


function uploadProfileImage($base64Image, $uuid, $folder, $bucket = 'services-images')
{
    global $projectUrl, $serviceRoleKey;

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
