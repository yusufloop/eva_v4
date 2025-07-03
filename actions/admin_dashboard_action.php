<?php
session_start();
require './config/config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ./index.php");
    exit();
}

$editMode = false;

function generateTable($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM EVA INNER JOIN Dependents ON EVA.DependentIDFK = Dependents.DependentID INNER JOIN Users AS UserEVA ON EVA.UserIDFK = UserEVA.UserID INNER JOIN Users AS UserDependent ON Dependents.UserIDFK = UserDependent.UserID;
                ');
    $stmt->execute();
    $users = $stmt->fetchAll();

    $headers = ['Username', 'Emergency No1', 'Emergency No2', 'Serial No', 'First name', 'Last name', 'Gender', 'DOB', 'Address', 'Medical Condition', ''];
    $userDatas = ['Email', 'EmergencyNo1', 'EmergencyNo2', 'SerialNoFK', 'Firstname', 'Lastname', 'Gender', 'DOB', 'Address', 'MedicalCondition'];
    // Create table headers from the $headers array
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    if (!empty($users[0])) {
        echo '<tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            foreach ($userDatas as $index => $userData) {
                echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($user[$userData] ?? '') . '</td>';
            }
            // Info, Update and Delete buttons
            echo '<td style="text-align: center;">';
            echo '<div style="display: inline-block;">';
            echo '<a href="device_details.php?serialNo=' . urlencode($user['SerialNoFK']) . '" class="info-btn" data-u_id="' . htmlspecialchars($user['SerialNoFK']) . '"> ℹ️ </a>';
            echo '<a href="javascript:void(0);" class="edit-btn" data-serialno="' . htmlspecialchars($user['SerialNoFK']) . '" onclick="editDevice(\'' . htmlspecialchars($user['SerialNoFK']) . '\')">✏️</a>';
            echo '<a href="actions/delete_eva_device.php?serialNo=' . urlencode($user['SerialNoFK']) . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this device?\');">🗑️</a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="' . count($headers) . '" style="text-align: center;">No data available.</td></tr>';
    }
}

generateUserList($pdo);
function generateUserList($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM EVA INNER JOIN Dependents ON EVA.DependentIDFK = Dependents.DependentID INNER JOIN Users AS UserEVA ON EVA.UserIDFK = UserEVA.UserID INNER JOIN Users AS UserDependent ON Dependents.UserIDFK = UserDependent.UserID;');
    $stmt = $pdo->prepare('SELECT * FROM Users');
    $stmt->execute();
    $users = $stmt->fetchAll();

    $_SESSION['users_data'] = $users;
}
//Populate edit data to Ajex
if (isset($_GET['serialNo'])) {
    $editMode = true;

    $serialNo = $_GET['serialNo'];
    // $username = $_SESSION['username'];

    $stmt = $pdo->prepare('SELECT * FROM EVA INNER JOIN Dependents ON EVA.DependentIDFK = Dependents.DependentID INNER JOIN Users AS UserEVA ON EVA.UserIDFK = UserEVA.UserID INNER JOIN Users AS UserDependent ON Dependents.UserIDFK = UserDependent.UserID WHERE SerialNoFK = ?');
    $stmt->execute([$serialNo]);
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
    $UserID = $_POST['hiddenUserIdInput'];
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
    // addOrUpdateSerialNumber($pdo, $username, $emergencyNo1, $emergencyNo2, $serialNo, $Firstname, $Lastname, $Gender, $Age, $Address, $Symptom);
}

