<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => "You don't have permission to delete inventory items."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['serialNo'])) {
    $serialNo = $_GET['serialNo'];
    
    try {
        $pdo = getDatabase();
        
        // Check if device is registered to any user
        $stmt = $pdo->prepare('SELECT is_registered FROM inventory WHERE serial_no = ?');
        $stmt->execute([$serialNo]);
        $isRegistered = $stmt->fetchColumn();
        
        if ($isRegistered == 1) {
            echo json_encode(['success' => false, 'message' => "Cannot delete device. It is currently registered to a user."]);
            exit;
        }
        
        // Delete device from inventory
        $stmt = $pdo->prepare('DELETE FROM inventory WHERE serial_no = ?');
        $result = $stmt->execute([$serialNo]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => "Device deleted successfully."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error deleting device."]);
        }
    } catch (PDOException $e) {
        error_log("Delete inventory error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['success' => false, 'message' => "Invalid request."]);
exit;