<?php


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_device'])) {
    // Debug: Print all POST data to see what we're receiving
    error_log("POST data received: " . print_r($_POST, true));
    
    // Extract form data with correct field names
    $UserID = $_POST['user_id'] ?? $_SESSION['UserID'] ?? null;
    $emergencyNo1 = $_POST['emergency_no1'] ?? '';
    $emergencyNo2 = $_POST['emergency_no2'] ?? '';
    $serialNo = $_POST['serial_no'] ?? '';
    
    // Dependent selection
    $dependentOption = $_POST['dependent_option'] ?? '';
    $existingDependent = $_POST['existing_dependent'] ?? '';
    
    // New dependent fields
    $Firstname = $_POST['firstname'] ?? '';
    $Lastname = $_POST['lastname'] ?? '';
    $Gender = $_POST['gender'] ?? '';
    $DOB = $_POST['dob'] ?? '';
    $Address = $_POST['address'] ?? '';
    $Postal = $_POST['postal_code'] ?? '';
    $MedicalCondition = $_POST['medical_condition'] ?? '';

    // Validation
    if (empty($UserID)) {
        $_SESSION['device_message'] = 'Error: User ID is required.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    if (empty($emergencyNo1) || empty($emergencyNo2) || empty($serialNo)) {
        $_SESSION['device_message'] = 'Error: Emergency numbers and serial number are required.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    if (empty($dependentOption)) {
        $_SESSION['device_message'] = 'Error: Please select a dependent option.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    // Validate dependent selection
    if ($dependentOption === 'existing' && empty($existingDependent)) {
        $_SESSION['device_message'] = 'Error: Please select an existing family member.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    if ($dependentOption === 'new' && (empty($Firstname) || empty($Lastname))) {
        $_SESSION['device_message'] = 'Error: First name and last name are required for new family member.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    // Check if serial number exists in inventory
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
    $stmt->execute([$serialNo]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $_SESSION['device_message'] = 'Error: The Serial Number you entered is not valid. Please check and try again.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    // Check if serial number is already registered
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM EVA WHERE SerialNoFK = ?');
    $stmt->execute([$serialNo]);
    $alreadyRegistered = $stmt->fetchColumn();

    if ($alreadyRegistered > 0) {
        $_SESSION['device_message'] = 'Error: This serial number is already registered to another device.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    // Process the device registration
    $finalDependentId = null;
    
    if ($dependentOption === 'existing') {
        $finalDependentId = $existingDependent;
    } else {
        // Create new dependent
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition])) {
            $finalDependentId = $pdo->lastInsertId();
        } else {
            $_SESSION['device_message'] = 'Error creating new family member: ' . implode(", ", $stmt->errorInfo());
            header("Location: ../../pages/dashboard.php");
            exit;
        }
    }

    if (!$finalDependentId) {
        $_SESSION['device_message'] = 'Error: Could not determine dependent for device registration.';
        header("Location: ../../pages/dashboard.php");
        exit;
    }

    // Register the device
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK) VALUES (?, ?, ?, ?, ?, ?)');
    
    if ($stmt->execute([$emergencyNo1, $emergencyNo2, $currentDateTime, $UserID, $serialNo, $finalDependentId])) {
        // Update inventory status
        $stmt = $pdo->prepare("UPDATE Inventory SET isRegistered = 1 WHERE SerialNo = ?");
        $stmt->execute([$serialNo]);
        
        $_SESSION['device_message'] = "Device registered successfully! Please reboot your device and wait for 5 minutes.";
    } else {
        $_SESSION['device_message'] = "Error registering device: " . implode(", ", $stmt->errorInfo());
    }

    header("Location: ../../pages/dashboard.php");
    exit;
}
?>