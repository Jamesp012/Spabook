<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔗 Direct Supabase Connection Test</h2>";

try {
    require_once 'config/credentials.php';
    echo "✅ Credentials loaded<br>";
    echo "Base URL: " . substr($baseUrl, 0, 50) . "...<br>";
    echo "API Key length: " . strlen($apiKey) . " characters<br>";
    
    echo "<h3>1. Test Direct CURL to Supabase</h3>";
    
    // Test direct connection to Supabase
    $url = "$baseUrl/therapist?select=*";
    
    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];
    
    echo "Testing URL: $url<br>";
    echo "Headers: <pre>" . print_r($headers, true) . "</pre>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    echo "<h4>Response Details:</h4>";
    echo "HTTP Code: $httpCode<br>";
    echo "CURL Error: " . ($curlError ?: 'None') . "<br>";
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
    
    curl_close($ch);
    
    // Try to decode response
    if ($response) {
        $decoded = json_decode($response, true);
        if ($decoded !== null) {
            echo "<h4>Decoded Response:</h4>";
            echo "<pre>" . print_r($decoded, true) . "</pre>";
            
            if (is_array($decoded)) {
                echo "✅ Valid array response with " . count($decoded) . " items<br>";
            }
        } else {
            echo "❌ JSON decode failed: " . json_last_error_msg() . "<br>";
        }
    }
    
    echo "<h3>2. Test Alternative Table Names</h3>";
    
    $tables_to_test = [
        'therapist',
        'therapists', 
        'Therapist',
        'Therapists',
        'therapist_table',
        'spa_therapist'
    ];
    
    foreach ($tables_to_test as $table) {
        echo "<h4>Testing: $table</h4>";
        
        $test_url = "$baseUrl/$table?select=*&limit=1";
        
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $test_response = curl_exec($ch);
        $test_httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: $test_httpCode - ";
        
        if ($test_httpCode == 200) {
            echo "✅ Table '$table' exists!<br>";
            $test_decoded = json_decode($test_response, true);
            if (is_array($test_decoded)) {
                echo "Record count: " . count($test_decoded) . "<br>";
                if (count($test_decoded) > 0) {
                    echo "Fields: " . implode(', ', array_keys($test_decoded[0])) . "<br>";
                }
            }
        } elseif ($test_httpCode == 404) {
            echo "❌ Table '$table' not found<br>";
        } elseif ($test_httpCode == 401) {
            echo "🔒 Authentication error<br>";
        } else {
            echo "⚠️ HTTP $test_httpCode<br>";
            echo "Response: " . substr($test_response, 0, 100) . "<br>";
        }
        
        echo "<br>";
    }
    
    echo "<h3>3. Test Services Table (for comparison)</h3>";
    
    $service_tables = ['services', 'booking_services', 'service'];
    
    foreach ($service_tables as $table) {
        echo "<h4>Testing services table: $table</h4>";
        
        $test_url = "$baseUrl/$table?select=*&limit=1";
        
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $test_response = curl_exec($ch);
        $test_httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: $test_httpCode - ";
        
        if ($test_httpCode == 200) {
            echo "✅ Table '$table' exists!<br>";
            $test_decoded = json_decode($test_response, true);
            if (is_array($test_decoded)) {
                echo "Record count: " . count($test_decoded) . "<br>";
                if (count($test_decoded) > 0) {
                    echo "Fields: " . implode(', ', array_keys($test_decoded[0])) . "<br>";
                    echo "Sample: <pre>" . print_r($test_decoded[0], true) . "</pre>";
                }
            }
        } else {
            echo "❌ Not found or error<br>";
        }
        
        echo "<br>";
    }
    
    echo "<h3>4. Test Insert (if therapist table found)</h3>";
    
    // If we found a working therapist table, try insert
    $working_table = null;
    foreach ($tables_to_test as $table) {
        $test_url = "$baseUrl/$table?select=*&limit=1";
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $test_response = curl_exec($ch);
        $test_httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($test_httpCode == 200) {
            $working_table = $table;
            break;
        }
    }
    
    if ($working_table) {
        echo "Found working table: $working_table<br>";
        
        $insert_data = [
            'therapist_name' => 'Direct Test ' . date('H:i:s'),
            'therapist_desc' => 'Direct CURL test',
            'service_id' => '1'
        ];
        
        echo "Attempting insert: <pre>" . print_r($insert_data, true) . "</pre>";
        
        $insert_url = "$baseUrl/$working_table";
        $insert_headers = array_merge($headers, ["Prefer: return=representation"]);
        
        $ch = curl_init($insert_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $insert_headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($insert_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $insert_response = curl_exec($ch);
        $insert_httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $insert_error = curl_error($ch);
        curl_close($ch);
        
        echo "Insert HTTP Code: $insert_httpCode<br>";
        echo "Insert Error: " . ($insert_error ?: 'None') . "<br>";
        echo "Insert Response: <pre>" . htmlspecialchars($insert_response) . "</pre>";
        
        if ($insert_httpCode == 201) {
            echo "✅ Insert successful!<br>";
        } else {
            echo "❌ Insert failed<br>";
        }
    } else {
        echo "❌ No working therapist table found for insert test<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; overflow-x: auto; }
h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
h4 { color: #555; margin-top: 20px; }
</style>