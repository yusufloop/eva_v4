<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>EVA</title>
    <link rel="stylesheet" href="css/forgetpassword.css" type="text/css">
</head>
<body>
<div id="box">
  <form id="forgetpasswordform" action="/actions/auth/forget-password.php"  method="POST">
  <h1>Forget password<span>Enter a valid email.</span></h1>
  <form>
      <input type="email" placeholder="Email Address" id="email" name="email" class="Email">
    <div class="button-container">
      <button name="forgetpassword" type="submit" class="btn btn-success">Submit</button>
      <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>

  </form>
</div>
</body>
</html>
