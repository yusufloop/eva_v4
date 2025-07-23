<?php
/**
 * Device Management Actions
 * Handles device view, edit, delete operations
 */

session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';
require_once '../../helpers/device_helpers.php';

// Check authentication
requireAuth();

$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? 'view';
$serialNo = $_GET['serial_no'] ?? $_POST['serial_no'] ?? '';

switch ($action) {
    case 'view':
        handleDeviceView($serialNo, $userId, $isAdmin);
        break;
    
    case 'edit':
        handleDeviceEdit($serialNo, $userId, $isAdmin);
        break;
    
    case 'update':
        handleDeviceUpdate($serialNo, $userId, $isAdmin);
        break;
    
    case 'delete':
        handleDeviceDelete($serialNo, $userId, $isAdmin);
        break;
    
    case 'get_details':
        handleGetDeviceDetails($serialNo, $userId, $isAdmin);
        break;
    
    default:
        $_SESSION['error_message'] = 'Invalid action';
        header('Location: ../../pages/dashboard.php');
        exit;
}

/**
 * Handle device view
 */
function handleDeviceView($serialNo, $userId, $isAdmin) {
    if (empty($serialNo)) {
        $_SESSION['error_message'] = 'Serial number is required';
        header('Location: ../../pages/dashboard.php');
        exit;
    }
    
    // Redirect to device view page
    header("Location: /device/view/" . urlencode($serialNo));
    exit;
}

/**
 * Handle device edit
 */
function handleDeviceEdit($serialNo, $userId, $isAdmin) {
    if (empty($serialNo)) {
        $_SESSION['error_message'] = 'Serial number is required';
        header('Location: ../../pages/dashboard.php');
        exit;
    }
    
    // Redirect to device edit page
    header("Location: /device/edit/" . urlencode($serialNo));
    exit;
}

/**
 * Handle device update
 */
function handleDeviceUpdate($serialNo, $userId, $isAdmin) {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = 'Invalid request method';
        header('Location: ../../pages/dashboard.php');
        exit;
    }
    
    try {
        // Validate required fields
        $emergencyNo1 = $_POST['family_contact1'] ?? '';
        $emergencyNo2 = $_POST['family_contact2'] ?? '';
        $dependentId = $_POST['dep_id'] ?? '';
        $assignedUserId = $isAdmin && !empty($_POST['user_id']) ? $_POST['user_id'] : $userId;
        
        if (empty($emergencyNo1) || empty($emergencyNo2) || empty($dependentId)) {
            $_SESSION['error_message'] = 'All fields are required';
            header("Location: /device/edit/" . urlencode($serialNo));
            exit;
        }
        
        // Get device eva_id
        $sql = "SELECT e.eva_id FROM eva_info e 
                JOIN inventory i ON e.inventory_id = i.inventory_id 
                WHERE i.serial_no = ?";
        
        if (!$isAdmin) {
            $sql .= " AND e.user_id = ?";
            $params = [$serialNo, $userId];
        } else {
            $params = [$serialNo];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $device = $stmt->fetch();
        
        if (!$device) {
            $_SESSION['error_message'] = 'Device not found or access denied';
            header("Location: /device/edit/" . urlencode($serialNo));
            exit;
        }
        
        // Update device
        $updateSql = "UPDATE eva_info SET 
                        family_contact1 = ?, 
                        family_contact2 = ?, 
                        dep_id = ?, 
                        user_id = ? 
                      WHERE eva_id = ?";
        
        $updateStmt = $pdo->prepare($updateSql);
        $result = $updateStmt->execute([
            $emergencyNo1, 
            $emergencyNo2, 
            $dependentId, 
            $assignedUserId, 
            $device['eva_id']
        ]);
        
        if ($result) {
            $_SESSION['success_message'] = 'Device updated successfully';
            header("Location: /device/view/" . urlencode($serialNo));
        } else {
            $_SESSION['error_message'] = 'Failed to update device';
            header("Location: /device/edit/" . urlencode($serialNo));
        }
        
    } catch (Exception $e) {
        error_log("Device update error: " . $e->getMessage());
        $_SESSION['error_message'] = 'An error occurred while updating the device';
        header("Location: /device/edit/" . urlencode($serialNo));
    }
    
    exit;
}

