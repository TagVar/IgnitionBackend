<?php
include("../../../../functions/login_check.php");
include("../../../../config/main_config.php");
include("../../../../config/package_manager_config.php");
include("../../config/config.php");
if (isset($_GET["edit"])) {
  $is_edit = true;
  $identifier = $_GET["which"];
  $password_placeholder = "Enter New Password";
  $user_query = "SELECT * FROM `logins` WHERE id='" . $identifier . "'";
  $user_result = $connection->query($user_query);
  $user = mysqli_fetch_array($user_result);
  $full_name = $user["name"];
  $email = $user["email"];
  $phone_number = $user["phone"];
  $username_field = $user["username"];
  $selected_user_permissions = explode(":", $user["permissions"]);
  foreach($selected_user_permissions as $key=>$selected_user_permission) {
    $selected_user_permissions[$key] = str_replace("&#58;", ":", $selected_user_permission);
  }
} else {
  $is_edit = false;
  $password_placeholder = "Create New Password";
}
function validate_phone($phone_number) {
  if (!ctype_digit(str_replace(" ", "", str_replace(".", "", str_replace("(", "", str_replace(")", "", str_replace("-", "", $phone_number))))))) {
    return false;
  } elseif ((strlen(preg_replace("/[^0-9]/", "", $phone_number)) > 11) || (strlen(preg_replace("/[^0-9]/", "", $phone_number)) < 10)) {
    return false;
  } else {
    return true;
  }
}
if (isset($_POST["submit_post"])) {
  $full_name = $_POST["full_name"];
  $email = $_POST["email"];
  $phone_number = $_POST["phone_number"];
  $username_field = $_POST["username_field"];
  $password_field = $_POST["password_field"];
  $confirm_password_field = $_POST["confirm_password_field"];
  $permissions_fields = $_POST["permissions_fields"];
  $root_checkbox = $_POST["root_checkbox"];
  if (str_replace(" ", "", $full_name) != "") {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      if (validate_phone($phone_number)) {
	if ((str_replace(" ", "", $username_field) != "") && (strpos($username_field, " ") == false)) {
	  $username_query = "SELECT * FROM `logins` WHERE username='" . $username_field . "' LIMIT 1";
	  $username_result = $connection->query($username_query);
	  if ((mysqli_num_rows($username_result) == 0) || ($is_edit)) {
	    if ((str_replace(" ", "", $password_field) != "") || str_replace(" ", "", $confirm_password_field) != "") {
	      if ($password_field != $confirm_password_field) {
		$error = "Passwords do not match.";
	      } elseif (strpos($password_field, " ") !== false) {
		$error = "Passwords may not contain spaces.";
	      } elseif (strlen($password_field) < 10) {
		$error = "Passwords must be at least 10 characters in length.";
	      } else {
		$final_password = password_hash($password_field, PASSWORD_DEFAULT);
	      }
	    } else {
	      if ($is_edit) {
		$final_password = $user["password"];
	      } else {
		$error = "You did not provide a user password.";
	      }
	    }
	    if ($error === null) {
	      if ($root_checkbox == "root") {
		$final_user_permissions = "root";
	      } else {
		$final_user_permissions_array = [];
		foreach($permissions_fields as $permission_field) {
		  $final_user_permissions_array[] = str_replace(":", "&#58;", $permission_field);
		}
		$final_user_permissions = implode(":", $final_user_permissions_array);
	      }
	      if ($is_edit) {
		$delete_user_query = "DELETE FROM `logins` WHERE id=$identifier";
		$connection->query($delete_user_query);
	      }
	      $add_query = "INSERT INTO `logins` (`name`, `email`, `phone`, `username`, `password`, `permissions`) VALUES ('$full_name', '$email', '$phone_number', '$username_field', '$final_password', '$final_user_permissions')";
	      $connection->query($add_query);
	      header('Location: ./logins.php');
	    }
	  } else {
	    $error = "The requested username already exists.";
	  }
	} else {
	  $error = "The username you provided is invalid. Usernames may not contain spaces.";
	}
      } else {
	$error = "The user phone number you provided is invalid.";
      }
    } else {
      $error = "The user email you provided is invalid.";
    }
  } else {
    $error = "Please enter a full name for the user.";
  }
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/settings.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading"><?php if ($is_edit === true) { echo "Edit User"; } elseif ($is_edit === false) { echo "Add New User"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" name="full_name" placeholder="Full Name" value="<?php echo $full_name; ?>"/>
        <input type="text" name="email" placeholder="Email" value="<?php echo $email; ?>"/>
        <input type="text" name="phone_number" placeholder="Phone Number" value="<?php echo $phone_number; ?>"/>
        <br />
        <br />
        <input type="text" name="username_field" placeholder="Username" value="<?php echo $username_field; ?>"/>
        <br />
        <br />
        <input type="password" name="password_field" placeholder="<?php echo $password_placeholder; ?>" value="<?php echo $password_field; ?>"/>
        <input type="password" name="confirm_password_field" placeholder="Confirm New Password" value="<?php echo $confirm_password_field; ?>"/>
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          } else {
	    echo "<br /><br /><br />";
          }
        ?>
	<b/>Permissions:</b>
	<br />
	<?php
	  if (in_array("root", $ignition_user_permissions)) {
	    if (!isset($_POST["submit_post"])) {
	      if(in_array("root", $selected_user_permissions)) {
		$root_checked = "checked";
	      } else {
		$root_checked = "";
	      }
	    } else {
	      if ($root_checkbox == "root") {
		$root_checked = "checked";
	      } else {
		$root_checked = "";
	      }
	    }
	    echo '<br /><input type="checkbox" name="root_checkbox" value="root" ' . $root_checked . '/> Give This User Root Permissions<br />';
	  }
	?>
	<div id="permissions-container">
	  <?php
	    if (empty($installed_packages) || (count($installed_packages) == 1)) {
	      echo "No packages installed.";
	    } else {
	      foreach($installed_packages as $package) {
		if ($package[4] !== true) {
		  $permission_name = $package[5];
		  if ((in_array($permission_name, $ignition_user_permissions)) || (in_array("root", $ignition_user_permissions))) {
		    $checkbox_label = $package[1];
		    if (!isset($_POST["submit_post"])) {
		      if(in_array($permission_name, $selected_user_permissions)) {
			$is_checked = "checked";
		      } else {
			$is_checked = "";
		      }
		    } else {
		      if (in_array($permission_name, $permissions_fields)) {
			$is_checked = "checked";
		      } else {
			$is_checked = "";
		      }
		    }
		    echo '<br /><input type="checkbox" name="permissions_fields[]" value="' . $permission_name . '" ' . $is_checked . '/>  ' . $checkbox_label;
		  }
	      }
	      }
	    }
	  ?>
	</div>
	<br />
	<br />
        <input type="submit" name="submit_post" value="<?php if ($is_edit === true) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add User"; } ?>" />
      </form>
      <form action="./logins.php">
        <input type="submit" value="Back To User Selection">
      </form>
    </div>
    <script tpye="text/javascript" src="../../javascript/jquery.js"></script>
    <script type="text/javascript" src="../../javascript/logins.js"></script>
  </body>
</html>
