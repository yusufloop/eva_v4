<?php
// Page configuration
$pageTitle = 'Call Logs';
$currentPage = 'call_logs';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';
require_once '../helpers/call_helper.php';

// Check authentication
requireAuth();

// Get current user info
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Page-specific assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css',
    '../assets/css/call-logs.css'
];

$additionalJS = [
    '../assets/js/dashboard.js',
    '../assets/js/call-logs.js'
];

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Call Logs', 'url' => '#']
];

// Get call logs data based on user role
if ($isAdmin) {
    $callLogs = getAllCallHistories();
    $stats = getCallHistoryStats();
} else {
    $callLogs = getUserCallHistories($userId);
    $stats = getCallHistoryStats($userId);
}

// Filter options for search
$statusOptions = [
    '' => 'All Status',
    'Unanswered' => 'Unanswered',
    'Active' => 'Active', 
    'Resolved' => 'Resolved',
    'Cancelled' => 'Cancelled'
];

$directionOptions = [
    '' => 'All Directions',
    'Incoming' => 'Incoming',
    'Outgoing' => 'Outgoing'
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
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-telephone"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Total Calls</span>
                        <span class="total-text">All Time</span>
                    </div>
                    <div class="stat-number total">
                        <?= $stats['total_calls'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon emergency">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Unanswered</span>
                        <span class="total-text">Needs Attention</span>
                    </div>
                    <div class="stat-number emergency">
                        <?= $stats['unanswered_calls'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon online">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Resolved</span>
                        <span class="total-text">Completed</span>
                    </div>
                    <div class="stat-number online">
                        <?= $stats['resolved_calls'] ?? 0 ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon offline">
                    <i class="bi bi-arrow-down-up"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Incoming</span>
                        <span class="total-text">vs Outgoing</span>
                    </div>
                    <div class="stat-number offline">
                        <?= $stats['incoming_calls'] ?? 0 ?>/<?= $stats['outgoing_calls'] ?? 0 ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Logs Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <h2 class="page-title">
                        <i class="bi bi-telephone-fill me-2"></i>
                        <?= $isAdmin ? 'All Call Logs' : 'My Call Logs' ?>
                    </h2>
                    
                    <!-- Search and Filter Controls -->
                    <div class="header-actions">
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Search calls..." class="form-control">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        
                        <select id="statusFilter" class="form-control">
                            <?php foreach ($statusOptions as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select id="directionFilter" class="form-control">
                            <?php foreach ($directionOptions as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button class="btn btn-outline-primary" onclick="exportCallLogs()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($callLogs)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="bi bi-telephone-x display-1 text-muted"></i>
                        <h3 class="mt-3">No Call Logs Found</h3>
                        <p class="text-muted">
                            <?= $isAdmin ? 'No call history records are available in the system.' : 'You don\'t have any call history yet.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Call Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-hover call-logs-table" id="callLogsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <i class="bi bi-phone me-1"></i>
                                        Device
                                    </th>
                                    <th>
                                        <i class="bi bi-person me-1"></i>
                                        Contact
                                    </th>
                                    <th>
                                        <i class="bi bi-arrow-up-down me-1"></i>
                                        Type
                                    </th>
                                    <th>
                                        <i class="bi bi-clock me-1"></i>
                                        Duration
                                    </th>
                                    <th>
                                        <i class="bi bi-calendar me-1"></i>
                                        Date & Time
                                    </th>
                                    <th>
                                        <i class="bi bi-geo-alt me-1"></i>
                                        Location
                                    </th>
                                    <?php if ($isAdmin): ?>
                                        <th>
                                            <i class="bi bi-person-circle me-1"></i>
                                            User
                                        </th>
                                    <?php endif; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($callLogs as $log): ?>
                                    <tr data-record-id="<?= $log['RecordID'] ?>" 
                                        data-status="<?= strtolower($log['Status']) ?>"
                                        data-direction="<?= strtolower($log['Direction']) ?>">
                                        
                                        <!-- Device Serial -->
                                        <td>
                                            <div class="device-info">
                                                <span class="device-serial"><?= htmlspecialchars($log['SerialNoFK']) ?></span>
                                                <?php if (!empty($log['DeviceType'])): ?>
                                                    <small class="device-type text-muted d-block">
                                                        <?= htmlspecialchars($log['DeviceType']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Contact Info -->
                                        <td>
                                            <div class="contact-info">
                                                <div class="contact-name fw-semibold">
                                                    <?= htmlspecialchars($log['Firstname'] . ' ' . $log['Lastname']) ?>
                                                </div>
                                                <div class="contact-number text-muted small">
                                                    <?= htmlspecialchars($log['Number'] ?? 'Unknown') ?>
                                                </div>
                                                <?php if (!empty($log['MedicalCondition'])): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-heart-pulse"></i>
                                                        Medical
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Call Type & Status -->
                                        <td>
                                            <div class="call-type-status">
                                                <!-- Direction Badge -->
                                                <span class="badge <?= $log['Direction'] == 'Incoming' ? 'bg-success' : 'bg-primary' ?> mb-1">
                                                    <i class="bi bi-<?= $log['Direction'] == 'Incoming' ? 'arrow-down-left' : 'arrow-up-right' ?>"></i>
                                                    <?= $log['Direction'] ?>
                                                </span>
                                                
                                                <!-- Status Badge -->
                                                <?php
                                                $statusClass = match($log['Status']) {
                                                    'Unanswered' => 'bg-danger',
                                                    'Active' => 'bg-warning text-dark',
                                                    'Resolved' => 'bg-success',
                                                    'Cancelled' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($log['Status']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <!-- Duration -->
                                        <td>
                                            <div class="duration-display">
                                                <i class="bi bi-stopwatch text-muted me-1"></i>
                                                <span class="duration-time">
                                                    <?= htmlspecialchars($log['Duration'] ?? '0:00') ?>
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <!-- Date & Time -->
                                        <td>
                                            <div class="datetime-info">
                                                <?php 
                                                $datetime = $log['Datetime'];
                                                // Handle different datetime formats
                                                if (strpos($datetime, ',') !== false) {
                                                    $datetime = preg_replace('/\+\d+$/', '', $datetime);
                                                    $dateTime = DateTime::createFromFormat('d-m-y,H:i:s', $datetime);
                                                } else {
                                                    $dateTime = new DateTime($datetime);
                                                }
                                                
                                                if ($dateTime): ?>
                                                    <div class="call-date fw-semibold">
                                                        <?= $dateTime->format('d/m/Y') ?>
                                                    </div>
                                                    <div class="call-time text-muted small">
                                                        <?= $dateTime->format('H:i:s') ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <?= htmlspecialchars($log['Datetime']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Location -->
                                        <td>
                                            <div class="location-info">
                                                <i class="bi bi-geo-alt text-muted me-1"></i>
                                                <span class="location-text">
                                                    <?= htmlspecialchars($log['Address'] ?? 'Unknown Location') ?>
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <!-- User (Admin only) -->
                                        <?php if ($isAdmin): ?>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div class="user-details">
                                                        <div class="user-email small">
                                                            <?= htmlspecialchars($log['UserEmail'] ?? 'No User') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <!-- Actions -->
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewCallDetails('<?= $log['RecordID'] ?>')"
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if ($log['Status'] == 'Unanswered' || $log['Status'] == 'Active'): ?>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="markAsResolved('<?= $log['RecordID'] ?>')"
                                                            title="Mark as Resolved">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($isAdmin): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteCallLog('<?= $log['RecordID'] ?>')"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <nav aria-label="Call logs pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
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

<!-- Call Details Modal -->
<div class="modal fade" id="callDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-telephone-fill me-2"></i>
                    Call Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="callDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>