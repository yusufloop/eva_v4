<?php
// Page configuration
$pageTitle = 'Device Details';
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
$device = getDeviceBySerial($serialNo, $isAdmin ? null : $userId);

if (!$device) {
    $_SESSION['error_message'] = 'Device not found or access denied';
    header('Location: dashboard.php');
    exit;
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
    ['title' => 'Device Details', 'url' => '#']
];

// Get device call history
$pdo = getDatabase();
try {
    $callHistorySql = "SELECT 
                        ch.call_id,
                        ch.call_date,
                        ch.number,
                        ch.direction,
                        ch.status,
                        ch.duration
                       FROM call_histories ch
                       JOIN eva_info e ON ch.eva_id = e.eva_id
                       JOIN inventory i ON e.inventory_id = i.inventory_id
                       WHERE i.serial_no = ?
                       ORDER BY ch.call_date DESC
                       LIMIT 10";
    
    $params = [$serialNo];
    if (!$isAdmin) {
        $callHistorySql = str_replace('WHERE i.serial_no = ?', 'WHERE i.serial_no = ? AND e.user_id = ?', $callHistorySql);
        $params[] = $userId;
    }
    
    $callStmt = $pdo->prepare($callHistorySql);
    $callStmt->execute($params);
    $callHistory = $callStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Device call history error: " . $e->getMessage());
    $callHistory = [];
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
        
        <!-- Device Header Card -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="device-icon">
                            📱
                        </div>
                        <div class="header-text">
                            <h2>Device: <?= htmlspecialchars($device['serial_no']) ?></h2>
                            <p><?= htmlspecialchars($device['DeviceType'] ?? 'EVA Device') ?></p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="window.location.href='//device/edit/<?= urlencode($serialNo) ?>'">
                            <i class="bi bi-pencil me-1"></i>Edit Device
                        </button>
                        <button class="btn btn-danger" onclick="deleteDevice('<?= htmlspecialchars($serialNo) ?>')">
                            <i class="bi bi-trash me-1"></i>Delete Device
                        </button>
                        <button class="btn btn-secondary" onclick="window.location.href='//pages/dashboard.php'">
                            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Information Cards -->
        <div class="row">
            <!-- Device Status Card -->
            <div class="col-md-6">
                <div class="eva-card">
                    <div class="card-header">
                        <h3>Device Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="status-info">
                            <div class="status-item">
                                <span class="status-label">Current Status:</span>
                                <span class="status-badge status-<?= $device['status'] ?>">
                                    <i class="bi bi-circle-fill me-1"></i>
                                    <?= ucfirst($device['status']) ?>
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Registration Status:</span>
                                <span class="status-badge status-<?= ($device['is_registered'] ?? 0) ? 'active' : 'inactive' ?>">
                                    <?= ($device['is_registered'] ?? 0) ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Last Seen:</span>
                                <span class="status-value">
                                    <?php 
                                    if ($device['lastseen']) {
                                        $lastSeen = new DateTime($device['lastseen']);
                                        echo $lastSeen->format('M j, Y H:i:s');
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Registered Date:</span>
                                <span class="status-value">
                                    <?php 
                                    if ($device['RegisteredDate'] ?? null) {
                                        $regDate = new DateTime($device['RegisteredDate']);
                                        echo $regDate->format('M j, Y H:i:s');
                                    } else {
                                        echo 'Not registered';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="col-md-6">
                <div class="eva-card">
                    <div class="card-header">
                        <h3>Emergency Contacts</h3>
                    </div>
                    <div class="card-body">
                        <div class="contact-info">
                            <div class="contact-item">
                                <span class="contact-label">Emergency Contact 1:</span>
                                <span class="contact-value">
                                    <?= htmlspecialchars($device['family_contact1'] ?? 'Not set') ?>
                                </span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Emergency Contact 2:</span>
                                <span class="contact-value">
                                    <?= htmlspecialchars($device['family_contact2'] ?? 'Not set') ?>
                                </span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-label">Telemedicine Contact:</span>
                                <span class="contact-value">
                                    <?= htmlspecialchars($device['telemed_contact'] ?? 'Not set') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Information Card -->
        <div class="eva-card">
            <div class="card-header">
                <h3>Assignment Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="assignment-info">
                            <div class="assignment-item">
                                <span class="assignment-label">Assigned User:</span>
                                <span class="assignment-value">
                                    <?= htmlspecialchars($device['UserEmail'] ?? 'Not assigned') ?>
                                </span>
                            </div>
                            <div class="assignment-item">
                                <span class="assignment-label">Family Member:</span>
                                <span class="assignment-value">
                                    <?= htmlspecialchars($device['Firstname'] ?? 'Not assigned') ?>
                                </span>
                            </div>
                            <div class="assignment-item">
                                <span class="assignment-label">Gender:</span>
                                <span class="assignment-value">
                                    <?= htmlspecialchars($device['Gender'] ?? 'Not specified') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="assignment-info">
                            <div class="assignment-item">
                                <span class="assignment-label">Date of Birth:</span>
                                <span class="assignment-value">
                                    <?php 
                                    if ($device['DOB'] ?? null) {
                                        $dob = new DateTime($device['DOB']);
                                        echo $dob->format('M j, Y');
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="assignment-item">
                                <span class="assignment-label">Address:</span>
                                <span class="assignment-value">
                                    <?= htmlspecialchars($device['Address'] ?? 'Not specified') ?>
                                </span>
                            </div>
                            <div class="assignment-item">
                                <span class="assignment-label">Medical Condition:</span>
                                <span class="assignment-value">
                                    <?= htmlspecialchars($device['MedicalCondition'] ?? 'None specified') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call History Card -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <h3>Recent Call History</h3>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline" onclick="window.location.href='call_logs.php'">
                            <i class="bi bi-list me-1"></i>View All Calls
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($callHistory)): ?>
                    <div class="empty-state">
                        <i class="bi bi-telephone-x display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No call history available for this device.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Number</th>
                                    <th>Direction</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($callHistory as $call): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $callDate = new DateTime($call['call_date']);
                                            echo $callDate->format('M j, Y H:i:s');
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($call['number'] ?? 'Unknown') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $call['direction'] === 'Incoming' ? 'primary' : 'success' ?>">
                                                <?= htmlspecialchars($call['direction']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($call['status']) ?>">
                                                <?= htmlspecialchars($call['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($call['duration'] ?? '0:00') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Device View Specific Styles - Following your blue-white theme */
.main-content {
    flex: 1;
    padding: 20px;
    transition: margin-left 0.3s ease;
    position: relative;
    z-index: 1;
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

.status-info, .contact-info, .assignment-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.status-item, .contact-item, .assignment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: rgba(247, 250, 252, 0.5);
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s ease;
}

.status-item:hover, .contact-item:hover, .assignment-item:hover {
    background: rgba(237, 242, 247, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.status-label, .contact-label, .assignment-label {
    font-weight: 600;
    color: #2d3748;
    flex: 1;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.status-value, .contact-value, .assignment-value {
    flex: 1;
    text-align: right;
    color: #1a202c;
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
    font-family: 'Inter', sans-serif;
}

.status-online {
    background: #48bb78;
    color: white;
}

.status-offline {
    background: #e53e3e;
    color: white;
}

.status-active {
    background: #4299e1;
    color: white;
}

.status-inactive {
    background: #a0aec0;
    color: white;
}

.status-unanswered {
    background: #ed8936;
    color: white;
}

.status-resolved {
    background: #48bb78;
    color: white;
}

/* Card headers */
.eva-card .card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: white;
    font-family: 'Inter', sans-serif;
}

/* Call history table improvements */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.table {
    margin: 0;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.table thead th {
    background: rgba(247, 250, 252, 0.8);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    font-weight: 600;
    padding: 16px;
    color: #2d3748;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(226, 232, 240, 0.3);
}

.table tbody tr:hover {
    background: rgba(247, 250, 252, 0.5);
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    color: #a0aec0;
    margin-bottom: 16px;
}

.empty-state p {
    color: #718096;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
}

/* Button improvements following your theme */
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

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover {
    background: #c53030;
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
    
    .status-item, .contact-item, .assignment-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 15px;
    }
    
    .status-value, .contact-value, .assignment-value {
        text-align: left;
        font-weight: 600;
    }
    
    .status-label, .contact-label, .assignment-label {
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .table thead th {
        padding: 12px 8px;
        font-size: 12px;
    }
    
    .table tbody td {
        padding: 12px 8px;
        font-size: 13px;
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
    
    .status-item, .contact-item, .assignment-item {
        padding: 12px;
    }
}
</style>

<script>
function editDevice(serialNo) {
    window.location.href = '//device/edit/' + encodeURIComponent(serialNo);
}

function deleteDevice(serialNo) {
    if (confirm('Are you sure you want to delete this device?\n\nThis action cannot be undone and will also delete all associated call history.')) {
        fetch('//actions/device/index.php?action=delete&serial_no=' + encodeURIComponent(serialNo), {
            method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
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