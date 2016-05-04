<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  if (isset($_POST["install_submit"])) {
    $requested_table_names = [
      $_POST["requested_user_table_name"],
      $_POST["requested_client_table_name"]
    ];
    $errors = [];
    $install_queries = [
      'CREATE TABLE `' . $requested_table_names[0] . '` (
	`id` INT(11) AUTO_INCREMENT PRIMARY KEY,
	`user_id` INT(11),
	`files` TEXT,
	`start` TEXT,
	`end` TEXT,
	`records` TEXT,
	`timezone`, TEXT
      )',
      'CREATE TABLE `' . $requested_table_names[1] . '` (
	`name` TEXT,
	`client_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
	`files` TEXT,
	`start` TEXT NULL,
	`end` TEXT NULL,
	`finished` INT(1),
	`paid` INT(1),
	`paid_amount` TEXT
      )'
    ];
    $succesful_table_creations = 0;
    $preexisting_tables = [];
    foreach($requested_table_names as $key=>$requested_table_name) {
      $table_exists_query = "SELECT 1 FROM `$requested_table_name` LIMIT 1";
      $table_exists_result = $connection->query($table_exists_query);
      if($table_exists_result !== false) {
	$errors[$key] = "The table name you specified already exists.";
	$preexisting_tables[] = $requested_table_name;
      } else {
	if (strlen($requested_table_name) > 50) {
	  $errors[$key] = "The table name you specified is longer than 50 characters";
	} else {
	  if (str_replace(" ", "", $requested_table_name) == "") {
	    $errors[$key] = "You must provide a table name.";
	  } else {
	    if (!preg_match('/^[a-zA-Z0-9_]+$/', $requested_table_name)) {
	      $errors[$key] = "The table name you specified contained illegal characters";
	    } else {
	      if ($requested_table_names[0] != $requested_table_names[1]) {
		if ($connection->query($install_queries[$key]) === TRUE) {
		  $succesful_table_creations++;
		} else {
		  $errors[$key] = "There was a problem creating this table. Please try again.";
		}
	      } else {
		$errors[$key] = "Client and User tables must have different names.";
	      }
	    }
	  }
	}
      }
    }
    if ($succesful_table_creations == 2) {
      $config_file = "config.php";
      file_put_contents($config_file, str_replace('$bookkeeping_user_table = ""', '$bookkeeping_user_table = "' . $requested_table_names[0] . '"', file_get_contents($config_file)));
      file_put_contents($config_file, str_replace('$bookkeeping_client_table = ""', '$bookkeeping_client_table = "' . $requested_table_names[1] . '"', file_get_contents($config_file)));
      header("location: ../../../../landing.php");
    } else {
      foreach($requested_table_names as $table_to_delete) {
	if (!in_array($table_to_delete, $preexisting_tables)) {
	  $connection->query('DROP TABLE IF EXISTS `' . $table_to_delete . '`;');
	}
      }
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/bookkeeping.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Install Package: Newsletter</span>
      <br />
      <form method="post" action="">
        <p>
          This page will install the Bookkeeping package. The name you enter into the field below will reflect the name of the table housing all of this package's data within your database. This package requires the pre-installed "Settings" package. If you've deleted this package, or installed a version of IgnitionBackend that doesn't include the "Settings" package, please re-install IgnitionBackend.
          <br />
          <br />
          This action is irreversible. You cannot change the table's name after installation. Please select your table name carefully.
          <br />
          <br />
          The table name you select may only contain letters, numbers, underscores, and may not be longer than 50 characters in total length.
        </p>
        <input type="text" name="requested_user_table_name" placeholder="Desired User Table Name"/>
        <br />
        <input type="text" name="requested_client_table_name" placeholder="Desired Client Table Name"/>
        <br />
        <?php
	  if (count($errors) > 0) {
	    echo "<br />";
	    foreach($errors as $key=>$error) {
	      if ($key == 0) {
		$error_table = "User Table Error: ";
	      } else {
		$error_table = "Client Table Error: ";
	      }
	      if ((count($errors) > 1) && ($key == 0)) {
		echo '<font color="red">' . $error_table . $errors[$key] . "</font><br />";
	      } else {
		echo '<font color="red">' . $error_table . $errors[$key] . "</font>";
	      }
	    }
	  }
	?>
        <br />
        <input type="submit" name="install_submit" value="Install" onclick='return confirm("This action is irreversible. Are you sure that you are satisfied with your entry?");'>
      </form>
      <form action="../../../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
