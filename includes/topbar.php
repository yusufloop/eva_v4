<div class="topbar-container">
    <nav class="eva-topbar">
        <div class="topbar-content">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle d-md-none" onclick="toggleMobileSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <!-- Page Header Section -->
            <div class="page-header-topbar">
                <div class="header-content-topbar">
                    <h1 class="page-title"><?= isset($pageTitle) ? $pageTitle : 'Dashboard' ?></h1>
                    <div class="header-actions">
                        <?php if (isset($headerActions) && is_array($headerActions)): ?>
                            <?php foreach ($headerActions as $action): ?>
                                <button class="btn <?= $action['class'] ?>" onclick="<?= $action['onclick'] ?>">
                                    <i class="<?= $action['icon'] ?>"></i> <?= $action['text'] ?>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dynamic Breadcrumb Section -->
                <div class="topbar-breadcrumb">
                    <nav class="eva-breadcrumb">
                        <span class="breadcrumb-text">
                            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                    <?php if ($index === count($breadcrumbs) - 1): ?>
                                        <?php echo htmlspecialchars($crumb['title']); ?>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($crumb['url']); ?>" class="breadcrumb-link">
                                            <?php if (isset($crumb['icon'])): ?>
                                                <i class="<?php echo $crumb['icon']; ?>"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($crumb['title']); ?>
                                        </a>
                                        <span class="breadcrumb-separator"> / </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default breadcrumb if none specified -->
                                <a href="../pages/dashboard.php" class="breadcrumb-link">
                                    <i class="bi bi-house"></i>
                                    Dashboard
                                </a>
                                <?php if (isset($pageTitle) && !in_array($pageTitle, ['Dashboard', 'Admin Dashboard', 'My Dashboard'])): ?>
                                    <span class="breadcrumb-separator"> / </span>
                                    <span><?php echo htmlspecialchars($pageTitle); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </nav>
                </div>
            </div>

            <!-- Right Section - Search & User -->
            <div class="topbar-actions">
                <!-- Search Container -->
                <div class="search-container d-none d-lg-block">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search..." class="search-input">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="notification-dropdown">
                    <button class="notification-btn" onclick="toggleNotifications()">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <div class="notification-panel" id="notificationPanel">
                        <div class="notification-header">
                            <h6>Notifications</h6>
                            <button class="mark-all-read">Mark all read</button>
                        </div>
                        <div class="notification-list">
                            <div class="notification-item unread">
                                <div class="notification-icon danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="notification-content">
                                    <h6>Emergency Alert</h6>
                                    <p>Device EVA-001 triggered emergency call</p>
                                    <span class="notification-time">2 minutes ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="user-dropdown">
                    <button class="user-toggle" onclick="toggleUserPanel()">
                        <div class="user-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <span class="user-name d-none d-sm-inline"><?= isset($currentUser) ? $currentUser : 'User' ?></span>
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    <div class="user-panel" id="userPanel">
                        <div class="user-info">
                            <div class="user-avatar large">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="user-details">
                                <h6><?= isset($currentUser) ? $currentUser : 'User' ?></h6>
                                <span><?= isset($currentUser) ? $currentUser : '' ?></span>
                            </div>
                        </div>
                        <div class="user-menu-divider"></div>
                        <a href="../pages/profile.php" class="user-menu-item">
                            <i class="bi bi-person"></i> Profile
                        </a>
                        <a href="../pages/settings.php" class="user-menu-item">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <div class="user-menu-divider"></div>
                        <a href="../actions/auth/logout.php" class="user-menu-item logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
/* Topbar Container - Fixed Positioning */
.topbar-container {
    position: fixed;
    top: 0;
    left: 280px;
    right: 0;
    z-index: 999;
    padding: 20px 20px 0;
    transition: left 0.3s ease;
}

/* EVA Topbar */
.eva-topbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.topbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 25px;
    gap: 20px;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    background: none;
    border: none;
    font-size: 24px;
    color: #4285f4;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.mobile-menu-toggle:hover {
    background: rgba(66, 133, 244, 0.1);
}

/* Page Header in Topbar */
.page-header-topbar {
    flex: 1;
    max-width: 600px;
}

.header-content-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
    white-space: nowrap;
}

.header-actions {
    display: flex;
    gap: 8px;
}

.header-actions .btn {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Breadcrumb in Topbar */
.topbar-breadcrumb {
    margin-top: 5px;
}

.eva-breadcrumb {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 8px;
    padding: 8px 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.breadcrumb-text {
    font-size: 13px;
    color: #5f6368;
    display: flex;
    align-items: center;
    gap: 8px;
}

.breadcrumb-link {
    color: #4285f4;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.breadcrumb-link:hover {
    color: #3367d6;
}

.breadcrumb-separator {
    color: #9aa0a6;
    font-size: 11px;
}

/* Topbar Actions */
.topbar-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Search Container */
.search-container {
    position: relative;
}

.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    padding: 10px 40px 10px 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 10px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}

.search-input:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    background: rgba(255, 255, 255, 1);
}

.search-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 16px;
    pointer-events: none;
}

/* Notification Styles */
.notification-dropdown {
    position: relative;
}

.notification-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #5f6368;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    transition: all 0.3s ease;
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-btn:hover {
    background: rgba(95, 99, 104, 0.1);
    color: #4285f4;
}

