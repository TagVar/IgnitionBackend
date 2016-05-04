<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$error = "";
if (isset($_GET["which"])) {
    $identifier = $_GET["which"];
    $is_edit = true;
    $heading = "Edit Data Point";
    $submit = "Save Data Point";
} elseif ($_POST["which"]) {
    $identifier = $_POST["which"];
    $is_edit = true;
    $heading = "Edit Data Point";
    $submit = "Save Data Point";
} else {
    $is_edit = false;
    $heading = "Add Data Point";
    $submit = "Add Data Point";
}
if ((isset($identifier)) && (!isset($_POST["submit"]))) {
    $data_query = "SELECT name, data FROM `$cms_table` WHERE id='$identifier'";
    $data_result = $connection->query($data_query);
    $data_data = $data_result->fetch_assoc();
    $name = $data_data["name"];
    $json = $data_data["data"];
} elseif (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $json = $_POST["json"];
    if (str_replace(" ", "", $name) == "") {
        $error = "You did not provide a name for this data.";
    } else {
        if ($is_edit) {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='data' AND id <> '$identifier'";
        } else {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='data'";
        } 
        $check_name_result = $connection->query($check_name_query);
        if (mysqli_num_rows($check_name_result) == 0) {
            @json_decode($json);
            if (json_last_error() === JSON_ERROR_NONE) {
                $validated = false;
                if ($is_edit) {
                    $delete_data_query = "DELETE FROM `$cms_table` WHERE id='$identifier'";
                    if ($connection->query($delete_data_query)) {
                        $validated = true;
                    }
                } else {
                    $validated = true;
                }
                if ($validated) {
                $add_data_query = "INSERT INTO `$cms_table` (type, name, data) VALUES ('data', '$name', '" . str_replace("'", "&#x00027;", $json) . "')";
                if ($connection->query($add_data_query)) {
                    header("location: ./");
                } else {
                    $error = "An error occurred. Please try again.";
                }
                } else {
                    $error = "An error occured. Please try again.";
                }
            } else {
                $error = "Your JSON is invalid.";
            }
        } else {
            $error = "A data point with this name already exists.";
        }
    }
}
if ($json == "") {
    $json = "{}";
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/cms.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading"><?php echo $heading; ?></span>
      <br />
      <form action="" method="post">
        <input type="hidden" value="<?php echo $identifier; ?>" name="which"/>
        <input type="text" name="name" placeholder="Data Point Name" value="<?php echo $name; ?>"/>
        <textarea name="json" id="content" placeholder="JSON"><?php echo $json; ?></textarea>
        <?php if ($error != "") { echo "<br /><br /><font color='red'>$error</font><br /><br />"; } ?>
        <input type="submit" name="submit" value="<?php echo $submit; ?>"/>
      </form>
      <form action="./">
        <input type="submit" value="Back To Data Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>