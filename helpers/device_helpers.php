<?php
// Device Management Functions

require_once __DIR__ . '/../config/config.php';
/**
 * Get all users for admin dropdown
 */
function getAllUsers() {
    $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('SELECT UserID, Email FROM Users WHERE IsVerified = 1 ORDER BY Email');
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
        $stmt = $pdo->prepare('SELECT DependentID, Firstname, Lastname, Address FROM Dependents WHERE UserIDFK = ? ORDER BY Firstname, Lastname');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get User Dependents Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all dependents (for admin)
 */
function getAllDependents() {
   $pdo = getDatabase(); 
    try {
        $stmt = $pdo->prepare('
            SELECT d.DependentID, d.Firstname, d.Lastname, d.Address, u.Email ,d.Gender, d.DOB
            FROM Dependents d 
            JOIN Users u ON d.UserIDFK = u.UserID 
            ORDER BY u.Email, d.Firstname, d.Lastname
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
                e.SerialNoFK as SerialNo,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.UserIDFK,
                e.DependentIDFK,
                e.DeviceStatus,
                u.Email as Email,
                d.Firstname,
                d.Lastname,
                d.Address,
                d.MedicalCondition,
                i.DeviceType,
                CASE 
                    WHEN e.LastOnline >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status,
                e.LastOnline,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM EVA e
            JOIN Users u ON e.UserIDFK = u.UserID
            JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            LEFT JOIN (
                SELECT SerialNoFK, COUNT(*) as count 
                FROM call_histories 
                WHERE Status = "Active" 
                GROUP BY SerialNoFK
            ) emergency_count ON e.SerialNoFK = emergency_count.SerialNoFK
            ORDER BY e.RegisteredDate DESC
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
                e.SerialNoFK as SerialNo,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.UserIDFK,
                e.DependentIDFK,
                
                d.Firstname,
                d.Lastname,
                d.Address,
                d.MedicalCondition,
                i.DeviceType,
                CASE 
                    WHEN e.LastOnline >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status,
                e.LastOnline,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM EVA e
            JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            LEFT JOIN (
                SELECT EVANo, COUNT(*) as count 
                FROM call_histories 
                WHERE Status = "Active" 
                GROUP BY EVANo
            ) emergency_count ON e.EVANo = emergency_count.EVANo
            WHERE e.UserIDFK = ?
            ORDER BY e.RegisteredDate DESC
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
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA');
        $stmt->execute();
        $total = $stmt->fetchColumn();

        // Online devices (active in last 5 minutes)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE LastOnline >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
        $stmt->execute();
        $online = $stmt->fetchColumn();

        // Offline devices
        $offline = $total - $online;

        // Active emergencies
        $stmt = $pdo->prepare('SELECT COUNT(DISTINCT EVANo) FROM call_histories WHERE Status = "Active"');
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
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE UserIDFK = ?');
        $stmt->execute([$userId]);
        $total = $stmt->fetchColumn();

        // User online devices
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE UserIDFK = ? AND LastOnline >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
        $stmt->execute([$userId]);
        $online = $stmt->fetchColumn();

        // User offline devices
        $offline = $total - $online;

        // User active emergencies
        $stmt = $pdo->prepare('
            SELECT COUNT(DISTINCT ch.EVANo) 
            FROM call_histories ch 
            JOIN EVA e ON ch.EVANo = e.EVANo 
            WHERE e.UserIDFK = ? AND ch.Status = "Active"
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
                u.Email as UserEmail,
                d.Firstname,
                d.Lastname,
                d.Address,
                d.MedicalCondition,
                d.Gender,
                d.DOB,
                d.PostalCode,
                i.DeviceType,
                CASE 
                    WHEN e.LastOnline >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status
            FROM EVA e
            JOIN Users u ON e.UserIDFK = u.UserID
            JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            WHERE e.SerialNoFK = ?
        ';
        
        $params = [$serialNo];
        
        // If userId provided, restrict to user's devices
        if ($userId !== null) {
            $sql .= ' AND e.UserIDFK = ?';
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
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ? AND isRegistered = 0');
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
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE SerialNoFK = ?');
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
        $stmt = $pdo->prepare('SELECT SerialNo, DeviceType FROM Inventory WHERE isRegistered = 0 ORDER BY SerialNo');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Available Inventory Error: " . $e->getMessage());
        return [];
    }
}
?>