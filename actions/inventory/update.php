<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    $_SESSION['error_message'] = "You don't have permission to update inventory items.";
    header('Location: ../../pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract form data
    $serialNo = trim($_POST['serialNo'] ?? '');
    $deviceType = trim($_POST['deviceType'] ?? '');
    
    // Validate input
    if (empty($serialNo) || empty($deviceType)) {
        $_SESSION['error_message'] = "Serial number and device type are required.";
        header('Location: ../../pages/inventory.php');
        exit;
    }
    
    try {
        $pdo = getDatabase();
        
        // Check if serial number exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
        $stmt->execute([$serialNo]);
        $exists = $stmt->fetchColumn();
        
        if (!$exists) {
            $_SESSION['error_message'] = "Device with this serial number does not exist.";
            header('Location: ../../pages/inventory.php');
            exit;
        }
        
        // Update device in inventory
        $stmt = $pdo->prepare('UPDATE Inventory SET DeviceType = ? WHERE SerialNo = ?');
        $result = $stmt->execute([$deviceType, $serialNo]);
        
        if ($result) {
            $_SESSION['success_message'] = "Device updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating device.";
        }
    } catch (PDOException $e) {
        error_log("Update inventory error: " . $e->getMessage());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    header('Location: ../../pages/inventory.php');
    exit;
}

// If not a POST request, redirect to inventory page
header('Location: ../../pages/inventory.php');
exit;