<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$error = "";
if (isset($_GET["which"])) {
    $identifier = $_GET["which"];
    $is_edit = true;
    $heading = "Edit Album";
    $submit = "Save Album";
} elseif ($_POST["which"]) {
    $identifier = $_POST["which"];
    $is_edit = true;
    $heading = "Edit Album";
    $submit = "Save Album";
} else {
    $is_edit = false;
    $heading = "Add Album";
    $submit = "Add Album";
}
if ((isset($identifier)) && (!isset($_POST["submit"]))) {
    $album_query = "SELECT name FROM `$cms_table` WHERE id='$identifier'";
    $album_result = $connection->query($album_query);
    $album_data = $album_result->fetch_assoc();
    $name = $album_data["name"];
} elseif (isset($_POST["submit"])) {
    $name = $_POST["name"];
    if (str_replace(" ", "", $name) == "") {
        $error = "You did not provide a name for this album.";
    } else {
        if ($is_edit) {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='album' AND id <> '$identifier'";
        } else {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='album'";
        } 
        $check_name_result = $connection->query($check_name_query);
        if (mysqli_num_rows($check_name_result) == 0) {
            if ($is_edit) {
                $add_album_query = "UPDATE `$cms_table` SET name='$name' WHERE id='$identifier'";
            } else {
                $add_album_query = "INSERT INTO `$cms_table` (type, name) VALUES ('album', '$name')";
            }
            if ($connection->query($add_album_query)) {
                header("location: ./");
            } else {
                $error = "An error occurred. Please try again.";
            }
        } else {
            $error = "An album with this name already exists.";
        }
    }
}
if ($is_edit) {
    $album_query = "SELECT name, data FROM `$cms_table` WHERE id='$identifier'";
    $album_result = $connection->query($album_query);
    $album_data = $album_result->fetch_assoc();
    $data_string = $album_data["data"];
    $image_data = explode(":", $data_string);
    foreach($image_data as $key=>$single_image_data) {
        $image_data[$key] = str_replace("&#x0003A;", ":", $single_image_data);
    }
    if ((substr_count($data_string, ":") == 0) && ($data_string == "")) {
        $image_count = 0;
    } else {
        $image_count = count(explode(":", $data_string));
    }
    foreach($image_data as $key=>$single_image_data) {
        $temp_data_array = explode(">", $single_image_data);
        foreach ($temp_data_array as $temp_key=>$temp_data) {
            $temp_data_array[$temp_key] = str_replace("&#x0003E;", ">", $temp_data);
        }
        $image_data[$key] = $temp_data_array;
    }
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
        <input type="text" name="name" placeholder="Album Name" value="<?php echo $name; ?>"/>
        <?php
            if ($is_edit) {
                echo "<br /><br />";
                if ($image_count > 0) {
                    foreach ($image_data as $key=>$single_image_data) {
                        echo "<div class='thumbnail-container'>
                            <img alt='" . $album_data["name"] . " - Image $key' class='preview-thumbnail' src='/nodes/images/" . str_replace("&#x0007C;", "|", $single_image_data[0]) . "'/>
                            <div class='remove-thumbnail'>
                                    X
                            </div>
                            <div class='edit-thumbnail'>
                                    Edit
                            </div>
                        </div>
                        ";
                    }
                } else {
                    echo "There are no images in this album.";
                }
                echo "<br /><br />";
            }
        ?>
        <?php if ($error != "") { echo "<br /><br /><font color='red'>$error</font><br /><br />"; } ?>
        <input type="submit" name="submit" value="<?php echo $submit; ?>"/>
      </form>
      <form action="./">
        <input type="submit" value="Back To Photo Album Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/album.js"></script>
  </body>
</html>
