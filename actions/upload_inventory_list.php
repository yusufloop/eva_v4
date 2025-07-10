<?php
session_start();
require_once './config/config.php'; // Updated path to config
require_once './helpers/auth_helper.php';

// Check authentication and admin role
requireAuth();
if (!hasRole('admin')) {
    $_SESSION['error_message'] = "You don't have permission to upload inventory data.";
    header('Location: ./pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Check if file is uploaded without errors
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        die("Error: File upload error!");
    }

    // Validate file type
    // $file_type = mime_content_type($_FILES['csv_file']['tmp_name']);
    // if ($file_type !== 'text/plain' && $file_type !== 'text/csv') {
    //     die("Error: Invalid file type. Only CSV files are allowed.");
    // }


    // Read the file
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$file) {
        die("Error: Cannot open the uploaded file.");
    }
    // Prepare default values
    $addedBy = $_SESSION['admin_username'] ?? 'System';
    $addedOn = date('Y-m-d H:i:s'); // Current server time

    // Skip the header row
    fgetcsv($file);

    $inserted_count = 0;
    $skipped_count = 0;
    while (($row = fgetcsv($file)) !== false) {
        // Ensure valid data
          if (count($row) !== 2) {
              echo "Invalid Row: " . implode(', ', $row) . "<br>";
              continue; // Skip invalid rows
          }

        $serialNo = trim($row[0]);
        $deviceType = trim($row[1]);

        // Check if the SerialNo already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Inventory WHERE SerialNo = ?');
        $stmt->execute([$serialNo]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $skipped_count++;
        } else {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO Inventory (SerialNo, DeviceType, AddedBy, AddedOn) VALUES (?, ?, ?, ?)");
            $stmt->execute([$serialNo, $deviceType, $addedBy, $addedOn]);
            $inserted_count++;
        }
    }

    fclose($file);

    // Provide feedback
    // echo "Import Complete: $inserted_count rows inserted, $skipped_count rows skipped.";
    $_SESSION['message'] = "Import Complete: $inserted_count rows inserted, $skipped_count rows skipped.";
} else {
    $_SESSION['error_message'] = "Error: No file uploaded.";
}
header("Location: ./pages/inventory.php");
exit;

?>
