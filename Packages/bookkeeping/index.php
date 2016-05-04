<?php
include("../../functions/login_check.php");
include("../../config/main_config.php");
include("config/config.php");
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/bookkeeping.css">
    <link rel="stylesheet" type="text/css" href="css/landing.css">
  </head>
  <body>
    <div id="main-container">
      <a class="sub-package-button left" href="timeclock">Timeclock</a>
      <a class="sub-package-button" href="file_manager">Manage Files</a>
      <a class="sub-package-button bottom left" href="records">Manage Records</a>
      <a class="sub-package-button bottom" href="settings">Settings</a>
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
