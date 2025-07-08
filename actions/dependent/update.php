<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dependentId = $_POST['dependentId'] ?? '';
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
        if (empty($dependentId)) {
            throw new Exception('Dependent ID is required.');
        }

        if (empty($firstname) || empty($lastname)) {
            throw new Exception('First name and last name are required.');
        }

        $pdo = getDatabase();
        
        // Check if user has permission to edit this dependent
        if (!hasRole('admin')) {
            $checkStmt = $pdo->prepare('SELECT UserIDFK FROM Dependents WHERE DependentID = ?');
            $checkStmt->execute([$dependentId]);
            $dependent = $checkStmt->fetch();
            
            if (!$dependent || $dependent['UserIDFK'] != getCurrentUserId()) {
                throw new Exception("You don't have permission to edit this dependent.");
            }
        }
        
        // Update dependent
        $stmt = $pdo->prepare("UPDATE Dependents SET UserIDFK = ?, Firstname = ?, Lastname = ?, Gender = ?, DOB = ?, Address = ?, PostalCode = ?, MedicalCondition = ? WHERE DependentID = ?");
        
        if ($stmt->execute([$userId, $firstname, $lastname, $gender, $dob, $address, $postalCode, $medicalCondition, $dependentId])) {
            $_SESSION['success_message'] = "Dependent updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error: Unable to update dependent.";
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