function addDevice($pdo, $UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition, $emergencyNo1, $emergencyNo2, $serialNo, $existingDependent)
{

    if (empty($existingDependent)) {
        $stmt = $pdo->prepare('INSERT INTO Dependents (UserIDFK, Firstname, Lastname, Gender, DOB, Address, PostalCode, MedicalCondition) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$UserID, $Firstname, $Lastname, $Gender, $DOB, $Address, $Postal, $MedicalCondition])) {
            $existingDependent = $pdo->lastInsertId();
        }
    }

    $stmt = $pdo->prepare('SELECT * FROM EVA WHERE SerialNoFK = ?');
    $stmt->execute([$serialNo]);
    $result = $stmt->fetch();

    if (!$result) {
        $currentDateTime = date('Y-m-d H:i:s'); // Get current server datetime

        // Insert a new record
        $stmt = $pdo->prepare('INSERT INTO EVA (EmergencyNo1, EmergencyNo2, RegisteredDate, UserIDFK, SerialNoFK, DependentIDFK) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$emergencyNo1, $emergencyNo2, $currentDateTime, $UserID, $serialNo, $existingDependent])) {
            $stmt = $pdo->prepare("UPDATE Inventory SET isRegistered = 1 WHERE SerialNo = ?");
            $stmt->execute([$serialNo]);

            $_SESSION['device_message'] = "Information added successfully! Please reboot your device and wait for 5 minutes." . $UserID;
        } else {
            $_SESSION['device_message'] = "Error adding information: " . implode(", ", $stmt->errorInfo());
        }
    } else {
        if ($UserID == $result['UserIDFK']) {
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

// function addOrUpdateSerialNumber($pdo, $username, $emergencyNo1, $emergencyNo2, $serialNo, $Firstname, $Lastname, $Gender, $Age, $Address, $Symptom) {

//     // Check if a serial number already exists for the user
//     $stmt = $pdo->prepare('SELECT * FROM userprofile WHERE UserId = ? AND SerialNo = ?');
//     $stmt->execute([$username, $serialNo]);
//     $result = $stmt->fetch();

//     if ($result) {
//         $stmt = $pdo->prepare("UPDATE userprofile SET EmergencyNo1 = ?, EmergencyNo2 = ?, Firstname = ?, Lastname = ?, Gender = ?, Age = ?, Address = ?, Symptom = ? WHERE userId = ? AND SerialNo = ?");

//         if ($stmt->execute([$emergencyNo1, $emergencyNo2, $Firstname, $Lastname, $Gender, $Age, $Address, $Symptom, $username, $serialNo])) {
//             $_SESSION['device_message'] = "Device record updated. Please reboot your device and wait for 5 minutes";
//         } else {
//             $_SESSION['device_message'] = "Error updating device: " . implode(", ", $stmt->errorInfo());
//         }

//     } else {
//         // Check if there is an existing device with an empty serial number for this user
//         $stmt = $pdo->prepare('SELECT * FROM userprofile WHERE UserId = ? AND (serialNo IS NULL OR serialNo = "")');
//         $stmt->execute([$username]);
//         $result = $stmt->fetchAll();

//         if (count($result) == 1) {
//             // Update the existing record with the new serial number and emergency numbers
//             $stmt = $pdo->prepare("UPDATE userprofile SET EmergencyNo1 = ?, EmergencyNo2 = ?, SerialNo = ?, Firstname = ?, Lastname = ?, Gender = ?, Age = ?, Address = ?, Symptom = ? WHERE userId = ? AND (SerialNo IS NULL OR SerialNo = '')");

//             if ($stmt->execute([$emergencyNo1, $emergencyNo2, $serialNo, $Firstname, $Lastname, $Gender, $Age, $Address, $Symptom, $username])) {
//                 $_SESSION['device_message'] = "Information added successfully! Please reboot your device and wait for 5 minutes.";
//             } else {
//                 $_SESSION['device_message'] = "Error updating user profile: " . implode(", ", $stmt->errorInfo());
//             }


//         } else {
//             // If the serial number doesn't exist, insert a new record with the same user ID
//             $stmt = $pdo->prepare('SELECT * FROM userprofile WHERE UserId = ?');
//             $stmt->execute([$username]);
//             $result = $stmt->fetch();
//             $password = $result['UserPassword'];

//             // Insert a new record
//             $stmt = $pdo->prepare('INSERT INTO userprofile (userId, UserPassword, EmergencyNo1, EmergencyNo2, SerialNo, Firstname, Lastname, Gender, Age, Address, Symptom, token, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "", 1)');
//             if ($stmt->execute([$username, $password, $emergencyNo1, $emergencyNo2, $serialNo, $Firstname, $Lastname, $Gender, $Age, $Address, $Symptom])) {
//                 $_SESSION['device_message'] = "Information added successfully! Please reboot your device and wait for 5 minutes.";
//             } else {
//                 $_SESSION['device_message'] = "Error adding information: " . implode(", ", $stmt->errorInfo());
//             }


//         }
//     }
//     // Redirect back to dashboard
// header("Location: ../dashboard.php");
// exit();

// }

// function getExistingDependents($pdo, $userId) {
//     try {
//         $stmt = $pdo->prepare("SELECT DependentID, Firstname, Lastname, Address FROM Dependents WHERE UserIDFK = ?");
//         $stmt->execute([$userId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         // Handle errors gracefully
//         echo "Error: " . $e->getMessage();
//         return [];
//     }
// }
