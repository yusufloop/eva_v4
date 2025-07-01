
<?php 		    		   
include 'functions/device_details_action.php';
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA</title>
    <link rel="stylesheet" href="css/dashboard.css" type="text/css">

</head>
<body>
	<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
	    <h1 class="mb-0">EVA Registration Dashboard</h1>
	    <div class="text-right">
	        <p class="mb-1 welcome-text">Welcome, <?php echo $_SESSION['admin_username']; ?>.</p>
	        <div class="align-items-center" style="margin-top: 5px;">
	            <a href="changepassword.php" id="showChangePasswordBtn" class="text-muted small mr-1">Change Password</a>
	            <span>|</span>
	            <a href="functions/logout_action.php" class="text-muted small ml-1 mr-4">Logout</a>
	        </div>
	    </div>
	</div>

    <div class="admin-dashboard-header">
	    <!-- Search Bar -->
	    <div class="search-bar-container">
	        <input type="text" id="searchBar" placeholder="Search..." class="form-control">
	    </div>
    </div>

	<table id="content-table3" class="table-responsive-full sort-table">
    	<?php generateTable($pdo); ?>
	</table>
	<script src="js/device_detail.js"></script>

</body>
</html>
