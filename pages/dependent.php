<?php
require '../helpers/device_helpers.php';
require '../actions/dashboard_functions.php';
require '../helpers/component_helper.php';

// Page assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css',
    '../assets/css/components/dashboard-panel.css'
];
$additionalJS = ['../assets/js/dashboard.js', '../assets/js/dependents.js'];

include '../includes/header.php';

// Role-based page configuration
if (hasRole('admin')):
    $pageTitle = 'All Dependents';
    $dependents = getAllDependents();
    $users = getAllUsers(); // For dropdown

    
elseif (hasRole('user')):
    $pageTitle = 'My Family Members';
    $dependents = getUserDependents($userId);
    $users = []; // Regular users don't need user dropdown
endif;
?>

<?php include '../includes/topbar.php'; ?>
<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
      

        <?php
        // Capture header actions
        ob_start();
        ?>
        <div class="search-container">
            <input type="text" id="dependentSearchBar" placeholder="Search dependents..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>
        <div class="filter-dropdown">
            <select id="statusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="with-device">With Device</option>
                <option value="without-device">Without Device</option>
                <option value="medical-condition">Has Medical Condition</option>
            </select>
        </div>
        <?php if (hasRole('admin')): ?>
            <button class="btn btn-sm btn-outline" onclick="openBulkActions()">
                <i class="fas fa-cog"></i> Bulk Actions
            </button>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openAddDependentModal()">
            <i class="fas fa-plus"></i> Add Dependent
        </button>
        <?php
        $headerActions = ob_get_clean();

        // Capture content
        ob_start();
        ?>
        
        <?php if (empty($dependents)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Dependents Found</h3>
                <p>
                    <?php if (hasRole('admin')): ?>
                        No dependents are currently registered in the system.
                    <?php else: ?>
                        You haven't registered any family members yet.
                    <?php endif; ?>
                </p>
                <button class="btn btn-primary" onclick="openAddDependentModal()">
                    <i class="fas fa-plus"></i> 
                    Add Dependent
                </button>
            </div>
        <?php else: ?>
            <div class="dependent-list">
                <?php foreach ($dependents as $dependent): ?>
                    <div class="dependent-item" data-status="<?= !empty($dependent['DeviceCount']) ? 'with-device' : 'without-device' ?>">
                        <div class="dependent-info">
                            <div class="dependent-header">
                                <span class="dependent-name"><?= htmlspecialchars($dependent['Firstname'] . ' ' . $dependent['Lastname']) ?></span>
                                <span class="dependent-status <?= !empty($dependent['DeviceCount']) ? 'status-active' : 'status-inactive' ?>">
                                    <i class="fas fa-circle"></i>
                                    <?= !empty($dependent['DeviceCount']) ? 'Has Device' : 'No Device' ?>
                                </span>
                            </div>
                            <div class="dependent-details">
                                <div class="dependent-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($dependent['Address'] ?? 'No Address') ?>
                                </div>
                                <?php if (hasRole('admin')): ?>
                                    <div class="dependent-user">
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($dependent['UserEmail'] ?? 'No User Assigned') ?>
                                    </div>
                                <?php endif; ?>
                                <div class="dependent-details-row">
                                    <span class="dependent-gender">
                                        <i class="fas fa-<?= $dependent['Gender'] == 'Male' ? 'mars' : 'venus' ?>"></i>
                                        <?= htmlspecialchars($dependent['Gender']) ?>
                                    </span>
                                    <span class="dependent-dob">
                                        <i class="fas fa-birthday-cake"></i>
                                        <?= htmlspecialchars($dependent['DOB']) ?>
                                    </span>
                                </div>
                                <?php if (!empty($dependent['MedicalCondition'])): ?>
                                    <div class="dependent-medical">
                                        <i class="fas fa-heartbeat"></i>
                                        Medical: <?= htmlspecialchars($dependent['MedicalCondition']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dependent-actions">
                            <button class="btn-icon" onclick="viewDependent('<?= $dependent['DependentID'] ?>')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" onclick="editDependent('<?= $dependent['DependentID'] ?>')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if (hasRole('admin') || $dependent['UserIDFK'] == $userId): ?>
                                <button class="btn-icon btn-danger" onclick="deleteDependent('<?= $dependent['DependentID'] ?>')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php
        $content = ob_get_clean();

        // Render the panel
        echo $templates->render('panel', [
            'icon' => 'fas fa-users',
            'title' => $pageTitle,
            'headerActions' => $headerActions,
            'content' => $content
        ]);
        ?>
    </div>
</div>

<!-- Add/Edit Dependent Modal -->
<div id="addDependentModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Dependent</h3>
            <button class="modal-close" onclick="closeModal('addDependentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="dependentForm" method="POST" action="../actions/dependents/add.php" class="modal-form">
            <input type="hidden" name="dependentId" id="dependentId" value="">
            
            <?php if (hasRole('admin')): ?>
                <!-- Admin can assign to any user -->
                <div class="form-group">
                    <label for="userId">Assign to User:</label>
                    <select name="user_id" id="userId" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['UserID'] ?>">
                                <?= htmlspecialchars($user['Email']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <!-- Regular user - assigned to themselves -->
                <input type="hidden" name="user_id" value="<?= $userId ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="Firstname" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="Lastname" placeholder="Last Name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="Gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="DOB" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="Address" placeholder="Full Address" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="postalCode">Postal Code:</label>
                    <input type="text" id="postalCode" name="PostalCode" placeholder="12345" required>
                </div>
                <div class="form-group">
                    <label for="medicalCondition">Medical Condition:</label>
                    <input type="text" id="medicalCondition" name="MedicalCondition" placeholder="Any medical conditions (optional)">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span id="submitButtonText">Add Dependent</span>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDependentModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>