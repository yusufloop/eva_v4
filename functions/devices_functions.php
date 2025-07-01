<?php
/**
 * Device Management Functions
 * Handles all device-related operations for the EVA system
 */

/**
 * Add a new device to the system
 * 
 * @param PDO $pdo Database connection
 * @param array $deviceData Device form data
 * @return array Result with success status and message
 */
function addDevice($pdo, $deviceData) {
    try {
        // Validate required fields
        $requiredFields = ['user_id', 'emergency_no1', 'emergency_no2', 'serial_no'];
        foreach ($requiredFields as $field) {
            if (empty($deviceData[$field])) {
                return ['success' => false, 'message' => "Missing required field: $field"];
            }
        }

        // Check if serial number exists in inventory
        if (!validateSerialNumber($pdo, $deviceData['serial_no'])) {
            return ['success' => false, 'message' => 'Error: The Serial Number you entered is not valid. Please check and try again.'];
        }

        // Check if device is already registered
        if (isDeviceRegistered($pdo, $deviceData['serial_no'])) {
            return updateExistingDevice($pdo, $deviceData);
        }

        // Handle dependent (existing or new)
        $dependentId = handleDependent($pdo, $deviceData);
        if (!$dependentId) {
            return ['success' => false, 'message' => 'Error creating or selecting dependent.'];
        }

        // Register the device
        return registerNewDevice($pdo, $deviceData, $dependentId);

    } catch (Exception $e) {
        error_log("Add Device Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An unexpected error occurred while adding the device.'];
    }
}

/**
 * Validate if serial number exists in inventory
 * 
 * @param PDO $pdo Database connection
 * @param string $serialNo Serial number to validate
 * @return bool True if valid, false otherwise
 */
function validateSerialNumber($pdo, $serialNo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
        $stmt->execute([$serialNo]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Validate Serial Number Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if device is already registered
 * 
 * @param PDO $pdo Database connection
 * @param string $serialNo Serial number to check
 * @return bool True if registered, false otherwise
 */
function isDeviceRegistered($pdo, $serialNo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE SerialNoFK = ?');
        $stmt->execute([$serialNo]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Check Device Registration Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle dependent creation or selection
 * 
 * @param PDO $pdo Database connection
 * @param array $deviceData Device form data
 * @return int|false Dependent ID or false on error
 */
function handleDependent($pdo, $deviceData) {
    try {
        // If existing dependent is selected
        if (!empty($deviceData['existing_dependent'])) {
            return (int)$deviceData['existing_dependent'];
        }

        // Create new dependent
        if (!empty($deviceData['firstname']) && !empty($deviceData['lastname'])) {
            return createNewDependent($pdo, $deviceData);
        }

        return false;
    } catch (Exception $e) {
        error_log("Handle Dependent Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new dependent
 * 
 * @param PDO $pdo Database connection
 * @param array $deviceData Device form data
 * @return int|false New dependent ID or false on error
 */
function createNewDependent($pdo, $deviceData) {
    try {
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
    } catch (Exception $e) {
        error_log("Create New Dependent Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register a new device
 * 
 * @param PDO $pdo Database connection
 * @param array $deviceData Device form data
 * @param int $dependentId Dependent ID
 * @return array Result with success status and message
 */
function registerNewDevice($pdo, $deviceData, $dependentId) {
    try {
        $currentDateTime = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare('
            INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK) 
            VALUES (?, ?, ?, ?, ?, ?)
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
            updateInventoryStatus($pdo, $deviceData['serial_no'], 1);
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
 * 
 * @param PDO $pdo Database connection
 * @param array $deviceData Device form data
 * @return array Result with success status and message
 */
function updateExistingDevice($pdo, $deviceData) {
    try {
        // Get existing device info
        $stmt = $pdo->prepare('SELECT UserIDFK FROM EVA WHERE SerialNoFK = ?');
        $stmt->execute([$deviceData['serial_no']]);
        $existingDevice = $stmt->fetch();

        if (!$existingDevice) {
            return ['success' => false, 'message' => 'Device not found.'];
        }

        // Check if user owns this device or is admin
        if ($existingDevice['UserIDFK'] != $deviceData['user_id'] && !isAdmin($deviceData['user_id'])) {
            return ['success' => false, 'message' => 'Error: The serial number you are trying to enter has already been used.'];
        }

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

/**
 * Update inventory registration status
 * 
 * @param PDO $pdo Database connection
 * @param string $serialNo Serial number
 * @param int $status Registration status (0 or 1)
 * @return bool Success status
 */
function updateInventoryStatus($pdo, $serialNo, $status) {
    try {
        $stmt = $pdo->prepare("UPDATE Inventory SET isRegistered = ? WHERE SerialNo = ?");
        return $stmt->execute([$status, $serialNo]);
    } catch (Exception $e) {
        error_log("Update Inventory Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all devices with their details
 * 
 * @param PDO $pdo Database connection
 * @param int|null $userId Optional user ID to filter by user's devices
 * @return array Array of devices
 */
function getAllDevicesWithStatus($pdo, $userId = null) {
    try {
        $sql = '
            SELECT 
                e.EVAID,
                e.SerialNoFK as SerialNo,
                e.EmergencyNo1,
                e.EmergencyNo2,
                e.RegisteredDate,
                e.DeviceStatus,
                e.LastOnline,
                u.Email as user_email,
                u.UserID as user_id,
                d.Firstname,
                d.Lastname,
                d.Address,
                CONCAT(d.Firstname, " ", d.Lastname) as dependent_name,
                i.DeviceType,
                CASE 
                    WHEN e.LastOnline > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN "online"
                    ELSE "offline"
                END as status,
                d.Address as location
            FROM EVA e
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
        ';
        
        if ($userId) {
            $sql .= ' WHERE e.UserIDFK = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Devices Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user's devices with status
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array Array of user's devices
 */
function getUserDevicesWithStatus($pdo, $userId) {
    return getAllDevicesWithStatus($pdo, $userId);
}

/**
 * Get device details by serial number
 * 
 * @param PDO $pdo Database connection
 * @param string $serialNo Serial number
 * @return array|false Device details or false if not found
 */
function getDeviceBySerialNo($pdo, $serialNo) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                e.*,
                d.*,
                u.Email as user_email,
                i.DeviceType
            FROM EVA e
            INNER JOIN Dependents d ON e.DependentIDFK = d.DependentID
            INNER JOIN Users u ON e.UserIDFK = u.UserID
            LEFT JOIN Inventory i ON e.SerialNoFK = i.SerialNo
            WHERE e.SerialNoFK = ?
        ');
        $stmt->execute([$serialNo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get Device By Serial Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a device
 * 
 * @param PDO $pdo Database connection
 * @param string $serialNo Serial number
 * @param int $userId User ID (for permission check)
 * @return array Result with success status and message
 */
function deleteDevice($pdo, $serialNo, $userId) {
    try {
        // Check if user owns this device or is admin
        $device = getDeviceBySerialNo($pdo, $serialNo);
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found.'];
        }

        if ($device['UserIDFK'] != $userId && !isAdmin($userId)) {
            return ['success' => false, 'message' => 'You do not have permission to delete this device.'];
        }

        // Delete device
        $stmt = $pdo->prepare('DELETE FROM EVA WHERE SerialNoFK = ?');
        $result = $stmt->execute([$serialNo]);

        if ($result && $stmt->rowCount() > 0) {
            // Update inventory status
            updateInventoryStatus($pdo, $serialNo, 0);
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
 * Check if user is admin
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return bool True if admin, false otherwise
 */
function isAdmin($pdo, $userId) {
    try {
        $stmt = $pdo->prepare('SELECT IsAdmin FROM Users WHERE UserID = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return $user && ($user['IsAdmin'] == 1 || $user['IsAdmin'] === 'Yes');
    } catch (Exception $e) {
        error_log("Check Admin Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get dashboard statistics
 * 
 * @param PDO $pdo Database connection
 * @param int|null $userId Optional user ID for user-specific stats
 * @return array Dashboard statistics
 */
function getDashboardStats($pdo, $userId = null) {
    try {
        $whereClause = $userId ? 'WHERE e.UserIDFK = ?' : '';
        $params = $userId ? [$userId] : [];

        // Get total devices
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM EVA e $whereClause");
        $stmt->execute($params);
        $totalDevices = $stmt->fetchColumn();

        // Get online devices (active in last 5 minutes)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM EVA e 
            $whereClause " . ($whereClause ? 'AND' : 'WHERE') . " e.LastOnline > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute($params);
        $onlineDevices = $stmt->fetchColumn();

        // Get offline devices
        $offlineDevices = $totalDevices - $onlineDevices;

        // Get active emergencies (this would depend on your emergency tracking system)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM Call_Histories ch 
            INNER JOIN EVA e ON ch.SerialNoFK = e.SerialNoFK
            $whereClause " . ($whereClause ? 'AND' : 'WHERE') . " ch.Status = 'Active' AND DATE(ch.Datetime) = CURDATE()
        ");
        $stmt->execute($params);
        $activeEmergencies = $stmt->fetchColumn();

        return [
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'offline_devices' => $offlineDevices,
            'active_emergencies' => $activeEmergencies
        ];
    } catch (Exception $e) {
        error_log("Get Dashboard Stats Error: " . $e->getMessage());
        return [
            'total_devices' => 0,
            'online_devices' => 0,
            'offline_devices' => 0,
            'active_emergencies' => 0
        ];
    }
}

/**
 * Process device form submission
 * 
 * @param PDO $pdo Database connection
 * @param array $postData POST data from form
 * @return array Result with success status and message
 */
function processDeviceForm($pdo, $postData) {
    // Map form data to standard format
    $deviceData = [
        'user_id' => $postData['user_id'] ?? $postData['hiddenUserIdInput'] ?? null,
        'emergency_no1' => $postData['emergency_no1'] ?? $postData['emergencyNo1'] ?? null,
        'emergency_no2' => $postData['emergency_no2'] ?? $postData['emergencyNo2'] ?? null,
        'serial_no' => $postData['serial_no'] ?? $postData['serialNo'] ?? null,
        'existing_dependent' => $postData['existing_dependent'] ?? $postData['existingDependent'] ?? null,
        'firstname' => $postData['firstname'] ?? $postData['Firstname'] ?? null,
        'lastname' => $postData['lastname'] ?? $postData['Lastname'] ?? null,
        'gender' => $postData['gender'] ?? $postData['Gender'] ?? null,
        'dob' => $postData['dob'] ?? $postData['DOB'] ?? null,
        'address' => $postData['address'] ?? $postData['Address'] ?? null,
        'postal_code' => $postData['postal_code'] ?? $postData['Postal'] ?? null,
        'medical_condition' => $postData['medical_condition'] ?? $postData['MedicalCondition'] ?? null
    ];

    return addDevice($pdo, $deviceData);
}
?>