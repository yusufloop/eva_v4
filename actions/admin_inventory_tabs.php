<?php
// session_start();
require 'config.php';

// if (!isset($_SESSION['admin_username'])) {
//     header("Location: ./index.php");
//     exit();
// }

$editMode = false;

function generateInventoryList($pdo){
    $stmt = $pdo->prepare('SELECT * FROM Inventory;');
    $stmt->execute();
    $Inventories = $stmt->fetchAll();

    $headers = ['SerialNo', 'AddedBy', 'AddedOn', 'Is Registered', 'Device Type', ''];
    $userDatas = ['SerialNo','AddedBy', 'AddedOn', 'isRegistered', 'DeviceType'];
    // Create table headers from the $headers array
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    if (!empty($Inventories[0])) {
        echo '<tbody>';
        foreach ($Inventories as $Inventory) {
            echo '<tr>';
            foreach ($userDatas as $index => $userData) {
                if ($userData === 'isRegistered') {
                    $displayValue = ($Inventory[$userData] == 1) ? 'Registered' : 'Not Registered';
                    echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($displayValue) . '</td>';
                } else {
                    echo '<td data-label="' . htmlspecialchars($headers[$index]) . '">' . htmlspecialchars($Inventory[$userData] ?? '') . '</td>';
                }
            }
        // Info, Update and Delete buttons
        echo '<td style="text-align: center;">';
        echo '<div style="display: inline-block;">';
        // echo '<a href="device_details.php?UserID=' . urlencode($user['UserID']) . '" class="info-btn" data-u_id="' . htmlspecialchars($user['UserID']) . '"> ℹ️ </a>';
        echo '<a href="javascript:void(0);" class="edit-btn" data-InventoryNo="' . htmlspecialchars($Inventory['InventoryNo']) . '" onclick="editInventory(\'' . htmlspecialchars($Inventory['InventoryNo']) . '\')">✏️</a>';
        // echo '<a href="actions/delete_user.php?UserID=' . urlencode($user['UserID']) . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this device?\');">🗑️</a>';
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
if (isset($_GET['InventoryNo'])) {
    $editMode = true;

    $InventoryNo = $_GET['InventoryNo'];
    // $username = $_SESSION['username'];

    $stmt = $pdo->prepare('SELECT * FROM Inventory WHERE InventoryNo = ?');
    $stmt->execute([$InventoryNo]);
    $device = $stmt->fetch();

    if ($device) {
        echo json_encode($device);
    } else {
        echo json_encode(['error' => 'Device not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    // Extract form data
    $InventoryId = $_POST['hiddenInventoryIdInput'];
    $DeviceType = $_POST['DeviceType'];


    editInventory($pdo, $InventoryId, $DeviceType);
}

function editInventory($pdo, $InventoryId, $DeviceType){
    session_start();
    $stmt = $pdo->prepare("UPDATE Inventory SET DeviceType = ? WHERE InventoryNo = ?");
    if ($stmt->execute([$DeviceType, $InventoryId])) {
        $_SESSION['message'] = "Information updated successfully!";
    } else {
        $_SESSION['message'] = "Error adding information: " . implode(", ", $stmt->errorInfo());
    }

    header("Location: ../admin.php");
    exit();
}

