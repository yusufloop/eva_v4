<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * Get all devices with status for admin view
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
                e.LastOnline,
                u.Email,
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
                END as status,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM EVA e
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            LEFT JOIN (
                SELECT SerialNoFK, COUNT(*) as count 
                FROM Call_Histories 
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
                e.DeviceStatus,
                e.LastOnline,
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
                END as status,
                COALESCE(emergency_count.count, 0) as emergency_count
            FROM EVA e
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            LEFT JOIN (
                SELECT SerialNoFK, COUNT(*) as count 
                FROM Call_Histories 
                WHERE Status = "Active" 
                GROUP BY SerialNoFK
            ) emergency_count ON e.SerialNoFK = emergency_count.SerialNoFK
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
 * Get device details by serial number
 */
function getDeviceBySerialNo($serialNo, $userId = null) {
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
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
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
 * Delete a device with permission check
 */
function deleteDevice($serialNo, $userId) {
    $pdo = getDatabase();
    
    try {
        // Check if user owns this device or is admin
        $device = getDeviceBySerialNo($serialNo);
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found.'];
        }

        // Check permissions
        $stmt = $pdo->prepare('SELECT IsAdmin FROM Users WHERE UserID = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $isAdmin = $user && ($user['IsAdmin'] == 1 || $user['IsAdmin'] === 'Yes');

        if ($device['UserIDFK'] != $userId && !$isAdmin) {
            return ['success' => false, 'message' => 'You do not have permission to delete this device.'];
        }

        // Delete device
        $stmt = $pdo->prepare('DELETE FROM EVA WHERE SerialNoFK = ?');
        $result = $stmt->execute([$serialNo]);

        if ($result && $stmt->rowCount() > 0) {
            // Update inventory status
            $stmt = $pdo->prepare('UPDATE Inventory SET isRegistered = 0 WHERE SerialNo = ?');
            $stmt->execute([$serialNo]);
            
            return ['success' => true, 'message' => 'Device successfully deleted.'];
        } else {
            return ['success' => false, 'message' => 'Error: Device could not be deleted. Please try again.'];
        }
    } catch (Exception $e) {
        error_log("Delete Device Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error deleting device.'];
    }
}

/**
 * Add a new device
 */
function addDevice($deviceData) {
    $pdo = getDatabase();
    
    try {
        // Validate required fields
        $requiredFields = ['user_id', 'emergency_no1', 'emergency_no2', 'serial_no'];
        foreach ($requiredFields as $field) {
            if (empty($deviceData[$field])) {
                return ['success' => false, 'message' => "Missing required field: $field"];
            }
        }

        // Check if serial number exists in inventory
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
        $stmt->execute([$deviceData['serial_no']]);
        $serialExists = $stmt->fetchColumn();

        if (!$serialExists) {
            return ['success' => false, 'message' => 'Error: The Serial Number you entered is not valid. Please check and try again.'];
        }

        // Check if device is already registered
        $stmt = $pdo->prepare('SELECT * FROM EVA WHERE SerialNoFK = ?');
        $stmt->execute([$deviceData['serial_no']]);
        $existingDevice = $stmt->fetch();

        if ($existingDevice) {
            // If device exists, check if it belongs to the same user
            if ($existingDevice['UserIDFK'] != $deviceData['user_id']) {
                return ['success' => false, 'message' => 'This serial number is already registered to another user.'];
            }
            
            // Update existing device
            return updateExistingDevice($pdo, $deviceData);
        }

        // Handle dependent assignment
        $dependentId = handleDependent($pdo, $deviceData);
        if (!$dependentId) {
            return ['success' => false, 'message' => 'Error handling dependent information.'];
        }

        // Register new device
        return registerNewDevice($pdo, $deviceData, $dependentId);

    } catch (Exception $e) {
        error_log("Add Device Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An unexpected error occurred while adding the device.'];
    }
}

/**
 * Handle dependent creation or selection
 */
function handleDependent($pdo, $deviceData) {
    try {
        // If existing dependent is selected
        if (!empty($deviceData['existing_dependent'])) {
            return (int)$deviceData['existing_dependent'];
        }

        // Create new dependent
        if (!empty($deviceData['firstname']) && !empty($deviceData['lastname'])) {
            $stmt = $pdo->prepare('
                INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            $result = $stmt->execute([
                $deviceData['user_id'],
                $deviceData['firstname'],
                $deviceData['lastname'],
                $deviceData['gender'] ?? null,
                $deviceData['dob'] ?? '2000-01-01',
                $deviceData['address'] ?? null,
                $deviceData['postal_code'] ?? null,
                $deviceData['medical_condition'] ?? null
            ]);

            return $result ? $pdo->lastInsertId() : false;
        }

        return false;
    } catch (Exception $e) {
        error_log("Handle Dependent Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register a new device
 */
function registerNewDevice($pdo, $deviceData, $dependentId) {
    try {
        $currentDateTime = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare('
            INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK, DeviceStatus) 
            VALUES (?, ?, ?, ?, ?, ?, "Active")
        ');
        
        $result = $stmt->execute([
            $deviceData['emergency_no1'],
            $deviceData['emergency_no2'],
            $currentDateTime,
            $deviceData['user_id'],
            $deviceData['serial_no'],
            $dependentId
        ]);

        if ($result) {
            // Mark inventory item as registered
            $stmt = $pdo->prepare('UPDATE Inventory SET isRegistered = 1 WHERE SerialNo = ?');
            $stmt->execute([$deviceData['serial_no']]);
            
            return ['success' => true, 'message' => 'Device registered successfully! Please reboot your device and wait for 5 minutes.'];
        } else {
            return ['success' => false, 'message' => 'Error registering device: ' . implode(", ", $stmt->errorInfo())];
        }
    } catch (Exception $e) {
        error_log("Register New Device Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error registering device.'];
    }
}

/**
 * Update existing device registration
 */
function updateExistingDevice($pdo, $deviceData) {
    try {
        // Handle dependent for update
        $dependentId = handleDependent($pdo, $deviceData);
        if (!$dependentId) {
            return ['success' => false, 'message' => 'Error handling dependent information.'];
        }

        // Update device
        $stmt = $pdo->prepare('
            UPDATE EVA 
            SET EmergencyNo1 = ?, EmergencyNo2 = ?, DependentIDFK = ? 
            WHERE SerialNoFK = ?
        ');
        
        $result = $stmt->execute([
            $deviceData['emergency_no1'],
            $deviceData['emergency_no2'],
            $dependentId,
            $deviceData['serial_no']
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Device record updated. Please reboot your device and wait for 5 minutes.'];
        } else {
            return ['success' => false, 'message' => 'Error updating device: ' . implode(", ", $stmt->errorInfo())];
        }
    } catch (Exception $e) {
        error_log("Update Existing Device Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error updating device.'];
    }
}
?>