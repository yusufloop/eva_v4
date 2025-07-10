<?php
// Page configuration
$pageTitle = 'Inventory Management';
$currentPage = 'inventory';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';
require_once '../helpers/device_helpers.php';
require_once '../helpers/component_helper.php';

// Check authentication
requireAuth();

// Get current user info
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Redirect if not admin
if (!$isAdmin) {
    $_SESSION['error_message'] = "You don't have permission to access the inventory management page.";
    header('Location: dashboard.php');
    exit;
}

// Page-specific assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css',
    '../assets/css/inventory.css'
];

$additionalJS = [
    '../assets/js/dashboard.js',
    '../assets/js/inventory.js'
];

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Inventory Management', 'url' => '#']
];

// Get inventory data
$pdo = getDatabase();
try {
    $stmt = $pdo->prepare('SELECT * FROM Inventory ORDER BY AddedOn DESC');
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM Inventory');
    $stmt->execute();
    $totalDevices = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare('SELECT COUNT(*) as registered FROM Inventory WHERE isRegistered = 1');
    $stmt->execute();
    $registeredDevices = $stmt->fetch(PDO::FETCH_ASSOC)['registered'];
    
    $availableDevices = $totalDevices - $registeredDevices;
} catch (PDOException $e) {
    error_log("Inventory query error: " . $e->getMessage());
    $inventory = [];
    $totalDevices = 0;
    $registeredDevices = 0;
    $availableDevices = 0;
}

// Include header
include '../includes/header.php';
?>

