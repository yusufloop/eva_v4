
<?php 		    		   
include 'functions/dashboard_action.php';

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css" type="text/css">
    <link rel="stylesheet" href="css/dashboard_form.css" type="text/css">

</head>
<body>
	<nav class="navbar">
	    <div class="navbar-container">
	        <div class="navbar-logo">
	            <a href="dashboard.php">EVA Dashboard</a>
	        </div>

	        <ul class="navbar-links">
	            <li><a href="dashboard.php">Home</a></li>
	            <li><a href="changepassword.php">Change Password</a></li>
	            <li><a href="managedependents.php">Manage Dependents</a></li>
	            <li><a href="functions/logout_action.php">Logout</a></li>
	        </ul>
	    </div>
	</nav>



 <!-- 	<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
	    <h1 class="mb-0">EVA Registration Dashboard</h1>
	    <div class="text-right">
	        <p class="mb-1 welcome-text">Welcome, <?php //echo $_SESSION['username']; ?>.</p>
	        <div class="align-items-center" style="margin-top: 5px;">
	            <a href="changepassword.php" id="showChangePasswordBtn" class="text-muted small mr-1">Change Password</a>
	            <span>|</span>
	            <a href="functions/logout_action.php" class="text-muted small ml-1 mr-4">Logout</a>
	        </div>
	    </div>
	</div>
 -->

	<!-- registered devices button -->
	<div class="device-info-container">
	    <p class="lead mb-0 device-text">Registered new devices:</p>
	    <button id="showFormBtn" class="btn btn-primary <?php echo $editMode ? 'd-none' : ''; ?>">Add Device</button>
	</div>


    <!-- Add/Edit Form -->
	<div id="addDeviceFormContainer" class="form-overlay hidden">
	    <div id="formScrollBGContainer">
	        <div class="form-scroll-container">
	            <form id="addDeviceForm" action="functions/dashboard_action.php" method="POST">
	                <!-- Emergency Number Fields -->
	                <div class="form-group">
	                    <label for="emergencyNo1">Emergency No 1:</label>
	                    <input type="text" placeholder="Emergency No 1" id="emergencyNo1" name="emergencyNo1" class="form-control" required>
	                </div>
	                <div class="form-group">
	                    <label for="emergencyNo2">Emergency No 2:</label>
	                    <input type="text" placeholder="Emergency No 2" id="emergencyNo2" name="emergencyNo2" class="form-control" required>
	                </div>
	                
	                <!-- Serial Number Field -->
	                <div class="form-group">
	                    <label for="serialNo">Serial No:</label>
	                    <input type="text" placeholder="Serial No" id="serialNo" name="serialNo" class="form-control" required>
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
					    <div class="custom-dropdown">
					        <input type="text" id="existingDependentInput" placeholder="Select Existing Dependent" readonly class="form-control">
					        <div class="dropdown-options hidden">
					            <?php if (!empty($dependents)): ?>
					                <?php foreach ($dependents as $dependent): ?>
					                    <div class="dropdown-option" data-value="<?php echo htmlspecialchars($dependent['DependentID']); ?>">
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

	                <!-- Form Buttons -->
	                <button name="add_device" type="submit" class="btn btn-success">Add Device</button>
	                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
	            </form>
	        </div>
	    </div>
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

	<!-- <h2>Table - responsive (data-label) full & Sortable</h2> -->
	<table id="content-table3" class="table-responsive-full sort-table">
    	<?php generateTable($pdo, $UserID); ?>
	</table>


	<script src="js/dashboard.js"></script>
<style>
/*Custom dropdown-list start*/

.custom-dropdown {
    position: relative;
}
.dropdown-options {
    border: 1px solid #ccc;
    background: white;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    width: 100%;
    z-index: 10;
}
.dropdown-option {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
.dropdown-option:hover {
    background: #f5f5f5;
}
.dropdown-option strong {
    font-size: 15px;
    display: block;
}
.dropdown-option .address {
    font-size: 12px;
    color: #888;
}
.hidden {
    display: none;
}

/*Custom dropdown-list end*/


</style>


</body>
</html>
