<?php
session_start();
require_once '../../config/config.php';
require_once '../../helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => "You don't have permission to export inventory data."]);
    exit;
}

try {
    $pdo = getDatabase();
    
    // Get all inventory items
    $stmt = $pdo->prepare('SELECT serial_no as SerialNo, device_type as DeviceType, add_by as AddedBy, add_on as AddedOn, is_registered as isRegistered FROM inventory ORDER BY add_on DESC');
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($inventory)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => "No inventory data to export."]);
        exit;
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_export_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Serial No', 'Device Type', 'Added By', 'Added On', 'Status']);
    
    // Add data rows
    foreach ($inventory as $device) {
        $status = $device['isRegistered'] ? 'Registered' : 'Available';
        fputcsv($output, [
            $device['SerialNo'],
            $device['DeviceType'],
            $device['AddedBy'],
            $device['AddedOn'],
            $status
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    error_log("Export inventory error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => "Database error: " . $e->getMessage()]);
    exit;
}