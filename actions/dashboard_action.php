<?php

session_start();
require './config/config.php';

if (isset($_SESSION['admin_username'])) {
    header("Location: ../pages/dashboard.php");
    exit();
}

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user ID from the users table
$UserID = $_SESSION['UserID'];

$dependents = getExistingDependents($pdo, $UserID);
$editMode = false;


function generateTable($pdo, $UserID) {
    // $stmt = $pdo->prepare('SELECT * FROM EVA WHERE UserId = ?');
    $stmt = $pdo->prepare('SELECT * FROM EVA INNER JOIN Dependents ON EVA.DependentIDFK = Dependents.DependentID WHERE EVA.UserIDFK = ?');
    $stmt->execute([$UserID]);
    $users = $stmt->fetchAll();

    $headers = ['Emergency No1', 'Emergency No2', 'Serial No', 'First name', 'Last name', 'Gender', 'DOB', 'Address', ''];
    $userDatas = ['EmergencyNo1', 'EmergencyNo2', 'SerialNoFK', 'Firstname', 'Lastname', 'Gender', 'DOB', 'Address'];
    // Create table headers from the $headers array
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    if (!empty($users[0]['SerialNoFK'])) {
        echo '<tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            foreach ($userDatas as $index => $userData) {
                echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($user[$userData] ?? '') . '</td>';
            }
        // Add Update and Delete buttons
        echo '<td>';
        echo '<a href="javascript:void(0);" class="edit-btn" data-serialno="' . htmlspecialchars($user['SerialNoFK']) . '" onclick="editDevice(\'' . htmlspecialchars($user['SerialNoFK']) . '\')">✏️</a>';

        echo '<a href="actions/delete_eva_device.php?serialNo=' . urlencode($user['SerialNoFK']) . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this device?\');">🗑️</a>'; // Delete button
        echo '</td>';


            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="'.count($headers).'" style="text-align: center;">No data available.</td></tr>';
    }
}
// //Populate edit data to Ajex
if (isset($_GET['serialNo'])) {
    $editMode = true;

    $serialNo = $_GET['serialNo'];
    $UserID = $_SESSION['UserID'];

    $stmt = $pdo->prepare('SELECT * FROM EVA INNER JOIN Dependents ON EVA.DependentIDFK = Dependents.DependentID WHERE EVA.UserIDFK = ? AND SerialNoFK = ?');
    $stmt->execute([$UserID, $serialNo]);
    $device = $stmt->fetch();

    if ($device) {
        echo json_encode($device);
    } else {
        echo json_encode(['error' => 'Device not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_device'])) {
    // Extract form data
    $UserID = $_SESSION['UserID'];

    $emergencyNo1 = $_POST['emergencyNo1'];
    $emergencyNo2 = $_POST['emergencyNo2'];
    $serialNo = $_POST['serialNo'];
    $Firstname = $_POST['Firstname'];
    $Lastname = $_POST['Lastname'];
    $Gender = $_POST['Gender'];
    $DOB = $_POST['DOB'];
    $Address = $_POST['Address'];
    $Postal = $_POST['Postal'];
    $existingDependent = $_POST['existingDependent'];
    $MedicalCondition = $_POST['MedicalCondition'];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
    $stmt->execute([$serialNo]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $_SESSION['device_message'] = 'Error: The Serial Number you entered is not valid. Please check and try again.';
        header("Location: ../dashboard.php");
        exit;
    }

    addDevice($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $emergencyNo1, $emergencyNo2, $serialNo, $existingDependent);
}

function addDevice($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $emergencyNo1, $emergencyNo2, $serialNo, $existingDependent){
    // $_SESSION['device_message'] = $existingDependent;

    if(empty($existingDependent)){
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition])) {
            $existingDependent = $pdo->lastInsertId();
        }
    }

    $stmt = $pdo->prepare('SELECT * FROM EVA WHERE SerialNoFK = ?');
    $stmt->execute([$serialNo]);
    $result = $stmt->fetch();

    if(!$result){
        $currentDateTime = date('Y-m-d H:i:s'); // Get current server datetime
        
        // Insert a new record
        $stmt = $pdo->prepare('INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$emergencyNo1, $emergencyNo2, $currentDateTime, $UserID, $serialNo, $existingDependent])) {
            $stmt = $pdo->prepare("UPDATE Inventory SET isRegistered = 1 WHERE SerialNo = ?");
            $stmt->execute([$serialNo]);
            
            $_SESSION['device_message'] = "Information added successfully! Please reboot your device and wait for 5 minutes.";
        } else {
            $_SESSION['device_message'] = "Error adding information: " . implode(", ", $stmt->errorInfo());
        }
    } else {
            if($UserID == $result['UserIDFK']){
                $stmt = $pdo->prepare("UPDATE EVA SET EmergencyNo1 = ?, EmergencyNo2 = ?, DependentIDFK = ? WHERE UserIDFK = ? AND SerialNoFK = ?");
            
            if ($stmt->execute([$emergencyNo1, $emergencyNo2, $existingDependent, $UserID, $serialNo])) {
                $_SESSION['device_message'] = "Device record updated. Please reboot your device and wait for 5 minutes";
            } else {
                $_SESSION['device_message'] = "Error updating device: " . implode(", ", $stmt->errorInfo());
            }
        } else {
            $_SESSION['device_message'] = "Error: The serial number you are trying to enter has already been used.";
        }
    }

    header("Location: ../dashboard.php");
    exit();

}

// Function to fetch existing dependents from the database
function getExistingDependents($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT DependentID, Firstname, Lastname, Address FROM Dependents WHERE UserIDFK = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle errors gracefully
        echo "Error: " . $e->getMessage();
        return [];
    }
}


?>

