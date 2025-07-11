<?php

class User
{

    public function validEmailAdd($php_fetch, $table, $email)
    {
        // Check if email already exists
        $existingUser = $php_fetch($table, ['select' => 'email', 'email' => $email]);

        if (is_array($existingUser) && isset($existingUser[0]['email'])) {
            // Email exists
            return 'exists';
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
            'agreed_to_terms' => true,
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

    public function signUpWithGoogle($php_fetch, $php_insert, $table, $email, $user_fullname, $supabase_uuid, $email_verified, $created_at, $update_at, $profile_image)
    {
        $item = array();
        $checkuuid = $php_fetch($table, ['select' => 'user_id', 'user_id' => $supabase_uuid]);
        if (is_array($checkuuid) && isset($checkuuid[0]['user_id'])) {
            // User already exists
        } else {
            // Check if user already exists
            $insert_google = $php_insert($table, [
                'email' => $email,
                'full_name' => $user_fullname,
                'user_id' => $supabase_uuid,
                'is_email_verified' => $email_verified,
                'created_at' => $created_at,
                'updated_at' => $update_at,
                'agreed_to_terms' => true,
                'profile_picture' => $profile_image

            ]);
        }
    }
}
