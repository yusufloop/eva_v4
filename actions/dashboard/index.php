<?php
/**
 * Dashboard Actions Index
 * Main entry point for dashboard-related actions
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/stats.php';
require_once __DIR__ . '/activities.php';

// Check authentication
requireAuth();

// Get current user ID and role
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_stats':
            if ($isAdmin) {
                $stats = getAdminDashboardData();
            } else {
                $stats = getUserDashboardData($userId);
            }
            echo json_encode($stats);
            break;
            
        case 'get_activities':
            $limit = $_POST['limit'] ?? 10;
            
            if ($isAdmin) {
                $activities = getRecentSystemActivities($limit);
            } else {
                $activities = getUserRecentActivities($userId, $limit);
            }
            echo json_encode($activities);
            break;
            
        case 'add_device':
            handleAddDevice($userId, $isAdmin);
            break;
            
        case 'add_user':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            handleAddUser();
            break;
            
        case 'delete_device':
            handleDeleteDevice($_POST['serial_no'] ?? '', $userId, $isAdmin);
            break;
            
        case 'device_details':
            if (isset($_GET['serial_no'])) {
                handleGetDeviceDetails($_GET['serial_no'], $userId, $isAdmin);
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

/**
 * Handle add device request
 */
function handleAddDevice($userId, $isAdmin) {
    global $pdo;
    
    try {
        // Validate required fields
        $required_fields = ['serial_no', 'emergency_no1', 'emergency_no2', 'dependentId'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                return;
            }
        }
        
        $serialNo = $_POST['serial_no'];
        $emergencyNo1 = $_POST['emergency_no1'];
        $emergencyNo2 = $_POST['emergency_no2'];
        $dependentId = $_POST['dependentId'];
        $assignedUserId = $isAdmin && !empty($_POST['userId']) ? $_POST['userId'] : $userId;
        
        // Check if device serial number exists in inventory and is not registered
        $inventoryCheckSql = "SELECT inventory_id FROM inventory WHERE serial_no = ? AND is_registered = 0";
        $inventoryStmt = $pdo->prepare($inventoryCheckSql);
        $inventoryStmt->execute([$serialNo]);
        $inventory = $inventoryStmt->fetch();
        
        if (!$inventory) {
            echo json_encode(['success' => false, 'message' => 'Device serial number not found in inventory or already registered']);
            return;
        }
        
        // Verify dependent belongs to user (if not admin)
        if (!$isAdmin) {
            $depCheckSql = "SELECT COUNT(*) FROM dependants WHERE dep_id = ? AND user_id = ?";
            $depCheckStmt = $pdo->prepare($depCheckSql);
            $depCheckStmt->execute([$dependentId, $userId]);
            
            if ($depCheckStmt->fetchColumn() == 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid dependent selection']);
                return;
            }
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Insert device into eva_info
            $sql = "INSERT INTO eva_info (telemed_contact, family_contact1, contact1_rel, family_contact2, contact2_rel, 
                                        reg_date, dep_id, user_id, inventory_id) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                '', $emergencyNo1, 'Emergency Contact', $emergencyNo2, 'Emergency Contact',
                $dependentId, $assignedUserId, $inventory['inventory_id']
            ]);
            
            if (!$result) {
                throw new Exception('Failed to insert device info');
            }
            
            // Update inventory to mark as registered
            $updateInventorySql = "UPDATE inventory SET is_registered = 1, reg_date = NOW() WHERE inventory_id = ?";
            $updateStmt = $pdo->prepare($updateInventorySql);
            $updateResult = $updateStmt->execute([$inventory['inventory_id']]);
            
            if (!$updateResult) {
                throw new Exception('Failed to update inventory');
            }
            
            // Commit transaction
            $pdo->commit();
            $result = true;
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollback();
            throw $e;
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Device added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add device']);
        }
        
    } catch (Exception $e) {
        error_log("Add device error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while adding the device']);
    }
}

/**
 * Handle add user request (admin only)
 */
