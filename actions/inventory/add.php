<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    $_SESSION['error_message'] = "You don't have permission to add inventory items.";
    header('Location: ../../pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract form data
    $serialNo = trim($_POST['serialNo'] ?? '');
    $deviceType = trim($_POST['deviceType'] ?? '');
    $addedBy = getCurrentUserEmail() ?? 'Admin';
    
    // Validate input
    if (empty($serialNo) || empty($deviceType)) {
        $_SESSION['error_message'] = "Serial number and device type are required.";
        header('Location: ../../pages/inventory.php');
        exit;
    }
    
    try {
        $pdo = getDatabase();
        
        // Check if serial number already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM inventory WHERE serial_no = ?');
        $stmt->execute([$serialNo]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            $_SESSION['error_message'] = "A device with this serial number already exists.";
            header('Location: ../../pages/inventory.php');
            exit;
        }
        
        // Add new device to inventory
        $stmt = $pdo->prepare('INSERT INTO inventory (serial_no, device_type, add_by, add_on, is_registered) VALUES (?, ?, ?, NOW(), 0)');
        $result = $stmt->execute([$serialNo, $deviceType, $addedBy]);
        
        if ($result) {
            $_SESSION['success_message'] = "Device added to inventory successfully.";
        } else {
            $_SESSION['error_message'] = "Error adding device to inventory.";
        }
    } catch (PDOException $e) {
        error_log("Add inventory error: " . $e->getMessage());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    header('Location: ../../pages/inventory.php');
    exit;
}

// If not a POST request, redirect to inventory page
header('Location: ../../pages/inventory.php');
exit;