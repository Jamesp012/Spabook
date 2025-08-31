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
            foreach ($user as $row) {
                // Return the role of the user
                return json_encode($row['role']);
            }
        } else {
            return null;
        }
    }

    public function therapistLogin($php_fetch, $php_update, $table, $email, $password)
    {
        try {
            // Since multi-condition queries fail, get all therapists first then filter by email in PHP
            $therapists = $php_fetch($table, '*', ['role' => 'Therapist']);
            
            // Check if query was successful
            if (isset($therapists['error'])) {
                return json_encode(['error' => 'Database error occurred']);
            }
            
            // Find therapist by email
            $user_data = null;
            if (is_array($therapists)) {
                foreach ($therapists as $therapist) {
                    if (isset($therapist['email']) && $therapist['email'] === $email) {
                        $user_data = $therapist;
                        break;
                    }
                }
            }
            
            if ($user_data) {
                // Check password (using default for now)
                $default_password = 'therapist123';
                
                if ($password === $default_password) {
                    // Start session and set therapist data
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['therapist_id'] = $user_data['user_id'];
                    $_SESSION['therapist_name'] = $user_data['full_name'];
                    $_SESSION['therapist_email'] = $user_data['email'];
                    
                    // Update last login time
                    try {
                        $php_update($table, ['updated_at' => date('c')], ['user_id' => $user_data['user_id']]);
                    } catch (Exception $e) {
                        // Log but don't fail login for this
                        error_log('Failed to update therapist login time: ' . $e->getMessage());
                    }
                    
                    return json_encode('Therapist');
                } else {
                    return json_encode(['error' => 'Invalid email or password']);
                }
            } else {
                return json_encode(['error' => 'Invalid email or password']);
            }
        } catch (Exception $e) {
            error_log('Therapist login error: ' . $e->getMessage());
            return json_encode(['error' => 'Login system error occurred']);
        }
    }

    public function getUserProfile($php_fetch, $table, $id)
    {
        // Fetch user profile using the provided fetch function
        $user = $php_fetch($table, '*', ['user_id' => $id]);

        if (is_array($user) && isset($user[0])) {
            return json_encode($user[0]);
        } else {
            return json_encode(['error' => 'User not found']);
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

    public function updateUserProfile($php_update, $table, $id, $contact, $address)
    {
        $data = [
            'contact_number' => $contact,
            'user_address' => $address
        ];
        $where = ['user_id' => $id];

        return $php_update($table, $data, $where) ? 'success' : 'fail';
    }

    public function updateUserRole($php_update, $table, $user_id, $new_role)
    {
        try {
            $result = $php_update($table, ['role' => $new_role], ['user_id' => $user_id]);
            if ($result) {
                return json_encode(['status' => 'success', 'message' => 'User role updated successfully']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Failed to update user role']);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function fetchUnifiedUsers($php_fetch, $table)
    {
        try {
            $users = $php_fetch($table, '*');
            
            if (is_array($users) && count($users) > 0) {
                // For each user, if they're a therapist, get their services info
                foreach ($users as &$user) {
                    if ($user['role'] === 'Therapist') {
                        // This would be enhanced later to get actual therapist services
                        $user['therapist_services'] = 'Massage, Spa Treatments';
                        $user['is_active'] = true; // Default active for now
                    } else {
                        $user['is_active'] = true; // All regular users are active by default
                    }
                }
                return json_encode($users);
            } else {
                return json_encode([]);
            }
        } catch (Exception $e) {
            error_log('Error in fetchUnifiedUsers: ' . $e->getMessage());
            return json_encode([]);
        }
    }

    public function deleteUser($php_update, $table, $user_id)
    {
        try {
            // Soft delete by setting role to 'deleted' or actually delete
            // For now, let's actually delete the user
            $result = $php_update($table, ['role' => 'deleted'], ['user_id' => $user_id]);
            
            if ($result) {
                return json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function addTherapistUser($php_insert, $table, $first_name, $last_name, $email, $password, $contact, $address, $specialties, $experience, $certification, $bio, $is_active)
    {
        try {
            // Generate a UUID for the user (you might want to use a proper UUID generator)
            $user_id = uniqid('therapist_', true);
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Create full name
            $full_name = trim($first_name . ' ' . $last_name);
            
            // Prepare user data for database insertion
            $userData = [
                'user_id' => $user_id,
                'full_name' => $full_name,
                'email' => $email,
                'role' => 'Therapist',
                'contact_number' => $contact,
                'address' => $address,
                'agreed_to_terms' => true,
                'is_email_verified' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                // Note: we'll store therapist-specific info in a JSON field or separate table later
            ];

            $result = $php_insert($table, $userData);
            
            if (isset($result['error'])) {
                // Check for duplicate email
                if (strpos($result['error'], 'duplicate') !== false || strpos($result['error'], 'unique') !== false) {
                    return json_encode(['status' => 'error', 'message' => 'Email address already exists']);
                }
                return json_encode(['status' => 'error', 'message' => 'Failed to create therapist account: ' . $result['error']]);
            }
            
            return json_encode([
                'status' => 'success', 
                'message' => 'Therapist account created successfully',
                'user_id' => $user_id
            ]);
            
        } catch (Exception $e) {
            error_log('Error in addTherapistUser: ' . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Database error occurred']);
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
            foreach ($user_data as $row) {
                $item_data[] = array(
                    'full_name' => $row['full_name'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'user_id' => $row['user_id'],
                    'profile_picture' => $row['profile_picture']
                );
            }

            // Encode the first row only
            return json_encode($item_data);
        } else {
            // No rows found; return null or an empty object
            return json_encode('pogi');
        }
    }


    //! ============================================================ ADMIN SECTION ============================================================

}
