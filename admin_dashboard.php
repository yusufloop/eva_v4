
<?php 		    		   
include 'functions/admin_dashboard_action.php';

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
	            <li><a href="functions/logout_action.php">Logout</a></li>
	        </ul>
	    </div>
	</nav>

    <!-- Search Bar and Registered Devices Container -->
    <div class="admin-dashboard-header">
		<!-- registered devices button -->
		<div class="device-info-container">
		    <p class="lead mb-0 device-text">Registered new devices:</p>
		    <button id="showAddDeviceFormBtn" class="btn btn-primary <?php echo $editMode ? 'd-none' : ''; ?>">Add Device</button>
		    <button id="showAddUserFormBtn" class="btn btn-primary <?php echo $editMode ? 'd-none' : ''; ?>">Add User</button>
		</div>
	    <!-- Search Bar -->
	    <div class="search-bar-container">
	        <input type="text" id="searchBar" placeholder="Search..." class="form-control">
	    </div>
    </div>

    <!-- Add/Edit Device Form -->
    <div id="addDeviceFormContainer" class="form-overlay hidden">
    	<div id="formScrollBGContainer">
	    	<div class="form-scroll-container">

		        <form id="addDeviceForm" action="functions/admin_dashboard_action.php" method="POST">
					<!-- Dropdown List -->
					<div class="form-group">
					    <label for="userId">Select User ID:</label>
					    <div class="custom-user-dropdown">
					        <input type="text" id="userIdInput" placeholder="Type to search..." class="form-control">
					        <div class="user-dropdown-options hidden">
					            <?php if (!empty($userIds)): ?>
					                <?php foreach ($userIds as $user): ?>
					                    <div class="user-dropdown-option" data-value="<?php echo htmlspecialchars($user['UserID']); ?>">
					                        <?php echo htmlspecialchars($user['Email']); ?>
					                    </div>
					                <?php endforeach; ?>
					            <?php endif; ?>
					        </div>
					        <input type="hidden" id="hiddenUserIdInput" name="hiddenUserIdInput">
					    </div>
					</div>

 		            <div class="form-group">
		                <label for="emergencyNo1">Emergency No 1:</label>
		                <input type="text" placeholder="Emergency No 1" id="emergencyNo1" name="emergencyNo1" class="form-control"  required>
		            </div>
		            <div class="form-group">
		                <label for="emergencyNo2">Emergency No 2:</label>
		                <input type="text" placeholder="Emergency No 2" id="emergencyNo2" name="emergencyNo2" class="form-control"  required>
		            </div>
		            <div class="form-group">
		                <label for="serialNo">Serial No:</label>
		                <input type="text" placeholder="Serial No" id="serialNo" name="serialNo" class="form-control"  required>
		            </div>
 	                <!-- Dependent Selection -->
	                <div class="form-group">
	                    <label for="dependentSelect">Select Dependent:</label>
	                    <select id="dependentSelect" name="dependentSelect" class="form-control" required>
	                        <option value="">Select an Option</option>
	                        <option value="existing">Select Existing Dependent</option>
	                        <option value="new">Add New Dependent</option>
	                    </select>
	                </div>

	                <!-- Existing Dependent Dropdown (Initially Hidden) -->
	                <div id="existingDependentContainer" class="form-group hidden">
	                    <label for="existingDependent">Existing Dependent:</label>
	                    <div class="custom-dependent-dropdown">
	                        <input type="text" id="existingDependentInput" placeholder="Select Existing Dependent" readonly class="form-control">
	                        <div class="dependent-dropdown-options hidden">
	                            <?php if (!empty($dependents)): ?>
	                                <?php foreach ($dependents as $dependent): ?>
	                                    <div class="dependent-dropdown-option" data-value="<?php echo htmlspecialchars($dependent['DependentID']); ?>">
	                                        <strong><?php echo htmlspecialchars($dependent['Firstname'] . ' ' . $dependent['Lastname']); ?></strong>
	                                        <div class="address"><?php echo htmlspecialchars($dependent['Address']); ?></div>
	                                    </div>
	                                <?php endforeach; ?>
	                            <?php endif; ?>
	                        </div>
	                    </div>
					    <input type="hidden" id="existingDependent" name="existingDependent">
					</div>

	                <!-- New Dependent Fields (Initially Hidden) -->
					<div id="newDependentContainer" class="form-group hidden">
					    <div class="form-group">
					        <label for="Firstname">First name:</label>
					        <input type="text" placeholder="First Name" id="Firstname" name="Firstname" class="form-control">
					    </div>
					    <div class="form-group">
					        <label for="Lastname">Last name:</label>
					        <input type="text" placeholder="Last Name" id="Lastname" name="Lastname" class="form-control">
					    </div>
					    <div class="form-group">
					        <label for="Gender">Gender:</label>
					        <select id="Gender" name="Gender" class="form-control">
					            <option value="">Select Gender</option>
					            <option value="Male">Male</option>
					            <option value="Female">Female</option>
					        </select>
					    </div>
					    <div class="form-group">
					        <label for="DOB">Date of Birth:</label>
					        <input type="date" id="DOB" name="DOB" class="form-control" value="2000-01-01">
					    </div>
					    <div class="form-group">
					        <label for="Address">Address:</label>
					        <input type="text" placeholder="Address" id="Address" name="Address" class="form-control">
					    </div>
					    <div class="form-group">
					        <label for="Postal">Postal:</label>
					        <input type="text" placeholder="Postal" id="Postal" name="Postal" class="form-control">
					    </div>
					    <div class="form-group">
					        <label for="MedicalCondition">Medical Condition:</label>
					        <input type="text" placeholder="MedicalCondition" id="MedicalCondition" name="MedicalCondition" class="form-control">
					    </div>
					</div>
 		            <button name="add_device" type="submit" class="btn btn-success">Add Device</button>
		            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
		        </form>
			</div>
		</div>
	</div>
    <!-- Add User Form -->
	<!-- <div class="container" id="container"> -->
		<div id="addUserFormContainer" class="form-overlay hidden">
			<!-- <div id="formScrollBGContainer"> -->
		    	<div class="form-scroll-container">
					<form action="functions/register_action.php" id="addUserForm" method="POST" onsubmit="return validateForm();">
						<h1>Create User</h1>
				        <?php if (!empty($error)): ?>
				            <p id="error-message" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
				        <?php endif; ?>
			            <input type="text" name="email" placeholder="Username" required />
						<input type="password" class="password" id="newPassword" name="newPassword" placeholder="Password" required/>
						<input type="password" class="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required/>
					    <div id="strong"><span></span></div>
					    <div id="valid"></div>
					    <small>Must be 6+ characters long and contain at least 1 upper case letter, 1 number, 1 special character</small>
		    			<div class="button-container">
							<button>Add User</button>
			            	<a href="dashboard.php" class="btn btn-secondary">Cancel</a>
					    </div>
					</form>
				</div>
			<!-- </div> -->
		</div>
	<?php
	if (isset($_SESSION['device_message'])) {
	    $message = $_SESSION['device_message'];
	    $color = stripos($message, 'error') !== false ? 'red' : 'green'; // If the word "error" is found, use red, otherwise use green
	    echo '<div class="alert" style="color: ' . $color . ';">' . htmlspecialchars($message) . '</div>';
	    unset($_SESSION['device_message']);
	}
	?>
	<?php
	if (isset($_SESSION['message'])) {
	    $message = $_SESSION['message'];
	    $color = stripos($message, 'error') !== false ? 'red' : 'green'; // If the word "error" is found, use red, otherwise use green
	    echo '<div class="alert" style="color: ' . $color . ';">' . htmlspecialchars($message) . '</div>';
	    unset($_SESSION['message']);
	}
	?>

	<table id="content-table3" class="table-responsive-full sort-table">
    	<?php generateTable($pdo); ?>
	</table>


	<script src="js/admin_dashboard.js"></script>

	<script src="vendor/jquery-3.6.0.min.js"></script>
	<script src="js/PasswordStrengthValidator.js"></script>


