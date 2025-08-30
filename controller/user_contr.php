<?php
// Ensure session is started for all user operations
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Run the test
if (isset($_POST['action'])) {
    date_default_timezone_set('Asia/Manila');
    require_once '../config/connection.php';
    require_once '../model/user_model.php';
    $User = new User();
    $action = trim($_POST['action']);
    $current_date = date('Y-m-d');
    $timestamp = new DateTime('now');
    $current_datetimestamp = $timestamp->format('Y-m-d H:i:s');
    switch ($action) {

        case 'login':
            $id = trim($_POST['id']);
            echo $User->login($php_fetch, 'users', $id);
            break;

        case 'therapist_login':
            echo json_encode(['error' => 'Endpoint removed']);
            break;

        case 'get_current_user':
            // Get current user ID from session
            if (isset($_SESSION['user_id'])) {
                echo json_encode(['status' => 'success', 'user_id' => $_SESSION['user_id']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No active session']);
            }
            break;

        case 'update_user_role':
            $user_id = trim($_POST['id']);
            $new_role = trim($_POST['role']);
            echo $User->updateUserRole($php_update, 'users', $user_id, $new_role);
            break;

        case 'fetch_unified_users':
            echo $User->fetchUnifiedUsers($php_fetch, 'users');
            break;

        case 'delete_user':
            $user_id = trim($_POST['user_id']);
            echo $User->deleteUser($php_update, 'users', $user_id);
            break;

        case 'add_therapist_user':
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $contact = trim($_POST['contact_number'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $specialties = trim($_POST['specialties'] ?? '');
            $experience = intval($_POST['experience'] ?? 0);
            $certification = trim($_POST['certification'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $is_active = isset($_POST['is_active']);

            echo $User->addTherapistUser(
                $php_insert, 'users', $first_name, $last_name, $email, $password,
                $contact, $address, $specialties, $experience, $certification, $bio, $is_active
            );
            break;

        case 'get_user_profile':
            $id = trim($_POST['id']);
            echo $User->getUserProfile($php_fetch, 'users', $id);
            break;
        case 'check_email_exists':
            $email = trim($_POST['email']);
            echo $User->validEmailAdd($php_fetch, 'users', $email);
            break;

        case 'sign_up_user':
            $email = trim($_POST['email']);
            $user_fullname = trim($_POST['user_fullname']);
            $user_address = trim($_POST['user_address']);
            $contact = trim($_POST['contact']);
            $supabase_uuid = trim($_POST['supabase_uuid']);
            // echo ($email . ' | ' . $user_fullname . ' | ' . $user_address . ' | ' . $contact . ' | ' . $supabase_uuid);
            echo $User->signUpUser($php_insert, 'users', $email, $user_fullname, $user_address, $contact, $supabase_uuid);
            break;

        case 'save_google_login':
            $email = trim($_POST['email']);
            $user_fullname = trim($_POST['user_fullname']);
            $supabase_uuid = trim($_POST['supabase_uuid']);
            $email_verified = trim($_POST['email_verified']);
            $created_at_raw = trim($_POST['created_at']);
            $date_create = new DateTime($created_at_raw);
            $created_at = $date_create->format('Y-m-d H:i:s');
            $update_at_raw = trim($_POST['update_at']);
            $date_update = new DateTime($update_at_raw);
            $update_at = $date_update->format('Y-m-d H:i:s');
            $base64image = trim($_POST['profile_image']);
            $imageupload = uploadProfileImage($base64image, $supabase_uuid, 'profile_images');
            echo $User->signUpWithGoogle($php_fetch, $php_insert, 'users', $email, $user_fullname, $supabase_uuid, $email_verified, $created_at, $update_at, $imageupload);
            break;


        case 'fetch_user_data':
            echo $user_data = $User->getUserProfileData($php_fetch, 'users');
            break;

        case 'update_role':
            $id = trim($_POST['id']);
            $role = trim($_POST['role']);
            echo $User->updateUserRole($php_update, 'users', $id, $role);
            break;
        case 'upload_receipt':
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $tmpName = $_FILES['receipt']['tmp_name'];
                $originalName = basename($_FILES['receipt']['name']);
                $fileName = uniqid('receipt_', true) . '_' . $originalName;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    // ✅ Optional: Save file path to DB with User info
                    echo json_encode([
                        'status' => 'success',
                        'filename' => $fileName,
                        'filepath' => $targetFile
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid file upload.']);
            }
            break;

    }
}