<?php include '../includes/topbar.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-box"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Total Devices</span>
                        <span class="total-text">All inventory</span>
                    </div>
                    <div class="stat-number total">
                        <?= $totalDevices ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon online">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Registered</span>
                        <span class="total-text">In use</span>
                    </div>
                    <div class="stat-number online">
                        <?= $registeredDevices ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon emergency">
                    <i class="bi bi-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Available</span>
                        <span class="total-text">Ready to assign</span>
                    </div>
                    <div class="stat-number emergency">
                        <?= $availableDevices ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="eva-card mb-4">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="inventory-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="header-text">
                            <h2>Import Inventory</h2>
                            <p>Upload CSV file with device information</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="../actions/upload_inventory_list.php" method="post" enctype="multipart/form-data" class="import-form">
                    <div class="file-upload-container">
                        <div class="file-upload-area" id="dropZone">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Drag & drop CSV file here or click to browse</p>
                            <span class="file-hint">Upload CSV file with columns: SerialNo, DeviceType, AddedBy</span>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" class="file-input" required>
                        </div>
                        <div class="selected-file" id="selectedFile">
                            <span class="file-name">No file selected</span>
                            <button type="button" class="remove-file" id="removeFile">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="import-actions">
                        <button type="button" id="browseButton" class="btn btn-outline">
                            <i class="bi bi-folder2-open"></i> Choose CSV File
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="inventory-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="header-text">
                            <h2>Inventory List</h2>
                            <p>Manage your device inventory</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="header-actions">
                        <div class="search-group">
                            <input type="text" id="inventorySearch" placeholder="Search by serial number or device type..." class="search-input">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="1">Registered</option>
                                <option value="0">Available</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <select id="typeFilter" class="filter-select">
                                <option value="">All Types</option>
                                <option value="Switch">Switch</option>
                                <option value="Heart Rate Monitor">Heart Rate Monitor</option>
                                <option value="GPS Tracker">GPS Tracker</option>
                                <option value="Fitness Band">Fitness Band</option>
                                <option value="Smartwatch">Smartwatch</option>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary" onclick="openAddDeviceModal()">
                            <i class="bi bi-plus-lg"></i> Add Device
                        </button>
                        
                        <button class="btn btn-outline" onclick="exportInventory()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($inventory)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="bi bi-box display-1 text-muted"></i>
                        <h3 class="mt-3">No Inventory Found</h3>
                        <p class="text-muted">
                            Your inventory is empty. Add devices manually or import from a CSV file.
                        </p>
                        <button class="btn btn-primary" onclick="openAddDeviceModal()">
                            <i class="bi bi-plus-lg"></i> Add Your First Device
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Inventory Table -->
                    <div class="inventory-table-container">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Serial No</th>
                                    <th>Device Type</th>
                                    <th>Added By</th>
                                    <th>Added On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <?php foreach ($inventory as $device): ?>
                                    <tr data-device-id="<?= htmlspecialchars($device['SerialNo']) ?>" 
                                        data-status="<?= $device['isRegistered'] ?>"
                                        data-type="<?= htmlspecialchars($device['DeviceType']) ?>">
                                        
                                        <!-- Serial Number -->
                                        <td>
                                            <div class="serial-cell">
                                                <?= htmlspecialchars($device['SerialNo']) ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Device Type -->
                                        <td>
                                            <div class="device-type-cell">
                                                <?= htmlspecialchars($device['DeviceType'] ?? 'Unknown') ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Added By -->
                                        <td>
                                            <div class="added-by-cell">
                                                <?= htmlspecialchars($device['AddedBy'] ?? 'System') ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Added On -->
                                        <td>
                                            <div class="added-on-cell">
                                                <?php 
                                                if ($device['AddedOn']) {
                                                    $addedOn = new DateTime($device['AddedOn']);
                                                    echo $addedOn->format('M j, Y H:i');
                                                } else {
                                                    echo 'Unknown';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td>
                                            <span class="status-badge status-<?= $device['isRegistered'] ? 'registered' : 'available' ?>">
                                                <?= $device['isRegistered'] ? 'Registered' : 'Available' ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit" 
                                                        onclick="editDevice('<?= htmlspecialchars($device['SerialNo']) ?>')" 
                                                        title="Edit Device">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn-action btn-delete" 
                                                        onclick="deleteDevice('<?= htmlspecialchars($device['SerialNo']) ?>')" 
                                                        title="Delete Device">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing 1-<?= min(10, count($inventory)) ?> of <?= count($inventory) ?> devices
                        </div>
                        <nav aria-label="Inventory pagination">
                            <ul class="pagination pagination-sm">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-labelledby="addDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDeviceModalLabel">
                    <i class="bi bi-plus-lg me-2"></i>Add New Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceForm" action="../actions/inventory/add.php" method="POST">
                    <div class="mb-3">
                        <label for="serialNo" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="serialNo" name="serialNo" required>
                        <div class="form-text">Enter the device serial number</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deviceType" class="form-label">Device Type</label>
                        <select class="form-select" id="deviceType" name="deviceType" required>
                            <option value="">Select Device Type</option>
                            <option value="Switch">Switch</option>
                            <option value="Heart Rate Monitor">Heart Rate Monitor</option>
                            <option value="GPS Tracker">GPS Tracker</option>
                            <option value="Fitness Band">Fitness Band</option>
                            <option value="Smartwatch">Smartwatch</option>
                        </select>
                    </div>
                    
                    <input type="hidden" id="addedBy" name="addedBy" value="<?= htmlspecialchars(getCurrentUserEmail()) ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('addDeviceForm').submit()">
                    <i class="bi bi-check-lg me-1"></i>Save Device
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeviceModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDeviceForm" action="../actions/inventory/update.php" method="POST">
                    <input type="hidden" id="editSerialNo" name="serialNo">
                    
                    <div class="mb-3">
                        <label for="editDeviceType" class="form-label">Device Type</label>
                        <select class="form-select" id="editDeviceType" name="deviceType" required>
                            <option value="Switch">Switch</option>
                            <option value="Heart Rate Monitor">Heart Rate Monitor</option>
                            <option value="GPS Tracker">GPS Tracker</option>
                            <option value="Fitness Band">Fitness Band</option>
                            <option value="Smartwatch">Smartwatch</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('editDeviceForm').submit()">
                    <i class="bi bi-check-lg me-1"></i>Update Device
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>