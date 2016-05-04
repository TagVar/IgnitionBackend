<?php
  $path_to_file =  str_replace($_SERVER["DOCUMENT_ROOT"], "", realpath(dirname(__FILE__)));
  include($_SERVER['DOCUMENT_ROOT'] . str_replace("/functions", "", $path_to_file) . '/config/main_config.php');
  session_start();
  if (($_SESSION["logged_in"] !== true) || (str_replace(" ", "", $_SESSION["user_id"]) == "")) {
    header('Location: ' . $path_to_file . '/../');
  } else {
    if ($_SESSION["user_id"] != 0) {
      $ignition_user_query = 'SELECT permissions FROM `logins` WHERE id = ' . $_SESSION["user_id"];
      $ignition_user_result = $connection->query($ignition_user_query);
      $ignition_user_data = $ignition_user_result->fetch_assoc();
      $_SESSION["permissions"] = $ignition_user_data["permissions"];
    } else {
      $_SESSION["permissions"] = "root";
    }
    $ignition_user_permissions = explode(":", $_SESSION["permissions"]);
    foreach($ignition_user_permissions as $key=>$ignition_permission) {
      $ignition_user_permissions[$key] = str_replace("&#58;", ":", $ignition_permission);
    }
  }
  if (!in_array("root", $ignition_user_permissions)) {
    if (strpos($_SERVER["SCRIPT_FILENAME"], 'packages') !== false) {
      $has_permission = false;
      foreach($ignition_user_permissions as $ignition_permission) {
	if (strpos($_SERVER["SCRIPT_FILENAME"], 'packages/' . $ignition_permission)) {
	  $has_permission = true;
	}
      }
      if (!$has_permission) {
	header('Location: ' . $path_to_file . '/../landing.php');
      }
    }
  }
 ?>
