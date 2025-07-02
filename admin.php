
<?php 	
session_start();
// require 'actions/config.php';	    		   
include 'actions/admin_users_tabs.php';
include 'actions/admin_inventory_tabs.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ./index.php");
    exit();
}

// Retrieve users' data from session
if (isset($_SESSION['users_data'])) {
    $users = $_SESSION['users_data'];
	$userIds = array_map(function ($user) {
	    return [
	        'UserID' => $user['UserID'], 
	        'Email' => $user['Email']
	    ];
	}, $users);
}

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css" type="text/css">
    <link rel="stylesheet" href="css/dashboard_form.css" type="text/css">
    <link rel="stylesheet" href="css/admin_tabs.css" type="text/css">

</head>
<body>
	<nav class="navbar">
	    <div class="navbar-container">
	        <div class="navbar-logo">
	            <a href="admin_dashboard.php">EVA Dashboard</a>
	        </div>

	        <ul class="navbar-links">
	            <li><a href="admin_dashboard.php">Home</a></li>
	            <li><a href="changepassword.php">Change Password</a></li>
	            <li><a href="admin_managedependents.php">Manage Dependents</a></li>
	            <li><a href="admin.php">Admin</a></li>
	            <li><a href="actions/logout_action.php">Logout</a></li>
	        </ul>
	    </div>
	</nav>

    <!-- Search Bar and Registered Devices Container -->
    <div class="admin-dashboard-header">
		<!-- registered devices button -->
		<div class="device-info-container">
		    <!-- <p class="lead mb-0 device-text">Registered new devices:</p>
		    <button id="showAddDeviceFormBtn" class="btn btn-primary <?php //echo $editMode ? 'd-none' : ''; ?>">Add Device</button>
		    <button id="showAddUserFormBtn" class="btn btn-primary <?php //echo $editMode ? 'd-none' : ''; ?>">Add User</button> -->
		</div>
	    <!-- Search Bar -->
<!-- 	    <div class="search-bar-container">
	        <input type="text" id="searchBar" placeholder="Search..." class="form-control">
	    </div>
 -->
     </div>

	<div id="addUserFormContainer" class="form-overlay">
		<!-- <div id="formScrollBGContainer"> -->
    	<div class="form-scroll-container">
			<form action="actions/admin_users_tabs.php" id="addUserForm" method="POST">
				<h1>Update User</h1>
			    <label for="email">Email:</label>
	            <input type="text" id="email" name="email" placeholder="Username" required />
	            <div class="form-group">
			        <label for="IsAdmin">Is Admin:</label>
			        <select id="IsAdmin" name="IsAdmin" class="form-control">
			            <option value=""></option>
			            <option value="No">No</option>
			            <option value="Yes">Yes</option>
			        </select>
			    </div>
			    <input type="hidden" id="hiddenUserIdInput" name="hiddenUserIdInput">
    			<div class="button-container">
					<button name="update_user">Update User</button>
	            	<a href="admin.php" class="btn btn-secondary">Cancel</a>
			    </div>
			</form>
		</div>
	</div>

	<div id="editInventoryFormContainer" class="form-overlay">
		<!-- <div id="formScrollBGContainer"> -->
    	<div class="form-scroll-container">
			<form action="actions/admin_inventory_tabs.php" id="editInventoryForm" method="POST">
				<h1>Update Inventory</h1>
			    <label for="SerialNo">Serial No:</label>
	            <input type="text" id="SerialNo" name="SerialNo" placeholder="Serial No" required />
			    <label for="DeviceType">Device Type:</label>
	            <input type="text" id="DeviceType" name="DeviceType" placeholder="Device Type" required />
 		        <input type="hidden" id="hiddenInventoryIdInput" name="hiddenInventoryIdInput">
    			<div class="button-container">
					<button name="update_inventory">Update Inventory</button>
	            	<a href="admin.php" class="btn btn-secondary">Cancel</a>
			    </div>
			</form>
		</div>
	</div>
	<?php
	if (isset($_SESSION['message'])) {
	    $message = $_SESSION['message'];
	    $color = stripos($message, 'error') !== false ? 'red' : 'green'; // If the word "error" is found, use red, otherwise use green
	    echo '<div class="alert" style="color: ' . $color . ';">' . htmlspecialchars($message) . '</div>';
	    unset($_SESSION['message']);
	}
	?>

<div class="tabset">
  <!-- Tab 1 -->
  <input type="radio" name="tabset" id="tab1" aria-controls="Users" checked>
  <label for="tab1">Users</label>
  <!-- Tab 2 -->
  <input type="radio" name="tabset" id="tab2" aria-controls="Inventory">
  <label for="tab2">Inventory</label>
  
  <div class="tab-panels">
    <section id="Users" class="tab-panel">
    	<div class="search-bar-container">
	        <input type="text" id="user-searchBar" placeholder="Search..." class="form-control">
	    </div>
		<table id="content-table3" class="table-responsive-full sort-table">
	    	<?php generateUserList($pdo) ?>
		</table>
  	</section>
    <section id="Inventory" class="tab-panel">
    	<div class="search-bar-container">
	        <input type="text" id="inventory-searchBar" placeholder="Search..." class="form-control">
	    </div>

		<form action="actions/upload_inventory_list.php" method="post" enctype="multipart/form-data">
		    <label for="csv_file">Select CSV File:</label>
		    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
		    <br><br>
		    <button type="submit">Import Data</button>
		</form>

		<table id="content-table4" class="table-responsive-full sort-table">
	    	<?php generateInventoryList($pdo) ?>
		</table>
    </section>
  </div>
  
</div>

	<script src="js/admin.js"></script>

	<script src="vendor/jquery-3.6.0.min.js"></script>
	<script src="js/PasswordStrengthValidator.js"></script>


</body>


</html>
