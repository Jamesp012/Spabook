<?php

class User
{
    //! ============================================================ USER SECTION ============================================================

    public function login($php_fetch, $table, $id)
    {
        // Fetch user data by ID
        $user = $php_fetch($table, 'role', ['user_id' => $id]);
        // var_dump($user); // Uncomment to debug

        if (is_array($user) && isset($user[0]['role'])) {
            return $user[0]['role'];
        } else {
            return null;
        }
    }

    public function getUserProfile($php_fetch, $table, $id)
    {
        // Fetch user profile by ID
        $user = $php_fetch($table, '*', ['user_id' => $id]);
        // var_dump($user); // Uncomment to debug

        if (is_array($user) && isset($user[0])) {
            return json_encode($user[0]);
        } else {
            return null;
        }
    }

    public function validEmailAdd($php_fetch, $table, $email)
    {
        // Check if email already exists
        $existingUser = $php_fetch($table, 'email', ['email' => $email]);

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
        $checkuuid = $php_fetch($table,  'user_id', ['user_id' => $supabase_uuid]);
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
    //! ============================================================ USER SECTION END ============================================================

    //! ============================================================ ADMIN SECTION ============================================================
    public function getUserProfileData($php_fetch, $table)
    {
        // Fetch user data by ID
        $item_data = array();
        $user_data = $php_fetch($table, 'full_name, email, role, user_id, profile_picture');
        if (!empty($user_data)) {

            // Encode the first row only
            return json_encode($user_data);
        } else {
            // No rows found; return null or an empty object
            return json_encode('pogi');
        }
    }

    public function updateUserRole($php_update, $table, $id, $role)
    {
        // Update user role
        $update_role = $php_update($table, ['role' => $role], ['user_id' => $id]);
        if (isset($update_role['error'])) {
            // Handle error
            return 'error';
        } else {
            // User signed up successfully
            return 'success';
        }
    }
    //! ============================================================ ADMIN SECTION ============================================================

}
