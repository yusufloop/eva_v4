<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

if (!hasRole('admin')) {
    header("Location: ../../index.php");
    exit();
}

function generateTable($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM Dependents');
    $stmt->execute();
    $Dependents = $stmt->fetchAll();

    $headers = ['First name', 'Last name', 'Gender', 'DOB', 'Address', 'PostalCode', 'MedicalCondition', ''];
    $userDatas = ['Firstname', 'Lastname', 'Gender', 'DOB', 'Address', 'PostalCode', 'MedicalCondition'];

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

    $stmt = $pdo->prepare('SELECT * FROM Dependents INNER JOIN Users ON Dependents.UserIDFK = Users.UserID WHERE DependentID = ?');
    $stmt->execute([$dependentID]);
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
    $UserID = $_POST['hiddenUserIdInput'];

    $Firstname = $_POST['Firstname'];
    $Lastname = $_POST['Lastname'];
    $Gender = $_POST['Gender'];
    $DOB = $_POST['DOB'];
    $Address = $_POST['Address'];
    $Postal = $_POST['PostalCode'];
    $existingDependent = $_POST['dependentId'];
    $MedicalCondition = $_POST['MedicalCondition'];

    addDependent($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $existingDependent);
}

function addDependent($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $existingDependent){
    if(empty($existingDependent)){
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition])) {
            $existingDependent = $pdo->lastInsertId();

            $_SESSION['device_message'] = "Dependent added successfully.";

        } else {
            $_SESSION['device_message'] = "Error: Not able to add dependent.";
        }
    } else {
            $stmt = $pdo->prepare("UPDATE Dependents SET Firstname = ?, Lastname = ?, Gender = ?, DOB = ?, Address = ?, PostalCode = ?, MedicalCondition = ?, UserIDFK = ? WHERE DependentID = ?");
            
            if ($stmt->execute([$Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $UserID, $existingDependent])) {
                $_SESSION['dependent_message'] = "Dependent record updated.";
            } else {
                $_SESSION['dependent_message'] = "Error updating dependent information: " . implode(", ", $stmt->errorInfo());
            }    
        }
        header("Location: ../../pages/admin_managedependents.php");
        exit();
}
?>