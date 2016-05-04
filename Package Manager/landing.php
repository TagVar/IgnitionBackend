<?php
  include("functions/login_check.php");
  include("config/main_config.php");
  include("config/package_manager_config.php");
 ?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
  </head>
  <body>
    <div id="main-container">
      <?php
	$packages_permitted = 0;
        if (empty($installed_packages) || (count($installed_packages) == 1)) {
          echo "<div class='heading'>You have not installed any packages.</div>";
        } else {
          foreach($installed_packages as $package) {
            if ($package[4] === true) {
              $button_action = $package[0] . $package[2];
              $button_value = $package[3];
            } else {
              $button_action = $package[0];
              $button_value = $package[1];
            }
            if ((in_array($package[5], $ignition_user_permissions)) || in_array("root", $ignition_user_permissions)) {
	      $packages_permitted++;
	      echo '<form action="' . $button_action . '/">
		<input type="submit" value="' . $button_value . '">
	      </form>';
	    }
          }
          if ($packages_permitted == 0) {
	    echo "<div class='heading'>You have not have permission to access any packages.</div>";
	  }
        }
      ?>
      <br />
      <br />
      <form action="functions/logout.php">
        <input type="submit" value="Log Out">
      </form>
    </div>
  </body>
</html>
