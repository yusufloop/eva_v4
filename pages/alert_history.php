<?php
// Page configuration
$pageTitle = 'Alert History';
$currentPage = 'alert_history';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';

// Check authentication
requireAuth();

// Get current user info
$userId = getCurrentUserId();
$isAdmin = hasRole('admin');

// Page-specific assets
$additionalCSS = [
    '../assets/css/dashboard.css',
    '../assets/css/components/stats-card.css',
    '../assets/css/alert-history.css'
];

$additionalJS = [
    '../assets/js/dashboard.js',
    '../assets/js/alert-history.js'
];

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Alert History', 'url' => '#']
];

// Static alert data (since database is unavailable)
$alertData = [
    [
        'id' => 1,
        'datetime' => '2024-01-15 14:30:25',
        'deviceId' => 'EVA-001',
        'alertType' => 'Emergency',
        'location' => 'Room 156A',
        'status' => 'Resolved',
        'resolvedBy' => 'Maria Santos',
        'priority' => 'High',
        'description' => 'Emergency call from Maria Santos'
    ],
    [
        'id' => 2,
        'datetime' => '2024-01-15 10:15:42',
        'deviceId' => 'EVA-002',
        'alertType' => 'Low Battery',
        'location' => 'Room 220B',
        'status' => 'Pending',
        'resolvedBy' => '-',
        'priority' => 'Medium',
        'description' => 'Device battery level below 20%'
    ]
];

