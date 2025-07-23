<?php
// Device Management Functions

require_once __DIR__ . '/../config/config.php';
/**
 * Get all users for admin dropdown
 */
function getAllUsers() {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('SELECT user_id as UserID, useremail as Email FROM users WHERE is_verified = 1 ORDER BY useremail');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Users Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user's dependents
 */
function getUserDependents($userId) {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                d.dep_id as DependentID, 
                d.fullname as Firstname, 
                "" as Lastname, 
                d.sex as Gender,
                d.dob as DOB,
                d.address as Address, 
                "" as PostalCode,
                d.med_condition as MedicalCondition,
                COUNT(e.eva_id) as DeviceCount
            FROM dependants d 
            LEFT JOIN eva_info e ON d.dep_id = e.dep_id
            WHERE d.user_id = ? 
            GROUP BY d.dep_id
            ORDER BY d.fullname
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get User Dependents Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all dependents (for admin) - Enhanced with all required fields
 */
function getAllDependents() {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                d.dep_id as DependentID, 
                d.fullname as Firstname, 
                "" as Lastname, 
                d.sex as Gender,
                d.dob as DOB,
                d.address as Address, 
                "" as PostalCode,
                d.med_condition as MedicalCondition,
                d.user_id as UserIDFK,
                u.useremail as UserEmail,
                COUNT(e.eva_id) as DeviceCount
            FROM dependants d 
            LEFT JOIN users u ON d.user_id = u.user_id 
            LEFT JOIN eva_info e ON d.dep_id = e.dep_id
            GROUP BY d.dep_id
            ORDER BY u.useremail, d.fullname
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Dependents Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all devices with status
 */
function getAllDevicesWithStatus() {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                i.serial_no as SerialNo,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                e.user_id as UserIDFK,
                e.dep_id as DependentIDFK,
                CASE 
                    WHEN i.is_registered = 1 THEN "Active"
                    ELSE "Inactive"
                END as DeviceStatus,
                u.useremail as Email,
                d.fullname as Firstname,
                "" as Lastname,
                d.address as Address,
                d.med_condition as MedicalCondition,
                i.device_type as DeviceType,
                CASE 
                    WHEN e.lastseen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status,
                e.lastseen,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM eva_info e
            JOIN users u ON e.user_id = u.user_id
            JOIN dependants d ON e.dep_id = d.dep_id
            JOIN inventory i ON e.inventory_id = i.inventory_id
            LEFT JOIN (
                SELECT eva_id, COUNT(*) as count 
                FROM call_histories 
                WHERE status = "Active" 
                GROUP BY eva_id
            ) emergency_count ON e.eva_id = emergency_count.eva_id
            ORDER BY e.reg_date DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Devices Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user devices with status
 */
function getUserDevicesWithStatus($userId) {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT 
                i.serial_no as SerialNo,
                e.family_contact1 as EmergencyNo1,
                e.family_contact2 as EmergencyNo2,
                e.reg_date as RegisteredDate,
                e.user_id as UserIDFK,
                e.dep_id as DependentIDFK,
                CASE 
                    WHEN i.is_registered = 1 THEN "Active"
                    ELSE "Inactive"
                END as DeviceStatus,
                d.fullname as Firstname,
                "" as Lastname,
                d.address as Address,
                d.med_condition as MedicalCondition,
                i.device_type as DeviceType,
                CASE 
                    WHEN e.lastseen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status,
                e.lastseen,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM eva_info e
            JOIN dependants d ON e.dep_id = d.dep_id
            JOIN inventory i ON e.inventory_id = i.inventory_id
            LEFT JOIN (
                SELECT eva_id, COUNT(*) as count 
                FROM call_histories 
                WHERE status = "Active" 
                GROUP BY eva_id
            ) emergency_count ON e.eva_id = emergency_count.eva_id
            WHERE e.user_id = ?
            ORDER BY e.reg_date DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get User Devices Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get device statistics
 */
function getDeviceStatistics() {
   $pdo = getDatabase(); 
    try {
        // Total devices
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM eva_info');
        $stmt->execute();
        $total = $stmt->fetchColumn();

        // Online devices (active in last 5 minutes)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM eva_info WHERE lastseen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
        $stmt->execute();
        $online = $stmt->fetchColumn();

        // Offline devices
        $offline = $total - $online;

        // Active emergencies
        $stmt = $pdo->prepare('SELECT COUNT(DISTINCT eva_id) FROM call_histories WHERE status = "Active"');
        $stmt->execute();
        $emergencies = $stmt->fetchColumn();

        return [
            'total' => $total,
            'online' => $online,
            'offline' => $offline,
            'emergencies' => $emergencies
        ];
    } catch (Exception $e) {
        error_log("Get Device Statistics Error: " . $e->getMessage());
        return [
            'total' => 0,
            'online' => 0,
            'offline' => 0,
            'emergencies' => 0
        ];
    }
}

/**
 * Get user device statistics
 */
function getUserDeviceStatistics($userId) {
   $pdo = getDatabase(); 
    try {
        // Total user devices
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM eva_info WHERE user_id = ?');
        $stmt->execute([$userId]);
        $total = $stmt->fetchColumn();

        // User online devices
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM eva_info WHERE user_id = ? AND lastseen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
        $stmt->execute([$userId]);
        $online = $stmt->fetchColumn();

        // User offline devices
        $offline = $total - $online;

        // User active emergencies
        $stmt = $pdo->prepare('
            SELECT COUNT(DISTINCT ch.eva_id) 
            FROM call_histories ch 
            JOIN eva_info e ON ch.eva_id = e.eva_id 
            WHERE e.user_id = ? AND ch.status = "Active"
        ');
        $stmt->execute([$userId]);
        $emergencies = $stmt->fetchColumn();

        return [
            'total' => $total,
            'online' => $online,
            'offline' => $offline,
            'emergencies' => $emergencies
        ];
    } catch (Exception $e) {
        error_log("Get User Device Statistics Error: " . $e->getMessage());
        return [
            'total' => 0,
            'online' => 0,
            'offline' => 0,
            'emergencies' => 0
        ];
    }
}

/**
 * Get device details by serial number
 */
function getDeviceBySerial($serialNo, $userId = null) {
   $pdo = getDatabase(); 
    try {
        $sql = '
            SELECT 
                e.*,
                i.serial_no,
                i.device_type as DeviceType,
                i.is_registered,
                i.reg_date as RegisteredDate,
                u.useremail as UserEmail,
                u.user_id as UserIDFK,
                d.fullname as Firstname,
                "" as Lastname,
                d.address as Address,
                d.med_condition as MedicalCondition,
                d.sex as Gender,
                d.dob as DOB,
                d.dep_id as DepIDFK,
                "" as PostalCode,
                CASE 
                    WHEN i.is_registered = 1 THEN "Active"
                    ELSE "Inactive"
                END as DeviceStatus,
                CASE 
                    WHEN e.lastseen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status
            FROM eva_info e
            JOIN users u ON e.user_id = u.user_id
            JOIN dependants d ON e.dep_id = d.dep_id
            JOIN inventory i ON e.inventory_id = i.inventory_id
            WHERE i.serial_no = ?
        ';
        
        $params = [$serialNo];
        
        // If userId provided, restrict to user's devices
        if ($userId !== null) {
            $sql .= ' AND e.user_id = ?';
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Device By Serial Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate serial number exists in inventory
 */
function validateSerialNumber($serialNo) {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM inventory WHERE serial_no = ? AND is_registered = 0');
        $stmt->execute([$serialNo]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Validate Serial Number Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if device is already registered
 */
function isDeviceRegistered($serialNo) {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM eva_info e JOIN inventory i ON e.inventory_id = i.inventory_id WHERE i.serial_no = ?');
        $stmt->execute([$serialNo]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Check Device Registered Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get available inventory for registration
 */
function getAvailableInventory() {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('SELECT serial_no as SerialNo, device_type as DeviceType FROM inventory WHERE is_registered = 0 ORDER BY serial_no');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Available Inventory Error: " . $e->getMessage());
        return [];
    }
}
?>