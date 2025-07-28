<?php
require_once '../config/credentials.php';
$body = json_decode(file_get_contents('php://input'), true);
$jwt = $body['token'] ?? '';

if (!$jwt) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

// Validate token with Supabase
$ch = curl_init($projectUrl . '/auth/v1/user');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $jwt",
    "apikey: $apiKey"
]);

$response = curl_exec($ch);
curl_close($ch);

$user = json_decode($response, true);

if (isset($user['error'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (isset($user['id'])) {
    // Valid user session
    echo json_encode(['user' => $user]);
} else {
    // Invalid session or error
    echo json_encode(['error' => 'Invalid session']);
}
