<?php
  $token = isset($_GET['token']) ? $_GET['token'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>EVA - Reset Password</title>
    <link rel="stylesheet" href="css/changepassword.css" type="text/css">
</head>
<body>
<div id="box">
  <form id="resetPasswordForm" action="actions/resetpassword_action.php" method="POST" onsubmit="return validateForm();">
    <h1>Reset Password <span>Enter a new strong password!</span></h1>
      <!-- Hidden field to pass the token -->
      <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($token); ?>">

      <!-- Password Fields -->
      <input type="password" placeholder="Enter New Password" id="newPassword" name="newPassword" class="password">
      <input type="password" placeholder="Confirm New Password" id="confirmPassword" name="confirmPassword" class="password">
    <div id="strong"><span></span></div>
    <div id="valid"></div>
    <small>Must be 6+ characters long and contain at least 1 uppercase letter, 1 number, 1 special character</small>
    <div class="button-container">
      <button name="resetpassword" type="submit" class="btn btn-success">Reset Password</button>
      <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
  <script src="vendor/jquery-3.6.0.min.js"></script>
  <script src="js/PasswordStrengthValidator.js"></script>
</body>
</html>
