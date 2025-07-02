<div class="topbar-container">
    <nav class="eva-topbar">
        <div class="topbar-content">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle d-md-none" onclick="toggleMobileSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div class="page-header-topbar">
                <div class="header-content-topbar">
                    <h1 class="page-title"><?= $pageTitle ?></h1>
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

                <!-- Breadcrumb Section -->
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
                                                <?php echo htmlspecialchars($crumb['title']); ?>
                                            </a>
                                            <i class="bi bi-chevron-right separator"></i>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="../pages/dashboard.php">
                                        <i class="bi bi-house"></i>
                                        Dashboard
                                    </a>
                                    <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard' && $pageTitle !== 'Admin Dashboard' && $pageTitle !== 'My Dashboard'): ?>
                                        <i class="bi bi-chevron-right separator"></i>
                                    <?php endif; ?>
                                </li>
                                <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard' && $pageTitle !== 'Admin Dashboard' && $pageTitle !== 'My Dashboard'): ?>
                                    <li class="breadcrumb-item active">
                                        <span><?php echo htmlspecialchars($pageTitle); ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Right Section -->
            <div class="topbar-actions">
                <!-- Notifications -->
                <div class="notification-dropdown">
                    <button class="notification-btn" onclick="toggleNotifications()">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <!-- Notification Dropdown -->
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
                            <div class="notification-item">
                                <div class="notification-icon success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <h6>Device Connected</h6>
                                    <p>Device EVA-002 came online</p>
                                    <span class="notification-time">15 minutes ago</span>
                                </div>
                            </div>
                        </div>
                        <div class="notification-footer">
                            <a href="../pages/notifications.php">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="user-dropdown">
                    <button class="user-btn" onclick="toggleUserMenu()">
                        <div class="user-avatar-small">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div class="user-panel" id="userPanel">
                        <div class="user-info-header">
                            <div class="user-avatar-large">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="user-details-large">
                                <h6><?php echo htmlspecialchars($username); ?></h6>
                                <span><?php echo $isAdmin ? 'Administrator' : 'User'; ?></span>
                            </div>
                        </div>
                        <div class="user-menu">
                            <a href="../pages/profile.php" class="user-menu-item">
                                <i class="bi bi-person"></i>
                                <span>Profile</span>
                            </a>
                            <a href="../pages/settings.php" class="user-menu-item">
                                <i class="bi bi-gear"></i>
                                <span>Settings</span>
                            </a>
                            <div class="user-menu-divider"></div>
                            <a href="../actions/auth/logout.php" class="user-menu-item logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
    header-content-topbar.{
        padding: 0;
    }
    .page-header-topbar{
        padding: 10px 10px 10px 10px;
    }
    /* Topbar Styles */
    .topbar-container {
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        z-index: 999;
        padding: 20px 20px 0;
    }

    .eva-topbar {
        background: var(--eva-content-bg);
        backdrop-filter: blur(15px);
        border-radius: var(--eva-border-radius);
        box-shadow: var(--eva-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .topbar-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 25px;
        gap: 20px;
    }
    .topbar-breadcrumb {
    flex: 1;
    max-width: 600px;
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
    color: var(--eva-primary);
    text-decoration: none;
    font-weight: 500;
    transition: var(--eva-transition);
    display: flex;
    align-items: center;
    gap: 6px;
}

.breadcrumb-item a:hover {
    color: var(--eva-primary-dark);
}

.breadcrumb-item.active span {
    color: var(--eva-dark);
    font-weight: 600;
}

.separator {
    color: var(--eva-secondary);
    font-size: 11px;
}

/* Mobile responsive for breadcrumb */
@media (max-width: 768px) {
    .topbar-breadcrumb {
        display: none; /* Hide breadcrumb on mobile to save space */
    }
    
    .topbar-content {
        justify-content: end;
    }
}

    .mobile-menu-toggle {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--eva-primary);
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        transition: var(--eva-transition);
    }

    .mobile-menu-toggle:hover {
        background: rgba(66, 133, 244, 0.1);
    }

    .topbar-search {
        flex: 1;
        max-width: 400px;
    }

    .search-container {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--eva-secondary);
        font-size: 16px;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid rgba(66, 133, 244, 0.1);
        border-radius: 12px;
        background: white;
        font-size: 14px;
        transition: var(--eva-transition);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--eva-primary);
        box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.15);
    }

    .topbar-actions {
        display: flex;
        align-items: right;
        right: auto;
        gap: 15px;
    }

    /* Notification Dropdown */
    .notification-dropdown {
        position: relative;
    }

    .notification-btn {
        background: none;
        border: none;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--eva-secondary);
        transition: var(--eva-transition);
        position: relative;
        cursor: pointer;
    }

    .notification-btn:hover {
        background: rgba(66, 133, 244, 0.1);
        color: var(--eva-primary);
    }

    .notification-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--eva-danger);
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }

    .notification-panel {
        position: absolute;
        top: 100%;
        right: 0;
        width: 350px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: none;
        z-index: 1000;
        margin-top: 8px;
    }

    .notification-panel.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-header {
        padding: 20px 20px 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-header h6 {
        margin: 0;
        font-weight: 600;
        color: var(--eva-dark);
    }

    .mark-all-read {
        background: none;
        border: none;
        color: var(--eva-primary);
        font-size: 12px;
        cursor: pointer;
        font-weight: 500;
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
        transition: var(--eva-transition);
    }

    .notification-item:hover {
        background: rgba(66, 133, 244, 0.02);
    }

    .notification-item.unread {
        background: rgba(66, 133, 244, 0.05);
    }

    .notification-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
    }

    .notification-icon.danger {
        background: var(--eva-danger);
    }

    .notification-icon.success {
        background: var(--eva-success);
    }

    .notification-icon.warning {
        background: var(--eva-warning);
    }

    .notification-content {
        flex: 1;
    }

    .notification-content h6 {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
        color: var(--eva-dark);
    }

    .notification-content p {
        margin: 0 0 4px;
        font-size: 13px;
        color: var(--eva-secondary);
        line-height: 1.4;
    }

    .notification-time {
        font-size: 11px;
        color: var(--eva-secondary);
        font-weight: 500;
    }

    .notification-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .notification-footer a {
        color: var(--eva-primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    /* User Dropdown */
    .user-dropdown {
        position: relative;
    }

    .user-btn {
        background: none;
        border: none;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 12px;
        transition: var(--eva-transition);
        cursor: pointer;
    }

    .user-btn:hover {
        background: rgba(66, 133, 244, 0.1);
    }

    .user-avatar-small {
        width: 32px;
        height: 32px;
        background: var(--eva-gradient);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .user-btn .user-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--eva-dark);
    }

    .user-btn i {
        color: var(--eva-secondary);
        font-size: 12px;
    }

    .user-panel {
        position: absolute;
        top: 100%;
        right: 0;
        width: 280px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: none;
        z-index: 1000;
        margin-top: 8px;
    }

    .user-panel.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    .user-info-header {
        padding: 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .user-avatar-large {
        width: 48px;
        height: 48px;
        background: var(--eva-gradient);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .user-details-large h6 {
        margin: 0 0 4px;
        font-weight: 600;
        color: var(--eva-dark);
        font-size: 14px;
    }

    .user-details-large span {
        font-size: 12px;
        color: var(--eva-secondary);
    }

    .user-menu {
        padding: 10px 0;
    }

    .user-menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        color: var(--eva-dark);
        text-decoration: none;
        transition: var(--eva-transition);
        font-size: 14px;
    }

    .user-menu-item:hover {
        background: rgba(66, 133, 244, 0.05);
        color: var(--eva-primary);
    }

    .user-menu-item.logout {
        color: var(--eva-danger);
    }

    .user-menu-item.logout:hover {
        background: rgba(220, 53, 69, 0.05);
        color: var(--eva-danger);
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

        .topbar-search {
            max-width: none;
            flex: 1;
        }

        .user-btn .user-name {
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
    function toggleMobileSidebar() {
        const sidebar = document.querySelector('.eva-sidebar');
        sidebar.classList.toggle('mobile-open');
    }

    function toggleNotifications() {
        const panel = document.getElementById('notificationPanel');
        const userPanel = document.getElementById('userPanel');

        userPanel.classList.remove('show');
        panel.classList.toggle('show');
    }

    function toggleUserMenu() {
        const panel = document.getElementById('userPanel');
        const notificationPanel = document.getElementById('notificationPanel');

        notificationPanel.classList.remove('show');
        panel.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const notificationDropdown = document.querySelector('.notification-dropdown');
        const userDropdown = document.querySelector('.user-dropdown');

        if (!notificationDropdown.contains(e.target)) {
            document.getElementById('notificationPanel').classList.remove('show');
        }

        if (!userDropdown.contains(e.target)) {
            document.getElementById('userPanel').classList.remove('show');
        }
    });
</script>