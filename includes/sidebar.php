<?php
// File: includes/sidebar.php
if ($isAdmin): ?>
    <div class="col-md-3 col-lg-2 px-0">
        <div class="sidebar">
            <!-- Logo -->
            <div class="sidebar-header">
                <div class="eva-logo">
                    <div class="eva-icon-box">
                        <i class="bi bi-house-gear-fill"></i>
                    </div>
                    <span class="logo-text">EVA</span>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <a href="../../admin_dashboard.php" class="nav-item <?php echo ($currentPage ?? '') == 'dashboard' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="../../devices.php" class="nav-item <?php echo ($currentPage ?? '') == 'devices' ? 'active' : ''; ?>">
                        <i class="bi bi-phone"></i>
                        <span>Devices</span>
                    </a>
                    
                    <a href="../../family_members.php" class="nav-item <?php echo ($currentPage ?? '') == 'family_members' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span>Family Members</span>
                    </a>
                    
                    <a href="../../call_logs.php" class="nav-item <?php echo ($currentPage ?? '') == 'call_logs' ? 'active' : ''; ?>">
                        <i class="bi bi-telephone"></i>
                        <span>Call Logs</span>
                    </a>
                    
                    <a href="../../alert_history.php" class="nav-item <?php echo ($currentPage ?? '') == 'alert_history' ? 'active' : ''; ?>">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Alert History</span>
                    </a>
                    
                    <a href="../../settings.php" class="nav-item <?php echo ($currentPage ?? '') == 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                    
                    <a href="../../profile.php" class="nav-item <?php echo ($currentPage ?? '') == 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                    
                    <a href="../../system_activities.php" class="nav-item <?php echo ($currentPage ?? '') == 'system_activities' ? 'active' : ''; ?>">
                        <i class="bi bi-activity"></i>
                        <span>System Activities</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <style>
    /* Enhanced Sidebar Styles to match your prototype */
    .sidebar {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border-radius: 0 25px 25px 0;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        min-height: calc(100vh - 40px);
        margin: 20px 0 20px 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sidebar-header {
        padding: 30px 25px 25px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        background: linear-gradient(135deg, rgba(66, 133, 244, 0.05), rgba(25, 118, 210, 0.02));
    }

    .eva-logo {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .eva-icon-box {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #4285f4, #1976d2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
    }

    .logo-text {
        font-size: 26px;
        font-weight: 700;
        color: #1976d2;
        letter-spacing: -0.5px;
    }

    .sidebar-content {
        padding: 20px 0;
        height: calc(100% - 100px);
        overflow-y: auto;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 0 15px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        color: #64748b;
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 500;
        font-size: 15px;
        position: relative;
        margin-bottom: 2px;
    }

    .nav-item:hover {
        background: linear-gradient(135deg, rgba(66, 133, 244, 0.08), rgba(66, 133, 244, 0.04));
        color: #1976d2;
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(66, 133, 244, 0.15);
    }

    .nav-item.active {
        background: linear-gradient(135deg, rgba(66, 133, 244, 0.12), rgba(25, 118, 210, 0.08));
        color: #1976d2;
        font-weight: 600;
        box-shadow: 0 3px 12px rgba(66, 133, 244, 0.2);
        position: relative;
    }

    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #4285f4, #1976d2);
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
        background: #4285f4;
        border-radius: 50%;
        opacity: 0.8;
    }

    .nav-item i {
        font-size: 18px;
        width: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .nav-item.active i {
        color: #4285f4;
        transform: scale(1.1);
    }

    .nav-item span {
        font-family: 'Segoe UI', system-ui, sans-serif;
        letter-spacing: -0.2px;
    }

    /* Scrollbar styling */
    .sidebar-content::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: rgba(66, 133, 244, 0.2);
        border-radius: 4px;
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background: rgba(66, 133, 244, 0.4);
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .sidebar {
            border-radius: 0;
            margin: 0;
            min-height: auto;
            position: fixed;
            z-index: 1000;
            width: 100%;
            height: auto;
            max-height: 80vh;
        }

        .sidebar-nav {
            flex-direction: row;
            overflow-x: auto;
            padding: 10px;
            gap: 8px;
        }

        .nav-item {
            flex-direction: column;
            min-width: 80px;
            padding: 12px 8px;
            text-align: center;
            gap: 6px;
        }

        .nav-item span {
            font-size: 12px;
            white-space: nowrap;
        }

        .nav-item.active::before {
            display: none;
        }

        .nav-item.active::after {
            bottom: 8px;
            top: auto;
            transform: none;
        }
    }

    /* Hover animations */
    .nav-item {
        overflow: hidden;
    }

    .nav-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }

    .nav-item:hover::before {
        left: 100%;
    }
    </style>
<?php endif; ?>