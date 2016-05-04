<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  if (isset($_POST["install_submit"])) {
    $requested_table_name = $_POST["requested_table_name"];
    $table_exists_query = "SELECT 1 FROM `$requested_table_name` LIMIT 1";
    $table_exists_result = $connection->query($table_exists_query);
    if($table_exists_result !== false) {
      $error = "The table name you specified already exists.";
    }
    else {
      if (strlen($requested_table_name) > 50) {
        $error = "The table name you specified is longer than 50 characters";
      } else {
        if (str_replace(" ", "", $requested_table_name) == "") {
          $error = "You must provide a table name.";
        } else {
          if (!preg_match('/^[a-zA-Z0-9_]+$/', $requested_table_name)) {
            $error = "The table name you specified contained illegal characters";
          } else {
            $install_query = 'CREATE TABLE `' . $requested_table_name . '` (
              id INT(11) AUTO_INCREMENT PRIMARY KEY,
              `group` TEXT,
              recipients TEXT
            )';
            if ($connection->query($install_query) === TRUE) {
              $config_file = "config.php";
              file_put_contents($config_file, str_replace('$newsletter_table = ""', '$newsletter_table = "' . $requested_table_name . '"', file_get_contents($config_file)));
              if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes")) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes");
              }
              if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes/images")) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/images");
              }
              if (!file_exists($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php")) {
                if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/nodes/config")) {
                  mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/config");
                }
                $node_config_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php", "w");
                $node_config_file_contents = '<?php
  $node_sql_host = "' . $sql_host . '";
  $node_sql_username = "' . $sql_username . '";
  $node_sql_password = "' . $sql_password . '";
  $node_sql_database = "' . $sql_database . '";
  $node_connection = new mysqli($node_sql_host, $node_sql_username, $node_sql_password, $node_sql_database);
?>';
                fwrite($node_config_file, $node_config_file_contents);
                fclose($node_config_file);
              }
              $node_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/nodes/newsletter.php", "w");
              $node_file_contents = '<?php
include($_SERVER["DOCUMENT_ROOT"] . "/nodes/config/config.php");
function add_recipient($group, $recipient) {
  global $node_connection;
  $recipients_query = "SELECT `recipients` FROM `' . $requested_table_name . '` WHERE `group` = \'" . $group . "\'";
  $recipients_result = mysqli_query($node_connection, $recipients_query);
  $recipients_data = mysqli_fetch_assoc($recipients_result);
  $recipients = $recipients_data["recipients"];
  if ($recipients == "") {
    $add_recipient_query = "UPDATE `' . $requested_table_name . '` SET `recipients`=\'" . $recipient . "\' WHERE `group`=\'" . $group . "\'";
  } else {
    $new_recipient_data = $recipients . ", " . $recipient;
    $add_recipient_query = "UPDATE `' . $requested_table_name . '` SET `recipients`=\'" . $new_recipient_data . "\' WHERE `group`=\'" . $group . "\'";
  }
  if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
    $unspaced_recipients = str_replace(" ", "", $recipients);
    $recipients_array = explode(",", $unspaced_recipients);
    $duplicate_counter = 0;
    foreach($recipients_array as $recipient_value) {
      if (strtolower($recipient_value) == strtolower($recipient)) {
        $duplicate_counter++;
      }
    }
    if ($duplicate_counter == 0) {
      if($node_connection->query($add_recipient_query)) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  } else {
    return false;
  }
}
function remove_recipient($group, $recipient) {
  global $node_connection;
  $recipients_query = "SELECT `recipients` FROM `' . $requested_table_name . '` WHERE `group` = \'" . $group . "\'";
  $recipients_result = mysqli_query($node_connection, $recipients_query);
  $recipients_data = mysqli_fetch_assoc($recipients_result);
  $spaced_recipients = $recipients_data["recipients"];
  $recipients = str_replace(" ", "", $spaced_recipients);
  $recipients_array = explode(",", $recipients);
  foreach($recipients_array as $key=>$value) {
    if (strtolower($value) == strtolower($recipient)) {
      unset($recipients_array[$key]);
    }
  }
  $updated_recipients = implode(", ", $recipients_array);
  $update_recipient_query = "UPDATE `' . $requested_table_name . '` SET `recipients`=\'" . $updated_recipients . "\' WHERE `group`=\'" . $group . "\'";
  if($node_connection->query($update_recipient_query)) {
    return true;
  } else {
    return false;
  }
}
?>';
              fwrite($node_file, $node_file_contents);
              fclose($node_file);
              header("location: ../../../../landing.php");
            } else {
              $error = "There was a problem installing this package. Please try again.";
            }
            $connection->query($install_query);
          }
        }
      }
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/newsletter.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Install Package: Newsletter</span>
      <br />
      <form method="post" action="">
        <p>
          This page will install the Newsletter Administration package. The name you enter into the field below will reflect the name of the table housing all of this package's data within your database.
          <br />
          <br />
          This action is irreversible. You cannot change the table's name after installation. Please select your table name carefully.
          <br />
          <br />
          The table name you select may only contain letters, numbers, underscores, and may not be longer than 50 characters in total length.
        </p>
        <input type="text" name="requested_table_name" placeholder="Desired Table Name"/>
        <br />
        <?php if ($error != null) { echo "<br /><font color='red'>$error</font><br />"; } ?>
        <br />
        <input type="submit" name="install_submit" value="Install" onclick='return confirm("This action is irreversible. Are you sure that you are satisfied with your entry?");'>
      </form>
      <form action="../../../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
