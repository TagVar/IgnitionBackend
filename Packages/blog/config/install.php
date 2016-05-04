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
              date DATE,
              title TEXT,
              description TEXT,
              content TEXT,
              comments TEXT
            )';
            if ($connection->query($install_query) === TRUE) {
              $config_file = "config.php";
              file_put_contents($config_file, str_replace('$blog_table = ""', '$blog_table = "' . $requested_table_name . '"', file_get_contents($config_file)));
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
              $node_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/nodes/blog.php", "w");
              $node_file_contents = '<?php
include($_SERVER["DOCUMENT_ROOT"] . "/nodes/config/config.php");
function render_entries($template, $params = ["date-format" => "M j, Y"]) {
  global $node_connection;
  $list_entry_data_query = "SELECT `id`, `title`, `description`, `date`, `comments` FROM `' . $requested_table_name . '`";
  $list_entry_data_result = $node_connection->query($list_entry_data_query);
  $entry_data = [];
  while ($row = $list_entry_data_result->fetch_array(MYSQL_ASSOC)) {
    $comments = $row["comments"];
    $comments_array = explode(">", $comments);
    $comments_count = count($comments_array);
    if (($comments_count == 1) && ($comments_array[0] == "")) {
      $comments_count--;
    }
    $entry_list[] = [
      "identifier" => $row["id"],
      "title" => $row["title"],
      "description" => $row["description"],
      "date" => $row["date"],
      "comments_count" => $comments_count
    ];
  }
  $final_entries_render = "";
  foreach($entry_list as $entry_data) {
    $entry = str_replace("{{ id }}", $entry_data["identifier"], $template);
    $entry = str_replace("{{ title }}", $entry_data["title"], $entry);
    $entry = str_replace("{{ description }}", $entry_data["description"], $entry);
    $entry = str_replace("{{ date }}", date($params["date-format"], strtotime($entry_data["date"])), $entry);
    $entry = str_replace("{{ comment-count }}", $entry_data["comments_count"], $entry);
    $final_entries_render = $final_entries_render . $entry;
  }
  echo $final_entries_render;
}
function render_entry($template, $identifier, $params = ["reference" => "id", "date-format" => "M j, Y"]) {
  global $node_connection;
  $list_entry_data_query = "SELECT `id`, `title`, `description`, `content`, `date`, `comments` FROM `' . $requested_table_name . '` WHERE `" . $reference . "` = \'" . $identifier . "\'";
  $list_entry_data_result = mysqli_query($node_connection, $list_entry_data_query);
  $entry_data = mysqli_fetch_assoc($list_entry_data_result);
  $comments = $entry_data["comments"];
  $comments_array = explode(">", $comments);
  $comments_count = count($comments_array);
  if (($comments_count == 1) && ($comments_array[0] == "")) {
    $comments_count--;
  }
  $entry = str_replace("{{ id }}", $entry_data["id"], $template);
  $entry = str_replace("{{ title }}", $entry_data["title"], $entry);
  $entry = str_replace("{{ description }}", $entry_data["description"], $entry);
  $entry = str_replace("{{ content }}", $entry_data["content"], $entry);
  $entry = str_replace("{{ date }}", date($params["date-format"], strtotime($entry_data["date"])), $entry);
  $entry = str_replace("{{ comment-count }}", $comments_count, $entry);
  echo $entry;
}
function render_comments($template, $identifier, $params = ["reference" => "id", "date-format" => "M j, Y"]) {
  global $node_connection;
  $comment_data_query = "SELECT `comments` FROM `' . $requested_table_name . '` WHERE `" . $params["reference"] . "` = \'" . $identifier . "\'";
  $comment_data_result = mysqli_query($node_connection, $comment_data_query);
  $comment_data = mysqli_fetch_assoc($comment_data_result);
  $comments = $comment_data["comments"];
  $comments_array = explode(">", $comments);
  $comments_count = count($comments_array);
  if (($comments_count == 1) && ($comments_array[0] == "")) {
    $comments_count--;
  }
  $final_render = "";
  foreach($comments_array as $comment) {
    $comment_value = explode(":", $comment);
    $comment_render = str_replace("{{ name }}", $comment_value[0], $template);
    $comment_render = str_replace("{{ content }}", $comment_value[1], $comment_render);
    $comment_render = str_replace("{{ email }}", $comment_value[2], $comment_render);
    $comment_render = str_replace("{{ date }}", date($params["date-format"], strtotime($comment_value[3])), $comment_render);
    $comment_render = str_replace("{{ comment-count }}", $comments_count, $comment_render);
    $final_render = $final_render . $comment_render;
  }
  echo $final_render;
}
function add_comment($information, $identifier, $params = ["reference" => "id"]) {
  global $node_connection;
  $comment_data_query = "SELECT `comments` FROM `' . $requested_table_name . '` WHERE `" . $params["reference"] . "` = \'" . $identifier . "\'";
  $comment_data_result = mysqli_query($node_connection, $comment_data_query);
  $comment_data = mysqli_fetch_assoc($comment_data_result);
  $comments = $comment_data["comments"];
  $comments_array = explode(">", $comments);
  $comments_count = count($comments_array);
  if (($comments_count == 1) && ($comments_array[0] == "")) {
    $comments_count--;
  }
  $input_data = [
    $information["name"],
    $information["content"],
    $information["email"],
    $date = date("Y-m-d")
  ];
  $insert_data = [];
  foreach($input_data as $value) {
    $comment_value = str_replace(">",  "&#x0003E;", $value);
    $comment_value = str_replace(":",  "&#x0003A;", $comment_value);
    $insert_data[] = $comment_value;
  }
  $complete_comment = implode(":", $insert_data);
  if ($comments_count > 0) {
    $complete_comment = ">" . $complete_comment;
  }
  $insert_string = $comments . $complete_comment;
  $insert_query = "UPDATE `' . $requested_table_name . '` SET `comments`=\'" . $insert_string . "\' WHERE `" . $params["reference"] . "`=\'" . $identifier . "\'";
  if($node_connection->query($insert_query)) {
    return true;
  } else {
    return false;
  }
}
function search_posts($template, $identifier, $params = ["reference" => "id", "date-format" => "M j, Y"]) {
  global $node_connection;
  if ($params["reference"] === "date") {
    $identifier = date("Y-m-d", strtotime($identifier));
  }
  if ($identifier == "all") {
    $list_entry_data_query = "SELECT `id`, `title`, `date` FROM `' . $requested_table_name . '`";
  } else {
    $list_entry_data_query = "SELECT `id`, `title`, `date` FROM `' . $requested_table_name . '` WHERE `" . $params["reference"] . "` = \'" . $identifier . "\'";
  }
  $list_entry_data_result = $node_connection->query($list_entry_data_query);
  $entry_data = [];
  while ($row = $list_entry_data_result->fetch_array(MYSQL_ASSOC)) {
    $entry_list[] = [
      "identifier" => $row["id"],
      "title" => $row["title"],
      "date" => $row["date"]
    ];
  }
  $final_render = "";
  if (!empty($entry_list)) {
    foreach($entry_list as $entry) {
      $entry_render = str_replace("{{ title }}", $entry["title"], $template);
      $entry_render = str_replace("{{ identifier }}", $entry["identifier"], $entry_render);
      $entry_render = str_replace("{{ date }}", date($params["date-format"], strtotime($entry["date"])), $entry_render);
      $final_render = $final_render . $entry_render;
    }
  }
  if ($template == "search") {
    if ($final_render === "") {
      return false;
    } else {
      return true;
    }
  } else {
    echo $final_render;
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
    <link rel="stylesheet" type="text/css" href="../../css/blog.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Install Package: Blog</span>
      <br />
      <form method="post" action="">
        <p>
          This page will install the Blog Administration package. The name you enter into the field below will reflect the name of the table housing all of this package's data within your database.
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