</body>

<style>
/* General Dropdown Styling */
.custom-user-dropdown,
.custom-dependent-dropdown {
    position: relative;
}

.user-dropdown-options,
.dependent-dropdown-options {
    border: 1px solid #ccc;
    background: #fff;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    width: 100%;
    z-index: 10;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 4px;
}

/* Option Styling */
.user-dropdown-option,
.dependent-dropdown-option {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
    font-size: 14px;
    font-family: Arial, sans-serif;
}

/* Highlight on Hover */
.user-dropdown-option:hover,
.dependent-dropdown-option:hover {
    background-color: #f5f5f5;
}

/* Address styling inside dependent dropdown */
.dependent-dropdown-option .address {
    font-size: 12px;
    color: #555;
    margin-top: 4px;
    display: block;
}

/* Hidden Dropdown */
.hidden {
    display: none;
}

/* Input Styling */
#userIdInput,
#existingDependentInput {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    box-sizing: border-box;
    cursor: pointer;
    background-color: #fff;
    font-size: 14px;
    font-family: Arial, sans-serif;
}

/* Hover and Focus Effects */
#userIdInput:hover,
#existingDependentInput:hover {
    border-color: #007bff;
}

#userIdInput:focus,
#existingDependentInput:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
}

/* Positioning for User and Dependent Dropdowns */
.custom-user-dropdown .user-dropdown-options,
.custom-dependent-dropdown .dependent-dropdown-options {
    top: calc(100% + 5px); /* Position dropdown below the input */
    left: 0;
    right: 0;
}
</style>

</html>
