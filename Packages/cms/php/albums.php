<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$album = $_POST["album"];
$identifier = $_POST["which"];
$album_query = "SELECT data FROM `$cms_table` WHERE id='$album'";
$album_result = $connection->query($album_query);
$album_data = $album_result->fetch_assoc();
$data_string = $album_data["data"];
$image_data = explode(":", $data_string);
unset($image_data[$identifier]);
$new_image_data = implode(":", $image_data);
$update_query = "UPDATE `$cms_table` SET data='$new_image_data' WHERE id='$album'";
if ($connection->query($update_query)) {
    echo "True";
} else {
    echo "False";
}
?>