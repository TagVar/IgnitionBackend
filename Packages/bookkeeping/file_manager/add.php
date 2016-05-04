<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$user_file_path = "./files/" . $_SESSION['user_id'] . "/";
$user_info_query = "SELECT files FROM `$bookkeeping_user_table` WHERE user_id = " . $_SESSION['user_id'];
if (!file_exists($user_file_path)) {
  mkdir($user_file_path);
} else {
  $known_files_array = [".", ".."];
  $unknown_files_array = array_diff(scandir($user_file_path), $known_files_array = [".", ".."]);
  $filtered_unknown_files_array = [];
  foreach($unknown_files_array as $key=>$unfiltered_filename) {
    $filtered_unknown_files_array[$key] = str_replace(":", "&#x0003A;", $unfiltered_filename);
  }
  $unknown_files_string = implode(":", $filtered_unknown_files_array);
  $user_info_result = $connection->query($user_info_query);
  $user_info = $user_info_result->fetch_assoc();
  if ($unknown_files_string != $user_info["files"]) {
    $update_files_query = "UPDATE `$bookkeeping_user_table` SET files='" . $unknown_files_string . "' WHERE user_id='" . $_SESSION["user_id"] . "'";
    $connection->query($update_files_query);
  }
}
$user_info_result = $connection->query($user_info_query);
$user_info = $user_info_result->fetch_assoc();
$files_array = explode(":", $user_info["files"]);
foreach($files_array as $key=>$file_name) {
  $files_array[$key] = str_replace("&#x0003A;", ":", $file_name);
}
if (isset($_POST["submit"])) {
  $new_name = $_POST["new_name"];
  $filename = $_POST["filename"];
  $uploaded_filename = basename($_FILES["file"]["name"]);
  if (!empty($_FILES['file']['name'])) {
    if ($new_name == "checked") {
      $new_name_checked = "checked"; 
      if (str_replace(" ", "", $filename) != "") {
        $requested_filename = $filename;
      } else {
        $error = "You did not provide a new name for your file.";
      }
    } else {
        $requested_filename = $uploaded_filename;
    }
    if (strpos($filename, '/') !== false) {
      $error = 'Filename cannot contain "/".';
    } else {
        if (!in_array($requested_filename, $files_array)) {
        if ($error == null) {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $user_file_path . $requested_filename)) {
            header("location: ./");
            } else {
            $error = "An error occured. Please try again.";
            }
        }
        } else {
        $error = "Filename already exists.";
        }
      }
  } else {
    $error = "You did not upload a file.";
  }
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/bookkeeping.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">File Manager</span>
      <br />
      <br />
      <form action="" method="post" enctype='multipart/form-data'>
	<input value="checked" type="checkbox" name="new_name" <?php echo $new_name_checked; ?>/> Use New Filename
	<input type="text" placeholder="New Filename" name="filename" value="<?php echo $filename; ?>"/>
	<br />
	<br />
	<input type="file" name="file">
	<?php
	  if ($error != "") {
	    echo "<br /><br /><font color='red'>$error</font><br /><br />";
	  }
	?>
	<input type="submit" name="submit" value="Add File">
      </form>
      <form action="./">
        <input type="submit" value="Back To File Manager">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="javascript/file_manager.js"></script>
  </body>
</html>