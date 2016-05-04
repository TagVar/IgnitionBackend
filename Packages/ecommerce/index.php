<?php
include("../../functions/login_check.php");
include("../../config/main_config.php");
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/ecom.css">
    <link rel="stylesheet" type="text/css" href="css/landing.css">
  </head>
  <body>
    <div id="main-container">
      <a class="sub-package-button left" href="products">Products</a>
      <a class="sub-package-button" href="promo">Promotional Codes</a>
      <a class="sub-package-button bottom left" href="records">Records</a>
      <a class="sub-package-button bottom" href="settings">Settings</a>
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>