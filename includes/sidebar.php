<?php if ($isAdmin): ?>
<div class="eva-sidebar">
    <div class="sidebar-container">
        <!-- Logo Section -->
        <div class="sidebar-header">
            <div class="eva-logo">
                <div class="eva-icon-box">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="logo-content">
                    <span class="logo-text">EVA</span>
                    <span class="logo-subtitle">Emergency Alert System</span>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <a href="../pages/dashboard.php" class="nav-item <?php echo ($currentPage ?? '') == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                
          
                
                <a href="../pages/dependents.php" class="nav-item <?php echo ($currentPage ?? '') == 'family_members' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Dependents</span>
                </a>
                
                <a href="../pages/call_logs.php" class="nav-item <?php echo ($currentPage ?? '') == 'call_logs' ? 'active' : ''; ?>">
                    <i class="bi bi-telephone"></i>
                    <span>Call Logs</span>
                </a>
                
                <a href="../pages/alert_history.php" class="nav-item <?php echo ($currentPage ?? '') == 'alert_history' ? 'active' : ''; ?>">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Alert History</span>
                </a>
                
                <!-- <a href="../pages/settings.php" class="nav-item <?php echo ($currentPage ?? '') == 'settings' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a> -->
                
                <!-- <a href="../pages/profile.php" class="nav-item <?php echo ($currentPage ?? '') == 'profile' ? 'active' : ''; ?>">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a> -->
                
                <a href="../pages/system_activities.php" class="nav-item <?php echo ($currentPage ?? '') == 'system_activities' ? 'active' : ''; ?>">
                    <i class="bi bi-activity"></i>
                    <span>System Activities</span>
                </a>
            </nav>
            
            <!-- User Info Section -->
            <!-- <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
                
            </div> -->
        </div>
    </div>
</div>

<style>
/* Enhanced Sidebar Styles */
.eva-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    z-index: 1000;
    padding: 20px;
}

.sidebar-container {
    background: var(--eva-sidebar-bg);
    backdrop-filter: blur(15px);
    border-radius: 25px ;
    box-shadow: var(--eva-shadow);
    height: 100%;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    background: linear-gradient(135deg, rgba(66, 133, 244, 0.05), rgba(25, 118, 210, 0.02));
}

.eva-logo {
    display: flex;
    align-items: center;
    gap: 15px;
}

.eva-icon-box {
    width: 48px;
    height: 48px;
    background: var(--eva-gradient);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
}

.logo-content {
    display: flex;
    flex-direction: column;
}

.logo-text {
    font-size: 28px;
    font-weight: 700;
    color: var(--eva-primary-dark);
    letter-spacing: -0.5px;
    line-height: 1;
}

.logo-subtitle {
    font-size: 11px;
    color: var(--eva-secondary);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.sidebar-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 25px;
    color: #64748b;
    text-decoration: none;
    border-radius: 12px;
    transition: var(--eva-transition);
    font-weight: 500;
    font-size: 15px;
    position: relative;
    margin: 0 15px 4px;
}

.nav-item:hover {
    background: linear-gradient(135deg, rgba(66, 133, 244, 0.08), rgba(66, 133, 244, 0.04));
    color: var(--eva-primary-dark);
    transform: translateX(4px);
    text-decoration: none;
}

.nav-item.active {
    background: linear-gradient(135deg, rgba(66, 133, 244, 0.12), rgba(25, 118, 210, 0.08));
    color: var(--eva-primary-dark);
    font-weight: 600;
    box-shadow: 0 3px 12px rgba(66, 133, 244, 0.2);
}

.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 24px;
    background: var(--eva-gradient);
    border-radius: 0 4px 4px 0;
}

.nav-item.active::after {
    content: '';
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: var(--eva-primary);
    border-radius: 50%;
    opacity: 0.8;
}

.nav-item i {
    font-size: 18px;
    width: 20px;
    text-align: center;
    transition: var(--eva-transition);
}

.nav-item.active i {
    color: var(--eva-primary);
    transform: scale(1.1);
}

.nav-item span {
    font-family: 'Inter', system-ui, sans-serif;
    letter-spacing: -0.2px;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 20px 25px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    background: linear-gradient(135deg, rgba(66, 133, 244, 0.02), rgba(25, 118, 210, 0.01));
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--eva-gradient);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--eva-dark);
    line-height: 1.2;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    max-width: 100%;
    
}

.user-role {
    font-size: 12px;
    color: var(--eva-secondary);
    font-weight: 400;
}

.logout-btn {
    width: 36px;
    height: 36px;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--eva-danger);
    text-decoration: none;
    transition: var(--eva-transition);
    font-size: 16px;
}

.logout-btn:hover {
    background: var(--eva-danger);
    color: white;
    transform: scale(1.05);
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(66, 133, 244, 0.2);
    border-radius: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(66, 133, 244, 0.4);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .eva-sidebar {
        width: 100%;
        height: auto;
        position: fixed;
        top: 0;
        left: 0;
        transform: translateX(-100%);
        transition: var(--eva-transition);
        z-index: 1050;
        padding: 10px;
    }

    .eva-sidebar.mobile-open {
        transform: translateX(0);
    }

    .sidebar-container {
        border-radius: 0 20px 20px 0;
        height: 100vh;
    }

    .sidebar-header {
        padding: 20px;
    }

    .eva-logo {
        gap: 12px;
    }

    .logo-text {
        font-size: 24px;
    }

    .nav-item {
        margin: 0 10px 4px;
        padding: 12px 20px;
    }

    .sidebar-footer {
        padding: 15px 20px;
    }
}
</style>

<?php else: ?>
<!-- User Sidebar (Regular Users) -->
<div class="eva-sidebar">
    <div class="sidebar-container">
        <!-- Logo Section -->
        <div class="sidebar-header">
            <div class="eva-logo">
                <div class="eva-icon-box">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="logo-content">
                    <span class="logo-text">EVA</span>
                    <span class="logo-subtitle">My Dashboard</span>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <a href="../pages/dashboard.php" class="nav-item <?php echo ($currentPage ?? '') == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>My Dashboard</span>
                </a>
                
                <a href="../pages/my_devices.php" class="nav-item <?php echo ($currentPage ?? '') == 'my_devices' ? 'active' : ''; ?>">
                    <i class="bi bi-phone"></i>
                    <span>My Devices</span>
                </a>
                
                <a href="../pages/my_dependents.php" class="nav-item <?php echo ($currentPage ?? '') == 'my_dependents' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>My Dependents</span>
                </a>
                
                <a href="../pages/my_call_history.php" class="nav-item <?php echo ($currentPage ?? '') == 'my_call_history' ? 'active' : ''; ?>">
                    <i class="bi bi-telephone"></i>
                    <span>Call History</span>
                </a>
                
                <a href="../pages/settings.php" class="nav-item <?php echo ($currentPage ?? '') == 'settings' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
                
                <a href="../pages/profile.php" class="nav-item <?php echo ($currentPage ?? '') == 'profile' ? 'active' : ''; ?>">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </nav>
            
            <!-- User Info Section -->
            <!-- <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser); ?></span>
                        <span class="user-role">User</span>
                    </div>
                </div>
                <a href="../actions/auth/logout.php" class="logout-btn" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div> -->
        </div>
    </div>
</div>
<?php endif; ?>