function handleAddUser() {
    global $pdo;
    
    try {
        // Validate required fields
        $required_fields = ['email', 'password'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                return;
            }
        }
        
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }
        
        // Password validation
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        // Check if user already exists
        $checkSql = "SELECT COUNT(*) FROM users WHERE useremail = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'User with this email already exists']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO users (useremail, password, is_admin, is_verified) 
                VALUES (?, ?, ?, 1)";
        
        $isAdminFlag = ($role === 'admin') ? 1 : 0;
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$email, $hashedPassword, $isAdminFlag]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
        }
        
    } catch (Exception $e) {
        error_log("Add user error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while creating the user']);
    }
}

/**
 * Handle delete device request
 */
function handleDeleteDevice($serialNo, $userId, $isAdmin) {
    global $pdo;
    
    try {
        if (empty($serialNo)) {
            echo json_encode(['success' => false, 'message' => 'Device serial number is required']);
            return;
        }
        
        // Check if device exists and user has permission to delete it
        if ($isAdmin) {
            $checkSql = "SELECT e.eva_id, e.inventory_id FROM eva_info e 
                        JOIN inventory i ON e.inventory_id = i.inventory_id 
                        WHERE i.serial_no = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$serialNo]);
        } else {
            $checkSql = "SELECT e.eva_id, e.inventory_id FROM eva_info e 
                        JOIN inventory i ON e.inventory_id = i.inventory_id 
                        WHERE i.serial_no = ? AND e.user_id = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$serialNo, $userId]);
        }
        
        $device = $checkStmt->fetch();
        if (!$device) {
            echo json_encode(['success' => false, 'message' => 'Device not found or access denied']);
            return;
        }
        
        // Start transaction to delete device and update inventory
        $pdo->beginTransaction();
        
        try {
            // Delete from eva_info
            $sql = "DELETE FROM eva_info WHERE eva_id = ?";
            $stmt = $pdo->prepare($sql);
            $result1 = $stmt->execute([$device['eva_id']]);
            
            // Update inventory to mark as not registered
            $updateSql = "UPDATE inventory SET is_registered = 0 WHERE inventory_id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $result2 = $updateStmt->execute([$device['inventory_id']]);
            
            if ($result1 && $result2) {
                $pdo->commit();
                $result = true;
            } else {
                $pdo->rollback();
                $result = false;
            }
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Device deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete device']);
        }
        
    } catch (Exception $e) {
        error_log("Delete device error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the device']);
    }
}

/**
 * Handle get device details request
 */
function handleGetDeviceDetails($serialNo, $userId, $isAdmin) {
    global $pdo;
    
    try {
        // Build query based on user role
        if ($isAdmin) {
            $sql = "SELECT e.*, u.useremail as UserEmail, dep.fullname as Firstname, '' as Lastname, 
                          dep.med_condition as MedicalCondition, i.serial_no as SerialNoFK, i.device_type as DeviceType,
                          e.family_contact1 as EmergencyNo1, e.family_contact2 as EmergencyNo2, dep.address as Address,
                          i.is_registered
                    FROM eva_info e
                    LEFT JOIN users u ON e.user_id = u.user_id
                    LEFT JOIN dependants dep ON e.dep_id = dep.dep_id
                    LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
                    WHERE i.serial_no = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$serialNo]);
        } else {
            $sql = "SELECT e.*, u.useremail as UserEmail, dep.fullname as Firstname, '' as Lastname, 
                          dep.med_condition as MedicalCondition, i.serial_no as SerialNoFK, i.device_type as DeviceType,
                          e.family_contact1 as EmergencyNo1, e.family_contact2 as EmergencyNo2, dep.address as Address,
                          i.is_registered
                    FROM eva_info e
                    LEFT JOIN users u ON e.user_id = u.user_id
                    LEFT JOIN dependants dep ON e.dep_id = dep.dep_id
                    LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
                    WHERE i.serial_no = ? AND e.user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$serialNo, $userId]);
        }
        
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            // Set device status based on registration
            $device['DeviceStatus'] = $device['is_registered'] ? 'Active' : 'Inactive';
            echo json_encode($device);
        } else {
            echo json_encode(['error' => 'Device not found or access denied']);
        }
        
    } catch (Exception $e) {
        error_log("Get device details error: " . $e->getMessage());
        echo json_encode(['error' => 'An error occurred while fetching device details']);
    }
}