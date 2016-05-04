<?php
include("../../../../functions/login_check.php");
include("../../../../config/main_config.php");
include("../../config/config.php");
$users_query = "SELECT * FROM `logins`";
$users_result = $connection->query($users_query);
if (isset($_GET["delete"])) {
  $identifier = $_GET["which"];
  $delete_user_query = "DELETE FROM `logins` WHERE id=$identifier";
  $connection->query($delete_user_query);
  header('Location: logins.php');
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/settings.css">
  </head>
  <body>
    <div id="main-container">
      <?php
        if (mysqli_num_rows($users_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Username
              </th>
              <th>
                Name
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($user = mysqli_fetch_array($users_result)) {
            echo "<tr>
                    <td>";
                echo $user["username"];
              echo "</td>
                    <td>";
                    echo $user["name"];
              echo "</td>
                    <td>";
                echo "<a href='view.php?which=" . $user["id"] . "'>View</a><br />";
                echo "<a href='edit.php?edit=true&which=" . $user["id"] . "'>Edit</a><br />";
                echo "<a onclick='return confirm(\"Are you sure you want to delete this user?\");' href='?delete=true&which=" . $user["id"] . "'>Delete</a>";
              echo "</td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no users to display.</div>
          ";
        }
      ?>
      <form action="./edit.php">
        <input type="submit" value="Create User">
      </form>
      <br />
      <br />
      <form action="../../">
        <input type="submit" value="Back to Ignition Settings">
      </form>
    </div>
  </body>
</html>