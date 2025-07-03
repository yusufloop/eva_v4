<?php
session_start();
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';

requireAuth();

// Fetch user ID from the users table
$UserID = $_SESSION['UserID'];


function generateTable($pdo, $UserID) {
    // $stmt = $pdo->prepare('SELECT * FROM EVA WHERE UserId = ?');
    $stmt = $pdo->prepare('SELECT * FROM Dependents WHERE UserIDFK = ?');
    $stmt->execute([$UserID]);
    $Dependents = $stmt->fetchAll();

    $headers = ['First name', 'Last name', 'Gender', 'DOB', 'Address', 'PostalCode', 'MedicalCondition', ''];
    $userDatas = ['Firstname', 'Lastname', 'Gender', 'DOB', 'Address', 'PostalCode', 'MedicalCondition'];
    // Create table headers from the $headers array    


    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    if (!empty($Dependents[0])) {
        echo '<tbody>';
        foreach ($Dependents as $Dependent) {
            echo '<tr>';
            foreach ($userDatas as $index => $DependentData) {
                echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($Dependent[$DependentData] ?? '') . '</td>';
            }
        // Add Update and Delete buttons
        echo '<td>';
        echo '<a href="javascript:void(0);" class="edit-btn" data-dependentID="' . htmlspecialchars($Dependent['DependentID']) . '" onclick="editDependent(\'' . htmlspecialchars($Dependent['DependentID']) . '\')">✏️</a>';

        // echo '<a href="actions/delete_eva_device.php?serialNo=' . urlencode($Dependent['DependentID']) . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this device?\');">🗑️</a>'; // Delete button
        echo '</td>';


            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="'.count($headers).'" style="text-align: center;">No data available.</td></tr>';
    }
}

if (isset($_GET['dependentID'])) {
    $editMode = true;

    $dependentID = $_GET['dependentID'];
    $UserID = $_SESSION['UserID'];

    $stmt = $pdo->prepare('SELECT * FROM Dependents WHERE UserIDFK = ? AND DependentID = ?');
    $stmt->execute([$UserID, $dependentID]);
    $device = $stmt->fetch();

    if ($device) {
        echo json_encode($device);
    } else {
        echo json_encode(['error' => 'Device not found']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_dependent'])) {
    // Extract form data
    $UserID = $_SESSION['UserID'];

    // $emergencyNo1 = $_POST['emergencyNo1'];
    // $emergencyNo2 = $_POST['emergencyNo2'];
    // $serialNo = $_POST['serialNo'];
    $Firstname = $_POST['Firstname'];
    $Lastname = $_POST['Lastname'];
    $Gender = $_POST['Gender'];
    $DOB = $_POST['DOB'];
    $Address = $_POST['Address'];
    $Postal = $_POST['PostalCode'];
    $existingDependent = $_POST['dependentId'];
    $MedicalCondition = $_POST['MedicalCondition'];

    // $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
    // $stmt->execute([$serialNo]);
    // $exists = $stmt->fetchColumn();

    // if (!$exists) {
    //     $_SESSION['device_message'] = 'Error: The Serial Number you entered is not valid. Please check and try again.';
    //     header("Location: ../dashboard.php");
    //     exit;
    // }

    addDependent($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $existingDependent);
}

function addDependent($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $existingDependent){
    echo $UserID . "," . $Firstname . "," . $Lastname . "," . $Gender . "," . $DOB . "," . $Address . "," . $Postal . "," . $MedicalCondition . "," . $existingDependent;    
     if(empty($existingDependent)){
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition])) {
            $existingDependent = $pdo->lastInsertId();

            $_SESSION['device_message'] = "Dependent added successfully.";

        } else {
            $_SESSION['device_message'] = "Error: Not able to add dependent.";
        }
    } else {
            $stmt = $pdo->prepare("UPDATE Dependents SET Firstname = ?, Lastname = ?, Gender = ?, DOB = ?, Address = ?, PostalCode = ?, MedicalCondition = ? WHERE DependentID = ? AND UserIDFK = ?");
            
            if ($stmt->execute([$Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $existingDependent, $UserID])) {
                $_SESSION['dependent_message'] = "Dependent record updated.";
            } else {
                $_SESSION['dependent_message'] = "Error updating dependent information: " . implode(", ", $stmt->errorInfo());
            }    
        }
        header("Location: ../managedependents.php");
        exit();

}
?>