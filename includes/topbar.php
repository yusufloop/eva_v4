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
                        <ol class="breadcrumb-list">
                            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                                            <li class="breadcrumb-item <?php echo $index === count($breadcrumbs) - 1 ? 'active' : ''; ?>">
                                                                <?php if ($index === count($breadcrumbs) - 1): ?>
                                                                                <span><?php echo htmlspecialchars($crumb['title']); ?></span>
                                                                <?php else: ?>
                                                                                <a href="<?php echo htmlspecialchars($crumb['url']); ?>">
                                                                                    <?php if (isset($crumb['icon'])): ?>
                                                                                                    <i class="<?php echo $crumb['icon']; ?>"></i>
                                                                                    <?php endif; ?>
                                                                                    <?php echo htmlspecialchars($crumb['title']); ?>
                                                                                </a>
                                                                                <i class="bi bi-chevron-right separator"></i>
                                                                <?php endif; ?>
                                                            </li>
                                            <?php endforeach; ?>
                            <?php else: ?>
                                            <!-- Default breadcrumb if none specified -->
                                            <li class="breadcrumb-item">
                                                <a href="../pages/dashboard.php">
                                                    <i class="bi bi-house"></i>
                                                    Dashboard
                                                </a>
                                                <?php if (isset($pageTitle) && !in_array($pageTitle, ['Dashboard', 'Admin Dashboard', 'My Dashboard'])): ?>
                                                                <i class="bi bi-chevron-right separator"></i>
                                                <?php endif; ?>
                                            </li>
                                            <?php if (isset($pageTitle) && !in_array($pageTitle, ['Dashboard', 'Admin Dashboard', 'My Dashboard'])): ?>
                                                            <li class="breadcrumb-item active">
                                                                <span><?php echo htmlspecialchars($pageTitle); ?></span>
                                                            </li>
                                            <?php endif; ?>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Right Section - User & Notifications -->
            <div class="topbar-actions">
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


                <!-- User Menu -->
                <div class="user-dropdown">
                    <button class="user-btn" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <i class="bi bi-person"></i>
                        </div>

                        <span class="user-name"><?= isset($currentUser) ? $currentUser : 'User' ?></span>
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
                        <a href="../auth/logout.php" class="user-menu-item logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
    /* Topbar Container */
    .topbar-container {
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        z-index: 999;
        padding: 20px 20px 0;
    }

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

    .breadcrumb-list {
        display: flex;
        align-items: center;
        margin: 0;
        padding: 0;
        list-style: none;
        gap: 8px;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .breadcrumb-item a {
        color: #4285f4;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumb-item a:hover {
        color: #3367d6;
    }

    .breadcrumb-item.active span {
        color: #5f6368;
        font-weight: 600;
    }

    .separator {
        color: #9aa0a6;
        font-size: 11px;
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
    }

    .mobile-menu-toggle:hover {
        background: rgba(66, 133, 244, 0.1);
    }

    /* Topbar Actions */
    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 15px;
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
    }

    .notification-panel {
        position: absolute;
        top: 100%;
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
        margin-top: 8px;
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

    .user-btn {
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

    .user-btn:hover {
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
        top: 100%;
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
        margin-top: 8px;
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
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

                // Make functions global
                window.toggleMobileSidebar = function() {
                    const sidebar = document.querySelector('.eva-sidebar');
                    if (sidebar) {
                        sidebar.classList.toggle('mobile-open');
                    }
                };

                window.toggleNotifications = function() {
                    const panel = document.getElementById('notificationPanel');
                    const userPanel = document.getElementById('userPanel');

                    // Close user panel if open
                    if (userPanel) {
                        userPanel.classList.remove('show');
                    }

                    // Toggle notification panel
                    if (panel) {
                        panel.classList.toggle('show');
                    } else {
                        console.error('Notification panel not found!');
                    }
                };

                window.toggleUserMenu = function() {
                    const panel = document.getElementById('userPanel');
                    const notificationPanel = document.getElementById('notificationPanel');

                    // Close notification panel if open
                    if (notificationPanel) {
                        notificationPanel.classList.remove('show');
                    }

                    // Toggle user panel
                    if (panel) {
                        panel.classList.toggle('show');
                        console.log('User menu toggled. Panel visible:', panel.classList.contains('show'));
                    } else {
                        console.error('User panel element not found! Check if ID="userPanel" exists');
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
    
</script>