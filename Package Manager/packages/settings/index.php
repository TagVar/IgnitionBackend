<?php
include("../../functions/login_check.php");
include("../../config/main_config.php");
include("config/config.php");
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/settings.css">
  </head>
  <body>
    <div id="main-container">
      <form action="./pages/login/logins.php">
        <input type="submit" value="Manage Logins">
      </form>
      <br />
      <br />
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>