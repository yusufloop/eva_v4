<?php


require '../helpers/device_helpers.php';
// require_once './actions/user_functions.php';
require '../actions/dashboard_functions.php';
require '../helpers/component_helper.php'; 


ob_start();

// Page assets
$additionalCSS = ['../assets/css/dashboard.css','../assets/css/components/stats-card.css','../assets/css/components/panel.css',];
$additionalJS = ['../assets/js/dashboard.js', ];

include '../includes/header.php'; 


// Role-based page configuration
if (hasRole('admin')):
    $pageTitle = hasRole('admin') ? 'Admin Dashboard' : 'My Dashboard';
    $dashboardData = getAdminDashboardData();
    $recentActivities = getRecentSystemActivities(10);
    $devices = getAllDevicesWithStatus();
   
elseif (hasRole('user')):
    $pageTitle = 'My Dashboard';
    $dashboardData = getUserDashboardData($userId);
    $recentActivities = getUserRecentActivities($userId, 5);
    $devices = getUserDevicesWithStatus($userId);
endif;
?>



<?php include '../includes/topbar.php'; ?>
<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <?php  include '../includes/sidebar.php'; ?>
    
<!-- Main Content -->
    <div class="main-content">
         <?php  include '../includes/topbar.php'; ?>

        <!-- Alert Messages -->
        <?php # displayAlerts(); ?>

       <div class="stats-grid">
    <?= $templates->render('stats-card', [
        'type' => 'online',
        'icon' => 'fas fa-wifi',
        'title' => 'Online',
        'subtitle' => hasRole('admin') ? $dashboardData['total_devices'] . ' Total' : 'Active',
        'value' => $dashboardData['online_devices']
    ]) ?>
    
    <?= $templates->render('stats-card', [
        'type' => 'offline',
        'icon' => 'fas fa-wifi-slash',
        'title' => 'Offline',
        'subtitle' => hasRole('admin') ? 'Devices' : 'Inactive',
        'value' => $dashboardData['offline_devices']
    ]) ?>
    
    <?= $templates->render('stats-card', [
        'type' => 'total',
        'icon' => 'fas fa-mobile-alt',
        'title' => 'Total Devices',
        'subtitle' => hasRole('admin') ? 'System Wide' : 'Registered',
        'value' => $dashboardData['total_devices']
    ]) ?>
    
    <?= $templates->render('stats-card', [
        'type' => 'emergency',
        'icon' => 'fas fa-exclamation-triangle',
        'title' => 'Active Emergencies',
        'subtitle' => 'Requiring Attention',
        'value' => $dashboardData['active_emergencies']
    ]) ?>
</div>

<?php ob_start(); ?>
        <div class="search-container">
            <input type="text" id="deviceSearchBar" placeholder="Search devices..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>
        <div class="filter-dropdown">
            <select id="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <?php if (hasRole('admin')): ?>
            <button class="btn btn-sm btn-outline" onclick="openBulkActions()">
                <i class="fas fa-cog"></i> Bulk Actions
            </button>
            <button class="btn btn-primary" onclick="openAddDeviceModal()">
                <i class="fas fa-plus"></i> Add Device
            </button>
            <button class="btn btn-secondary" onclick="openAddUserModal()">
                <i class="fas fa-user-plus"></i> Add User
            </button>
        <?php endif; ?>
<?php $devicesHeaderActions = ob_get_clean(); ?>

<?php ob_start(); ?>

<?php if (empty($devices)): ?>
    <div class="empty-state">
        <i class="fas fa-mobile-alt"></i>
        <h3>No Devices Found</h3>
        <p>
            <?php if (hasRole('admin')): ?>
                No devices are currently registered in the system.
            <?php else: ?>
                You haven't registered any devices yet.
            <?php endif; ?>
        </p>
        <button class="btn btn-primary" onclick="openAddDeviceModal()">
            <i class="fas fa-plus"></i> 
            <?= hasRole('admin') ? 'Add Device' : 'Register Device' ?>
        </button>
    </div>
