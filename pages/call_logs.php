<?php
require '../helpers/device_helpers.php';
require '../actions/dashboard_functions.php';
require '../helpers/component_helper.php';
require '../helpers/call_helper.php';

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
    $pageTitle = 'All Call Logs';
    $dependents = getAllDependents();
    $users = getAllUsers(); // For dropdown
    $callHistories = getAllCallHistories();

   
    
elseif (hasRole('user')):
    $pageTitle = 'Call Logs';
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
            <div class="table-responsive">
    <table class="table table-hover eva-table">
        <thead class="table-dark">
            <tr>
                <th scope="col">
                    <i class="bi bi-phone me-2"></i>Device Serial
                </th>
                <th scope="col">
                    <i class="bi bi-geo-alt me-2"></i>Address
                </th>
                <th scope="col">
                    <i class="bi bi-tag me-2"></i>Type
                </th>
                <?php if (hasRole('admin')): ?>
                    <th scope="col">
                        <i class="bi bi-person me-2"></i>User
                    </th>
                <?php endif; ?>
                <th scope="col">
                    <i class="bi bi-clock me-2"></i>Duration
                </th>
                <th scope="col">
                    <i class="bi bi-calendar me-2"></i>Timestamp
                </th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($callHistories as $callHistory): ?>
                <tr class="align-middle" data-record-id="<?= $callHistory['RecordID'] ?>">
                    <!-- Device Serial -->
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="device-info">
                                <span class="fw-bold text-primary"><?= htmlspecialchars($callHistory['SerialNoFK']) ?></span>
                                <?php if (!empty($callHistory['Status']) && in_array($callHistory['Status'], ['Unanswered', 'Active'])): ?>
                                    <span class="badge bg-danger ms-2">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Emergency
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Address -->
                    <td>
                        <div class="address-info">
                            <i class="bi bi-geo-alt text-muted me-2"></i>
                            <span><?= htmlspecialchars($callHistory['Address'] ?? 'Unknown Location') ?></span>
                        </div>
                    </td>
                    
                    <!-- Type (Call Direction & Status) -->
                    <td>
                        <div class="d-flex flex-column">
                            <!-- Direction Badge -->
                            <span class="badge <?= $callHistory['Direction'] == 'Incoming' ? 'bg-success' : 'bg-primary' ?> mb-1">
                                <i class="bi bi-<?= $callHistory['Direction'] == 'Incoming' ? 'arrow-down-left' : 'arrow-up-right' ?> me-1"></i>
                                <?= htmlspecialchars($callHistory['Direction']) ?>
                            </span>
                            <!-- Status Badge -->
                            <?php
                            $statusClass = match($callHistory['Status']) {
                                'Unanswered' => 'bg-danger',
                                'Active' => 'bg-warning text-dark',
                                'Resolved' => 'bg-info',
                                'Cancelled' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= htmlspecialchars($callHistory['Status']) ?>
                            </span>
                        </div>
                    </td>
                    
                    <!-- User (Admin only) -->
                    <?php if (hasRole('admin')): ?>
                        <td>
                            <div class="user-info">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($callHistory['Firstname'] . ' ' . $callHistory['Lastname']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($callHistory['UserEmail'] ?? 'No Email') ?></small>
                                    </div>
                                </div>
                            </div>
                        </td>
                    <?php endif; ?>
                    
                    <!-- Duration -->
                    <td>
                        <div class="duration-info">
                            <i class="bi bi-stopwatch text-muted me-2"></i>
                            <span class="fw-semibold"><?= htmlspecialchars($callHistory['Duration'] ?? '0:00') ?></span>
                        </div>
                    </td>
                    
                    <!-- Timestamp -->
                    <td>
                        <div class="timestamp-info">
                            <?php 
                            $datetime = $callHistory['Datetime'];
                            // Handle different datetime formats
                            if (strpos($datetime, ',') !== false) {
                                // Format: "24-10-21,16:13:52"
                                $datetime = preg_replace('/\+\d+$/', '', $datetime);
                                $dateTime = DateTime::createFromFormat('d-m-y,H:i:s', $datetime);
                            } else {
                                // Standard format
                                $dateTime = new DateTime($datetime);
                            }
                            
                            if ($dateTime): ?>
                                <div class="fw-semibold"><?= $dateTime->format('d/m/Y') ?></div>
                                <small class="text-muted"><?= $dateTime->format('H:i:s') ?></small>
                            <?php else: ?>
                                <span class="text-muted"><?= htmlspecialchars($callHistory['Datetime']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    
                </tr>
            <?php endforeach; ?>
            
            <!-- Empty state -->
            <?php if (empty($callHistories)): ?>
                <tr>
                    <td colspan="<?= hasRole('admin') ? '7' : '6' ?>" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-telephone-x display-4 mb-3"></i>
                            <h5>No Call Logs Found</h5>
                            <p>No call history records match your criteria.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
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