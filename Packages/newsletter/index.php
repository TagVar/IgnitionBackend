<?php
include("../../functions/login_check.php");
include("../../config/main_config.php");
include("config/config.php");
$groups_query = "SELECT * FROM `$newsletter_table`";
$groups_result = $connection->query($groups_query);
if (isset($_GET["delete"])) {
  $identifier = $_GET["which"];
  $delete_group_query = "DELETE FROM `$newsletter_table` WHERE id=$identifier";
  $connection->query($delete_group_query);
  header('Location: index.php');
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/newsletter.css">
  </head>
  <body>
    <div id="main-container">
      <?php
        if (mysqli_num_rows($groups_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Group
              </th>
              <th>
                Recipients
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($group = mysqli_fetch_array($groups_result)) {
            echo "<tr>
                    <td>";
                echo $group["group"];
              echo "</td>
                    <td>";
                echo $group["recipients"];
              echo "</td>
                    <td>";
                echo "<a href='send.php?which=" . $group["id"] . "'>Send Newsletter</a><br />";
                echo "<a href='edit.php?edit=true&which=" . $group["id"] . "'>Edit</a><br />";
                echo "<a onclick='return confirm(\"Are you sure you want to delete this group?\");' href='?delete=true&which=" . $group["id"] . "'>Delete</a>";
              echo "</td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no groups to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Group">
      </form>
      <form action="../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
  </body>
</html>
