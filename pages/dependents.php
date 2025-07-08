<?php
require '../helpers/device_helpers.php';
require '../actions/dashboard/stats.php';
require '../helpers/component_helper.php';
require_once '../helpers/auth_helper.php';
require_once '../actions/dependent/list.php';

// Page assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css',
    '../assets/css/components/panel.css'
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
            <!-- Table View -->
            <div class="table-container">
                <table class="eva-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Address</th>
                            <th>Postal Code</th>
                            <th>Medical Condition</th>
                            <?php if (hasRole('admin')): ?>
                                <th>User</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dependents as $dependent): ?>
                            <tr data-status="<?= !empty($dependent['DeviceCount']) ? 'with-device' : 'without-device' ?>">
                                <td data-label="First Name">
                                    <div class="cell-content">
                                        <span class="name-text"><?= htmlspecialchars($dependent['Firstname']) ?></span>
                                        <?php if (!empty($dependent['DeviceCount'])): ?>
                                            <span class="device-badge">
                                                <i class="fas fa-mobile-alt"></i>
                                                <?= $dependent['DeviceCount'] ?> device(s)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Last Name">
                                    <?= htmlspecialchars($dependent['Lastname']) ?>
                                </td>
                                <td data-label="Gender">
                                    <span class="gender-badge <?= strtolower($dependent['Gender']) ?>">
                                        <i class="fas fa-<?= $dependent['Gender'] == 'Male' ? 'mars' : 'venus' ?>"></i>
                                        <?= htmlspecialchars($dependent['Gender']) ?>
                                    </span>
                                </td>
                                <td data-label="DOB">
                                    <div class="date-info">
                                        <span class="date"><?= date('d/m/Y', strtotime($dependent['DOB'])) ?></span>
                                        <span class="age"><?= date_diff(date_create($dependent['DOB']), date_create('today'))->y ?> years</span>
                                    </div>
                                </td>
                                <td data-label="Address">
                                    <div class="address-info">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($dependent['Address'] ?? 'No Address') ?>
                                    </div>
                                </td>
                                <td data-label="Postal Code">
                                    <?= htmlspecialchars($dependent['PostalCode'] ?? '-') ?>
                                </td>
                                <td data-label="Medical Condition">
                                    <?php if (!empty($dependent['MedicalCondition'])): ?>
                                        <span class="medical-condition">
                                            <i class="fas fa-heartbeat"></i>
                                            <?= htmlspecialchars($dependent['MedicalCondition']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="no-condition">None</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (hasRole('admin')): ?>
                                    <td data-label="User">
                                        <div class="user-info">
                                            <i class="fas fa-user"></i>
                                            <?= htmlspecialchars($dependent['UserEmail'] ?? 'No User') ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" onclick="viewDependent('<?= $dependent['DependentID'] ?>')" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon btn-edit" onclick="editDependent('<?= $dependent['DependentID'] ?>')" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if (hasRole('admin') || $dependent['UserIDFK'] == $userId): ?>
                                            <button class="btn-icon btn-delete" onclick="deleteDependent('<?= $dependent['DependentID'] ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        
        <form id="dependentForm" method="POST" action="../actions/dependent/add.php" class="modal-form">
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

<style>
/* Table Styles */
.table-container {
    overflow-x: auto;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.eva-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 14px;
}

.eva-table thead {
    background: linear-gradient(135deg, #4285f4, #1976d2);
    color: white;
}

.eva-table th {
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
}

.eva-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.eva-table tbody tr:hover {
    background: rgba(66, 133, 244, 0.02);
}

/* Cell Content Styles */
.cell-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.name-text {
    font-weight: 600;
    color: #2d3748;
}

.device-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(66, 133, 244, 0.1);
    color: #4285f4;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.gender-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.gender-badge.male {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.gender-badge.female {
    background: rgba(236, 72, 153, 0.1);
    color: #ec4899;
}

.date-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date {
    font-weight: 500;
    color: #2d3748;
}

.age {
    font-size: 11px;
    color: #718096;
}

.address-info {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #4a5568;
}

.medical-condition {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #e53e3e;
    font-weight: 500;
}

.no-condition {
    color: #a0aec0;
    font-style: italic;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #4a5568;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
}

.btn-icon {
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

/* Responsive Table */
@media (max-width: 768px) {
    .eva-table,
    .eva-table thead,
    .eva-table tbody,
    .eva-table th,
    .eva-table td,
    .eva-table tr {
        display: block;
    }

    .eva-table thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }

    .eva-table tr {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 16px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .eva-table td {
        border: none;
        padding: 8px 0;
        position: relative;
        padding-left: 35%;
    }

    .eva-table td:before {
        content: attr(data-label) ": ";
        position: absolute;
        left: 0;
        width: 30%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: 600;
        color: #4a5568;
        font-size: 12px;
    }

    .action-buttons {
        justify-content: flex-end;
        margin-top: 12px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>