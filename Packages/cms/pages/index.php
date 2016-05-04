<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$page_query = "SELECT name, id FROM `$cms_table` WHERE type='page'";
$page_result = $connection->query($page_query);
$rendered_pages = [];
if (isset($_GET["delete"])) {
    $delete_page_query = "DELETE FROM `$cms_table` WHERE id='" . $_GET["which"] . "'";
    $connection->query($delete_page_query);
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
      <span class="heading">Pages</span>
      <br />
      <br />
      <?php
        if (mysqli_num_rows($page_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Page
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($page = mysqli_fetch_array($page_result)) {
            if (!in_array($page["name"], $rendered_pages)) {
                $rendered_pages[] = $page["name"];
                echo "<tr>
                        <td>";
                    echo $page["name"];
                echo "</td>
                        <td>";
                    echo "<a href='edit.php?which=" . $page["id"] . "'>Edit</a><br />";
                    echo "<a onclick='return confirm(\"Are you sure you want to delete this page?\");' href='?delete=true&which=" . $page["id"] . "'>Delete</a>";
                echo "</td>
                    </tr>";
              }
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no pages to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Page">
      </form>
      <form action="../">
        <input type="submit" value="Back To CMS Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>