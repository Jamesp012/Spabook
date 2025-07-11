<?php
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
            $profile_image = trim($_POST['profile_image']);
            // echo ($email . ' | ' . $user_fullname . ' | ' . $supabase_uuid . ' | ' . $email_verified . ' | ' . $created_at . ' | ' . $update_at . ' | ' . $profile);
            echo $User->signUpWithGoogle($php_fetch, $php_insert, 'users', $email, $user_fullname, $supabase_uuid, $email_verified, $created_at, $update_at, $profile_image);
            break;
    }
}
