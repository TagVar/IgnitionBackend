<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$data_query = "SELECT name, id FROM `$cms_table` WHERE type='data'";
$data_result = $connection->query($data_query);
$rendered_datas = [];
if (isset($_GET["delete"])) {
    $delete_data_query = "DELETE FROM `$cms_table` WHERE id='" . $_GET["which"] . "'";
    $connection->query($delete_data_query);
    header("location: ./");
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/cms.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Data Points</span>
      <br />
      <br />
      <?php
        if (mysqli_num_rows($data_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Data Point
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($data = mysqli_fetch_array($data_result)) {
            if (!in_array($data["name"], $rendered_datas)) {
                $rendered_datas[] = $data["name"];
                echo "<tr>
                        <td>";
                    echo $data["name"];
                echo "</td>
                        <td>";
                    echo "<a href='edit.php?which=" . $data["id"] . "'>Edit</a><br />";
                    echo "<a onclick='return confirm(\"Are you sure you want to delete this data?\");' href='?delete=true&which=" . $data["id"] . "'>Delete</a>";
                echo "</td>
                    </tr>";
              }
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no data points to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Data Point">
      </form>
      <form action="../">
        <input type="submit" value="Back To CMS Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>