<?php

require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

requireAuth();

$pdo = getDatabase();
// Only process POST requests
// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['device_error'] = 'Invalid request method.';
    header('Location: ../../pages/dashboard.php');
    exit();
}

// Check if this is an add device request
if (!isset($_POST['add_device'])) {
    $_SESSION['device_error'] = 'Invalid form submission.';
    header('Location: ../../pages/dashboard.php');
    exit();
}

try {
    // Extract and validate form data
    $userId = $_POST['user_id'] ?? $_POST['hiddenUserIdInput'] ?? null;
    $emergencyNo1 = trim($_POST['emergency_no1'] ?? $_POST['emergencyNo1'] ?? '');
    $emergencyNo2 = trim($_POST['emergency_no2'] ?? $_POST['emergencyNo2'] ?? '');
    $serialNo = trim($_POST['serial_no'] ?? $_POST['serialNo'] ?? '');
    $dependentOption = $_POST['dependent_option'] ?? $_POST['dependentSelect'] ?? '';
    $existingDependent = $_POST['existing_dependent'] ?? $_POST['existingDependent'] ?? null;
    
    // New dependent data
    $firstname = trim($_POST['firstname'] ?? $_POST['Firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? $_POST['Lastname'] ?? '');
    $gender = $_POST['gender'] ?? $_POST['Gender'] ?? '';
    $dob = $_POST['dob'] ?? $_POST['DOB'] ?? '';
    $address = trim($_POST['address'] ?? $_POST['Address'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? $_POST['Postal'] ?? '');
    $medicalCondition = trim($_POST['medical_condition'] ?? $_POST['MedicalCondition'] ?? '');

    // Validation
    if (empty($userId)) {
        throw new Exception('User ID is required.');
    }

    if (empty($emergencyNo1) || empty($emergencyNo2)) {
        throw new Exception('Both emergency numbers are required.');
    }

    if (empty($serialNo)) {
        throw new Exception('Device serial number is required.');
    }

    if (empty($dependentOption)) {
        throw new Exception('Please select a family member option.');
    }

    // Validate emergency numbers (basic phone validation)
    if (!preg_match('/^[\+]?[0-9\-\(\)\s]+$/', $emergencyNo1) || !preg_match('/^[\+]?[0-9\-\(\)\s]+$/', $emergencyNo2)) {
        throw new Exception('Invalid emergency number format.');
    }

    // Check if user has permission (regular users can only add for themselves)
    if (!hasRole('admin') && $userId != getCurrentUserId()) {
        throw new Exception('You can only add devices for yourself.');
    }

    // Validate serial number exists in inventory
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
    $stmt->execute([$serialNo]);
    $serialExists = $stmt->fetchColumn();

    if (!$serialExists) {
        throw new Exception('Invalid serial number. Please check and try again.');
    }

    // Check if serial number is already registered
    $stmt = $pdo->prepare('SELECT * FROM EVA WHERE SerialNoFK = ?');
    $stmt->execute([$serialNo]);
    $existingDevice = $stmt->fetch();

    if ($existingDevice) {
        // If device exists, check if it belongs to the same user
        if ($existingDevice['UserIDFK'] != $userId) {
            throw new Exception('This serial number is already registered to another user.');
        }
        
        // If same user, we'll update the existing record
        $isUpdate = true;
    } else {
        $isUpdate = false;
    }

    // Handle dependent assignment
    $dependentId = null;
    
    if ($dependentOption === 'existing') {
        if (empty($existingDependent)) {
            throw new Exception('Please select an existing family member.');
        }
        
        // Verify dependent belongs to the user
        $stmt = $pdo->prepare('SELECT DependentID FROM Dependents WHERE DependentID = ? AND UserIDFK = ?');
        $stmt->execute([$existingDependent, $userId]);
        $dependent = $stmt->fetch();
        
        if (!$dependent) {
            throw new Exception('Invalid family member selection.');
        }
        
        $dependentId = $existingDependent;
        
    } elseif ($dependentOption === 'new') {
        // Validate new dependent data
        if (empty($firstname) || empty($lastname)) {
            throw new Exception('First name and last name are required for new family member.');
        }
        
        if (empty($gender) || empty($dob)) {
            throw new Exception('Gender and date of birth are required for new family member.');
        }
        
        if (empty($address)) {
            throw new Exception('Address is required for new family member.');
        }
        
        // Validate date of birth
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
            throw new Exception('Invalid date of birth format.');
        }
        
        // Check if dependent already exists (prevent duplicates)
        $stmt = $pdo->prepare('SELECT DependentID FROM Dependents WHERE UserIDFK = ? AND Firstname = ? AND Lastname = ? AND DOB = ?');
        $stmt->execute([$userId, $firstname, $lastname, $dob]);
        $existingDep = $stmt->fetch();
        
        if ($existingDep) {
            $dependentId = $existingDep['DependentID'];
        } else {
            // Create new dependent
            $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $result = $stmt->execute([$userId, $firstname, $lastname, $gender, $dob, $address, $postalCode, $medicalCondition]);
            
            if (!$result) {
                throw new Exception('Failed to create new family member: ' . implode(', ', $stmt->errorInfo()));
            }
            
            $dependentId = $pdo->lastInsertId();
        }
    } else {
        throw new Exception('Invalid dependent option selected.');
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        if ($isUpdate) {
            // Update existing device
            $stmt = $pdo->prepare('UPDATE EVA SET EmergencyNo1 = ?, EmergencyNo2 = ?, DependentIDFK = ? WHERE UserIDFK = ? AND SerialNoFK = ?');
            $result = $stmt->execute([$emergencyNo1, $emergencyNo2, $dependentId, $userId, $serialNo]);
            
            if (!$result) {
                throw new Exception('Failed to update device: ' . implode(', ', $stmt->errorInfo()));
            }
            
            $successMessage = 'Device updated successfully! Please reboot your device and wait for 5 minutes.';
            
        } else {
            // Insert new device - THIS WAS MISSING!
            $currentDateTime = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK) VALUES (?, ?, ?, ?, ?, ?)');
            $result = $stmt->execute([$emergencyNo1, $emergencyNo2, $currentDateTime, $userId, $serialNo, $dependentId]);
            
            if (!$result) {
                throw new Exception('Failed to add device: ' . implode(', ', $stmt->errorInfo()));
            }
            
            // Update inventory to mark as registered
            $stmt = $pdo->prepare('UPDATE Inventory SET isRegistered = 1 WHERE SerialNo = ?');
            $stmt->execute([$serialNo]);
            
            $successMessage = 'Device added successfully! Please reboot your device and wait for 5 minutes.';
        }

        // Commit transaction
        $pdo->commit();
        
        $_SESSION['device_success'] = $successMessage;

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Add Device Error: " . $e->getMessage());
    $_SESSION['device_error'] = $e->getMessage();
}

// Redirect back to dashboard
header('Location: ../../pages/dashboard.php');
exit();
?>