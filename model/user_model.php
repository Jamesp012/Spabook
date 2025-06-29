<?php

class User
{

    public function validEmailAdd($php_fetch, $table, $email)
    {
        // Check if email already exists
        $existingUser = $php_fetch($table, ['select' => 'email', 'email' => $email]);

        if (is_array($existingUser) && isset($existingUser[0]['email'])) {
            // Email exists
            return $existingUser[0]['email'];
        } else {
            // Email does not exist
            return 'not_exist';
        }
    }

    public function signUpUser($php_insert, $table, $email, $user_fullname,  $user_address, $contact, $supabase_uuid)
    {
        // Check if email already exists
        $sign_up_user = $php_insert($table, [
            'full_name' => $user_fullname,
            'address' => $user_address,
            'contact_number' => $contact,
            'email' => $email,
            'agred_to_terms' => true,
            'user_id' => $supabase_uuid,
        ]);
        if (isset($sign_up_user['error'])) {
            // Handle error
            return 'error';
        } else {
            // User signed up successfully
            return 'success';
        }
    }
}
