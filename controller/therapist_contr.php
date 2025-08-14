<?php
require_once '../model/therapist_model.php';
require_once '../config/connection.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Initialize model
$TherapistModel = new TherapistModel();

function response($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if (!$action) {
        response(['status' => 'error', 'message' => 'No POST action specified']);
    }

    switch ($action) {
        case 'get_therapists_by_service':
            $service_id = $_POST['service_id'] ?? null;
            if (!$service_id) {
                response(['status' => 'error', 'message' => 'Service ID is required']);
            }
            try {
                $result = $TherapistModel->getTherapistsByService($php_fetch, 'therapist', $service_id);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_therapists_by_service: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_all_therapists':
            try {
                $result = $TherapistModel->getAllTherapists($php_fetch, 'therapist');
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_all_therapists: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_therapist_by_id':
            $therapist_id = $_POST['therapist_id'] ?? null;
            if (!$therapist_id) {
                response(['status' => 'error', 'message' => 'Therapist ID is required']);
            }
            try {
                $therapist_result = $TherapistModel->getTherapistById($php_fetch, 'therapist', $therapist_id);
                $therapist = json_decode($therapist_result, true);
                
                if ($therapist && isset($therapist['therapistid'])) {
                    // Get assigned services for this therapist
                    $assigned_services = $TherapistModel->getTherapistServices($therapist['therapistid'], $php_fetch);
                    $therapist['assigned_services'] = $assigned_services;
                    $therapist['service_ids'] = array_column($assigned_services, 'id');
                }
                
                response($therapist);
            } catch (Exception $e) {
                error_log("Error in get_therapist_by_id: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_therapist_services':
            $therapist_id = $_POST['therapist_id'] ?? null;
            if (!$therapist_id) {
                response(['status' => 'error', 'message' => 'Therapist ID is required']);
            }
            try {
                $services = $TherapistModel->getTherapistServices($therapist_id, $php_fetch);
                response(['services' => $services, 'service_ids' => array_column($services, 'id')]);
            } catch (Exception $e) {
                error_log("Error in get_therapist_services: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'add_therapist':
            try {


                
                $therapist_name = $_POST['therapist_name'] ?? null;
                $service_ids = $_POST['service_ids'] ?? [];
                $therapist_desc = $_POST['therapist_desc'] ?? '';
                
                // Debug logging
                error_log("Extracted - Name: '$therapist_name'");
                error_log("Extracted - Service IDs type: " . gettype($service_ids));
                error_log("Extracted - Service IDs value: " . print_r($service_ids, true));
                
                // Handle both old single service_id and new multiple service_ids
                if (isset($_POST['service_id'])) {
                    $service_ids = [$_POST['service_id']];
                    error_log("Using legacy service_id: " . $_POST['service_id']);
                }
                
                // Ensure service_ids is an array and handle various formats
                if (!is_array($service_ids)) {
                    error_log("Converting non-array service_ids: '$service_ids'");
                    if (is_string($service_ids) && strpos($service_ids, ',') !== false) {
                        // Handle comma-separated string
                        $service_ids = explode(',', $service_ids);
                    } else {
                        $service_ids = [$service_ids];
                    }
                }
                
                // Remove empty values and ensure numeric
                $service_ids = array_filter(array_map('trim', $service_ids), function($id) {
                    return !empty($id) && $id !== null && $id !== '';
                });
                
                // Convert to integers
                $service_ids = array_map('intval', $service_ids);
                $service_ids = array_filter($service_ids, function($id) {
                    return $id > 0;
                });
                
                error_log("Final processed service IDs: " . print_r($service_ids, true));
                
                // Validation
                if (!$therapist_name || trim($therapist_name) === '') {
                    error_log("Validation failed - Empty therapist name");
                    response(['status' => 'error', 'message' => 'Therapist name is required']);
                    return;
                }
                
                if (empty($service_ids)) {
                    error_log("Validation failed - No valid service IDs");
                    response(['status' => 'error', 'message' => 'At least one valid service must be selected']);
                    return;
                }
                
                // Prepare data for model - ONLY fields that exist in actual schema
                // Schema: therapistid, therapist_name, service_id, therapist_desc, rate
                $data = [
                    'therapist_name' => trim($therapist_name),
                    'therapist_desc' => trim($therapist_desc),
                    'service_id' => implode(',', $service_ids), // Store multiple service IDs as comma-separated string
                    'rate' => isset($_POST['rate']) ? intval($_POST['rate']) : 0
                ];
                
                error_log("Final data for model: " . print_r($data, true));
                
                // Call model
                $result = $TherapistModel->addTherapist($php_insert, 'therapist', $data);
                $decoded_result = json_decode($result, true);
                
                error_log("Model result: " . $result);
                error_log("Decoded result: " . print_r($decoded_result, true));
                
                if ($decoded_result === null) {
                    error_log("JSON decode failed for result: " . $result);
                    response(['status' => 'error', 'message' => 'Invalid response from model']);
                    return;
                }
                
                response($decoded_result);
                
            } catch (Exception $e) {
                error_log("Exception in add_therapist: " . $e->getMessage());
                error_log("Exception trace: " . $e->getTraceAsString());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'update_therapist':
            $therapist_id = $_POST['therapist_id'] ?? null;
            $therapist_name = $_POST['therapist_name'] ?? null;
            $service_ids = $_POST['service_ids'] ?? [];
            $therapist_desc = $_POST['therapist_desc'] ?? '';
            
            // Handle both old single service_id and new multiple service_ids
            if (isset($_POST['service_id'])) {
                $service_ids = [$_POST['service_id']];
            }
            
            // Ensure service_ids is an array and handle various formats
            if (!is_array($service_ids)) {
                if (is_string($service_ids) && strpos($service_ids, ',') !== false) {
                    $service_ids = explode(',', $service_ids);
                } else {
                    $service_ids = [$service_ids];
                }
            }
            
            // Clean service IDs
            $service_ids = array_filter(array_map('trim', array_map('intval', $service_ids)), function($id) {
                return $id > 0;
            });
            
            if (!$therapist_id || !$therapist_name) {
                response(['status' => 'error', 'message' => 'Therapist ID and name are required']);
            }
            
            if (empty($service_ids)) {
                response(['status' => 'error', 'message' => 'At least one service must be selected']);
            }
            
            try {
                // Update data - ONLY fields that exist in actual schema
                // Schema: therapistid, therapist_name, service_id, therapist_desc, rate
                $data = [
                    'therapist_name' => $therapist_name,
                    'therapist_desc' => $therapist_desc,
                    'service_id' => implode(',', $service_ids), // Store multiple service IDs as comma-separated string
                    'rate' => isset($_POST['rate']) ? intval($_POST['rate']) : 0
                ];
                $result = $TherapistModel->updateTherapist($php_update, 'therapist', $therapist_id, $data);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in update_therapist: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'delete_therapist':
            $therapist_id = $_POST['therapist_id'] ?? null;
            if (!$therapist_id) {
                response(['status' => 'error', 'message' => 'Therapist ID is required']);
            }
            try {
                $result = $TherapistModel->deleteTherapist($php_update, 'therapist', $therapist_id);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in delete_therapist: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_all_therapists_admin':
            try {
                $result = $TherapistModel->getAllTherapistsWithServices($php_fetch, 'therapist', 'services');
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_all_therapists_admin: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_therapist_stats':
            try {
                $result = $TherapistModel->getTherapistStats($php_fetch, 'therapist', 'services');
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_therapist_stats: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'get_available_therapists':
            $service_id = $_POST['service_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;
            
            if (!$service_id || !$date || !$time) {
                response(['status' => 'error', 'message' => 'Service ID, date, and time are required']);
            }
            
            try {
                $result = $TherapistModel->getAvailableTherapists($php_fetch, $service_id, $date, $time);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_available_therapists: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'update_therapist_status':
            $therapist_id = $_POST['therapist_id'] ?? null;
            $active = $_POST['active'] ?? null;
            
            if (!$therapist_id || $active === null) {
                response(['status' => 'error', 'message' => 'Therapist ID and active status are required']);
            }
            
            $activeStatus = ($active === 'true' || $active === '1' || $active === 1) ? 1 : 0;
            
            try {
                // Get current therapist data
                $therapist = $php_fetch('therapist', '*', ['therapistid' => $therapist_id]);
                if (!$therapist || count($therapist) === 0) {
                    response(['status' => 'error', 'message' => 'Therapist not found']);
                }
                
                $therapistData = $therapist[0];
                $therapistName = $therapistData['therapist_name'] ?? 'Unknown';
                $currentDesc = $therapistData['therapist_desc'] ?? '';
                
                error_log("=== THERAPIST STATUS UPDATE DEBUG ===");
                error_log("Therapist ID: $therapist_id");
                error_log("New Active Status: $activeStatus");
                error_log("Current Description: '$currentDesc'");
                
                // Update the description to indicate status
                $statusNote = $activeStatus ? '' : ' [INACTIVE]';
                
                // Remove any existing status markers first
                $cleanDesc = preg_replace('/ \[INACTIVE\]$/', '', $currentDesc);
                $cleanDesc = trim($cleanDesc);
                
                // Add new status marker if needed
                $newDesc = $cleanDesc . $statusNote;
                
                error_log("Clean Description: '$cleanDesc'");
                error_log("New Description: '$newDesc'");
                
                // Update the database
                $updateData = ['therapist_desc' => $newDesc];
                $updated = $php_update('therapist', $updateData, ['therapistid' => $therapist_id]);
                
                error_log("Update Data: " . print_r($updateData, true));
                error_log("Update Result: " . print_r($updated, true));
                
                // Check if update was successful
                if ($updated !== null && (!isset($updated['error']))) {
                    response([
                        'status' => 'success',
                        'message' => "Therapist '{$therapistName}' " . ($activeStatus ? 'activated' : 'deactivated') . " successfully",
                        'debug_info' => [
                            'old_desc' => $currentDesc,
                            'new_desc' => $newDesc,
                            'update_result' => $updated
                        ]
                    ]);
                } else {
                    error_log("Update failed: " . print_r($updated, true));
                    response([
                        'status' => 'error', 
                        'message' => 'Failed to update therapist status in database',
                        'debug_info' => [
                            'update_result' => $updated
                        ]
                    ]);
                }
                
            } catch (Exception $e) {
                error_log("Error in update_therapist_status: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        case 'test_connection':
            response(['status' => 'success', 'message' => 'Controller is working!', 'timestamp' => date('Y-m-d H:i:s')]);
            break;

        case 'test_services':
            try {
                error_log("=== TESTING SERVICES FETCH ===");
                
                // First get available services
                $services = $php_fetch('booking_services', '*');
                error_log("Available services: " . print_r($services, true));
                
                response([
                    'status' => 'success',
                    'message' => 'Services fetched',
                    'services' => $services
                ]);
                
            } catch (Exception $e) {
                error_log("Test services exception: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Test failed: ' . $e->getMessage()]);
            }
            break;

        case 'check_therapist_schema':
            try {
                error_log("=== CHECKING THERAPIST TABLE SCHEMA ===");
                
                // Try to get existing therapists to see what columns exist
                $existing_therapists = $php_fetch('therapist', '*', ['limit' => 1]);
                
                response([
                    'status' => 'success',
                    'message' => 'Schema check completed',
                    'existing_therapists' => $existing_therapists,
                    'note' => 'Check the columns available in existing records'
                ]);
                
            } catch (Exception $e) {
                error_log("Schema check exception: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Schema check failed: ' . $e->getMessage()]);
            }
            break;

        case 'test_add_manual':
            try {
                error_log("=== TESTING MANUAL ADD - SCHEMA COMPATIBLE ===");
                
                $service_id = $_POST['service_id'] ?? 1; // Default to ID 1
                $therapist_name = $_POST['therapist_name'] ?? 'Schema Test Therapist ' . date('His');
                
                error_log("Using service ID: " . $service_id);
                error_log("Using therapist name: " . $therapist_name);
                error_log("Database schema: therapistid, therapist_name, service_id, therapist_desc");
                
                // Test with EXACT schema fields only
                $test_data = [
                    'therapist_name' => $therapist_name,
                    'therapist_desc' => 'Test description - schema compatible',
                    'service_id' => intval($service_id) // Ensure integer for service_id
                ];
                
                error_log("Schema-compatible test data: " . print_r($test_data, true));
                
                $result = $TherapistModel->addTherapist($php_insert, 'therapist', $test_data);
                $decoded = json_decode($result, true);
                
                error_log("Schema test result: " . $result);
                
                response([
                    'status' => $decoded['status'] ?? 'unknown',
                    'message' => 'Schema-compatible test completed',
                    'result' => $decoded,
                    'raw_result' => $result,
                    'test_data_sent' => $test_data,
                    'schema_note' => 'Using exact database schema: therapistid, therapist_name, service_id, therapist_desc'
                ]);
                
            } catch (Exception $e) {
                error_log("Schema test exception: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Schema test failed: ' . $e->getMessage()]);
            }
            break;

        case 'test_add_simple':
            try {
                error_log("=== TESTING SIMPLE ADD ===");
                
                // Try multiple service table names and queries
                $service_id = null;
                $services_info = [];
                
                // Try different approaches to get services
                $attempts = [
                    ['table' => 'booking_services', 'select' => '*'],
                    ['table' => 'booking_services', 'select' => 'id'],
                    ['table' => 'services', 'select' => '*'],
                    ['table' => 'service', 'select' => '*']
                ];
                
                foreach ($attempts as $attempt) {
                    try {
                        error_log("Trying table: " . $attempt['table'] . " with select: " . $attempt['select']);
                        $services = $php_fetch($attempt['table'], $attempt['select']);
                        error_log("Result: " . print_r($services, true));
                        
                        $services_info[] = [
                            'table' => $attempt['table'],
                            'select' => $attempt['select'],
                            'result' => $services,
                            'has_error' => isset($services['error']),
                            'is_empty' => empty($services),
                            'count' => is_array($services) ? count($services) : 0
                        ];
                        
                        if ($services && !isset($services['error']) && !empty($services)) {
                            // Try to extract service ID from various possible field names
                            if (isset($services[0])) {
                                $service_id = $services[0]['id'] ?? $services[0]['service_id'] ?? $services[0]['serviceid'] ?? null;
                            } else {
                                $service_id = $services['id'] ?? $services['service_id'] ?? $services['serviceid'] ?? null;
                            }
                            
                            if ($service_id) {
                                error_log("Found service ID: " . $service_id . " from table: " . $attempt['table']);
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error with table " . $attempt['table'] . ": " . $e->getMessage());
                        $services_info[] = [
                            'table' => $attempt['table'],
                            'select' => $attempt['select'],
                            'error' => $e->getMessage()
                        ];
                    }
                }
                
                if (!$service_id) {
                    // Try to create a test service for testing
                    error_log("No existing services found, attempting to create test service");
                    
                    try {
                        $test_service_data = [
                            'service_name' => 'Test Service for Therapist Testing',
                            'description' => 'Temporary service created for testing therapist functionality',
                            'price' => 100,
                            'per_minute' => 60,
                            'service_picture' => null
                        ];
                        
                        $service_result = $php_insert('booking_services', $test_service_data);
                        error_log("Test service creation result: " . print_r($service_result, true));
                        
                        if ($service_result && !isset($service_result['error'])) {
                            $service_id = $service_result[0]['id'] ?? $service_result['id'] ?? null;
                            if ($service_id) {
                                error_log("Created test service with ID: " . $service_id);
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Failed to create test service: " . $e->getMessage());
                    }
                }
                
                if (!$service_id) {
                    response([
                        'status' => 'error', 
                        'message' => 'No services available for testing and failed to create test service',
                        'debug_info' => $services_info,
                        'note' => 'Tried multiple table names, queries, and service creation',
                        'suggestion' => 'Please create at least one service in Manage Services first'
                    ]);
                    return;
                }
                
                // Test with minimal data - ONLY schema fields
                // Schema: therapistid, therapist_name, service_id, therapist_desc
                $test_data = [
                    'therapist_name' => 'Test Therapist ' . date('His'),
                    'therapist_desc' => 'Test description',
                    'service_id' => $service_id // Use single service_id (not array)
                ];
                
                error_log("Test data: " . print_r($test_data, true));
                
                $result = $TherapistModel->addTherapist($php_insert, 'therapist', $test_data);
                $decoded = json_decode($result, true);
                
                error_log("Test result: " . $result);
                
                response([
                    'status' => 'success', 
                    'message' => 'Test add completed',
                    'result' => $decoded,
                    'raw_result' => $result,
                    'used_service_id' => $service_id,
                    'services_debug' => $services_info
                ]);
                
            } catch (Exception $e) {
                error_log("Test add exception: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Test failed: ' . $e->getMessage()]);
            }
            break;

        default:
            response(['status' => 'error', 'message' => 'Unknown POST action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if (!$action) {
        response(['status' => 'error', 'message' => 'No GET action specified']);
    }

    switch ($action) {
        case 'get_therapists_by_service':
            $service_id = $_GET['service_id'] ?? null;
            if (!$service_id) {
                response(['status' => 'error', 'message' => 'Service ID is required']);
            }
            try {
                $result = $TherapistModel->getTherapistsByService($php_fetch, 'therapist', $service_id);
                response(json_decode($result, true));
            } catch (Exception $e) {
                error_log("Error in get_therapists_by_service: " . $e->getMessage());
                response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;

        default:
            response(['status' => 'error', 'message' => 'Unknown GET action']);
    }
}
?>