.notification-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #ea4335;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    min-width: 16px;
    text-align: center;
    line-height: 1;
}

.notification-panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
    width: 350px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.notification-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h6 {
    margin: 0;
    font-weight: 600;
    color: #202124;
}

.mark-all-read {
    background: none;
    border: none;
    color: #4285f4;
    font-size: 13px;
    cursor: pointer;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 12px;
    transition: background 0.3s ease;
}

.notification-item:hover {
    background: rgba(0, 0, 0, 0.02);
}

.notification-item.unread {
    background: rgba(66, 133, 244, 0.02);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.notification-icon.danger {
    background: rgba(234, 67, 53, 0.1);
    color: #ea4335;
}

.notification-content h6 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #202124;
}

.notification-content p {
    margin: 0 0 4px 0;
    font-size: 13px;
    color: #5f6368;
}

.notification-time {
    font-size: 12px;
    color: #9aa0a6;
}

/* User Dropdown */
.user-dropdown {
    position: relative;
}

.user-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.user-toggle:hover {
    background: rgba(0, 0, 0, 0.05);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #4285f4;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.user-avatar.large {
    width: 48px;
    height: 48px;
    font-size: 20px;
}

.user-name {
    font-size: 14px;
    font-weight: 500;
    color: #202124;
}

.user-panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
    width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.user-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-info {
    padding: 20px;
    display: flex;
    gap: 12px;
    align-items: center;
}

.user-details h6 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #202124;
}

.user-details span {
    font-size: 12px;
    color: #5f6368;
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #5f6368;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 14px;
}

.user-menu-item:hover {
    background: rgba(66, 133, 244, 0.05);
    color: #4285f4;
}

.user-menu-item.logout {
    color: #ea4335;
}

.user-menu-item.logout:hover {
    background: rgba(234, 67, 53, 0.05);
    color: #ea4335;
}

.user-menu-divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.1);
    margin: 8px 0;
}

/* Mobile Responsive */
@media (max-width: 1200px) {
    .topbar-container {
        left: 250px;
    }
}

@media (max-width: 768px) {
    .topbar-container {
        left: 0;
        padding: 10px;
    }

    .topbar-content {
        padding: 12px 15px;
        gap: 15px;
    }

    .page-header-topbar {
        max-width: none;
        flex: 1;
    }

    .page-title {
        font-size: 1.2rem;
    }

    .topbar-breadcrumb {
        display: none;
    }

    .user-name {
        display: none;
    }

    .notification-panel,
    .user-panel {
        width: 300px;
        right: -10px;
    }

    .search-input {
        width: 200px;
    }
}

@media (max-width: 576px) {
    .header-content-topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .header-actions {
        align-self: flex-end;
    }

    .search-input {
        width: 150px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make functions global for onclick handlers
    window.toggleMobileSidebar = function() {
        const sidebar = document.querySelector('.eva-sidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');
        
        if (sidebar) {
            sidebar.classList.toggle('mobile-open');
        }
        
        if (overlay) {
            overlay.classList.toggle('show');
        }
    };

    window.toggleNotifications = function() {
        const panel = document.getElementById('notificationPanel');
        const userPanel = document.getElementById('userPanel');

        // Close user panel if open
        if (userPanel && userPanel.classList.contains('show')) {
            userPanel.classList.remove('show');
        }

        // Toggle notification panel
        if (panel) {
            panel.classList.toggle('show');
        }
    };

    window.toggleUserPanel = function() {
        const panel = document.getElementById('userPanel');
        const notificationPanel = document.getElementById('notificationPanel');

        // Close notification panel if open
        if (notificationPanel && notificationPanel.classList.contains('show')) {
            notificationPanel.classList.remove('show');
        }

        // Toggle user panel
        if (panel) {
            panel.classList.toggle('show');
        }
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const notificationDropdown = document.querySelector('.notification-dropdown');
        const userDropdown = document.querySelector('.user-dropdown');

        // Check if click is outside notification dropdown
        if (notificationDropdown && !notificationDropdown.contains(e.target)) {
            const panel = document.getElementById('notificationPanel');
            if (panel && panel.classList.contains('show')) {
                panel.classList.remove('show');
            }
        }

        // Check if click is outside user dropdown
        if (userDropdown && !userDropdown.contains(e.target)) {
            const panel = document.getElementById('userPanel');
            if (panel && panel.classList.contains('show')) {
                panel.classList.remove('show');
            }
        }
    });

    // Handle escape key to close dropdowns
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const notificationPanel = document.getElementById('notificationPanel');
            const userPanel = document.getElementById('userPanel');
            
            if (notificationPanel && notificationPanel.classList.contains('show')) {
                notificationPanel.classList.remove('show');
            }
            
            if (userPanel && userPanel.classList.contains('show')) {
                userPanel.classList.remove('show');
            }
        }
    });

    // Adjust topbar position based on sidebar state
    function adjustTopbarPosition() {
        const sidebar = document.querySelector('.eva-sidebar');
        const topbar = document.querySelector('.topbar-container');
        
        if (sidebar && topbar) {
            if (window.innerWidth <= 768) {
                topbar.style.left = '0';
            } else {
                topbar.style.left = '280px';
            }
        }
    }

    // Initial adjustment
    adjustTopbarPosition();

    // Adjust on window resize
    window.addEventListener('resize', adjustTopbarPosition);
});
</script>