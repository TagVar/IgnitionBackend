<?php
  include("../../functions/login_check.php");
  include("../../config/main_config.php");
  include("config/config.php");
  $posts_query = "SELECT * FROM `$blog_table`";
  $posts_result = $connection->query($posts_query);

  if (isset($_GET["delete"])) {
    $identifier = $_GET["which"];
    $delete_entry_query = "DELETE FROM `$blog_table` WHERE id=$identifier";
    $connection->query($delete_entry_query);
    header('Location: index.php');
  }
 ?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="css/blog.css">
  </head>
  <body>
    <div id="main-container">
      <?php
        if (mysqli_num_rows($posts_result) != 0) {
          echo "<table>
                  <tr>
                    <th>
                      Entry Title
                    </th>
                    <th>
                      Date Added
                    </th>
                    <th>
                      Actions
                    </th>
                  </tr>
          ";
            while ($entry = mysqli_fetch_array($posts_result)) {
              echo "<tr>";
                echo "<td>";
                  echo $entry["title"];
                echo "</td>";
                echo "<td>";
                  echo date("F d, Y", strtotime($entry["date"]));
                echo "</td>";
                echo "<td>";
                  echo "<a href='edit.php?edit=true&which=" . $entry["id"] . "'>Edit</a><br />";
                  echo "<a onclick='return confirm(\"Are you sure you want to delete this post?\");' href='?delete=true&which=" . $entry["id"] . "'>Delete</a>";
                echo "</td>";
              echo "</tr>";
            }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no blog posts to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Post">
      </form>
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