<?php else: ?>
    <div class="device-list">
        <?php foreach ($devices as $device): ?>
            <div class="device-item" data-status="<?= $device['DeviceStatus'] ?>">
                <div class="device-info">
                    <div class="device-header">
                        <span class="device-serial"><?= htmlspecialchars($device['SerialNo']) ?></span>
                        <span class="device-status status-<?= $device['DeviceStatus'] ?>">
                            <i class="fas fa-circle"></i>
                            <?= ucfirst($device['DeviceStatus']) ?>
                        </span>
                    </div>
                    <div class="device-details">
                        <div class="device-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($device['Address'] ?? 'No Location') ?>
                        </div>
                        <?php if (hasRole('admin')): ?>
                            <div class="device-user">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($device['Email']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="device-dependent">
                            <i class="fas fa-users"></i>
                            <?= htmlspecialchars($device['Firstname'] . ' ' . $device['Lastname']) ?>
                        </div>
                    </div>
                </div>
                <div class="device-actions">
                    <button class="btn-icon" onclick="viewDevice('<?= $device['SerialNo'] ?>')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" onclick="editDevice('<?= $device['SerialNo'] ?>')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if (hasRole('admin') || $device['user_id'] == $userId): ?>
                        <button class="btn-icon btn-danger" onclick="deleteDevice('<?= $device['SerialNo'] ?>')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $deviceContent = ob_get_clean();?>

<?php 
echo $templates->render('panel', [
    'icon' => 'fas fa-clock',
    'title' => 'Recent Activities',
    'headerActions' => $devicesHeaderActions,
    'content' => $deviceContent
]);

?>

<!-- Add Device Modal -->
<div id="addDeviceModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3><?= hasRole('admin') ? 'Add Device to System' : 'Register My Device' ?></h3>
            <button class="modal-close" onclick="closeModal('addDeviceModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" class="modal-form">
            <input type="hidden" name="add_device" value="1">
            
            <?php if (hasRole('admin')): ?>
                <!-- Admin can assign to any user -->
                <div class="form-group">
                    <label for="userId">Assign to User:</label>
                    <div class="custom-select">
                        <select name="user_id" id="userId" required>
                            <option value="">Select User</option>
                            <?php foreach (getAllUsers() as $user): ?>
                                <option value="<?= $user['UserID'] ?>">
                                    <?= htmlspecialchars($user['Email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular user - device assigned to themselves -->
                <input type="hidden" name="user_id" value="<?= $userId ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="emergencyNo1">Emergency Number 1:</label>
                    <input type="tel" id="emergencyNo1" name="emergency_no1" placeholder="+60123456789" required>
                </div>
                <div class="form-group">
                    <label for="emergencyNo2">Emergency Number 2:</label>
                    <input type="tel" id="emergencyNo2" name="emergency_no2" placeholder="+60123456789" required>
                </div>
            </div>
            
             <div class="form-group">
                <label for="serialNo">Device Serial Number:</label>
                <input type="text" id="serialNo" name="serial_no" placeholder="Enter device serial number" required>
                <small>Serial number found on your device label</small>
            </div>
            
            <div class="form-group">
                <label for="dependentSelect">Assign to Family Member:</label>
                <select id="dependentSelect" name="dependent_option" required onchange="toggleDependentFields()">
                    <option value="">Select Option</option>
                    <option value="existing">Existing Family Member</option>
                    <option value="new">Add New Family Member</option>
                </select>
            </div>
            
            <!-- Existing Dependent Selection -->
            <div id="existingDependentGroup" class="form-group hidden">
                <label for="existingDependent">Select Family Member:</label>
                <select id="existingDependent" name="existing_dependent">
                    <option value="">Choose family member</option>
                    <?php 
                    $dependents = hasRole('admin') ? getAllDependents() : getUserDependents($userId);
                    foreach ($dependents as $dependent): 
                    ?>
                        <option value="<?= $dependent['DependentID'] ?>">
                            <?= htmlspecialchars($dependent['Firstname'] . ' ' . $dependent['Lastname']) ?>
                            <?php if (isset($dependent['Email'])): ?>
                                (<?= htmlspecialchars($dependent['Email']) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- New Dependent Fields -->
            <div id="newDependentGroup" class="form-group hidden">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="firstname" placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="lastname" placeholder="Last Name">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" placeholder="Full Address">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="postalCode">Postal Code:</label>
                        <input type="text" id="postalCode" name="postal_code" placeholder="12345">
                    </div>
                    <div class="form-group">
                        <label for="medicalCondition">Medical Condition:</label>
                        <input type="text" id="medicalCondition" name="medical_condition" placeholder="Any medical conditions">
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?= hasRole('admin') ? 'Add Device' : 'Register Device' ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDeviceModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add User Modal (Admin Only) -->
<?php if (hasRole('admin')): ?>
<div id="addUserModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Create New User</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/actions/auth/register.php" method="POST" class="modal-form">
            <div class="form-group">
                <label for="userEmail">Email Address:</label>
                <input type="email" id="userEmail" name="email" placeholder="user@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="userPassword">Password:</label>
                <input type="password" id="userPassword" name="newPassword" required>
                <small>Must be 6+ characters with uppercase, number, and special character</small>
            </div>
            
            <div class="form-group">
                <label for="confirmUserPassword">Confirm Password:</label>
                <input type="password" id="confirmUserPassword" name="confirmPassword" required>
            </div>
            
            <div class="form-group">
                <label for="userRole">User Role:</label>
                <select id="userRole" name="user_role">
                    <option value="user">Regular User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Create User
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>