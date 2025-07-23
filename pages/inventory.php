<?php
// Page configuration
$pageTitle = 'Inventory Management';
$currentPage = 'inventory';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';

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

// Get inventory data with improved query
$pdo = getDatabase();
try {
    // Get inventory with registration info
    $stmt = $pdo->prepare('
        SELECT 
            i.inventory_id,
            i.serial_no,
            i.device_type,
            i.add_by,
            i.add_on,
            i.is_registered,
            i.reg_date,
            CASE 
                WHEN e.eva_id IS NOT NULL THEN u.useremail
                ELSE NULL
            END as assigned_user,
            CASE 
                WHEN e.eva_id IS NOT NULL THEN d.fullname
                ELSE NULL
            END as assigned_dependent
        FROM inventory i
        LEFT JOIN eva_info e ON i.inventory_id = e.inventory_id
        LEFT JOIN users u ON e.user_id = u.user_id
        LEFT JOIN dependants d ON e.dep_id = d.dep_id
        ORDER BY i.add_on DESC
    ');
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get comprehensive stats
    $stats = [
        'total' => 0,
        'registered' => 0,
        'available' => 0,
        'types' => []
    ];
    
    foreach ($inventory as $device) {
        $stats['total']++;
        if ($device['is_registered']) {
            $stats['registered']++;
        } else {
            $stats['available']++;
        }
        
        $type = $device['device_type'] ?? 'Unknown';
        if (!isset($stats['types'][$type])) {
            $stats['types'][$type] = 0;
        }
        $stats['types'][$type]++;
    }
    
} catch (PDOException $e) {
    error_log("Inventory query error: " . $e->getMessage());
    $inventory = [];
    $stats = ['total' => 0, 'registered' => 0, 'available' => 0, 'types' => []];
}

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Inventory Management', 'url' => '#']
];

// Include header
include '../includes/header.php';
?>

