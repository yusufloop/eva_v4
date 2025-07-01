
<?php 		    		   
include 'functions/managedependents_action.php';

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css" type="text/css">
    <link rel="stylesheet" href="css/managedependents_form.css" type="text/css">

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

	<!-- registered devices button -->
	<div class="device-info-container">
	    <p class="lead mb-0 device-text">Add new dependent:</p>
	    <button id="showFormBtn" class="btn btn-primary <?php echo $editMode ? 'd-none' : ''; ?>">Add Dependent</button>
	</div>


<!-- Add/Edit Dependent Form -->
<div id="addDependentFormContainer" class="form-overlay hidden">
    <div id="formScrollBGContainer">
        <div class="form-scroll-container">
            <form id="addDependentForm" action="functions/managedependents_action.php" method="POST">
                <!-- Hidden Dependent ID -->
                <input type="hidden" id="dependentId" name="dependentId">

                <!-- First Name Field -->
                <div class="form-group">
                    <label for="Firstname">First Name:</label>
                    <input type="text" placeholder="First Name" id="Firstname" name="Firstname" class="form-control" required>
                </div>

                <!-- Last Name Field -->
                <div class="form-group">
                    <label for="Lastname">Last Name:</label>
                    <input type="text" placeholder="Last Name" id="Lastname" name="Lastname" class="form-control" required>
                </div>

                <!-- Gender Field -->
                <div class="form-group">
                    <label for="Gender">Gender:</label>
                    <select id="Gender" name="Gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <!-- Date of Birth Field -->
                <div class="form-group">
                    <label for="DOB">Date of Birth:</label>
                    <input type="date" id="DOB" name="DOB" class="form-control" value="2000-01-01" required>
                </div>

                <!-- Address Field -->
                <div class="form-group">
                    <label for="Address">Address:</label>
                    <input type="text" placeholder="Address" id="Address" name="Address" class="form-control" required>
                </div>

                <!-- Postal Code Field -->
                <div class="form-group">
                    <label for="PostalCode">Postal Code:</label>
                    <input type="text" placeholder="Postal Code" id="PostalCode" name="PostalCode" class="form-control" required>
                </div>

                <!-- Medical Condition Field -->
                <div class="form-group">
                    <label for="MedicalCondition">Medical Condition:</label>
                    <input type="text" placeholder="Medical Condition" id="MedicalCondition" name="MedicalCondition" class="form-control" required>
                </div>

                <!-- Form Buttons -->
                <div class="button-container">
	                <button name="add_dependent" type="submit" class="btn btn-success">Add Dependent</button>
	                <a href="managedependents.php" class="btn btn-secondary">Cancel</a>
				</div>

            </form>
        </div>
    </div>
</div>
	<?php
	if (isset($_SESSION['dependent_message'])) {
	    $message = $_SESSION['dependent_message'];
	    $color = stripos($message, 'error') !== false ? 'red' : 'green'; // If the word "error" is found, use red, otherwise use green
	    echo '<div class="alert" style="color: ' . $color . ';">' . htmlspecialchars($message) . '</div>';
	    unset($_SESSION['dependent_message']);
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


	<script src="js/managedependents.js"></script>



</body>
</html>
