<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$error = "";
$outside_checked = "checked";
if (isset($_GET["which"])) {
    $identifier = $_GET["which"];
} elseif (isset($_POST["which"])) {
    $identifier = $_POST["which"];
}
$album_query = "SELECT name, data FROM `$cms_table` WHERE id='$identifier'";
$album_result = $connection->query($album_query);
$album_data = $album_result->fetch_assoc();
$name = $album_data["name"];
$data_string = $album_data["data"];
if (isset($_GET["image"])) {
    $image_identifier = $_GET["image"];
} else if (isset($_POST["image_identifier"])) {
    $image_identifier = $_POST["image_identifier"];
} else {
    $image_identifier = "";
    $page_heading = "Add Image To " . $name;
    $submit_button = "Add Image";
}
if ($image_identifier != "") {
    $page_heading = "Edit Image Attributes";
    $submit_button = "Save Attributes";
    if (!isset($_POST["submit"])) {
        $image_data = explode(":", $data_string);
        foreach($image_data as $key=>$single_image_data) {
            $image_data[$key] = str_replace("&#x0003A;", ":", $single_image_data);
        }
        foreach($image_data as $key=>$single_image_data) {
            $temp_data_array = explode(">", $single_image_data);
            foreach ($temp_data_array as $temp_key=>$temp_data) {
                $temp_data_array[$temp_key] = str_replace("&#x0003E;", ">", $temp_data);
            }
            $image_data[$key] = $temp_data_array;
        }
        foreach($image_data as $key=>$single_image_data) {
            foreach($single_image_data as $data_key=>$data_value) {
                if ($data_key != 0) {
                    $temp_attribute_array = explode("|", $data_value);
                    foreach($temp_attribute_array as $temp_attribute_key=>$temp_attribute_value) {
                        $temp_attribute_array[$temp_attribute_key] = str_replace("&#x0007C;", "|", $temp_attribute_value);
                    }
                    $image_data[$key][$data_key] = $temp_attribute_array;
                }
            }
        }
        $current_image = $image_data[$image_identifier];
        unset($current_image[0]);
        $attribute_names_value = [];
        $attribute_contents_value = [];
        foreach($current_image as $current_attribute) {
            $attribute_names_value[] = $current_attribute[0];
            $attribute_contents_value[] = $current_attribute[1];
        }
    }
}
if (isset($_POST["submit"])) {
    $attribute_names_value = $_POST["attribute_names"];
    $attribute_contents_value = $_POST["attribute_contents"];
}
if ((isset($_POST["submit"])) && ($image_identifier == "")) {
    $url = $_POST["url_box"];
    $already_selected = $_POST["already_selected"];
    $image_src = $_POST["image_src"];
    $upload_input = $_FILES["upload_input"];
    $current_file_location = $upload_input["tmp_name"];
    $current_file_name = $upload_input["name"];
    $current_file_size = $upload_input["size"];
    $image_exploded_filename = explode(".", $current_file_name);
    $image_file_type = strtolower(array_pop($image_exploded_filename));
    $image_accepted_filetypes = [
        "png",
        "jpg",
        "gif",
        "jpeg"
    ];
    if ($image_src == "outside") {
        $outside_checked = "checked";
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            $exploded_url = explode(".", $url);
            $url_file_type = strtolower(array_pop($exploded_url));
            if (in_array($url_file_type, $image_accepted_filetypes)) {
                $image_name_to_add = $url;
            } else {
                $error = "URL does not point to image.";
            }
        } else {
            $error = "Invalid URL.";
        }
    } elseif ($image_src == "previous") {
        $previous_checked = "checked";
        $outside_checked = "";
        if ($_POST["previous_image"] != "") {
            $image_name_to_add = $_POST["previous_image"];
        } else {
            $error = "You did not select an image.";
        }
    } else {
        $upload_checked = "checked";
        $outside_checked = "";
        if ($current_file_size <= 20000000) {
            if (in_array($image_file_type, $image_accepted_filetypes)) {
                $new_file_name = uniqid('', true) . "." . $image_file_type;
                if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
                    $new_file_name = uniqid('', true) . $image_file_type;
                }
                if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
                    $error = "An error occured. Please try again.";
                } else {
                    if (move_uploaded_file($current_file_location, $_SERVER["DOCUMENT_ROOT"] . "/nodes/images/" . $new_file_name)) {
                        $image_name_to_add = $new_file_name;
                    } else {
                    $error = "An error occured. Please try again.";
                    }
                }
            } else {
            $error = "Only PNG, JPG, JPEG, and GIF file extensions are accepted.";
            }
        } else {
            $error = "Images may not exceed 20 megabytes in size.";
        }
    }
}
if (($error == "") && (isset($_POST["submit"]))) {
    $new_attributes_array = [];
    if (!empty($attribute_names_value)) {
        $empty_names = false;
        foreach($attribute_names_value as $attribute_name) {
            if (str_replace(" ", "", $attribute_name) == "") {
                $empty_names = true;
            }
        }
        if ($empty_names) {
            $error = "Attribute names cannot be blank.";
        } else {
            if(count(array_unique($attribute_names_value)) < count($attribute_names_value)) {
                $error = "Duplicate attribute names are not permitted.";
            } else {
                foreach($attribute_names_value as $index=>$attribute_name) {
                    $new_attributes_array[$index] = str_replace(":", "&#x0003A;", str_replace(">", "&#x0003E;", str_replace("|", "&#x0007C;", $attribute_name) . "|" . str_replace("|", "&#x0007C;", $attribute_contents_value[$index])));
                }
            }
        }
    }
    if ($error == "") {
        if ($image_identifier == "") { 
            $new_entry = str_replace(":", "&#x0003A;", str_replace(">", "&#x0003E;", str_replace("|", "&#x0007C;", $image_name_to_add))) . ">" . implode(">", $new_attributes_array);
            if ($data_string == "") {
                $final_data_string = $new_entry;
            } else {
                $final_data_string = $data_string . ":" . $new_entry;
            }
            $update_data_query = "UPDATE `$cms_table` SET data='" . str_replace("'", "&#x00027;", $final_data_string) . "' WHERE id='$identifier'";
            if ($connection->query($update_data_query)) {
                header("location: ./");
            } else {
                $error = "An error occured; please try again.";
            }
        } else {
            $image_data = explode(":", $data_string);
            foreach($image_data as $key=>$single_image_data) {
                $image_data[$key] = str_replace("&#x0003A;", ":", $single_image_data);
            }
            foreach($image_data as $key=>$single_image_data) {
                $temp_data_array = explode(">", $single_image_data);
                foreach ($temp_data_array as $temp_key=>$temp_data) {
                    $temp_data_array[$temp_key] = str_replace("&#x0003E;", ">", $temp_data);
                }
                $image_data[$key] = $temp_data_array;
            }
            foreach($image_data as $key=>$single_image_data) {
                foreach($single_image_data as $data_key=>$data_value) {
                    if ($data_key != 0) {
                        $temp_attribute_array = explode("|", $data_value);
                        foreach($temp_attribute_array as $temp_attribute_key=>$temp_attribute_value) {
                            $temp_attribute_array[$temp_attribute_key] = str_replace("&#x0007C;", "|", $temp_attribute_value);
                        }
                        $image_data[$key][$data_key] = $temp_attribute_array;
                    }
                }
            }
            $current_image = $image_data[$image_identifier];
            $new_entry = str_replace(":", "&#x0003A;", str_replace(">", "&#x0003E;", str_replace("|", "&#x0007C;", $current_image[0]))) . ">" . implode(">", $new_attributes_array);
            $updated_image_data_array = explode(":", $data_string);
            $updated_image_data_array[$image_identifier] = $new_entry;
            $updated_data_string = implode(":", $updated_image_data_array);
            $update_attributes_query = "UPDATE `$cms_table` SET data='" . str_replace("'", "&#x00027;", $updated_data_string) . "' WHERE id='$identifier'";
            if ($connection->query($update_attributes_query)) {
                header("location: ./");
            } else {
                $error = "An error occured; please try again.";
            }
        }
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
      <span class="heading"><?php echo $page_heading; ?></span>
      <br />
      <br />
      <form action="" method="post" enctype="multipart/form-data" method="post">
        <input type="hidden" value="<?php echo $identifier; ?>" name="which"/>
        <input type="hidden" value="<?php echo $image_identifier; ?>" name="image_identifier"/>
        <input type="hidden" value="" name="previous_image"/>
        <input type="hidden" value="<?php echo $already_selected; ?>" name="already_selected"/>
        <?php
            if ($image_identifier == "") {
                echo '
                <br />
                <br />
                <span class="sub-heading">Image Selector</span>
                <br />
                <br />
                <input type="radio" name="image_src" id="outside" value="outside" ' . $outside_checked . '>Use an URL
                <br />
                <input type="radio" name="image_src" id="previous" value="previous" ' . $previous_checked . '>Use a Previously Uploaded Image
                <br />
                <input type="radio" name="image_src" id="upload" value="upload" ' . $upload_checked . '>Upload a New Image
                <br />
                <br />';
                $image_array = glob($_SERVER['DOCUMENT_ROOT'] . "/nodes/images/" . "*.{jpg,png,gif,jpeg}", GLOB_BRACE);
                foreach($image_array as $image) {
                $path_to_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $image);
                echo '<img src="' . $path_to_image . '" class="add-upload-thumbnail"/>';
                }
                echo '<input type="text" name="url_box" placeholder="URL" />
                <input type="file" name="upload_input" />
                <br />
                <br />';
            }
        ?>
        <br />
        <span class="sub-heading">Image Attributes</span>
        <br />
        <br />
        <div id="attribute-container">
          <?php
            if (!empty($attribute_names_value)) {
              foreach($attribute_names_value as $index=>$attribute_name) {
                echo '
                  <div id="attribute-form-' . $index . '">
                    <input name="attribute_names[]" class="buttoned-input" type="text" placeholder="Attribute Name" value="' . $attribute_name . '"/>
                    <div onclick="removeAttributeForm(' . $index . ')" class="remove-button">
                      X
                    </div>
                    <textarea name="attribute_contents[]" class="short-textarea" placeholder="Attribute Content">' . $attribute_contents_value[$index] . '</textarea>
                    <br />
                    <br />
                  </div>';
              }
            }
          ?>
        </div>
        <input type="button" name="add_attribute" value="Add Attribute" />
        <br />
        <br >
        <br />
        <?php if ($error != "") { echo "<font color='red'>$error</font><br /><br />"; } ?>
        <input type="submit" name="submit" value="<?php echo $submit_button; ?>"/>
      </form>
      <form action="./">
        <input type="submit" value="Back To Photo Album Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/add.js"></script>
  </body>
</html>