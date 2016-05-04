<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
if (isset($_GET["filename"])) {
    $current_filename = $_GET["filename"];
} else {
    $current_filename = $_POST["current_filename"];
}
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
$filename = $current_filename;
if (isset($_POST["submit"])) {
  $filename = $_POST["filename"];
  if (strpos($filename, '/') !== false) {
    $error = 'Filename cannot contain "/".';
  } else {
    if (str_replace(" ", "", $filename) != "") {
        if (!in_array($requested_filename, $files_array)) {
        rename($user_file_path . $current_filename, $user_file_path . $filename);
        header("location: ./");
        } else {
        $error = "Filename already exists.";
        }
    } else {
        $error = "You did not provide a new filename.";
    }
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
      <span class="heading">File Manager - Rename File <?php echo $current_filename; ?></span>
      <br />
      <br />
      <form action="" method="post" enctype='multipart/form-data'>
        <input type="hidden" value="<?php echo $current_filename; ?>" name="current_filename"/>
	<input type="text" placeholder="New Filename" name="filename" value="<?php echo $filename; ?>"/>
	<?php
	  if ($error != "") {
	    echo "<br /><br /><font color='red'>$error</font><br /><br />";
	  }
	?>
	<input type="submit" name="submit" value="Rename File">
      </form>
      <form action="./">
        <input type="submit" value="Back To File Manager">
      </form>
    </div>
  </body>
</html>