/**
 * Handle device delete
 */
function handleDeviceDelete($serialNo, $userId, $isAdmin) {
    global $pdo;
    header('Content-Type: application/json');
    
    if (empty($serialNo)) {
        echo json_encode(['success' => false, 'message' => 'Serial number is required']);
        exit;
    }
    
    try {
        // Get device info
        $sql = "SELECT e.eva_id, e.inventory_id FROM eva_info e 
                JOIN inventory i ON e.inventory_id = i.inventory_id 
                WHERE i.serial_no = ?";
        
        if (!$isAdmin) {
            $sql .= " AND e.user_id = ?";
            $params = [$serialNo, $userId];
        } else {
            $params = [$serialNo];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $device = $stmt->fetch();
        
        if (!$device) {
            echo json_encode(['success' => false, 'message' => 'Device not found or access denied']);
            exit;
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Delete call histories first (foreign key constraint)
            $deleteCallsSql = "DELETE FROM call_histories WHERE eva_id = ?";
            $deleteCallsStmt = $pdo->prepare($deleteCallsSql);
            $deleteCallsStmt->execute([$device['eva_id']]);
            
            // Delete device from eva_info
            $deleteDeviceSql = "DELETE FROM eva_info WHERE eva_id = ?";
            $deleteDeviceStmt = $pdo->prepare($deleteDeviceSql);
            $deleteDeviceResult = $deleteDeviceStmt->execute([$device['eva_id']]);
            
            // Update inventory to mark as not registered
            $updateInventorySql = "UPDATE inventory SET is_registered = 0 WHERE inventory_id = ?";
            $updateInventoryStmt = $pdo->prepare($updateInventorySql);
            $updateInventoryResult = $updateInventoryStmt->execute([$device['inventory_id']]);
            
            if ($deleteDeviceResult && $updateInventoryResult) {
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Device deleted successfully']);
            } else {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete device']);
            }
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Device delete error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the device']);
    }
    
    exit;
}

/**
 * Handle get device details (AJAX)
 */
function handleGetDeviceDetails($serialNo, $userId, $isAdmin) {
    global $pdo;
    header('Content-Type: application/json');
    
    if (empty($serialNo)) {
        echo json_encode(['error' => 'Serial number is required']);
        exit;
    }
    
    try {
        $sql = "SELECT 
                    e.eva_id,
                    e.telemed_contact,
                    e.family_contact1,
                    e.family_contact2,
                    e.contact1_rel,
                    e.contact2_rel,
                    e.reg_date,
                    e.lastseen,
                    i.serial_no,
                    i.device_type,
                    i.is_registered,
                    d.fullname,
                    d.address,
                    d.med_condition,
                    d.sex,
                    d.dob,
                    u.useremail
                FROM eva_info e
                JOIN inventory i ON e.inventory_id = i.inventory_id
                JOIN dependants d ON e.dep_id = d.dep_id
                JOIN users u ON e.user_id = u.user_id
                WHERE i.serial_no = ?";
        
        if (!$isAdmin) {
            $sql .= " AND e.user_id = ?";
            $params = [$serialNo, $userId];
        } else {
            $params = [$serialNo];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            // Add additional computed fields
            $device['status'] = $device['lastseen'] && 
                              (time() - strtotime($device['lastseen'])) < 300 ? 'online' : 'offline';
            $device['device_status'] = $device['is_registered'] ? 'Active' : 'Inactive';
            
            echo json_encode($device);
        } else {
            echo json_encode(['error' => 'Device not found or access denied']);
        }
        
    } catch (Exception $e) {
        error_log("Get device details error: " . $e->getMessage());
        echo json_encode(['error' => 'An error occurred while fetching device details']);
    }
    
    exit;
}
?>