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
    global $baseUrl, $serviceRoleKey;

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
        default => 'bin', // fallback
    };

    $filename = "$folder/$uuid.$extension";
    $url = "$baseUrl/storage/v1/object/$bucket/$filename";

    $headers = [
        "Authorization: Bearer $serviceRoleKey",
        "Content-Type: $mimeType"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $imageData,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return "$baseUrl/storage/v1/object/public/$bucket/$filename";
    }

    return false;
}
