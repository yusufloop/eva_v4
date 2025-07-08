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
                        <span class="status-text">Active</span>
                        <span class="total-text">In Progress</span>
                    </div>
                    <div class="stat-number offline">
                        <?= $stats['active_calls'] ?? 0 ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Logs Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="call-log-icon">
                            📞
                        </div>
                        <div class="header-text">
                            <h2>Call Log</h2>
                            <p>Call Log activities</p>
                        </div>
                    </div>
                    
                    <!-- Filter Controls -->
                    <div class="header-actions">
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">Filter</option>
                                <option value="unanswered">Unanswered</option>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <select id="periodFilter" class="filter-select">
                                <option value="30">Last 30 Days</option>
                                <option value="7">Last 7 Days</option>
                                <option value="1">Today</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
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
                    <!-- Device List Header -->
                    <div class="device-list-header">
                        <h3>Device List</h3>
                    </div>

                    <!-- Call Logs Table -->
                    <div class="device-table-container">
                        <table class="device-table">
                            <thead>
                                <tr>
                                    <th>Device Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Duration</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($callLogs as $index => $log): ?>
                                    <tr data-record-id="<?= $log['RecordID'] ?>" 
                                        data-status="<?= strtolower($log['Status']) ?>"
                                        data-direction="<?= strtolower($log['Direction']) ?>">
                                        
                                        <!-- Device Name -->
                                        <td>
                                            <div class="device-name-cell">
                                                <!-- <div class="device-name"><?= htmlspecialchars($log['SerialNoFK']) ?></div> -->
                                                <div class="device-name">Serial: <?= htmlspecialchars($log['SerialNoFK']) ?></div>
                                            </div>
                                        </td>
                                        
                                        <!-- Location -->
                                        <td>
                                            <div class="location-cell">
                                                <div class="location-main"><?= htmlspecialchars($log['Address'] ?? 'Unknown Location') ?></div>
                                                <div class="location-sub">Floor 2, West Wing</div>
                                            </div>
                                        </td>
                                        
                                        <!-- Type (Status) -->
                                        <td>
                                            <?php
                                            $statusClass = match(strtolower($log['Status'])) {
                                                'cancelled' => 'status-cancelled',
                                                'unanswered' => 'status-unanswered', 
                                                'resolved' => 'status-resolved',
                                                'active' => 'status-active',
                                                default => 'status-default'
                                            };
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= htmlspecialchars($log['Status']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- User -->
                                        <td>
                                            <div class="user-name">
                                                <?= htmlspecialchars($log['Firstname'] . ' ' . $log['Lastname']) ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Duration -->
                                        <td>
                                            <div class="duration">
                                                <?= htmlspecialchars($log['Duration'] ?? '0:00') ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Timestamp -->
                                        <td>
                                            <div class="timestamp">
                                                <?php 
                                                $datetime = $log['Datetime'];
                                                if (strpos($datetime, ',') !== false) {
                                                    $datetime = preg_replace('/\+\d+$/', '', $datetime);
                                                    $dateTime = DateTime::createFromFormat('d-m-y,H:i:s', $datetime);
                                                } else {
                                                    $dateTime = new DateTime($datetime);
                                                }
                                                
                                                if ($dateTime): ?>
                                                    <?= $dateTime->format('Y-m-d H:i:s') ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($log['Datetime']) ?>
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
                        <div class="pagination-info">
                            Showing 1-10 of <?= count($callLogs) ?> devices
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" disabled>‹</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn">›</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>