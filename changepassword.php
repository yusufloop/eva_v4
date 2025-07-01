<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA</title>
    <link rel="stylesheet" href="css/changepassword.css" type="text/css">
</head>
<body>
<div id="box">
  <form id="changePasswordForm" action="functions/changepassword_action.php"  method="POST" onsubmit="return validateForm();">
  <h1>Change Password <span>choose a good one!</span></h1>
  <form>
      <input type="password" placeholder="Current Password" id="currentPassword" name="currentPassword" class="password">
      <input type="password" placeholder="Enter New Password" id="newPassword" name="newPassword" class="password">
      <input type="password" placeholder="Enter Confirm Password" id="confirmPassword" name="confirmPassword" class="password">
    <div id="strong"><span></span></div>
    <div id="valid"></div>
    <small>Must be 6+ characters long and contain at least 1 upper case letter, 1 number, 1 special character</small>
    <!-- </p> -->
    <div class="button-container">
      <button name="changepassword" type="submit" class="btn btn-success">Change Password</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>

  </form>
</div>
  <script src="vendor/jquery-3.6.0.min.js"></script>
  <script src="js/PasswordStrengthValidator.js"></script>

</body>
</html>
