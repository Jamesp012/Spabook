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
    }
}
