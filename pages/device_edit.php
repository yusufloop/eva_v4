<?php
// Page configuration
$pageTitle = 'Edit Device';
$currentPage = 'devices';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';
require_once '../helpers/device_helpers.php';

// Check authentication
requireAuth();

// Get current user info
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Get device serial number
$serialNo = $_GET['serial_no'] ?? '';

if (empty($serialNo)) {
    $_SESSION['error_message'] = 'Device serial number is required';
    header('Location: dashboard.php');
    exit;
}

// Get device details
$device = null;
try {
    $pdo = getDatabase();
    
    if ($isAdmin) {
        $sql = "SELECT e.*, u.useremail, dep.fullname, 
                       i.serial_no, i.device_type, i.inventory_id, i.is_registered,
                       e.family_contact1, e.family_contact2, e.reg_date
                FROM eva_info e
                LEFT JOIN users u ON e.user_id = u.user_id
                LEFT JOIN dependants dep ON e.dep_id = dep.dep_id
                LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
                WHERE i.serial_no = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$serialNo]);
    } else {
        $sql = "SELECT e.*, u.useremail, dep.fullname, 
                       i.serial_no, i.device_type, i.inventory_id, i.is_registered,
                       e.family_contact1, e.family_contact2, e.reg_date
                FROM eva_info e
                LEFT JOIN users u ON e.user_id = u.user_id
                LEFT JOIN dependants dep ON e.dep_id = dep.dep_id
                LEFT JOIN inventory i ON e.inventory_id = i.inventory_id
                WHERE i.serial_no = ? AND e.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$serialNo, $userId]);
    }
    
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$device) {
        $_SESSION['error_message'] = 'Device not found or access denied';
        header('Location: dashboard.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Device fetch error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error fetching device details';
    header('Location: dashboard.php');
    exit;
}

// Get all users and dependents for dropdowns
$users = [];
$dependents = [];

try {
    // Get all users (admin only)
    if ($isAdmin) {
        $usersSql = "SELECT user_id, useremail FROM users ORDER BY useremail";
        $usersStmt = $pdo->prepare($usersSql);
        $usersStmt->execute();
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get dependents (all for admin, user's only for regular users)
    if ($isAdmin) {
        $dependentsSql = "SELECT d.dep_id, d.fullname, u.useremail 
                         FROM dependants d 
                         LEFT JOIN users u ON d.user_id = u.user_id 
                         ORDER BY d.fullname";
        $dependentsStmt = $pdo->prepare($dependentsSql);
        $dependentsStmt->execute();
        $dependents = $dependentsStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $dependentsSql = "SELECT dep_id, fullname FROM dependants WHERE user_id = ? ORDER BY fullname";
        $dependentsStmt = $pdo->prepare($dependentsSql);
        $dependentsStmt->execute([$userId]);
        $dependents = $dependentsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error fetching users/dependents: " . $e->getMessage());
}

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
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Device Details', 'url' => '//device/view/' . urlencode($serialNo)],
    ['title' => 'Edit Device', 'url' => '#']
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
        
        <!-- Device Edit Header Card -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="device-icon">
                            ✏️
                        </div>
                        <div class="header-text">
                            <h2>Edit Device: <?= htmlspecialchars($device['serial_no']) ?></h2>
                            <p>Update device information and settings</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="header-actions">
                        <button class="btn btn-secondary" onclick="window.location.href='//device/view/<?= urlencode($serialNo) ?>'">
                            <i class="bi bi-eye me-1"></i>View Device
                        </button>
                        <button class="btn btn-outline" onclick="window.location.href='//pages/dashboard.php'">
                            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <form id="editDeviceForm" method="POST">
            <div class="row">
                <!-- Device Information Card -->
                <div class="col-md-6">
                    <div class="eva-card">
                        <div class="card-header">
                            <h3>Device Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="serial_no" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_no" name="serial_no" 
                                       value="<?= htmlspecialchars($device['serial_no']) ?>" readonly>
                                <div class="form-text">Device serial number cannot be changed</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="device_type" class="form-label">Device Type</label>
                                <select class="form-select" id="device_type" name="device_type">
                                    <option value="EVA-Standard" <?= ($device['DeviceType'] === 'EVA-Standard') ? 'selected' : '' ?>>EVA Standard</option>
                                    <option value="EVA-Pro" <?= ($device['DeviceType'] === 'EVA-Pro') ? 'selected' : '' ?>>EVA Pro</option>
                                    <option value="EVA-Mini" <?= ($device['DeviceType'] === 'EVA-Mini') ? 'selected' : '' ?>>EVA Mini</option>
                                    <option value="Heart Rate Monitor" <?= ($device['DeviceType'] === 'Heart Rate Monitor') ? 'selected' : '' ?>>Heart Rate Monitor</option>
                                    <option value="GPS Tracker" <?= ($device['DeviceType'] === 'GPS Tracker') ? 'selected' : '' ?>>GPS Tracker</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="telemed_contact" class="form-label">Telemedicine Contact</label>
                                <input type="tel" class="form-control" id="telemed_contact" name="telemed_contact" 
                                       value="<?= htmlspecialchars($device['telemed_contact'] ?? '') ?>" 
                                       placeholder="Enter telemedicine contact number">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contacts Card -->
                <div class="col-md-6">
                    <div class="eva-card">
                        <div class="card-header">
                            <h3>Emergency Contacts</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="family_contact1" class="form-label">Emergency Contact 1 *</label>
                                <input type="tel" class="form-control" id="family_contact1" name="family_contact1" 
                                       value="<?= htmlspecialchars($device['family_contact1'] ?? '') ?>" 
                                       placeholder="Enter first emergency contact" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact1_rel" class="form-label">Relationship 1</label>
                                <input type="text" class="form-control" id="contact1_rel" name="contact1_rel" 
                                       value="<?= htmlspecialchars($device['contact1_rel'] ?? 'Emergency Contact') ?>" 
                                       placeholder="e.g., Spouse, Child, Caregiver">
                            </div>
                            
                            <div class="form-group">
                                <label for="family_contact2" class="form-label">Emergency Contact 2 *</label>
                                <input type="tel" class="form-control" id="family_contact2" name="family_contact2" 
                                       value="<?= htmlspecialchars($device['family_contact2'] ?? '') ?>" 
                                       placeholder="Enter second emergency contact" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact2_rel" class="form-label">Relationship 2</label>
                                <input type="text" class="form-control" id="contact2_rel" name="contact2_rel" 
                                       value="<?= htmlspecialchars($device['contact2_rel'] ?? 'Emergency Contact') ?>" 
                                       placeholder="e.g., Doctor, Friend, Neighbor">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Information Card -->
            <?php if ($isAdmin): ?>
            <div class="eva-card">
                <div class="card-header">
                    <h3>Assignment Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id" class="form-label">Assigned User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Select User</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['user_id'] ?>" 
                                                <?= ($device['user_id'] == $user['user_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['useremail']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dep_id" class="form-label">Assigned Dependent *</label>
                                <select class="form-select" id="dep_id" name="dep_id" required>
                                    <option value="">Select Dependent</option>
                                    <?php foreach ($dependents as $dependent): ?>
                                        <option value="<?= $dependent['dep_id'] ?>" 
                                                <?= ($device['dep_id'] == $dependent['dep_id']) ? 'selected' : '' ?>
                                                data-user="<?= htmlspecialchars($dependent['useremail'] ?? '') ?>">
                                            <?= htmlspecialchars($dependent['fullname']) ?>
                                            <?php if (!empty($dependent['useremail'])): ?>
                                                (<?= htmlspecialchars($dependent['useremail']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Non-admin users can only change dependent -->
            <div class="eva-card">
                <div class="card-header">
                    <h3>Assignment Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Assigned User</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($device['UserEmail'] ?? 'You') ?>" readonly>
                                <input type="hidden" name="user_id" value="<?= $userId ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dep_id" class="form-label">Assigned Dependent *</label>
                                <select class="form-select" id="dep_id" name="dep_id" required>
                                    <option value="">Select Dependent</option>
                                    <?php foreach ($dependents as $dependent): ?>
                                        <option value="<?= $dependent['dep_id'] ?>" 
                                                <?= ($device['dep_id'] == $dependent['dep_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dependent['fullname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Form Actions -->
            <div class="eva-card">
                <div class="card-body">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <button type="button" class="btn btn-outline" onclick="window.location.href='//device/view/<?= urlencode($serialNo) ?>'">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Device Edit Specific Styles - Following your blue-white theme */
.main-content {
    flex: 1;
    padding: 20px;
    transition: margin-left 0.3s ease;
    position: relative;
    z-index: 1;
}

/* Form styling */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.form-control, .form-select {
    padding: 12px 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus, .form-select:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    outline: none;
    background: white;
}

.form-control:read-only {
    background: rgba(247, 250, 252, 0.8);
    cursor: not-allowed;
}

.form-text {
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
    font-style: italic;
}

/* Full width layout */
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

.device-icon {
    font-size: 24px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
}

.header-text h2 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}

.header-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.row {
    margin: 0 -12.5px;
}

.col-md-6 {
    padding: 0 12.5px;
    margin-bottom: 25px;
}

/* Card headers */
.eva-card .card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: white;
    font-family: 'Inter', sans-serif;
}

/* Form actions */
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
    align-items: center;
    padding: 20px 0;
}

/* Button improvements following your theme */
.btn {
    padding: 12px 24px;
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
    min-width: 120px;
    justify-content: center;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-primary {
    background: #4299e1;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #3182ce;
}

.btn-secondary {
    background: #a0aec0;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #718096;
}

.btn-outline {
    background: transparent;
    color: #4299e1;
    border: 1px solid #4299e1;
}

.btn-outline:hover:not(:disabled) {
    background: #4299e1;
    color: white;
}

/* Loading state */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    color: white;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .main-content {
        padding: 15px;
    }
    
    .eva-card {
        margin-bottom: 20px;
    }
    
    .eva-card .card-header {
        padding: 15px 20px;
    }
    
    .eva-card .card-body {
        padding: 20px 15px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .header-left {
        gap: 12px;
    }
    
    .header-text h2 {
        font-size: 20px;
    }
    
    .header-actions {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    
    .header-actions .btn {
        justify-content: center;
        width: 100%;
    }
    
    .row {
        margin: 0 -10px;
    }
    
    .col-md-6 {
        padding: 0 10px;
        margin-bottom: 20px;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .form-actions .btn {
        width: 100%;
        min-width: auto;
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
    
    .device-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .header-text h2 {
        font-size: 18px;
    }
    
    .form-control, .form-select {
        padding: 10px 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editDeviceForm');
    const saveBtn = document.getElementById('saveBtn');
    
    // Store original form data for reset functionality
    const originalFormData = new FormData(form);
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveDevice();
    });
    
    // Auto-sync user selection with dependent selection (admin only)
    <?php if ($isAdmin): ?>
    const userSelect = document.getElementById('user_id');
    const depSelect = document.getElementById('dep_id');
    
    if (userSelect && depSelect) {
        userSelect.addEventListener('change', function() {
            const selectedUserId = this.value;
            
            // Filter dependents based on selected user
            Array.from(depSelect.options).forEach(option => {
                if (option.value === '') return; // Skip empty option
                
                const optionUser = option.dataset.user;
                if (!selectedUserId || optionUser === document.querySelector(`#user_id option[value="${selectedUserId}"]`).textContent) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Clear dependent selection if current selection doesn't match user
            if (depSelect.value) {
                const currentDepOption = depSelect.querySelector(`option[value="${depSelect.value}"]`);
                if (currentDepOption && currentDepOption.style.display === 'none') {
                    depSelect.value = '';
                }
            }
        });
    }
    <?php endif; ?>
});

function saveDevice() {
    const form = document.getElementById('editDeviceForm');
    const saveBtn = document.getElementById('saveBtn');
    const formData = new FormData(form);
    
    // Add action to form data
    formData.append('action', 'update');
    
    // Validate required fields
    if (!formData.get('family_contact1') || !formData.get('family_contact2') || !formData.get('dep_id')) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.classList.add('loading');
    
    fetch('//actions/device/index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = '//device/view/<?= urlencode($serialNo) ?>';
            }, 1500);
        } else {
            showToast(result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating device:', error);
        showToast('Error updating device', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.classList.remove('loading');
    });
}

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('editDeviceForm').reset();
        
        // Reset to original values (you could store these in data attributes or hidden fields)
        // For now, just reload the page
        window.location.reload();
    }
}

// Show toast notification (reuse from dashboard)
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