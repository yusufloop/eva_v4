<?php
/**
 * Dashboard Actions Index
 * Main entry point for dashboard-related actions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/stats.php';
require_once __DIR__ . '/devices.php';
require_once __DIR__ . '/activities.php';

// Check authentication
requireAuth();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = getCurrentUserId();
    
    switch ($action) {
        case 'get_stats':
            $isAdmin = hasRole('admin');
            if ($isAdmin) {
                $stats = getAdminDashboardStats();
            } else {
                $stats = getUserDashboardStats($userId);
            }
            echo json_encode($stats);
            break;
            
        case 'get_devices':
            $isAdmin = hasRole('admin');
            if ($isAdmin) {
                $devices = getAllDevicesWithStatus();
            } else {
                $devices = getUserDevicesWithStatus($userId);
            }
            echo json_encode($devices);
            break;
            
        case 'get_activities':
            $isAdmin = hasRole('admin');
            $limit = $_POST['limit'] ?? 10;
            
            if ($isAdmin) {
                $activities = getRecentSystemActivities($limit);
            } else {
                $activities = getUserRecentActivities($userId, $limit);
            }
            echo json_encode($activities);
            break;
            
        case 'add_device':
            $deviceData = [
                'user_id' => $_POST['user_id'] ?? $userId,
                'emergency_no1' => $_POST['emergency_no1'] ?? '',
                'emergency_no2' => $_POST['emergency_no2'] ?? '',
                'serial_no' => $_POST['serial_no'] ?? '',
                'existing_dependent' => $_POST['existing_dependent'] ?? '',
                'firstname' => $_POST['firstname'] ?? '',
                'lastname' => $_POST['lastname'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'dob' => $_POST['dob'] ?? '',
                'address' => $_POST['address'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'medical_condition' => $_POST['medical_condition'] ?? ''
            ];
            
            $result = addDevice($deviceData);
            echo json_encode($result);
            break;
            
        case 'delete_device':
            $serialNo = $_POST['serial_no'] ?? '';
            $result = deleteDevice($serialNo, $userId);
            echo json_encode($result);
            break;
            
        case 'get_device':
            $serialNo = $_POST['serial_no'] ?? '';
            $device = getDeviceBySerialNo($serialNo, hasRole('admin') ? null : $userId);
            echo json_encode($device ?: ['error' => 'Device not found']);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $userId = getCurrentUserId();
    
    switch ($action) {
        case 'device_details':
            $serialNo = $_GET['serial_no'] ?? '';
            $device = getDeviceBySerialNo($serialNo, hasRole('admin') ? null : $userId);
            
            if ($device) {
                echo json_encode($device);
            } else {
                echo json_encode(['error' => 'Device not found']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit;
}

// If no valid request method, return error
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>