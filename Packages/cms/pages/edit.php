<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$error = "";
if (isset($_GET["which"])) {
    $identifier = $_GET["which"];
    $is_edit = true;
    $heading = "Edit Page";
    $submit = "Save Page";
} elseif ($_POST["which"]) {
    $identifier = $_POST["which"];
    $is_edit = true;
    $heading = "Edit Page";
    $submit = "Save Page";
} else {
    $is_edit = false;
    $heading = "Add Page";
    $submit = "Add Page";
}
if ((isset($identifier)) && (!isset($_POST["submit"]))) {
    $page_query = "SELECT name, data FROM `$cms_table` WHERE id='$identifier'";
    $page_result = $connection->query($page_query);
    $page_data = $page_result->fetch_assoc();
    $name = $page_data["name"];
    $html = $page_data["data"];
} elseif (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $html = $_POST["html"];
    if (str_replace(" ", "", $name) == "") {
        $error = "You did not provide a name for this page.";
    } else {
        if ($is_edit) {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='page' AND id <> '$identifier'";
        } else {
          $check_name_query = "SELECT * FROM `$cms_table` WHERE name='$name' AND type='page'";
        } 
        $check_name_result = $connection->query($check_name_query);
        if (mysqli_num_rows($check_name_result) == 0) {
            $validated = false;
            if ($is_edit) {
                $delete_page_query = "DELETE FROM `$cms_table` WHERE id='$identifier'";
                if ($connection->query($delete_page_query)) {
                    $validated = true;
                }
            } else {
                $validated = true;
            }
            if ($validated) {
              $add_page_query = "INSERT INTO `$cms_table` (type, name, data) VALUES ('page', '$name', '" . str_replace("'", "&#x00027;", $html) . "')";
              if ($connection->query($add_page_query)) {
                header("location: ./");
              } else {
                $error = "An error occurred. Please try again.";
              }
            } else {
                $error = "An error occured. Please try again.";
            }
        } else {
            $error = "A page with this name already exists.";
        }
    }
}
if (isset($_POST["add_image"])) {
    $title_value = $_POST["image_post_title"];
    $content_value = $_POST["image_post_content"];
    $description_value = $_POST["image_post_description"];
    $identifier = $_POST["image_post_identifier"];
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
    if ($current_file_size <= 20000000) {
        if (in_array($image_file_type, $image_accepted_filetypes)) {
        $new_file_name = uniqid('', true) . "." . $image_file_type;
        if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
            $new_file_name = uniqid('', true) . $image_file_type;
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
            $image_upload_error = "An error occured. Please try again.";
            $reshow = "true";
        } else {
            if (move_uploaded_file($current_file_location, $_SERVER["DOCUMENT_ROOT"] . "/nodes/images/" . $new_file_name)) {
            $add_to_textbox = $new_file_name;
            } else {
            $image_upload_error = "An error occured. Please try again.";
            $reshow = "true";
            }
        }
        } else {
        $image_upload_error = "Only PNG, JPG, JPEG, and GIF file extensions are accepted.";
        $reshow = "true";
        }
    } else {
        $image_upload_error = "Images may not exceed 20 megabytes in size.";
        $reshow = "true";
    }
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/cms.css">
  </head>
  <body>
   <div id="image-alert">
      <div class="heading">Upload an Image</div>
      <br />
      <form name="image_form" action="" enctype="multipart/form-data" method="post">
        <input type="hidden" name="image_post_title" />
        <input type="hidden" name="image_post_content" />
        <input type="hidden" name="image_post_description" />
        <input type="hidden" name="image_post_identifier" value="<?php echo $identifier; ?>" />
        <input type="hidden" name="image_post_name" value="<?php echo $add_to_textbox; ?>"/>
        <input type="hidden" name="image_reshow" value="<?php echo $reshow; ?>" />
        <input type="radio" name="image_src" value="outside" checked>Use an URL
        <br />
        <input type="radio" name="image_src" value="previous">Use a Previously Uploaded Image
        <br />
        <input type="radio" name="image_src" id="upload" value="upload">Upload a New Image
        <br />
        <br />
        <?php
        $image_array = glob($_SERVER['DOCUMENT_ROOT'] . "/nodes/images/" . "*.{jpg,png,gif,jpeg}", GLOB_BRACE);
        foreach($image_array as $image) {
          $path_to_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $image);
          echo '<img src="' . $path_to_image . '" class="upload-thumbnail"/>';
        }
        ?>
        <input type="text" name="url_box" placeholder="URL" />
        <input type="file" name="upload_input" />
        <input type="submit" value="Add Image" name="add_image" />
      </form>
      <button name="cancel_image_upload">Cancel</button>
    </div>
    <div id="main-container" class="content-border">
      <span class="heading"><?php echo $heading; ?></span>
      <br />
      <form action="" method="post">
        <input type="hidden" value="<?php echo $identifier; ?>" name="which"/>
        <input type="text" name="name" placeholder="Page Name" value="<?php echo $name; ?>"/>
        <table id="content-styling-bar">
          <tr>
            <td>Bold</td>
            <td>Italic</td>
            <td>Underline</td>
            <td>Main Heading</td>
            <td>Sub Heading</td>
            <td>Link</td>
            <td>Quote</td>
            <td>Unordered List</td>
            <td>Ordered List</td>
            <td>Image</td>
          </tr>
        </table>
        <textarea name="html" id="content" placeholder="HTML"><?php echo $html; ?></textarea>
        <?php if ($error != "") { echo "<br /><br /><font color='red'>$error</font><br /><br />"; } ?>
        <input type="submit" name="submit" value="<?php echo $submit; ?>"/>
      </form>
      <form action="./">
        <input type="submit" value="Back To CMS Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/content_bar.js"></script>
  </body>
</html>
