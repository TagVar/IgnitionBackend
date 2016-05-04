<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  if (isset($_POST["install_submit"])) {
    $requested_table_name = $_POST["requested_table_name"];
    $table_exists_query = "SELECT 1 FROM `$requested_table_name` LIMIT 1";
    $table_exists_result = $connection->query($table_exists_query);
    if($table_exists_result !== false) {
      $error = "The table name you specified already exists.";
    }
    else {
      if (strlen($requested_table_name) > 50) {
        $error = "The table name you specified is longer than 50 characters";
      } else {
        if (str_replace(" ", "", $requested_table_name) == "") {
          $error = "You must provide a table name.";
        } else {
          if (!preg_match('/^[a-zA-Z0-9_]+$/', $requested_table_name)) {
            $error = "The table name you specified contained illegal characters";
          } else {
            $install_query = 'CREATE TABLE `' . $requested_table_name . '` (
              id INT(11) AUTO_INCREMENT PRIMARY KEY,
              type TEXT,
              name TEXT,
              data TEXT
            )';
            if ($connection->query($install_query) === TRUE) {
              $config_file = "config.php";
              file_put_contents($config_file, str_replace('$cms_table = ""', '$cms_table = "' . $requested_table_name . '"', file_get_contents($config_file)));
              if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes")) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes");
              }
              if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes/images")) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/images");
              }
              if (!file_exists($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php")) {
                if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/nodes/config")) {
                  mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/config");
                }
                $node_config_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php", "w");
                $node_config_file_contents = '<?php
  $node_sql_host = "' . $sql_host . '";
  $node_sql_username = "' . $sql_username . '";
  $node_sql_password = "' . $sql_password . '";
  $node_sql_database = "' . $sql_database . '";
  $node_connection = new mysqli($node_sql_host, $node_sql_username, $node_sql_password, $node_sql_database);
?>';
                fwrite($node_config_file, $node_config_file_contents);
                fclose($node_config_file);
              }
              $node_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/nodes/cms.php", "w");
              $node_file_contents = '<?php
include($_SERVER["DOCUMENT_ROOT"] . "/nodes/config/config.php");
$cms_table = "' . $requested_table_name . '";
function get_data($datapoint_name) {
  global $node_connection;
  global $cms_table;
  $data_query = "SELECT data FROM `$cms_table` WHERE name=\'$datapoint_name\' AND type=\'data\'";
  $data_result = $node_connection->query($data_query);
  if (mysqli_num_rows($data_result) != 0) {
    $data_result_array = $data_result->fetch_assoc();
    return $data_result_array["data"];
  } else {
    return false;
  }
}
function render_template($template_name) {
  global $node_connection;
  global $cms_table;
  $data_query = "SELECT data FROM `$cms_table` WHERE name=\'$template_name\' AND type=\'template\'";
  $data_result = $node_connection->query($data_query);
  if (mysqli_num_rows($data_result) != 0) {
    $data_result_array = $data_result->fetch_assoc();
    echo $data_result_array["data"];
  } else {
    return false;
  }
}
function render_page($page_name) {
  global $node_connection;
  global $cms_table;
  $data_query = "SELECT data FROM `$cms_table` WHERE name=\'$page_name\' AND type=\'page\'";
  $data_result = $node_connection->query($data_query);
  if (mysqli_num_rows($data_result) != 0) {
    $data_result_array = $data_result->fetch_assoc();
    $page = $data_result_array["data"];
    preg_match_all(\'#\{\{ (.*?) \}\}#s\', $page, $matches, PREG_PATTERN_ORDER);
    $template_names = array_unique($matches[1]);
    $matches = array_unique($matches[0]);
    foreach($matches as $key=>$match) {
        $template_query = "SELECT data FROM `$cms_table` WHERE name=\'" . $template_names[$key] . "\' AND type=\'template\'";
        $template_result = $node_connection->query($template_query);
        if (mysqli_num_rows($data_result) != 0) {
            $template_result_array = $template_result->fetch_assoc();
            $page = str_replace($match, $template_result_array["data"], $page);
        }
    }
    echo $page;
  } else {
    return false;
  }
}
function render_album($album_name, $template, $params = ["repeat-attributes" => false, "attribute-template" => ""]) {
  global $node_connection;
  global $cms_table;
  $data_query = "SELECT data FROM `$cms_table` WHERE name=\'$album_name\' AND type=\'album\'";
  $data_result = $node_connection->query($data_query);
  if (mysqli_num_rows($data_result) != 0) {
    $final_render = "";
    $data_result_array = $data_result->fetch_assoc();
    $data_string = $data_result_array["data"];
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
    foreach($image_data as $key=>$single_image) {
        $single_image_template = str_replace("{{ image-source }}", "/nodes/images/" . $single_image[0], $template);
        unset($single_image[0]);
        foreach($single_image as $attribute_key=>$attribute) {
            $single_image_template = str_replace("{{ " . $attribute[0] . " }}", $attribute[1], $single_image_template);
        }
        if ($params["repeat-attributes"] === true) {
            $repeated_attribute_string = "";
            foreach($single_image as $attribute_key=>$attribute) {
                $repeated_attribute_string = $repeated_attribute_string . str_replace("{{ attribute }}", $attribute[1], $params["attribute-template"]);
            }
            $single_image_template = str_replace("{{ repeated-attributes }}", $repeated_attribute_string, $single_image_template);
        }
        $final_render = $final_render . $single_image_template;
    }
    echo $final_render;
  } else {
    return false;
  }
}
?>';
              fwrite($node_file, $node_file_contents);
              fclose($node_file);
              header("location: ../../../../landing.php");
            } else {
              $error = "There was a problem installing this package. Please try again.";
            }
            $connection->query($install_query);
          }
        }
      }
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/cms.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Install Package: Content Management System</span>
      <br />
      <form method="post" action="">
        <p>
          This page will install the Content Management System package. The name you enter into the field below will reflect the name of the table housing all of this package's data within your database.
          <br />
          <br />
          This action is irreversible. You cannot change the table's name after installation. Please select your table name carefully.
          <br />
          <br />
          The table name you select may only contain letters, numbers, underscores, and may not be longer than 50 characters in total length.
        </p>
        <input type="text" name="requested_table_name" placeholder="Desired Table Name"/>
        <br />
        <?php if ($error != null) { echo "<br /><font color='red'>$error</font><br />"; } ?>
        <br />
        <input type="submit" name="install_submit" value="Install" onclick='return confirm("This action is irreversible. Are you sure that you are satisfied with your entry?");'>
      </form>
      <form action="../../../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
