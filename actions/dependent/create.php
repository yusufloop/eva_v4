<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'] ?? getCurrentUserId();
    $firstname = trim($_POST['Firstname'] ?? '');
    $lastname = trim($_POST['Lastname'] ?? '');
    $gender = $_POST['Gender'] ?? '';
    $dob = $_POST['DOB'] ?? '';
    $address = trim($_POST['Address'] ?? '');
    $postalCode = trim($_POST['PostalCode'] ?? '');
    $medicalCondition = trim($_POST['MedicalCondition'] ?? '');

    try {
        // Validation
        if (empty($firstname) || empty($lastname)) {
            throw new Exception('First name and last name are required.');
        }

        if (empty($gender) || empty($dob)) {
            throw new Exception('Gender and date of birth are required.');
        }

        if (empty($address)) {
            throw new Exception('Address is required.');
        }

        // Check permissions
        if (!hasRole('admin') && $userId != getCurrentUserId()) {
            throw new Exception('You can only add dependents for yourself.');
        }

        $pdo = getDatabase();
        
        // Add new dependent
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        
        if ($stmt->execute([$userId, $firstname, $lastname, $gender, $dob, $address, $postalCode, $medicalCondition])) {
            $_SESSION['success_message'] = "Dependent added successfully.";
        } else {
            $_SESSION['error_message'] = "Error: Unable to add dependent.";
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Redirect based on user role
    if (hasRole('admin')) {
        header("Location: ../../pages/dependents.php");
    } else {
        header("Location: ../../pages/my_dependents.php");
    }
    exit;
}
?>