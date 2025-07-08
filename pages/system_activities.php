<?php
// Page configuration
$pageTitle = 'System Activities';
$currentPage = 'system_activities';

// Include dependencies
require_once '../config/config.php';
require_once '../helpers/auth_helper.php';
require_once '../actions/dashboard/activities.php';
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
    ['title' => 'System Activities', 'url' => '#']
];

// Get activities data based on user role
if ($isAdmin) {
    $activities = getRecentSystemActivities(50); // Get more activities for admin
} else {
    $activities = getUserRecentActivities($userId, 20);
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
        
        <!-- System Activities Panel -->
        <div class="eva-card">
            <div class="card-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="activity-icon-header">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="header-text">
                            <h2>System Activities</h2>
                            <p>Recent system events and activities</p>
                        </div>
                    </div>
                    
                    <!-- Filter Controls -->
                    <div class="header-actions">
                        <div class="search-group">
                            <input type="text" id="activitySearch" placeholder="Search activities..." class="search-input">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        
                        <div class="filter-group">
                            <select id="typeFilter" class="filter-select">
                                <option value="">All Types</option>
                                <option value="emergency">Emergency</option>
                                <option value="device">Device</option>
                                <option value="user">User</option>
                                <option value="system">System</option>
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
                <?php if (empty($activities)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="bi bi-activity display-1 text-muted"></i>
                        <h3 class="mt-3">No Activities Found</h3>
                        <p class="text-muted">
                            <?= $isAdmin ? 'No system activities are available.' : 'You don\'t have any activities yet.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Activities Timeline -->
                    <div class="activities-timeline">
                        <?php foreach ($activities as $index => $activity): ?>
                            <div class="activity-item" data-type="<?= htmlspecialchars($activity['type']) ?>">
                                <div class="activity-icon activity-<?= htmlspecialchars($activity['type']) ?>">
                                    <i class="bi <?= getActivityIcon($activity['type']) ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                                        <div class="activity-time"><?= formatTimeAgo($activity['created_at']) ?></div>
                                    </div>
                                    <div class="activity-description"><?= htmlspecialchars($activity['description']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* System Activities Specific Styles */
.card-header {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    padding: 20px 24px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.activity-icon-header {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(66, 133, 244, 0.1);
    border-radius: 8px;
    color: #4285f4;
    font-size: 18px;
}

.header-text h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.header-text p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.search-group {
    position: relative;
}

.search-input {
    padding: 8px 35px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    width: 200px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

.filter-group {
    position: relative;
}

.filter-select {
    padding: 8px 32px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: white;
    font-size: 14px;
    color: #333;
    min-width: 120px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px;
}

.filter-select:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

/* Activities Timeline */
.activities-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-left: 20px;
}

.activities-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 20px;
    width: 2px;
    background: #e0e0e0;
}

.activity-item {
    position: relative;
    padding: 20px 0 20px 40px;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    position: absolute;
    left: 0;
    top: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    z-index: 1;
}

.activity-emergency {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.activity-device {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.activity-user {
    background: linear-gradient(135deg, #10b981, #059669);
}

.activity-system {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
}

.activity-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.activity-item:hover .activity-content {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.activity-title {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 15px;
}

.activity-time {
    color: #999;
    font-size: 12px;
}

.activity-description {
    color: #666;
    font-size: 14px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 4rem;
    color: #e2e8f0;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #4a5568;
    margin-bottom: 10px;
}

.empty-state p {
    font-size: 1rem;
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .header-actions {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .search-input {
        width: 100%;
    }
    
    .activities-timeline {
        padding-left: 10px;
    }
    
    .activities-timeline::before {
        left: 10px;
    }
    
    .activity-item {
        padding-left: 30px;
    }
    
    .activity-icon {
        width: 30px;
        height: 30px;
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeActivityFilters();
    initializeActivitySearch();
});

function initializeActivityFilters() {
    const typeFilter = document.getElementById('typeFilter');
    const periodFilter = document.getElementById('periodFilter');
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterActivities);
    }
    
    if (periodFilter) {
        periodFilter.addEventListener('change', filterActivities);
    }
}

function initializeActivitySearch() {
    const searchInput = document.getElementById('activitySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterActivitiesBySearch(searchTerm);
        });
    }
}

function filterActivities() {
    const typeFilter = document.getElementById('typeFilter')?.value.toLowerCase() || '';
    const periodFilter = document.getElementById('periodFilter')?.value || '';
    
    const activityItems = document.querySelectorAll('.activity-item');
    let visibleCount = 0;
    
    activityItems.forEach(item => {
        const type = item.dataset.type || '';
        const timestamp = item.querySelector('.activity-time')?.textContent || '';
        
        // Check type filter
        const matchesType = !typeFilter || type === typeFilter;
        
        // Check period filter (simplified)
        let matchesPeriod = true;
        if (periodFilter && periodFilter !== 'all') {
            // This is a simplified check - in a real app, you'd parse the relative time
            if (timestamp.includes('days ago')) {
                const days = parseInt(timestamp);
                matchesPeriod = !isNaN(days) && days <= parseInt(periodFilter);
            } else if (timestamp.includes('hours ago') || timestamp.includes('minutes ago') || timestamp.includes('Just now')) {
                matchesPeriod = true; // Today
            }
        }
        
        if (matchesType && matchesPeriod) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    toggleEmptyState(visibleCount === 0);
}

function filterActivitiesBySearch(searchTerm) {
    const activityItems = document.querySelectorAll('.activity-item');
    let visibleCount = 0;
    
    activityItems.forEach(item => {
        const title = item.querySelector('.activity-title')?.textContent.toLowerCase() || '';
        const description = item.querySelector('.activity-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    toggleEmptyState(visibleCount === 0);
}

function toggleEmptyState(show) {
    let emptyState = document.querySelector('.empty-state');
    const timelineContainer = document.querySelector('.activities-timeline');
    
    if (show) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <i class="bi bi-filter display-1 text-muted"></i>
                <h3 class="mt-3">No Activities Found</h3>
                <p class="text-muted">No activities match your current filters.</p>
                <button class="btn btn-primary mt-3" onclick="resetFilters()">
                    <i class="bi bi-arrow-repeat me-2"></i>Reset Filters
                </button>
            `;
            
            if (timelineContainer) {
                timelineContainer.style.display = 'none';
                timelineContainer.parentNode.appendChild(emptyState);
            }
        } else {
            emptyState.style.display = 'block';
            if (timelineContainer) timelineContainer.style.display = 'none';
        }
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (timelineContainer) timelineContainer.style.display = 'flex';
    }
}

function resetFilters() {
    const typeFilter = document.getElementById('typeFilter');
    const periodFilter = document.getElementById('periodFilter');
    const searchInput = document.getElementById('activitySearch');
    
    if (typeFilter) typeFilter.value = '';
    if (periodFilter) periodFilter.value = '30';
    if (searchInput) searchInput.value = '';
    
    // Reset all items to visible
    document.querySelectorAll('.activity-item').forEach(item => {
        item.style.display = '';
    });
    
    // Hide empty state
    toggleEmptyState(false);
}
</script>

<?php include '../includes/footer.php'; ?>