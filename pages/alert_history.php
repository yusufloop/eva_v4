<?php
// Page configuration
$pageTitle = 'Alert History';
$currentPage = 'alert_history';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';
require_once '../helpers/component_helper.php';

// Check authentication
requireAuth();

// Get current user info
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
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'bi bi-house'],
    ['title' => 'Alert History', 'url' => '#']
];

// Static alert data - Simple timeline format
$alerts = [
    [
        'time' => '2:34 PM',
        'event' => 'Emergency resolved in Room 156A by Maria Santos'
    ],
    [
        'time' => '1:22 PM', 
        'event' => 'Emergency resolved in Room 159A by Akmal'
    ]
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
        
        <!-- Alert Activities Card -->
        <div class="container mt-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon me-3">
                            <i class="bi bi-exclamation-triangle-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">Alert Activities</h4>
                            <small class="text-muted">ALERT HISTORY</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($alerts)): ?>
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash display-1 text-muted"></i>
                            <h3 class="mt-3 text-muted">No Alert Activities</h3>
                            <p class="text-muted">No recent alert activities to display.</p>
                        </div>
                    <?php else: ?>
                        <!-- Alert Timeline -->
                        <div class="alert-timeline">
                            <?php foreach ($alerts as $index => $alert): ?>
                                <div class="alert-item <?= $index === count($alerts) - 1 ? 'last-item' : '' ?>">
                                    <div class="time-marker">
                                        <i class="bi bi-clock me-2"></i>
                                        <?= htmlspecialchars($alert['time']) ?>
                                    </div>
                                    <div class="event-text">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <?= htmlspecialchars($alert['event']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Alert Activities Specific Styles */
.alert-icon {
    width: 40px;
    height: 40px;
    background: rgba(66, 133, 244, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
}

.card-body {
    padding: 30px 24px;
}

/* Alert Timeline */
.alert-timeline {
    border-left: 3px solid #4285f4;
    padding-left: 20px;
    position: relative;
}

.alert-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    position: relative;
}

.alert-item.last-item {
    border-bottom: none;
}

.alert-item::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 20px;
    width: 8px;
    height: 8px;
    background: #4285f4;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #4285f4;
}

.time-marker {
    font-weight: 600;
    color: #4285f4;
    font-size: 0.9rem;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.event-text {
    color: #6c757d;
    margin-top: 5px;
    font-size: 0.95rem;
    line-height: 1.5;
    display: flex;
    align-items: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
    
    .card-header {
        padding: 16px 20px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .alert-timeline {
        padding-left: 16px;
    }
    
    .alert-item::before {
        left: -22px;
    }
    
    .alert-icon {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }
    
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .time-marker {
        font-size: 0.85rem;
    }
    
    .event-text {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
    
    .alert-timeline {
        padding-left: 12px;
    }
    
    .alert-item::before {
        left: -18px;
        width: 6px;
        height: 6px;
    }
}

/* Animation for timeline items */
.alert-item {
    opacity: 0;
    transform: translateX(-20px);
    animation: slideInLeft 0.5s ease-out forwards;
}

.alert-item:nth-child(1) {
    animation-delay: 0.1s;
}

.alert-item:nth-child(2) {
    animation-delay: 0.2s;
}

.alert-item:nth-child(3) {
    animation-delay: 0.3s;
}

@keyframes slideInLeft {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Hover effects */
.alert-item:hover {
    background: rgba(66, 133, 244, 0.02);
    border-radius: 8px;
    margin: 0 -10px;
    padding: 15px 10px;
    transition: all 0.3s ease;
}

.alert-item:hover .time-marker {
    color: #1976d2;
}

.alert-item:hover .event-text {
    color: #495057;
}
</style>

<?php include '../includes/footer.php'; ?>