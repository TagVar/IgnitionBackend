<?php
  include("../../functions/login_check.php");
  include("../../config/main_config.php");
  include("config/config.php");
  if (isset($_POST["send_email"])) {
    $from_value = $_POST["from"];
    $subject_value = $_POST["subject"];
    $bcc_value = $_POST["bcc"];
    $content_value = $_POST["content"];
    if (str_replace(" ", "", $subject_value) != "") {
      if (str_replace(" ", "", $from_value) != "") {
        if (str_replace(" ", "", $content_value) != "") {
          $bcc_array = explode(",", $bcc_value);
          $bcc_array = array_map('trim', $bcc_array);
          $bcc_data = [
            "count" => count($bcc_array),
            "valid" => 0
          ];
          $bcc_data["counts"] = array_count_values(array_map('strtolower', $bcc_array));
          $bcc_data["counts"] = array_change_key_case($bcc_data["counts"], CASE_LOWER);
          $bcc_data["duplicates"] = [];
          foreach($bcc_array as $bcc_email) {
            if (filter_var($bcc_email, FILTER_VALIDATE_EMAIL) === false) {
              $bcc_data["invalid"][] = $bcc_email;
            } else {
              $bcc_data["valid"]++;
            }
            if ($bcc_data["counts"][strtolower($bcc_email)] > 1) {
              if (!in_array(strtolower($bcc_email), array_map('strtolower', $bcc_data["duplicates"]))) {
                $bcc_data["duplicates"][] = $bcc_email;
              }
            }
          }
          if (empty($bcc_data["duplicates"])) {
            if (($bcc_data["valid"] == $bcc_data["count"]) || (str_replace(" ", "", $bcc_email) == "")) {
              $mail_body = "
              <html>
                <body>
                  " . $content_value . "
                </body>
              </html>
              ";
              $recipients_query = "SELECT `recipients` FROM `" . $newsletter_table . "` WHERE `id` = '" . $_GET["which"] . "'";
              $recipients_result = mysqli_query($connection, $recipients_query);
              $recipients_data = mysqli_fetch_assoc($recipients_result);
              $spaced_recipients = $recipients_data["recipients"];
              $recipients = str_replace(" ", "", $spaced_recipients);
              $headers = "MIME-Version: 1.0\r\n";
              $headers .= "Content-type:text/html;charset=UTF-8\r\n";
              $headers .= 'From: ' . $from_value . "\r\n";
              $headers .= 'Bcc: ' . $recipients . "\r\n";
              $headers .= 'Cc: ' . str_replace(" ", "", $bcc_value) . "\r\n";
              if (mail(null, $subject_value, $mail_body, $headers)) {
                header("Location: index.php?sent=true");
              } else {
                if ($recipients == "") {
                  $error = "This group does not have any recipients. Please add newsletter recipient(s) and try again.";
                } else {
                  $error = "There was an error sending the newsletter. Please try again.";
                }
              }
            } else {
              if (count($bcc_data["invalid"]) == 1) {
                $error = "The following email was not valid: ";
              } else {
                $error = "The following emails were not valid: ";
              }
              foreach($bcc_data["invalid"] as $failed_email) {
                $error = $error . $failed_email . ", ";
              }
              $error = chop($error, ", ") . ".";
            }
          } else {
            if (count($bcc_data["duplicates"]) == 1) {
              $error = "The following email was found in the list more than once: ";
            } else {
              $error = "The following emails were found in the list more than once: ";
            }
            foreach ($bcc_data["duplicates"] as $duplicate) {
              $error = $error . $duplicate . ", ";
            }
            $error = chop($error, ", ") . ".";
          }
        } else {
          $error = "You did not enter any content.";
        }
      } else {
        $error = 'You must specify a subject.';
      }
    } else {
      $error = 'The "From" field is required.';
    }
  }
  if (isset($_POST["add_image"])) {
  	$from_value = $_POST["image_post_from"];
    $subject_value = $_POST["image_post_subject"];
    $bcc_value = $_POST["image_post_bcc"];
    $content_value = $_POST["image_post_content"];
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
    <link rel="stylesheet" type="text/css" href="css/newsletter.css">
  </head>
  <body onload="<?php if ($image_upload_error != "") { echo "uploadAlert('" . $image_upload_error . "');"; } ?>">
    <div id="image-alert">
      <div class="heading">Upload an Image</div>
      <br />
      <form name="image_form" action="" enctype="multipart/form-data" method="post">
        <input type="hidden" name="image_post_bcc" />
        <input type="hidden" name="image_post_from" />
        <input type="hidden" name="image_post_subject" />
        <input type="hidden" name="image_post_content" />
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
      <span class="heading">Send Newsletter</span>
      <br />
      <form method="post" action="">
        <input type="text" name="subject" placeholder="Subject" value="<?php echo $subject_value; ?>"/>
        <input type="text" name="from" placeholder="From" value="<?php echo $from_value; ?>"/>
        <input type="text" name="bcc" placeholder="CC (Sperate Emails By Comma)" value="<?php echo $bcc_value; ?>"/>
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
        <textarea name="content" id="content" placeholder="Email Content"><?php echo $content_value; ?></textarea>
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="send_email" value="Send" />
      </form>
      <form action=".">
        <input type="submit" value="Back To Group Selection">
      </form>
    </div>
    <script src="javascript/jquery.js" type="text/javascript"></script>
    <script src="javascript/content_bar.js" type="text/javascript"></script>
  </body>
</html>