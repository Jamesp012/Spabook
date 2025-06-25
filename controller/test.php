<?php
// Run the test
if (isset($_POST['action'])) {
    date_default_timezone_set('Asia/Manila');
    require_once '../config/connection.php';
    require_once '../model/test_model.php';
    $TestDatabase = new TestDatabase();
    $action = trim($_POST['action']);
    $current_date = date('Y-m-d');
    $timestamp = new DateTime('now');
    $current_datetimestamp = $timestamp->format('Y-m-d H:i:s');
    switch ($action) {
        case 'test_database':
            $name = 'Bandolf Conrad G. Alfuen';
            $email = 'bandolfalfuen@gmai.com';
            echo $TestDatabase->testDatabase($php_insert, 'users',$name, $email);
            break;
    }
}
