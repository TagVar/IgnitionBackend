<?php
include("../../functions/login_check.php");
include("../../config/main_config.php");
include("config/config.php");
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/cms.css">
    <link rel="stylesheet" type="text/css" href="css/landing.css">
  </head>
  <body>
    <div id="main-container">
      <a class="sub-package-button left" href="albums">Photo Albums</a>
      <a class="sub-package-button" href="data">Data</a>
      <a class="sub-package-button bottom left" href="pages">Pages</a>
      <a class="sub-package-button bottom" href="templates">Templates</a>
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
