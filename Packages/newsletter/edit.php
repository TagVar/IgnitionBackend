<?php
  include("../../functions/login_check.php");
  include("../../config/main_config.php");
  include("config/config.php");
  if (isset($_GET["edit"])) {
    $is_edit = true;
    $identifier = $_GET["which"];
  } else {
    $is_edit = false;
  }
  if ($is_edit === true) {
    $group_query = "SELECT * FROM `$newsletter_table` WHERE id=$identifier";
    $group_result = $connection->query($group_query);
    $group = $group_result->fetch_assoc();
    $group_name_value = $group["group"];
    $recipients_value = $group["recipients"];
  }
  if (isset($_POST["submit_post"])) {
    $group_name_value = $_POST["group_name"];
    $recipients_value = $_POST["recipients"];
    if (str_replace(" ", "", $group_name_value) != "") {
      $requested_recipients = explode(",", $recipients_value);
      $requested_recipients = array_map('trim', $requested_recipients);
      $duplicate_data["counts"] = array_count_values(array_map('strtolower', $requested_recipients));
      $duplicate_data["counts"] = array_change_key_case($duplicate_data["counts"], CASE_LOWER);
      $duplicate_data["duplicates"] = [];
      foreach($requested_recipients as $email) {
        if ($duplicate_data["counts"][strtolower($email)] > 1) {
          if (!in_array(strtolower($email), array_map('strtolower', $duplicate_data["duplicates"]))) {
            $duplicate_data["duplicates"][] = $email;
          }
        }
      }
      if (empty($duplicate_data["duplicates"])) {
        $email_array_data["count"] = count($requested_recipients);
        foreach($requested_recipients as $email) {
          if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
           $email_array_data["passed_validation"]++;
          } else {
           $email_array_data["failed_validation"][] = $email;
          }
        }
        if (($email_array_data["count"] == $email_array_data["passed_validation"]) || ($recipients_value == "")) {
          $row_exists_query = mysqli_query($connection, "SELECT * FROM `$newsletter_table` WHERE `group`='$group_name_value' AND `id`!='$identifier'");
          if (!mysqli_num_rows($row_exists_query) > 0) {
            if ($is_edit === true) {
              $update_query = "UPDATE `$newsletter_table` SET `group`='$group_name_value', `recipients`='$recipients_value' WHERE `id`='$identifier'";
              $connection->query($update_query);
              header('Location: .');
            } elseif ($is_edit === false) {
              $add_query = "INSERT INTO $newsletter_table (`group`, `recipients`) VALUES ('$group_name_value', '$recipients_value')";
              $connection->query($add_query);
              header('Location: .');
            }
          } else {
            $error = "A group with that name already exists.";
          }
        } else {
          if (count($email_array_data["failed_validation"]) == 1) {
            $error = "The following email was not valid: ";
          } else {
            $error = "The following emails were not valid: ";
          }
          foreach($email_array_data["failed_validation"] as $failed_email) {
            $error = $error . $failed_email . ", ";
          }
          $error = chop($error, ", ") . ".";
        }
      } else {
        if (count($duplicate_data["duplicates"]) == 1) {
          $error = "The following email was found in the list more than once: ";
        } else {
          $error = "The following emails were found in the list more than once: ";
        }
        foreach ($duplicate_data["duplicates"] as $duplicate) {
          $error = $error . $duplicate . ", ";
        }
        $error = chop($error, ", ") . ".";
      }
    } else {
      $error = "Please enter group name.";
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="css/newsletter.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading"><?php if ($is_edit === true) { echo "Edit Newsletter Group"; } elseif ($is_edit === false) { echo "Add New Newsletter Group"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" name="group_name" placeholder="Group Name" value="<?php echo $group_name_value; ?>"/>
        <textarea name="recipients" id="content" placeholder="Recipients (Seperate Emails By Comma)"><?php echo $recipients_value; ?></textarea>
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="submit_post" value="<?php if ($is_edit === true) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add Group"; } ?>" />
      </form>
      <form action=".">
        <input type="submit" value="Back To Group Selection">
      </form>
    </div>
  </body>
</html>
