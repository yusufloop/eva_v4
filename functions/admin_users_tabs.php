<?php
// session_start();
require 'config.php';

// if (!isset($_SESSION['admin_username'])) {
//     header("Location: ./index.php");
//     exit();
// }

// $editMode = false;

function generateUserList($pdo){
    $stmt = $pdo->prepare('SELECT * FROM Users;');
    $stmt->execute();
    $users = $stmt->fetchAll();

    $headers = ['Email', 'Token(KIV)', 'Is Verified', 'Is Admin', ''];
    $userDatas = ['Email','', 'IsVerified', 'IsAdmin'];
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
                if ($userData === 'IsVerified') {
                    $displayValue = ($user[$userData] == 1) ? 'Verified' : 'Not Verified';
                    echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($displayValue) . '</td>';
                } else {
                    echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($user[$userData] ?? '') . '</td>';
                }
            }
        // Info, Update and Delete buttons
        echo '<td style="text-align: center;">';
        echo '<div style="display: inline-block;">';
        // echo '<a href="device_details.php?UserID=' . urlencode($user['UserID']) . '" class="info-btn" data-u_id="' . htmlspecialchars($user['UserID']) . '"> ℹ️ </a>';
        echo '<a href="javascript:void(0);" class="edit-btn" data-userid="' . htmlspecialchars($user['UserID']) . '" onclick="editUser(\'' . htmlspecialchars($user['UserID']) . '\')">✏️</a>';
        // echo '<a href="functions/delete_user.php?UserID=' . urlencode($user['UserID']) . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this device?\');">🗑️</a>';
        echo '</div>';
        echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
    } else {
        echo '<tr><td colspan="'.count($headers).'" style="text-align: center;">No data available.</td></tr>';
    }


}
// //Populate edit data to Ajex
if (isset($_GET['userId'])) {
    $editMode = true;

    $userId = $_GET['userId'];
    // $username = $_SESSION['username'];

    $stmt = $pdo->prepare('SELECT * FROM Users WHERE UserID = ?');
    $stmt->execute([$userId]);
    $users = $stmt->fetch();

    if ($users) {
        echo json_encode($users);
    } else {
        echo json_encode(['error' => 'Device not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    // Extract form data
    $UserID = $_POST['hiddenUserIdInput'];
    $IsAdmin = $_POST['IsAdmin'];

    // echo "HERE1";
    updateUser($pdo, $UserID, $IsAdmin);
}


function updateUser($pdo, $UserID, $IsAdmin){
    session_start();
    $stmt = $pdo->prepare("UPDATE Users SET IsAdmin = ? WHERE UserID = ?");
    
    if ($stmt->execute([$IsAdmin, $UserID])) {        
        $_SESSION['message'] = "Information updated successfully!";
    } else {
        $_SESSION['message'] = "Error adding information: " . implode(", ", $stmt->errorInfo());
    }

    header("Location: ../admin.php");
    exit();
}

