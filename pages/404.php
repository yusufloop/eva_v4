<?php
// Page configuration
$pageTitle = 'Page Not Found';
$currentPage = '404';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';

// Check authentication
requireAuth();

// Page-specific assets
$additionalCSS = [
    '../assets/css/dashboard.css'
];

// Breadcrumb configuration
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '../dashboard', 'icon' => 'bi bi-house'],
    ['title' => '404 - Page Not Found', 'url' => '#']
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
        <div class="eva-card text-center">
            <div class="card-body" style="padding: 60px 40px;">
                <div style="font-size: 72px; color: #ddd; margin-bottom: 20px;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h1 style="font-size: 48px; color: #666; margin-bottom: 16px;">404</h1>
                <h3 style="color: #666; margin-bottom: 20px;">Page Not Found</h3>
                <p style="color: #888; margin-bottom: 30px; font-size: 16px;">
                    The page you're looking for doesn't exist or has been moved.
                </p>
                
                <div style="margin-bottom: 30px;">
                    <p><strong>Possible reasons:</strong></p>
                    <ul style="text-align: left; display: inline-block; color: #666;">
                        <li>The URL was typed incorrectly</li>
                        <li>The page has been moved or deleted</li>
                        <li>You don't have permission to access this page</li>
                    </ul>
                </div>
                
                <div>
                    <button class="btn btn-primary me-3" onclick="window.location.href='dashboard.php'">
                        <i class="bi bi-house me-1"></i>Go to Dashboard
                    </button>
                    <button class="btn btn-secondary" onclick="history.back()">
                        <i class="bi bi-arrow-left me-1"></i>Go Back
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="eva-card text-center">
                    <div class="card-body">
                        <i class="bi bi-house-door display-6 text-primary mb-2"></i>
                        <h5>Dashboard</h5>
                        <p class="text-muted small">View your main dashboard</p>
                        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Visit</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="eva-card text-center">
                    <div class="card-body">
                        <i class="bi bi-box display-6 text-success mb-2"></i>
                        <h5>Inventory</h5>
                        <p class="text-muted small">Manage device inventory</p>
                        <a href="inventory.php" class="btn btn-outline-success btn-sm">Visit</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="eva-card text-center">
                    <div class="card-body">
                        <i class="bi bi-telephone display-6 text-info mb-2"></i>
                        <h5>Call Logs</h5>
                        <p class="text-muted small">View call history</p>
                        <a href="call_logs.php" class="btn btn-outline-info btn-sm">Visit</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="eva-card text-center">
                    <div class="card-body">
                        <i class="bi bi-gear display-6 text-warning mb-2"></i>
                        <h5>Settings</h5>
                        <p class="text-muted small">Manage your settings</p>
                        <a href="profile.php" class="btn btn-outline-warning btn-sm">Visit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>