// Calculate stats from static data
$totalAlerts = count($alertData);
$resolvedAlerts = count(array_filter($alertData, fn($alert) => $alert['status'] === 'Resolved'));
$pendingAlerts = count(array_filter($alertData, fn($alert) => $alert['status'] === 'Pending'));
$highPriorityAlerts = count(array_filter($alertData, fn($alert) => $alert['priority'] === 'High'));

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
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Total Alerts</span>
                        <span class="total-text">All Time</span>
                    </div>
                    <div class="stat-number total">
                        <?= $totalAlerts ?>
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
                        <?= $resolvedAlerts ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon offline">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">Pending</span>
                        <span class="total-text">Awaiting Action</span>
                    </div>
                    <div class="stat-number offline">
                        <?= $pendingAlerts ?>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon emergency">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        <span class="status-text">High Priority</span>
                        <span class="total-text">Critical</span>
                    </div>
                    <div class="stat-number emergency">
                        <?= $highPriorityAlerts ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert History Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="alert-history-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="header-text">
                            <h2>Alert History</h2>
                            <p>System alert activities and notifications</p>
                        </div>
                    </div>
                    
                    <!-- Filter Controls -->
                    <div class="header-actions">
                        <div class="filter-group">
                            <select id="alertTypeFilter" class="filter-select">
                                <option value="">All Types</option>
                                <option value="emergency">Emergency</option>
                                <option value="device">Device</option>
                                <option value="system">System</option>
                                <option value="low battery">Low Battery</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="resolved">Resolved</option>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <select id="priorityFilter" class="filter-select">
                                <option value="">All Priority</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($alertData)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="bi bi-bell-slash display-1 text-muted"></i>
                        <h3 class="mt-3">No Alerts Found</h3>
                        <p class="text-muted">
                            <?= $isAdmin ? 'No alert history records are available in the system.' : 'You don\'t have any alert history yet.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Alert History Table -->
                    <div class="alert-table-container">
                        <table class="alert-table table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">
                                        <i class="bi bi-hash me-2"></i>ID
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-calendar me-2"></i>DateTime
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-phone me-2"></i>Device ID
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Alert Type
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-geo-alt me-2"></i>Location
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-check-circle me-2"></i>Status
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-person me-2"></i>Resolved By
                                    </th>
                                    <th scope="col">
                                        <i class="bi bi-flag me-2"></i>Priority
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alertData as $alert): ?>
                                    <tr data-alert-id="<?= $alert['id'] ?>" 
                                        data-type="<?= strtolower($alert['alertType']) ?>"
                                        data-status="<?= strtolower($alert['status']) ?>"
                                        data-priority="<?= strtolower($alert['priority']) ?>"
                                        class="alert-row">
                                        
                                        <!-- ID -->
                                        <td>
                                            <span class="alert-id">#<?= str_pad($alert['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                        </td>
                                        
                                        <!-- DateTime -->
                                        <td>
                                            <div class="datetime-cell">
                                                <div class="date"><?= date('d/m/Y', strtotime($alert['datetime'])) ?></div>
                                                <div class="time"><?= date('H:i:s', strtotime($alert['datetime'])) ?></div>
                                            </div>
                                        </td>
                                        
                                        <!-- Device ID -->
                                        <td>
                                            <div class="device-cell">
                                                <i class="bi bi-phone me-2 text-primary"></i>
                                                <span class="device-id"><?= htmlspecialchars($alert['deviceId']) ?></span>
                                            </div>
                                        </td>
                                        
                                        <!-- Alert Type -->
                                        <td>
                                            <?php
                                            $typeClass = match(strtolower($alert['alertType'])) {
                                                'emergency' => 'alert-type-emergency',
                                                'low battery' => 'alert-type-device',
                                                'device' => 'alert-type-device',
                                                'system' => 'alert-type-system',
                                                default => 'alert-type-default'
                                            };
                                            
                                            $typeIcon = match(strtolower($alert['alertType'])) {
                                                'emergency' => 'bi-exclamation-triangle-fill',
                                                'low battery' => 'bi-battery-half',
                                                'device' => 'bi-phone',
                                                'system' => 'bi-gear',
                                                default => 'bi-bell'
                                            };
                                            ?>
                                            <span class="alert-type-badge <?= $typeClass ?>">
                                                <i class="<?= $typeIcon ?> me-1"></i>
                                                <?= htmlspecialchars($alert['alertType']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Location -->
                                        <td>
                                            <div class="location-cell">
                                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                                <?= htmlspecialchars($alert['location']) ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td>
                                            <?php
                                            $statusClass = match(strtolower($alert['status'])) {
                                                'resolved' => 'status-resolved',
                                                'pending' => 'status-pending',
                                                'active' => 'status-active',
                                                default => 'status-default'
                                            };
                                            
                                            $statusIcon = match(strtolower($alert['status'])) {
                                                'resolved' => 'bi-check-circle-fill',
                                                'pending' => 'bi-clock-fill',
                                                'active' => 'bi-play-circle-fill',
                                                default => 'bi-circle'
                                            };
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <i class="<?= $statusIcon ?> me-1"></i>
                                                <?= htmlspecialchars($alert['status']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Resolved By -->
                                        <td>
                                            <div class="resolved-by-cell">
                                                <?php if ($alert['resolvedBy'] !== '-'): ?>
                                                    <i class="bi bi-person-check me-2 text-success"></i>
                                                    <?= htmlspecialchars($alert['resolvedBy']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Priority -->
                                        <td>
                                            <?php
                                            $priorityClass = match(strtolower($alert['priority'])) {
                                                'high' => 'priority-high',
                                                'medium' => 'priority-medium',
                                                'low' => 'priority-low',
                                                default => 'priority-default'
                                            };
                                            
                                            $priorityIcon = match(strtolower($alert['priority'])) {
                                                'high' => 'bi-exclamation-circle-fill',
                                                'medium' => 'bi-dash-circle-fill',
                                                'low' => 'bi-info-circle-fill',
                                                default => 'bi-circle'
                                            };
                                            ?>
                                            <span class="priority-badge <?= $priorityClass ?>">
                                                <i class="<?= $priorityIcon ?> me-1"></i>
                                                <?= htmlspecialchars($alert['priority']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing 1-<?= count($alertData) ?> of <?= count($alertData) ?> alerts
                        </div>
                        <nav aria-label="Alert pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <span class="page-link">‹</span>
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
                                    <a class="page-link" href="#">›</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>