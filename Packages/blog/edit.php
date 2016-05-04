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
    $post_query = "SELECT * FROM `$blog_table` WHERE id=$identifier";
    $post_result = $connection->query($post_query);
    $post = $post_result->fetch_assoc();
    $comments_value = $post["comments"];
    $original_comments_array = explode(">", $comments_value);
    $comments_array = [];
    foreach($original_comments_array as $comment) {
      $individual_comment_array = explode(":", $comment);
      $comments_array[] = $individual_comment_array;
    }
  }
  if (isset($_POST["submit_post"])) {
    $title_value = $_POST["title"];
    $content_value = $_POST["content"];
    $description_value = $_POST["description"];
    $comment_deletions_array = $_POST["comment_deletions"];
    if (str_replace(" ", "", $title_value) != "") {
      if (str_replace(" ", "", $content_value) != "") {
        $row_exists_query = mysqli_query($connection, "SELECT * FROM `$blog_table` WHERE `title`='$title_value' AND `id`!='$identifier'");
        if (!mysqli_num_rows($row_exists_query) > 0) {
          if ($is_edit === true) {
            if (empty($comment_deletions_array)) {
              $update_comments_string = "";
            } else {
              foreach($comment_deletions_array as $deletion) {
                unset($original_comments_array[$deletion]);
              }
              $update_comments_string = implode(">", $original_comments_array);
            }
            $update_query = "UPDATE `$blog_table` SET `title`='$title_value', `description`='$description_value', `content`='$content_value', `comments`='$update_comments_string' WHERE `id`='$identifier'";
            $connection->query($update_query);
            header('Location: .');
          } elseif ($is_edit === false) {
            $current_date = date('Y-m-d');
            $add_query = "INSERT INTO $blog_table (`date`, `title`, `description`, `content`, `comments`) VALUES ('$current_date', '$title_value', '$description_value', '$content_value', '')";
            $connection->query($add_query);
            header('Location: .');
          }
        } else {
          $error = "A post with that tile already exists.";
        }
      } else {
        $error = "You did not enter any content.";
      }
    } else {
      $error = "Please enter a title.";
    }
  } elseif ($is_edit === true) {
    $title_value = $post["title"];
    $content_value = $post["content"];
    $description_value = $post["description"];
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
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="css/blog.css">
  </head>
  <body onload="<?php if ($image_upload_error != "") { echo "uploadAlert('" . $image_upload_error . "');"; } ?>">
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
      <span class="heading"><?php if ($is_edit === true) { echo "Edit Blog Post"; } elseif ($is_edit === false) { echo "Add New Blog Post"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" name="title" placeholder="Title" value="<?php echo $title_value; ?>"/>
        <textarea name="description" id="description" placeholder="Description"><?php echo $description_value; ?></textarea>
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
        <textarea name="content" id="content" placeholder="Post Content"><?php echo $content_value; ?></textarea>
        <?php
          if ($comments_value != "") {
            echo '<br /><br /><div class="heading">Comments</div>';
            foreach($comments_array as $index=>$comment) {
              if ($comment_deletions_array !== null) {
                if (in_array($index, $comment_deletions_array)) {
                  $checked = "checked";
                } else {
                  $checked = "";
                }
              }
              echo '<div class="content-border comment-margin"><span class="comment-name">';
                echo $comment[0];
                echo '</span><br /><p class="comment-content">';
                echo $comment[1];
                echo '<br /></p>';
                echo $comment[2];
                echo "<br /><br />";
                echo $comment[3];
                echo '<br /><br /><input name="comment_deletions[]" type="checkbox" value="' . $index . '" ' . $checked . '/> Delete This Comment';
              echo '</div>';
            }
            echo '<br />';
          }
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="submit_post" value="<?php if ($is_edit === true) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add Post"; } ?>" />
      </form>
      <form action=".">
        <input type="submit" value="Back To Post Selection">
      </form>
    </div>
    <script src="javascript/jquery.js" type="text/javascript"></script>
    <script src="javascript/content_bar.js" type="text/javascript"></script>
  </body>
</html>
