<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
function get_filesize($path, $file_name) {
  $file_path = $path . $file_name;
  $bytes = filesize($file_path);
  $kilobyte = 1024;
  $megabyte = $kilobyte * 1024;
  $gigabyte = $megabyte * 1024;
  if ($bytes < $kilobyte) {
    $unit = "B";
    $unit_defined_size = $bytes;
  } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
    $unit = "KB";
    $unit_defined_size = $bytes/1024;
  } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
    $unit = "MB";
    $unit_defined_size = $bytes/$megabyte;
  } else {
    $unit = "GB";
    $unit_defined_size = $bytes/$gigabyte;
  }
  return round($unit_defined_size, 2) . " " . $unit;
}
$user_file_path = "./files/" . $_SESSION['user_id'] . "/";
if ($_GET["delete"] == true) {
  $file_to_delete = $_GET["which"];
  unlink($user_file_path . $file_to_delete);
}
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
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/bookkeeping.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>" />
      <span class="heading">File Manager</span>
      <br />
      <br />
      <?php
        if ((substr_count($user_info["files"], ':') >= 1) || ((!empty($files_array)) && (str_replace(" ", "", $files_array[0]) != ""))) {
          echo "
          <table>
            <tr>
              <th>
                Filename
              </th>
              <th>
                Filesize
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          foreach($files_array as $file_name) {
            echo "<tr>
                    <td>";
                echo $file_name;
              echo "</td>
		    <td>";
		echo get_filesize($user_file_path, $file_name);
	      echo "</td>
		    <td>";
                echo "<a href='php/download.php?filename=$file_name'>Download</a><br />";
                echo "<a href='rename.php?filename=$file_name'>Rename</a><br />";
                echo "<a onclick='return confirm(\"Are you sure you want to delete this file?\");' href='?delete=true&which=" . $file_name . "'>Delete</a>";
              echo "</td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "<div class='heading'>You have not added any files.</div>";
        }
      ?>
      <form action="./add.php">
        <input type="submit" value="Add File">
      </form>
      <form action="../">
        <input type="submit" value="Back To Bookkeeping Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="javascript/file_manager.js"></script>
  </body>
</html>