<?php include '../includes/topbar.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Page Header -->
        <div class="eva-card mb-4">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="page-icon">
                            📦
                        </div>
                        <div class="header-text">
                            <h1>Inventory Management</h1>
                            <p>Manage your device inventory and track assignments</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openAddDeviceModal()">
                            <i class="bi bi-plus-lg me-1"></i>Add Device
                        </button>
                        <button class="btn btn-outline" onclick="openImportModal()">
                            <i class="bi bi-cloud-upload me-1"></i>Import CSV
                        </button>
                        <button class="btn btn-secondary" onclick="exportInventory()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon total-devices">
                    <i class="bi bi-boxes"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total Devices</div>
                    <div class="stat-change">All inventory items</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon registered-devices">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['registered'] ?></div>
                    <div class="stat-label">Registered</div>
                    <div class="stat-change">Currently in use</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon available-devices">
                    <i class="bi bi-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['available'] ?></div>
                    <div class="stat-label">Available</div>
                    <div class="stat-change">Ready to assign</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon device-types">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= count($stats['types']) ?></div>
                    <div class="stat-label">Device Types</div>
                    <div class="stat-change">Different categories</div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="eva-card mb-4">
            <div class="card-body">
                <div class="filters-container">
                    <div class="search-container">
                        <div class="search-box">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   id="inventorySearch" 
                                   placeholder="Search by serial number, device type, or assigned user..." 
                                   class="search-input">
                            <button class="clear-search" id="clearSearch" style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="1">Registered</option>
                                <option value="0">Available</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Device Type</label>
                            <select id="typeFilter" class="filter-select">
                                <option value="">All Types</option>
                                <?php foreach (array_keys($stats['types']) as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Sort By</label>
                            <select id="sortFilter" class="filter-select">
                                <option value="add_on">Date Added</option>
                                <option value="serial_no">Serial Number</option>
                                <option value="device_type">Device Type</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button class="btn btn-outline btn-sm" onclick="clearAllFilters()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Content -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <h3>Device Inventory</h3>
                    </div>
                    <div class="header-actions">
                        <div class="view-toggle">
                            <button class="view-btn" data-view="cards" title="Card View">
                                <i class="bi bi-grid-3x2-gap"></i>
                            </button>
                            <button class="view-btn active" data-view="table" title="Table View">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                        <div class="results-info">
                            <span id="resultsCount"><?= count($inventory) ?></span> devices
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($inventory)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            📦
                        </div>
                        <h3>No Devices in Inventory</h3>
                        <p>Your inventory is empty. Start by adding devices manually or importing from a CSV file.</p>
                        <div class="empty-actions">
                            <button class="btn btn-primary" onclick="openAddDeviceModal()">
                                <i class="bi bi-plus-lg me-1"></i>Add First Device
                            </button>
                            <button class="btn btn-outline" onclick="openImportModal()">
                                <i class="bi bi-cloud-upload me-1"></i>Import CSV
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    
                    <!-- Card View -->
                    <div id="cardView" class="inventory-cards-container" style="display: none;">
                        <?php foreach ($inventory as $device): ?>
                            <div class="device-card" 
                                 data-serial="<?= htmlspecialchars($device['serial_no']) ?>"
                                 data-type="<?= htmlspecialchars($device['device_type'] ?? '') ?>"
                                 data-status="<?= $device['is_registered'] ?>"
                                 data-user="<?= htmlspecialchars($device['assigned_user'] ?? '') ?>">
                                
                                <div class="device-card-header">
                                    <div class="device-info">
                                        <div class="device-icon">
                                            <?php
                                            $type = $device['device_type'] ?? 'Unknown';
                                            $icon = match($type) {
                                                'Switch' => '🔌',
                                                'Heart Rate Monitor' => '💓',
                                                'GPS Tracker' => '📍',
                                                'Fitness Band' => '⌚',
                                                'Smartwatch' => '⌚',
                                                default => '📱'
                                            };
                                            echo $icon;
                                            ?>
                                        </div>
                                        <div class="device-details">
                                            <h4 class="device-serial"><?= htmlspecialchars($device['serial_no']) ?></h4>
                                            <p class="device-type"><?= htmlspecialchars($device['device_type'] ?? 'Unknown') ?></p>
                                        </div>
                                    </div>
                                    <div class="device-status">
                                        <span class="status-badge status-<?= $device['is_registered'] ? 'registered' : 'available' ?>">
                                            <?= $device['is_registered'] ? 'Registered' : 'Available' ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="device-card-body">
                                    <?php if ($device['is_registered'] && ($device['assigned_user'] || $device['assigned_dependent'])): ?>
                                        <div class="assignment-info">
                                            <div class="assignment-item">
                                                <i class="bi bi-person-fill"></i>
                                                <span><?= htmlspecialchars($device['assigned_user'] ?? 'Unknown User') ?></span>
                                            </div>
                                            <?php if ($device['assigned_dependent']): ?>
                                                <div class="assignment-item">
                                                    <i class="bi bi-heart-fill"></i>
                                                    <span><?= htmlspecialchars($device['assigned_dependent']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="unassigned-info">
                                            <i class="bi bi-circle text-muted"></i>
                                            <span class="text-muted">Not assigned</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="device-meta">
                                        <div class="meta-item">
                                            <span class="meta-label">Added by:</span>
                                            <span class="meta-value"><?= htmlspecialchars($device['add_by'] ?? 'System') ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Added on:</span>
                                            <span class="meta-value">
                                                <?php
                                                if ($device['add_on']) {
                                                    $addedOn = new DateTime($device['add_on']);
                                                    echo $addedOn->format('M j, Y');
                                                } else {
                                                    echo 'Unknown';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="device-card-actions">
                                    <?php if ($device['is_registered']): ?>
                                        <button class="btn btn-sm btn-outline" onclick="viewDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline" onclick="editDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="Edit Device">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="Delete Device">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Table View (Default) -->
                    <div id="tableView" class="inventory-table-container">
                        <div class="table-responsive">
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Assignment</th>
                                        <th>Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTableBody">
                                    <?php foreach ($inventory as $device): ?>
                                        <tr data-serial="<?= htmlspecialchars($device['serial_no']) ?>"
                                            data-type="<?= htmlspecialchars($device['device_type'] ?? '') ?>"
                                            data-status="<?= $device['is_registered'] ?>"
                                            data-user="<?= htmlspecialchars($device['assigned_user'] ?? '') ?>">
                                            
                                            <td>
                                                <div class="device-cell">
                                                    <div class="device-icon-small">
                                                        <?php
                                                        $type = $device['device_type'] ?? 'Unknown';
                                                        echo match($type) {
                                                            'Switch' => '🔌',
                                                            'Heart Rate Monitor' => '💓',
                                                            'GPS Tracker' => '📍',
                                                            'Fitness Band' => '⌚',
                                                            'Smartwatch' => '⌚',
                                                            default => '📱'
                                                        };
                                                        ?>
                                                    </div>
                                                    <div class="device-info-table">
                                                        <div class="device-serial-table"><?= htmlspecialchars($device['serial_no']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="device-type-badge"><?= htmlspecialchars($device['device_type'] ?? 'Unknown') ?></span>
                                            </td>
                                            
                                            <td>
                                                <span class="status-badge status-<?= $device['is_registered'] ? 'registered' : 'available' ?>">
                                                    <?= $device['is_registered'] ? 'Registered' : 'Available' ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <?php if ($device['is_registered'] && ($device['assigned_user'] || $device['assigned_dependent'])): ?>
                                                    <div class="assignment-cell">
                                                        <div class="assigned-user"><?= htmlspecialchars($device['assigned_user'] ?? 'Unknown') ?></div>
                                                        <?php if ($device['assigned_dependent']): ?>
                                                            <div class="assigned-dependent"><?= htmlspecialchars($device['assigned_dependent']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                                <div class="added-info">
                                                    <div class="added-date">
                                                        <?php
                                                        if ($device['add_on']) {
                                                            $addedOn = new DateTime($device['add_on']);
                                                            echo $addedOn->format('M j, Y');
                                                        } else {
                                                            echo 'Unknown';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="added-by">by <?= htmlspecialchars($device['add_by'] ?? 'System') ?></div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="table-actions">
                                                    <?php if ($device['is_registered']): ?>
                                                        <button class="btn-action btn-view" onclick="viewDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn-action btn-edit" onclick="editDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn-action btn-delete" onclick="deleteDevice('<?= htmlspecialchars($device['serial_no']) ?>')" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- No Results Found -->
                    <div id="noResults" class="no-results" style="display: none;">
                        <div class="no-results-icon">
                            🔍
                        </div>
                        <h3>No devices found</h3>
                        <p>Try adjusting your search criteria or filters.</p>
                        <button class="btn btn-outline" onclick="clearAllFilters()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                        </button>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-lg me-2"></i>Add New Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDeviceForm" action="../actions/inventory/add.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="serialNo" class="form-label">Serial Number *</label>
                        <input type="text" class="form-control" id="serialNo" name="serialNo" required>
                        <div class="form-text">Enter unique device serial number</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deviceType" class="form-label">Device Type *</label>
                        <select class="form-select" id="deviceType" name="deviceType" required>
                            <option value="">Select Device Type</option>
                            <option value="Switch">Switch</option>
                            <option value="Heart Rate Monitor">Heart Rate Monitor</option>
                            <option value="GPS Tracker">GPS Tracker</option>
                            <option value="Fitness Band">Fitness Band</option>
                            <option value="Smartwatch">Smartwatch</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="addedBy" value="<?= htmlspecialchars(getCurrentUserEmail() ?? 'Admin') ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Add Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Import Devices from CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/upload_inventory_list.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="import-info mb-3">
                        <h6>CSV Format Requirements:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-1"></i> Columns: serial_no, device_type, add_by</li>
                            <li><i class="bi bi-check-circle text-success me-1"></i> First row should contain headers</li>
                            <li><i class="bi bi-check-circle text-success me-1"></i> UTF-8 encoding recommended</li>
                        </ul>
                    </div>
                    
                    <div class="file-upload-area" id="dropZone">
                        <div class="upload-content">
                            <i class="bi bi-cloud-arrow-up upload-icon"></i>
                            <p class="upload-text">Drag & drop your CSV file here</p>
                            <p class="upload-subtext">or click to browse</p>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" class="file-input" required>
                        </div>
                        <div class="selected-file" id="selectedFile" style="display: none;">
                            <i class="bi bi-file-earmark-text file-icon"></i>
                            <span class="file-name"></span>
                            <button type="button" class="remove-file" onclick="removeFile()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>Import Devices
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDeviceForm" action="../actions/inventory/update.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="editSerialDisplay" readonly>
                        <input type="hidden" id="editSerialNo" name="serialNo">
                    </div>
                    
                    <div class="form-group">
                        <label for="editDeviceType" class="form-label">Device Type *</label>
                        <select class="form-select" id="editDeviceType" name="deviceType" required>
                            <option value="Switch">Switch</option>
                            <option value="Heart Rate Monitor">Heart Rate Monitor</option>
                            <option value="GPS Tracker">GPS Tracker</option>
                            <option value="Fitness Band">Fitness Band</option>
                            <option value="Smartwatch">Smartwatch</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Update Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Inventory Page Styles - Modern Blue-White Theme */

/* Base Layout */
.main-content {
    flex: 1;
    padding: 20px;
    transition: margin-left 0.3s ease;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

/* Cards */
.eva-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 25px;
    overflow: hidden;
}

.eva-card .card-header {
    padding: 20px 25px;
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.eva-card .card-body {
    padding: 25px;
}

/* Header Content */
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.page-icon {
    font-size: 32px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
}

.header-text h1 {
    margin: 0 0 4px 0;
    font-size: 28px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
}

.header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(226, 232, 240, 0.8);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.total-devices {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.registered-devices {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.stat-icon.available-devices {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
}

.stat-icon.device-types {
    background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
    font-family: 'Inter', sans-serif;
}

.stat-label {
    font-size: 16px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 2px;
}

.stat-change {
    font-size: 14px;
    color: #718096;
}

/* Filters Container */
.filters-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.search-container {
    flex: 1;
}

.search-box {
    position: relative;
    max-width: 500px;
}

.search-input {
    width: 100%;
    padding: 12px 16px 12px 45px;
    border: 2px solid rgba(226, 232, 240, 0.8);
    border-radius: 25px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.search-input:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 16px;
}

.clear-search {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.clear-search:hover {
    background: #f7fafc;
    color: #4a5568;
}

.filters-row {
    display: flex;
    gap: 16px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 140px;
}

.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-select {
    padding: 10px 14px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.3s ease;
}

.filter-select:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    outline: none;
}

.filter-actions {
    display: flex;
    align-items: end;
}

/* View Toggle */
.view-toggle {
    display: flex;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 4px;
    gap: 2px;
}

.view-btn {
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.7);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn.active,
.view-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.results-info {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    font-weight: 500;
}

/* Card View */
.inventory-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.device-card {
    background: white;
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.device-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-color: #4299e1;
}

.device-card-header {
    padding: 20px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.device-info {
    display: flex;
    gap: 16px;
    align-items: center;
}

.device-icon {
    font-size: 32px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(66, 153, 225, 0.1);
    border-radius: 10px;
}

.device-details h4 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
}

.device-type {
    margin: 0;
    color: #718096;
    font-size: 14px;
}

.device-card-body {
    padding: 20px;
}

.assignment-info {
    margin-bottom: 16px;
}

.assignment-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #4a5568;
}

.assignment-item i {
    color: #4299e1;
    width: 16px;
}

.unassigned-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 14px;
}

.device-meta {
    border-top: 1px solid rgba(226, 232, 240, 0.5);
    padding-top: 16px;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
}

.meta-label {
    color: #718096;
}

.meta-value {
    color: #4a5568;
    font-weight: 500;
}

.device-card-actions {
    padding: 16px 20px;
    background: rgba(247, 250, 252, 0.5);
    border-top: 1px solid rgba(226, 232, 240, 0.5);
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Table View */
.inventory-table-container {
    overflow-x: auto;
}

.inventory-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.inventory-table th {
    background: rgba(247, 250, 252, 0.8);
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    white-space: nowrap;
}

.inventory-table td {
    padding: 16px 12px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.3);
    vertical-align: middle;
}

.inventory-table tr:hover {
    background: rgba(247, 250, 252, 0.5);
}

.device-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.device-icon-small {
    font-size: 20px;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(66, 153, 225, 0.1);
    border-radius: 8px;
}

.device-serial-table {
    font-weight: 600;
    color: #2d3748;
}

.device-type-badge {
    background: rgba(66, 153, 225, 0.1);
    color: #4299e1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.assignment-cell {
    min-width: 150px;
}

.assigned-user {
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 2px;
}

.assigned-dependent {
    font-size: 12px;
    color: #718096;
}

.added-info {
    min-width: 120px;
}

.added-date {
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 2px;
}

.added-by {
    font-size: 12px;
    color: #718096;
}

.table-actions {
    display: flex;
    gap: 4px;
}

/* Status Badges */
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-registered {
    background: #48bb78;
    color: white;
}

.status-available {
    background: #ed8936;
    color: white;
}

/* Action Buttons */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: #4299e1;
    color: white;
}

.btn-primary:hover {
    background: #3182ce;
}

.btn-secondary {
    background: #a0aec0;
    color: white;
}

.btn-secondary:hover {
    background: #718096;
}

.btn-outline {
    background: transparent;
    color: #4299e1;
    border: 1px solid #4299e1;
}

.btn-outline:hover {
    background: #4299e1;
    color: white;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover {
    background: #c53030;
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
}

.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action.btn-edit {
    color: #4299e1;
}

.btn-action.btn-edit:hover {
    background: #4299e1;
    color: white;
    border-color: #4299e1;
}

.btn-action.btn-delete {
    color: #e53e3e;
}

.btn-action.btn-delete:hover {
    background: #e53e3e;
    color: white;
    border-color: #e53e3e;
}

.btn-action.btn-view {
    color: #718096;
}

.btn-action.btn-view:hover {
    background: #718096;
    color: white;
    border-color: #718096;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 12px 0;
    color: #4a5568;
    font-size: 24px;
    font-weight: 600;
}

.empty-state p {
    color: #718096;
    font-size: 16px;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.empty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.no-results {
    text-align: center;
    padding: 40px 20px;
}

.no-results-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.no-results h3 {
    margin: 0 0 8px 0;
    color: #4a5568;
    font-size: 20px;
}

.no-results p {
    color: #718096;
    margin-bottom: 20px;
}

/* Modal Styles */
.modal-content {
    border-radius: 15px;
    border: none;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    border-bottom: none;
    padding: 20px 25px;
}

.modal-title {
    font-weight: 600;
    font-size: 18px;
}

.btn-close {
    filter: invert(1);
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid rgba(226, 232, 240, 0.8);
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control,
.form-select {
    padding: 12px 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus,
.form-select:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    outline: none;
}

.form-text {
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
}

/* File Upload */
.file-upload-area {
    border: 2px dashed rgba(226, 232, 240, 0.8);
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.file-upload-area:hover {
    border-color: #4299e1;
    background: rgba(66, 153, 225, 0.05);
}

.file-upload-area.dragover {
    border-color: #4299e1;
    background: rgba(66, 153, 225, 0.1);
}

.upload-content {
    pointer-events: none;
}

.upload-icon {
    font-size: 48px;
    color: #a0aec0;
    margin-bottom: 16px;
}

.upload-text {
    font-size: 16px;
    font-weight: 600;
    color: #4a5568;
    margin: 0 0 4px 0;
}

.upload-subtext {
    font-size: 14px;
    color: #718096;
    margin: 0;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.selected-file {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px;
    background: rgba(66, 153, 225, 0.1);
    border-radius: 8px;
    margin-top: 16px;
}

.file-icon {
    font-size: 20px;
    color: #4299e1;
}

.file-name {
    font-weight: 500;
    color: #2d3748;
}

.remove-file {
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
}

.import-info h6 {
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 12px;
}

.import-info ul {
    margin: 0;
}

.import-info li {
    color: #718096;
    margin-bottom: 4px;
    font-size: 14px;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .main-content {
        padding: 15px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
        gap: 10px;
    }
    
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
    }
    
    .filters-container {
        gap: 15px;
    }
    
    .filters-row {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .inventory-cards-container {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .device-card-header {
        padding: 16px;
    }
    
    .device-card-body {
        padding: 16px;
    }
    
    .device-card-actions {
        padding: 12px 16px;
    }
    
    .inventory-table-container {
        font-size: 13px;
    }
    
    .inventory-table th,
    .inventory-table td {
        padding: 12px 8px;
    }
    
    .view-toggle {
        order: -1;
        width: 100%;
        justify-content: center;
    }
    
    .results-info {
        text-align: center;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 10px;
    }
    
    .eva-card .card-header {
        padding: 15px;
    }
    
    .eva-card .card-body {
        padding: 15px;
    }
    
    .page-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
        padding: 12px;
    }
    
    .header-text h1 {
        font-size: 22px;
    }
    
    .header-text p {
        font-size: 14px;
    }
    
    .stat-card {
        padding: 20px 15px;
        gap: 15px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-number {
        font-size: 24px;
    }
    
    .device-info {
        gap: 12px;
    }
    
    .device-icon {
        width: 40px;
        height: 40px;
        font-size: 24px;
    }
    
    .empty-actions {
        flex-direction: column;
    }
    
    .empty-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .modal-body {
        padding: 20px 15px;
    }
    
    .modal-footer {
        padding: 15px;
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('inventorySearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    // View toggle
    const viewBtns = document.querySelectorAll('.view-btn');
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');
    
    let currentView = 'table';
    let filteredItems = [];
    
    // Search input handler
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
            filterItems();
        });
    }
    
    // Clear search
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.style.display = 'none';
            filterItems();
        });
    }
    
    // Filter handlers
    [statusFilter, typeFilter, sortFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', filterItems);
        }
    });
    
    // View toggle handlers
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            if (view !== currentView) {
                viewBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                if (view === 'table') {
                    if (cardView) cardView.style.display = 'none';
                    if (tableView) tableView.style.display = 'block';
                } else {
                    if (cardView) cardView.style.display = 'grid';
                    if (tableView) tableView.style.display = 'none';
                }
                currentView = view;
            }
        });
    });
    
    // Filter items function
    function filterItems() {
        const searchTerm = searchInput?.value.toLowerCase() || '';
        const statusValue = statusFilter?.value || '';
        const typeValue = typeFilter?.value || '';
        
        const cardItems = document.querySelectorAll('.device-card');
        const tableRows = document.querySelectorAll('#inventoryTableBody tr');
        
        let visibleCount = 0;
        
        // Filter cards
        cardItems.forEach(card => {
            const serial = card.dataset.serial.toLowerCase();
            const type = card.dataset.type.toLowerCase();
            const status = card.dataset.status;
            const user = card.dataset.user.toLowerCase();
            
            const matchesSearch = !searchTerm || 
                serial.includes(searchTerm) || 
                type.includes(searchTerm) || 
                user.includes(searchTerm);
            
            const matchesStatus = !statusValue || status === statusValue;
            const matchesType = !typeValue || type === typeValue.toLowerCase();
            
            if (matchesSearch && matchesStatus && matchesType) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Filter table rows
        tableRows.forEach(row => {
            const serial = row.dataset.serial.toLowerCase();
            const type = row.dataset.type.toLowerCase();
            const status = row.dataset.status;
            const user = row.dataset.user.toLowerCase();
            
            const matchesSearch = !searchTerm || 
                serial.includes(searchTerm) || 
                type.includes(searchTerm) || 
                user.includes(searchTerm);
            
            const matchesStatus = !statusValue || status === statusValue;
            const matchesType = !typeValue || type === typeValue.toLowerCase();
            
            if (matchesSearch && matchesStatus && matchesType) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update results count and show/hide no results
        if (resultsCount) {
            resultsCount.textContent = visibleCount;
        }
        
        if (visibleCount === 0 && (cardItems.length > 0 || tableRows.length > 0)) {
            if (noResults) noResults.style.display = 'block';
            if (cardView) cardView.style.display = 'none';
            if (tableView) tableView.style.display = 'none';
        } else {
            if (noResults) noResults.style.display = 'none';
            if (currentView === 'cards') {
                if (cardView) cardView.style.display = 'grid';
                if (tableView) tableView.style.display = 'none';
            } else {
                if (cardView) cardView.style.display = 'none';
                if (tableView) tableView.style.display = 'block';
            }
        }
    }
    
    // File upload handling
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('csv_file');
    const selectedFile = document.getElementById('selectedFile');
    const importBtn = document.getElementById('importBtn');
    
    if (dropZone && fileInput) {
        // Drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });
    }
    
    function handleFileSelect(file) {
        if (selectedFile && importBtn) {
            selectedFile.style.display = 'flex';
            selectedFile.querySelector('.file-name').textContent = file.name;
            importBtn.disabled = false;
        }
    }
    
    // Initialize
    filterItems();
});

// Modal functions
function openAddDeviceModal() {
    const modal = new bootstrap.Modal(document.getElementById('addDeviceModal'));
    modal.show();
}

function openImportModal() {
    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

function editDevice(serialNo) {
    // You'll need to fetch device data and populate the modal
    const modal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
    document.getElementById('editSerialNo').value = serialNo;
    document.getElementById('editSerialDisplay').value = serialNo;
    modal.show();
}

function deleteDevice(serialNo) {
    if (confirm(`Are you sure you want to delete device ${serialNo}?\n\nThis action cannot be undone.`)) {
        // Handle deletion
        window.location.href = `../actions/inventory/delete.php?serialNo=${encodeURIComponent(serialNo)}`;
    }
}

function viewDevice(serialNo) {
    window.location.href = `//device/view/${encodeURIComponent(serialNo)}`;
}

function clearAllFilters() {
    document.getElementById('inventorySearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('sortFilter').value = 'add_on';
    document.getElementById('clearSearch').style.display = 'none';
    
    // Trigger filter update
    const event = new Event('input');
    document.getElementById('inventorySearch').dispatchEvent(event);
}

function exportInventory() {
    window.open('../actions/inventory/export.php', '_blank');
}

function removeFile() {
    const fileInput = document.getElementById('csv_file');
    const selectedFile = document.getElementById('selectedFile');
    const importBtn = document.getElementById('importBtn');
    
    if (fileInput) fileInput.value = '';
    if (selectedFile) selectedFile.style.display = 'none';
    if (importBtn) importBtn.disabled = true;
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
</script>

<?php include '../includes/footer.php'; ?>