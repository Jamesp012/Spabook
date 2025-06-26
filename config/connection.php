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

$php_fetch = function ($table, $select = '*', $filters = []) {
    $params = array_merge(['select' => $select], $filters);
    return supabaseRequest('GET', $table, $params);
};

// Insert (POST)
$php_insert = function ($table, $data) {
    return supabaseRequest('POST', $table, $data);
};

// Update (PATCH)
$php_update = function ($table, $id, $data) {
    $endpoint = "$table?id=eq.$id";
    return supabaseRequest('PATCH', $endpoint, $data);
};

// Delete (DELETE)
$php_delete = function ($table, $id) {
    $endpoint = "$table?id=eq.$id";
    return supabaseRequest('DELETE', $endpoint);
};
