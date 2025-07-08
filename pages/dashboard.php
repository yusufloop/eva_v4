<?php
session_start();
require_once '../config/config.php';
require_once '../helpers/device_helpers.php'; // Reuse existing helper
require_once '../helpers/auth_helper.php';
require_once '../actions/dashboard/stats.php';
require_once '../actions/dashboard/activities.php';

// Check authentication
requireAuth();

// Page configuration
$pageTitle = hasRole('admin') ? 'Admin Dashboard' : 'My Dashboard';
$currentPage = 'dashboard';
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Page-specific assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css'
];

$additionalJS = [
    '../assets/js/dashboard.js'
];

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '#']
];

// Get dashboard data based on user role
if ($isAdmin) {
    $dashboardData = getAdminDashboardData();
    $recentActivities = getRecentSystemActivities(10);
    $devices = getAllDevicesWithStatus();
    $users = getAllUsers();
    $dependents = getAllDependents();
} else {
    $dashboardData = getUserDashboardData($userId);
    $recentActivities = getUserRecentActivities($userId, 5);
    $devices = getUserDevicesWithStatus($userId);
    $users = [];
    $dependents = getUserDependents($userId);
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
                <div class="stat-icon online">
                    <i class="bi bi-wifi"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Online</span>
                        <span class="total-text">Active Devices</span>
                    </div>
                    <div class="stat-number online">
                        <?= $dashboardData['online_devices'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon offline">
                    <i class="bi bi-wifi-off"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Offline</span>
                        <span class="total-text">Inactive Devices</span>
                    </div>
                    <div class="stat-number offline">
                        <?= $dashboardData['offline_devices'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-phone"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Total Devices</span>
                        <span class="total-text">Registered</span>
                    </div>
                    <div class="stat-number total">
                        <?= $dashboardData['total_devices'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon emergency">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Emergencies</span>
                        <span class="total-text">Active Alerts</span>
                    </div>
                    <div class="stat-number emergency">
                        <?= $dashboardData['active_emergencies'] ?? 0 ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Management Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="device-icon">
                            📱
                        </div>
                        <div class="header-text">
                            <h2><?= $isAdmin ? 'All Devices' : 'My Devices' ?></h2>
                            <p>Device management and monitoring</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="header-actions">
                        <div class="search-group">
                            <input type="text" id="deviceSearch" placeholder="Search devices..." class="search-input">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary" onclick="addDevice()">
                            <i class="bi bi-plus-lg me-1"></i>Add Device
                        </button>
                        
                        <?php if ($isAdmin): ?>
                            <button class="btn btn-secondary" onclick="addUser()">
                                <i class="bi bi-person-plus me-1"></i>Add User
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($devices)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="bi bi-phone-vibrate display-1 text-muted"></i>
                        <h3 class="mt-3">No Devices Found</h3>
                        <p class="text-muted">
                            <?= $isAdmin ? 'No devices are registered in the system.' : 'You haven\'t registered any devices yet.' ?>
                        </p>
                        <button class="btn btn-primary" onclick="addDevice()">
                            <i class="bi bi-plus-lg me-1"></i>Add Your First Device
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Device Table -->
                    <div class="device-table-container">
                        <table class="device-table">
                            <thead>
                                <tr>
                                    <th>Device ID</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Assigned User</th>
                                    <th>Last Online</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deviceTableBody">
                                <?php foreach ($devices as $device): ?>
                                    <tr data-device-id="<?= htmlspecialchars($device['SerialNo']) ?>" 
                                        data-status="<?= htmlspecialchars($device['status']) ?>">
                                        
                                        <!-- Device ID -->
                                        <td>
                                            <div class="device-id-cell">
                                                <div class="device-name"><?= htmlspecialchars($device['SerialNo']) ?></div>
                                                <div class="device-type"><?= htmlspecialchars($device['DeviceType'] ?? 'EVA Device') ?></div>
                                            </div>
                                        </td>
                                        
                                        <!-- Location -->
                                        <td>
                                            <div class="location-cell">
                                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                                <?= htmlspecialchars($device['Address'] ?? 'Unknown Location') ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td>
                                            <span class="status-badge status-<?= $device['DeviceStatus'] ?>">
                                                <i class="bi bi-circle-fill me-1"></i>
                                                <?= ucfirst($device['DeviceStatus']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Assigned User -->
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-name"><?= htmlspecialchars($device['Email'] ?? 'Unknown') ?></div>
                                                <div class="dependent-name"><?= htmlspecialchars($device['Firstname'] . ' ' . $device['Lastname']) ?></div>
                                            </div>
                                        </td>
                                        
                                        <!-- Last Online -->
                                        <td>
                                            <div class="last-online">
                                                <?php 
                                                if ($device['LastOnline']) {
                                                    $lastOnline = new DateTime($device['LastOnline']);
                                                    echo $lastOnline->format('M j, Y H:i');
                                                } else {
                                                    echo 'Never';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" 
                                                        onclick="viewDevice('<?= htmlspecialchars($device['SerialNo']) ?>')" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
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
                            Showing 1-<?= min(10, count($devices)) ?> of <?= count($devices) ?> devices
                        </div>
                        <nav aria-label="Device pagination">
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
        
        <!-- Recent Activities Panel -->
        <?php if (!empty($recentActivities)): ?>
        <div class="eva-card mt-4">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="activity-icon">
                            📋
                        </div>
                        <div class="header-text">
                            <h2>Recent Activities</h2>
                            <p>Latest system activities and events</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="activity-list">
                    <?php foreach (array_slice($recentActivities, 0, 5) as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon-wrapper">
                                <i class="<?= getActivityIcon($activity['type']) ?> <?= getActivityColorClass($activity['type']) ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                                <div class="activity-description"><?= htmlspecialchars($activity['description']) ?></div>
                                <div class="activity-time"><?= formatTimeAgo($activity['created_at']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Device Modal -->
<div class="modal fade" id="viewDeviceModal" tabindex="-1" aria-labelledby="viewDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDeviceModalLabel">
                    <i class="bi bi-phone me-2"></i>Device Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deviceDetails">
                <!-- Device details will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editDeviceFromModal()">
                    <i class="bi bi-pencil me-1"></i>Edit Device
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-labelledby="addDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDeviceModalLabel">
                    <i class="bi bi-plus-lg me-2"></i>Add New Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="deviceId" class="form-label">Device Serial Number</label>
                                <input type="text" class="form-control" id="deviceId" name="deviceId" required>
                                <div class="form-text">Enter the device serial number found on the device label</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="deviceType" class="form-label">Device Type</label>
                                <select class="form-select" id="deviceType" name="deviceType" required>
                                    <option value="">Select Device Type</option>
                                    <option value="EVA-Standard">EVA Standard</option>
                                    <option value="EVA-Pro">EVA Pro</option>
                                    <option value="EVA-Mini">EVA Mini</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergencyNo1" class="form-label">Emergency Number 1</label>
                                <input type="tel" class="form-control" id="emergencyNo1" name="emergencyNo1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergencyNo2" class="form-label">Emergency Number 2</label>
                                <input type="tel" class="form-control" id="emergencyNo2" name="emergencyNo2" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="Room 101, Building A" required>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                        <div class="mb-3">
                            <label for="userId" class="form-label">Assign to User</label>
                            <select class="form-select" id="userId" name="userId" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['UserID'] ?>"><?= htmlspecialchars($user['Email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="userId" name="userId" value="<?= $userId ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="dependentId" class="form-label">Assign to Family Member</label>
                        <select class="form-select" id="dependentId" name="dependentId" required>
                            <option value="">Select Family Member</option>
                            <?php foreach ($dependents as $dependent): ?>
                                <option value="<?= $dependent['DependentID'] ?>">
                                    <?= htmlspecialchars($dependent['Firstname'] . ' ' . $dependent['Lastname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDevice()">
                    <i class="bi bi-check-lg me-1"></i>Save Device
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeviceModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Device
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDeviceForm">
                    <input type="hidden" id="editDeviceId" name="deviceId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editDeviceSerial" class="form-label">Device Serial Number</label>
                                <input type="text" class="form-control" id="editDeviceSerial" name="deviceSerial" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editDeviceType" class="form-label">Device Type</label>
                                <select class="form-select" id="editDeviceType" name="deviceType" required>
                                    <option value="EVA-Standard">EVA Standard</option>
                                    <option value="EVA-Pro">EVA Pro</option>
                                    <option value="EVA-Mini">EVA Mini</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editEmergencyNo1" class="form-label">Emergency Number 1</label>
                                <input type="tel" class="form-control" id="editEmergencyNo1" name="emergencyNo1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editEmergencyNo2" class="form-label">Emergency Number 2</label>
                                <input type="tel" class="form-control" id="editEmergencyNo2" name="emergencyNo2" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editLocation" name="location" required>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                        <div class="mb-3">
                            <label for="editUserId" class="form-label">Assigned User</label>
                            <select class="form-select" id="editUserId" name="userId" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['UserID'] ?>"><?= htmlspecialchars($user['Email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="editDependentId" class="form-label">Assigned Family Member</label>
                        <select class="form-select" id="editDependentId" name="dependentId" required>
                            <?php foreach ($dependents as $dependent): ?>
                                <option value="<?= $dependent['DependentID'] ?>">
                                    <?= htmlspecialchars($dependent['Firstname'] . ' ' . $dependent['Lastname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateDevice()">
                    <i class="bi bi-check-lg me-1"></i>Update Device
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal (Admin Only) -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="userPassword" name="password" required>
                        <div class="form-text">Must be at least 6 characters with uppercase, number, and special character</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userRole" class="form-label">User Role</label>
                        <select class="form-select" id="userRole" name="role">
                            <option value="user">Regular User</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">
                    <i class="bi bi-check-lg me-1"></i>Create User
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Dashboard Specific Styles */
.card-header {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    padding: 20px 24px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.device-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff5f5;
    border-radius: 8px;
}

.header-text h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.header-text p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.search-group {
    position: relative;
}

.search-input {
    padding: 8px 35px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    width: 200px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

.filter-group {
    position: relative;
}

.filter-select {
    padding: 8px 32px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: white;
    font-size: 14px;
    color: #333;
    min-width: 120px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px;
}

/* Device Table */
.device-table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.device-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.device-table thead {
    background: #f8f9fa;
}

.device-table thead th {
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e0e0e0;
}

.device-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.device-table tbody tr:hover {
    background: #f8f9fa;
}

.device-table tbody tr:last-child td {
    border-bottom: none;
}

/* Table Cell Styles */
.device-id-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.device-name {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 14px;
}

.device-type {
    font-size: 12px;
    color: #666;
}

.location-cell {
    display: flex;
    align-items: center;
    color: #1a1a1a;
}

.user-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    font-weight: 500;
    color: #1a1a1a;
    font-size: 14px;
}

.dependent-name {
    font-size: 12px;
    color: #666;
}

.last-online {
    color: #666;
    font-size: 13px;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-Active {
    background: #dcfce7;
    color: #16a34a;
}

.status-Inactive {
    background: #fee2e2;
    color: #dc2626;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
}

.btn-action {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 12px;
}

.btn-view {
    background: rgba(66, 133, 244, 0.1);
    color: #4285f4;
}

.btn-view:hover {
    background: #4285f4;
    color: white;
}

.btn-edit {
    background: rgba(251, 191, 36, 0.1);
    color: #f59e0b;
}

.btn-edit:hover {
    background: #f59e0b;
    color: white;
}

.btn-delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.btn-delete:hover {
    background: #ef4444;
    color: white;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    margin-top: 16px;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 4rem;
    color: #e2e8f0;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #4a5568;
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 1rem;
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .header-actions {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .search-input {
        width: 100%;
    }
    
    .device-table-container {
        overflow-x: auto;
    }
    
    .device-table {
        min-width: 600px;
    }
}
</style>

<script>
// Static device data for demonstration
const devices = <?= json_encode($devices) ?>;
const users = <?= json_encode($users) ?>;
const dependents = <?= json_encode($dependents) ?>;
const dashboardApiUrl = '../actions/dashboard/index.php';

let currentEditingDevice = null;

// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeFilters();
});

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('deviceSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterDevices(searchTerm);
        });
    }
}

// Filter functionality
function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value.toLowerCase();
            filterDevicesByStatus(status);
        });
    }
}

// Filter devices by search term
function filterDevices(searchTerm) {
    const tableRows = document.querySelectorAll('#deviceTableBody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter devices by status
function filterDevicesByStatus(status) {
    const tableRows = document.querySelectorAll('#deviceTableBody tr');
    
    tableRows.forEach(row => {
        const deviceStatus = row.dataset.status;
        if (!status || deviceStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View device details
function viewDevice(deviceId) {
    // Use API to get device details
    fetch(`${dashboardApiUrl}?action=device_details&serial_no=${encodeURIComponent(deviceId)}`)
        .then(response => response.json())
        .then(device => {
            if (device.error) {
                showToast(device.error, 'error');
                return;
            }
            
            document.getElementById('deviceDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Device Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Device ID:</strong></td><td>${device.SerialNoFK}</td></tr>
                            <tr><td><strong>Type:</strong></td><td>${device.DeviceType || 'EVA Device'}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-badge status-${device.DeviceStatus}"><i class="bi bi-circle-fill me-1"></i>${device.DeviceStatus}</span></td></tr>
                            <tr><td><strong>Location:</strong></td><td>${device.Address || 'Unknown'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Assignment Details</h6>
                        <table class="table table-sm">
                            <tr><td><strong>User:</strong></td><td>${device.UserEmail || 'Unknown'}</td></tr>
                            <tr><td><strong>Family Member:</strong></td><td>${device.Firstname} ${device.Lastname}</td></tr>
                            <tr><td><strong>Emergency 1:</strong></td><td>${device.EmergencyNo1 || 'Not set'}</td></tr>
                            <tr><td><strong>Emergency 2:</strong></td><td>${device.EmergencyNo2 || 'Not set'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Additional Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Registered Date:</strong></td><td>${device.RegisteredDate ? new Date(device.RegisteredDate).toLocaleDateString() : 'Unknown'}</td></tr>
                            <tr><td><strong>Last Online:</strong></td><td>${device.LastOnline ? new Date(device.LastOnline).toLocaleString() : 'Never'}</td></tr>
                            <tr><td><strong>Medical Condition:</strong></td><td>${device.MedicalCondition || 'None specified'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            currentEditingDevice = deviceId;
            new bootstrap.Modal(document.getElementById('viewDeviceModal')).show();
        })
        .catch(error => {
            console.error('Error fetching device details:', error);
            showToast('Error loading device details', 'error');
        });
}

// Edit device from modal
function editDeviceFromModal() {
    bootstrap.Modal.getInstance(document.getElementById('viewDeviceModal')).hide();
    editDevice(currentEditingDevice);
}

// Edit device
function editDevice(deviceId) {
    const device = devices.find(d => d.SerialNo === deviceId);
    if (device) {
        // Populate edit form
        document.getElementById('editDeviceId').value = device.SerialNo;
        document.getElementById('editDeviceSerial').value = device.SerialNo;
        document.getElementById('editDeviceType').value = device.DeviceType || 'EVA-Standard';
        document.getElementById('editEmergencyNo1').value = device.EmergencyNo1 || '';
        document.getElementById('editEmergencyNo2').value = device.EmergencyNo2 || '';
        document.getElementById('editLocation').value = device.Address || '';
        
        if (document.getElementById('editUserId')) {
            document.getElementById('editUserId').value = device.UserIDFK || '';
        }
        document.getElementById('editDependentId').value = device.DependentIDFK || '';
        
        new bootstrap.Modal(document.getElementById('editDeviceModal')).show();
    }
}

// Delete device
function deleteDevice(deviceId) {
    if (confirm(`Are you sure you want to delete device ${deviceId}?\n\nThis action cannot be undone.`)) {
        // Use API to delete device
        const formData = new FormData();
        formData.append('action', 'delete_device');
        formData.append('serial_no', deviceId);
        
        fetch(dashboardApiUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                
                // Remove from table
                const row = document.querySelector(`tr[data-device-id="${deviceId}"]`);
                if (row) {
                    row.remove();
                }
                
                // Refresh page after a delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting device:', error);
            showToast('Error deleting device', 'error');
        });
    }
}

// Add device
function addDevice() {
    // Reset form
    document.getElementById('addDeviceForm').reset();
    new bootstrap.Modal(document.getElementById('addDeviceModal')).show();
}

// Save device
function saveDevice() {
    const form = document.getElementById('addDeviceForm');
    const formData = new FormData(form);
    
    // Basic validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Add action to form data
    formData.append('action', 'add_device');
    
    // Map form fields to expected API format
    formData.append('serial_no', formData.get('deviceId'));
    formData.append('emergency_no1', formData.get('emergencyNo1'));
    formData.append('emergency_no2', formData.get('emergencyNo2'));
    formData.append('address', formData.get('location'));
    formData.append('existing_dependent', formData.get('dependentId'));
    
    // Use API to save device
    fetch(dashboardApiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('addDeviceModal')).hide();
            showToast(result.message, 'success');
            
            // Refresh page after a delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving device:', error);
        showToast('Error saving device', 'error');
    });
}

// Update device
function updateDevice() {
    const form = document.getElementById('editDeviceForm');
    const formData = new FormData(form);
    
    // Basic validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Simulate updating
    const deviceData = {
        deviceId: formData.get('deviceId'),
        deviceType: formData.get('deviceType'),
        location: formData.get('location'),
        userId: formData.get('userId'),
        dependentId: formData.get('dependentId'),
        emergencyNo1: formData.get('emergencyNo1'),
        emergencyNo2: formData.get('emergencyNo2')
    };
    
    console.log('Updating device:', deviceData);
    
    // Close modal and show success message
    bootstrap.Modal.getInstance(document.getElementById('editDeviceModal')).hide();
    showToast('Device updated successfully!', 'success');
    
    // In a real application, you would make an API call and refresh the table
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Add user (Admin only)
function addUser() {
    // Reset form
    document.getElementById('addUserForm').reset();
    new bootstrap.Modal(document.getElementById('addUserModal')).show();
}

// Save user
function saveUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    // Basic validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Password validation
    const password = formData.get('password');
    const confirmPassword = formData.get('confirmPassword');
    
    if (password !== confirmPassword) {
        showToast('Passwords do not match!', 'error');
        return;
    }
    
    if (password.length < 6) {
        showToast('Password must be at least 6 characters long!', 'error');
        return;
    }
    
    // Simulate saving
    const userData = {
        email: formData.get('email'),
        role: formData.get('role')
    };
    
    console.log('Saving user:', userData);
    
    // Close modal and show success message
    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
    showToast('User created successfully!', 'success');
    
    // In a real application, you would make an API call
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast element
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
    
    // Add to toast container
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Refresh dashboard
function refreshDashboard() {
    location.reload();
}

// Activity helper functions
function getActivityIcon(type) {
    const icons = {
        'emergency': 'bi-exclamation-triangle',
        'device': 'bi-phone',
        'user': 'bi-person',
        'system': 'bi-gear'
    };
    return icons[type] || 'bi-bell';
}

function getActivityColorClass(type) {
    const colors = {
        'emergency': 'text-danger',
        'device': 'text-primary',
        'user': 'text-success',
        'system': 'text-info'
    };
    return colors[type] || 'text-secondary';
}

function formatTimeAgo(datetime) {
    const time = new Date() - new Date(datetime);
    const seconds = Math.floor(time / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 2592000) return Math.floor(seconds / 86400) + ' days ago';
    
    return new Date(datetime).toLocaleDateString();
}
</script>

<style>
/* Activity List Styles */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.activity-icon-wrapper {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 14px;
    margin-bottom: 4px;
}

.activity-description {
    color: #666;
    font-size: 13px;
    margin-bottom: 4px;
}

.activity-time {
    color: #999;
    font-size: 12px;
}
</style>

<?php include '../includes/footer.php'; ?>