<?php

class TestDatabase
{
    public function testDatabase($php_insert, $table, $name, $email)
    {
        // Call the shared function from connection.php
        $response =  $php_insert($table, [
            'google_id' => '1234567890',
            'name' => $name,
            'email' => $email
        ]);

        if (is_array($response)) {
            return "✅ Connection successful. Sample data:\n" . print_r($response, true);
        } else {
            return "❌ Connection failed. Response:\n" . var_export($response, true);
        }
    }
}
