<?php
include("../../../../functions/login_check.php");
include("../../../../config/main_config.php");
include("../../../../config/package_manager_config.php");
include("../../config/config.php");
$identifier = $_GET["which"];
$user_query = "SELECT * FROM `logins` WHERE id='" . $identifier . "'";
$user_result = $connection->query($user_query);
$user = mysqli_fetch_array($user_result);
$full_name = $user["name"];
$email = $user["email"];
$phone_number = $user["phone"];
$view_username = $user["username"];
if ($user["permissions"] != "") {
  $selected_user_permissions = explode(":", $user["permissions"]);
} else {
  $selected_user_permissions = [];
}
foreach($selected_user_permissions as $key=>$selected_user_permission) {
  $selected_user_permissions[$key] = str_replace("&#58;", ":", $selected_user_permission);
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/settings.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">View User</span>
      <br />
      <br />
      Full Name: <?php echo $full_name; ?>
      <br />
      Username: <?php echo $view_username; ?>
      <br  />
      Email: <?php echo $email; ?>
      <br  />
      Phone Number: <?php echo $phone_number; ?>
      <br />
      <br />
      <b>User Permissions:</b>
      <?php
      if ($user["permissions"] != "root") {
	if (empty($installed_packages) || (count($installed_packages) == 1)) {
	  echo "No packages installed.";
	} else {
	  if (empty($selected_user_permissions)) {
	    echo "<br />This user does not have any permissions.";
	  } else {
	    foreach($installed_packages as $package) {
	      if ($package[4] !== true) {
		$permission_name = $package[5];
		$package_name = $package[1];
		if(in_array($permission_name, $selected_user_permissions)) {
		  echo "<br />" . $package_name;
		}
	      }
	    }
	  }
	}
      } else {
	echo "<br />This User Has Root Priviliges";
      }
      ?>
      <br />
      <br />
      <form action="./logins.php">
        <input type="submit" value="Back To User Selection">
      </form>
    </div>
  </body>
</html>