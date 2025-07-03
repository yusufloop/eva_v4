<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dependentId = $_POST['dependentId'] ?? '';
    $userId = $_POST['user_id'] ?? $_SESSION['UserID'];
    $firstname = $_POST['Firstname'];
    $lastname = $_POST['Lastname'];
    $gender = $_POST['Gender'];
    $dob = $_POST['DOB'];
    $address = $_POST['Address'];
    $postalCode = $_POST['PostalCode'];
    $medicalCondition = $_POST['MedicalCondition'] ?? '';

    try {
        if (empty($dependentId)) {
            // Add new dependent
            $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            
            if ($stmt->execute([$userId, $firstname, $lastname, $gender, $dob, $address, $postalCode, $medicalCondition])) {
                $_SESSION['success_message'] = "Dependent added successfully.";
            } else {
                $_SESSION['error_message'] = "Error: Unable to add dependent.";
            }
        } else {
            // Update existing dependent
            // Check if user has permission to edit this dependent
            if (!hasRole('admin')) {
                $checkStmt = $pdo->prepare('SELECT UserIDFK FROM Dependents WHERE DependentID = ?');
                $checkStmt->execute([$dependentId]);
                $dependent = $checkStmt->fetch();
                
                if (!$dependent || $dependent['UserIDFK'] != $_SESSION['UserID']) {
                    $_SESSION['error_message'] = "You don't have permission to edit this dependent.";
                    header("Location: ../../pages/dependents.php");
                    exit;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE Dependents SET UserIDFK = ?, Firstname = ?, Lastname = ?, Gender = ?, DOB = ?, Address = ?, PostalCode = ?, MedicalCondition = ? WHERE DependentID = ?");
            
            if ($stmt->execute([$userId, $firstname, $lastname, $gender, $dob, $address, $postalCode, $medicalCondition, $dependentId])) {
                $_SESSION['success_message'] = "Dependent updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error: Unable to update dependent.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    header("Location: ../../pages/dependents.php");
    exit;
}
?>