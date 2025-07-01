<?php

$isAdmin = isset($_SESSION['admin_username']) || (isset($_SESSION['user_data']) && $_SESSION['user_data']['IsAdmin'] == 1);
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : (isset($_SESSION['user_data']) ? $_SESSION['user_data']['Email'] : '');

?>

<!-- <nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <a href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'dashboard.php'; ?>">
                <span>EVA</span> Dashboard
            </a>
        </div>

        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
        </div>
        <ul class="navbar-links">
            <li>
                <a href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'dashboard.php'; ?>" class="<?php echo ($currentPage ?? '') == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            </li>
            <?php if ($isAdmin): ?>
                <li><a href="devices.php" class="<?php echo ($currentPage ?? '') == 'devices' ? 'active' : ''; ?>">Devices</a></li>
                <li><a href="family_members.php" class="<?php echo ($currentPage ?? '') == 'family_members' ? 'active' : ''; ?>">family Members</a></li>
                <li><a href="call_logs.php" class="<?php echo ($currentPage ?? '') == 'call_logs' ? 'active' : ''; ?>">Call Logs</a></li>
                <li><a href="inventory.php" class="<?php echo ($currentPage ?? '') == 'inventory' ? 'active' : ''; ?>">Inventory</a></li>
                <li><a href="system_activities.php" class="<?php echo ($currentPage ?? '') == 'system_activities' ? 'active' : ''; ?>">System Activities</a></li>
            <?php else: ?>
                <li><a href="my_devices.php" class="<?php echo ($currentPage ?? '') == 'my_devices' ? 'active' : ''; ?>">My Devices</a></li>
                <li><a href="my_dependents.php" class="<?php echo ($currentPage ?? '') == 'my_dependents' ? 'active' : ''; ?>">My Dependents</a></li>
                <li><a href="my_call_history.php" class="<?php echo ($currentPage ?? '') == 'my_call_history' ? 'active' : ''; ?>">My Call History</a></li>
            <?php endif; ?>

            <li><a href="settinga.php" class="<?php echo ($currentPage ?? '') == 'settings' ? 'active' : ''; ?>">Settings</a></li>
            <li><a href="controllers/AuthController.php?action=logout">Logout</a></li>
        </ul>
    </div>
</nav> -->